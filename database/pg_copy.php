<?php

$destinoPermitido = ['postgres', 'inventario-postgres'];
if (getenv('DB_CONNECTION') !== 'pgsql' || !in_array(getenv('DB_HOST'), $destinoPermitido, true)) {
    fwrite(STDERR, "rechazado: el destino no es un PostgreSQL de Inventarios\n");
    exit(1);
}

function envfile(string $path): array
{
    $vars = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
        if ($line === '' || str_starts_with(ltrim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $vars[$key] = $value;
    }
    return $vars;
}

$secret = envfile('/run/secrets/sqlserver.env');
$src = new PDO(
    "sqlsrv:Server={$secret['DB_HOST']},{$secret['DB_PORT']};Database={$secret['DB_DATABASE']};Encrypt=yes;TrustServerCertificate=true;LoginTimeout=20",
    $secret['DB_USERNAME'],
    $secret['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$dst = new PDO(
    'pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$srcTables = $src->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='dbo' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
$dst->exec('CREATE EXTENSION IF NOT EXISTS unaccent');
$pgTables = $dst->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);

$missing = array_values(array_diff($srcTables, $pgTables));
if ($missing) {
    fwrite(STDERR, 'faltan tablas en PostgreSQL: '.implode(',', $missing)."\n");
    exit(1);
}

$timestamps = $dst->query("SELECT table_name, column_name FROM information_schema.columns WHERE table_schema='public' AND data_type='timestamp without time zone'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($timestamps as $column) {
    $table = '"'.str_replace('"', '""', $column['table_name']).'"';
    $name = '"'.str_replace('"', '""', $column['column_name']).'"';
    $dst->exec("ALTER TABLE {$table} ALTER COLUMN {$name} TYPE timestamp(3)");
}

$quoted = array_map(fn ($t) => '"'.str_replace('"', '""', $t).'"', $srcTables);
$dst->exec('TRUNCATE TABLE '.implode(', ', $quoted).' RESTART IDENTITY CASCADE');

$dst->exec("SET session_replication_role = 'replica'");
$copied = 0;

foreach ($srcTables as $table) {
    $meta = $src->query("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='dbo' AND TABLE_NAME=".$src->quote($table)." ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
    $types = [];
    foreach ($meta as $col) {
        $types[$col['COLUMN_NAME']] = $col['DATA_TYPE'];
    }
    $columns = array_keys($types);
    $rows = $src->query('SELECT * FROM [dbo].['.$table.']')->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo "{$table}\t0\n";
        continue;
    }
    $colSql = implode(',', array_map(fn ($c) => '"'.str_replace('"', '""', $c).'"', $columns));
    $marks = implode(',', array_fill(0, count($columns), '?'));
    $hasIdentity = (bool) $dst->query("SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND is_identity='YES' AND table_name=".$dst->quote($table))->fetchColumn();
    $override = $hasIdentity ? ' OVERRIDING SYSTEM VALUE' : '';
    $insert = $dst->prepare('INSERT INTO "'.str_replace('"', '""', $table)."\" ({$colSql}){$override} VALUES ({$marks})");
    foreach ($rows as $row) {
        $values = [];
        foreach ($columns as $column) {
            $value = $row[$column] ?? null;
            $type = $types[$column];
            if ($value instanceof DateTimeInterface) {
                $value = $type === 'date' ? $value->format('Y-m-d') : $value->format('Y-m-d H:i:s.v');
            }
            if (is_string($value) && in_array($type, ['char', 'nchar'], true)) {
                $value = rtrim($value);
            }
            if ($value === '' && !in_array($type, ['char', 'nchar', 'varchar', 'nvarchar', 'text', 'ntext'], true)) {
                $value = null;
            }
            if ($type === 'bit' && $value !== null) {
                $value = in_array($value, [1, '1', true, 't', 'true'], true) ? 'true' : 'false';
            }
            $values[] = $value;
        }
        $insert->execute($values);
    }
    $copied += count($rows);
    echo "{$table}\t".count($rows)."\n";
}

$dst->exec("SET session_replication_role = 'origin'");

$sequences = $dst->query("SELECT table_name, column_name, column_default FROM information_schema.columns WHERE table_schema='public' AND column_default LIKE 'nextval(%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sequences as $sequence) {
    if (!preg_match("/nextval\\('([^']+)'/", $sequence['column_default'], $match)) {
        continue;
    }
    $table = $sequence['table_name'];
    $column = $sequence['column_name'];
    $max = $dst->query('SELECT MAX("'.str_replace('"', '""', $column).'") FROM "'.str_replace('"', '""', $table).'"')->fetchColumn();
    if ($max === null) {
        $dst->query('SELECT setval('.$dst->quote($match[1]).', 1, false)');
    } else {
        $dst->query('SELECT setval('.$dst->quote($match[1]).', '.(int) $max.', true)');
    }
}

echo "COPIED_ROWS\t{$copied}\n";
echo "TABLES\t".count($srcTables)."\n";

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

function norm(mixed $value, string $type): string
{
    if ($value === null) {
        return '\\N';
    }
    if ($value instanceof DateTimeInterface) {
        $value = $type === 'date' ? $value->format('Y-m-d') : $value->format('Y-m-d H:i:s.v');
    }
    if (in_array($type, ['bit', 'bool', 'boolean'], true)) {
        return in_array($value, [1, '1', true, 't', 'true'], true) ? '1' : '0';
    }
    if (in_array($type, ['decimal', 'numeric', 'money'], true)) {
        $text = (string) $value;
        if (str_contains($text, '.')) {
            $text = rtrim(rtrim($text, '0'), '.');
        }
        return $text;
    }
    $text = (string) $value;
    if (in_array($type, ['char', 'nchar', 'character', 'bpchar'], true)) {
        $text = rtrim($text);
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}:\d{2}(\.\d+)?)?/', $text, $m) && in_array($type, ['date', 'datetime', 'datetime2', 'timestamp', 'timestamp without time zone'], true)) {
        $text = str_replace('T', ' ', $text);
        if ($type === 'date') {
            return substr($text, 0, 10);
        }
        if (!empty($m[1])) {
            $fraction = substr(rtrim($m[1], '0'), 0, 4);
            $text = substr($text, 0, 19).$fraction;
        }
        return $text;
    }
    return $text;
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

$tables = $src->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='dbo' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
$srcRows = 0;
$dstRows = 0;
$diffs = 0;

foreach ($tables as $table) {
    $meta = $src->query("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='dbo' AND TABLE_NAME=".$src->quote($table)." ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
    $types = [];
    foreach ($meta as $col) {
        $types[$col['COLUMN_NAME']] = strtolower($col['DATA_TYPE']);
    }
    $pk = $src->query("SELECT c.name FROM sys.indexes i JOIN sys.index_columns ic ON i.object_id=ic.object_id AND i.index_id=ic.index_id JOIN sys.columns c ON c.object_id=ic.object_id AND c.column_id=ic.column_id JOIN sys.tables t ON t.object_id=i.object_id WHERE i.is_primary_key=1 AND t.name=".$src->quote($table)." ORDER BY ic.key_ordinal")->fetchAll(PDO::FETCH_COLUMN);
    $order = $pk ? implode(',', array_map(fn ($c) => '['.$c.']', $pk)) : '(SELECT NULL)';
    $srcData = $src->query('SELECT * FROM [dbo].['.$table.'] ORDER BY '.$order)->fetchAll(PDO::FETCH_ASSOC);
    $dstOrder = $pk ? implode(',', array_map(fn ($c) => '"'.str_replace('"', '""', $c).'"', $pk)) : '1';
    $dstData = $dst->query('SELECT * FROM "'.str_replace('"', '""', $table).'" ORDER BY '.$dstOrder)->fetchAll(PDO::FETCH_ASSOC);
    $srcRows += count($srcData);
    $dstRows += count($dstData);
    $pack = function (array $rows) use ($types): array {
        $out = [];
        foreach ($rows as $row) {
            $item = [];
            foreach ($types as $column => $type) {
                $value = $row[$column] ?? null;
                if ($column === 'password' || $column === 'remember_token' || $column === 'payload') {
                    $item[$column] = $value === null ? '\\N' : hash('sha256', (string) $value);
                    continue;
                }
                $item[$column] = norm($value, $type);
            }
            $out[] = json_encode($item, JSON_UNESCAPED_UNICODE);
        }
        return $out;
    };
    $left = $pack($srcData);
    $right = $pack($dstData);
    sort($left, SORT_STRING);
    sort($right, SORT_STRING);
    $same = $left === $right;
    if (!$same) {
        $diffs++;
        echo "DIFF\t{$table}\tsrc=".count($srcData)."\tdst=".count($dstData)."\n";
    } else {
        echo "OK\t{$table}\t".count($srcData)."\n";
    }
}

$user = $src->query('SELECT id, LEN(password) AS n, LEFT(password,4) AS p, password FROM users')->fetch(PDO::FETCH_ASSOC);
$pgUser = $dst->query('SELECT id, length(password) AS n, left(password,4) AS p, password FROM users')->fetch(PDO::FETCH_ASSOC);
echo "USER_SRC\t{$user['id']}\t{$user['n']}\t{$user['p']}\n";
echo "USER_DST\t{$pgUser['id']}\t{$pgUser['n']}\t{$pgUser['p']}\n";
echo 'PASSWORD_TEXT_MATCH='.($user['password'] === $pgUser['password'] ? 'yes' : 'no')."\n";
unset($user['password'], $pgUser['password']);

$srcTipos = $src->query('SELECT Nombre FROM Tipo ORDER BY TipoID')->fetchAll(PDO::FETCH_COLUMN);
$dstTipos = $dst->query('SELECT "Nombre" FROM "Tipo" ORDER BY "TipoID"')->fetchAll(PDO::FETCH_COLUMN);
echo 'TIPOS_MATCH='.($srcTipos === $dstTipos ? 'yes' : 'no')."\n";
echo 'TIPOS='.implode('|', $dstTipos)."\n";

$fks = $src->query("SELECT OBJECT_NAME(fk.parent_object_id) AS child, COL_NAME(fc.parent_object_id, fc.parent_column_id) AS child_col, OBJECT_NAME(fk.referenced_object_id) AS parent, COL_NAME(fc.referenced_object_id, fc.referenced_column_id) AS parent_col FROM sys.foreign_keys fk JOIN sys.foreign_key_columns fc ON fk.object_id=fc.constraint_object_id")->fetchAll(PDO::FETCH_ASSOC);
$orphans = 0;
foreach ($fks as $fk) {
    $sql = 'SELECT COUNT(*) FROM "'.$fk['child'].'" c LEFT JOIN "'.$fk['parent'].'" p ON c."'.$fk['child_col'].'"=p."'.$fk['parent_col'].'" WHERE c."'.$fk['child_col'].'" IS NOT NULL AND p."'.$fk['parent_col'].'" IS NULL';
    $n = (int) $dst->query($sql)->fetchColumn();
    if ($n > 0) {
        $orphans += $n;
        echo "ORPHAN\t{$fk['child']}.{$fk['child_col']}\t{$n}\n";
    }
}

$seq = $dst->query("SELECT pg_get_serial_sequence('public.users','id')")->fetchColumn();
$next = $seq ? $dst->query('SELECT last_value, is_called FROM '.$seq)->fetch(PDO::FETCH_ASSOC) : null;
$modelo = $dst->query("SELECT last_value, is_called FROM \"Modelo_ModeloID_seq\"")->fetch(PDO::FETCH_ASSOC);
echo 'MODELO_SEQUENCE='.json_encode($modelo)."\n";
echo 'USERS_SEQUENCE='.json_encode($next)."\n";
echo "ROWS_SRC\t{$srcRows}\nROWS_DST\t{$dstRows}\nDIFF_TABLES\t{$diffs}\nORPHANS\t{$orphans}\n";
exit($diffs === 0 && $orphans === 0 && $srcRows === $dstRows ? 0 : 2);

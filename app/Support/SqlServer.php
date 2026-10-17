<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Expresiones compatibles con SQL Server y PostgreSQL.
 * En PostgreSQL iguala la collation Modern_Spanish_CI_AS: sin distinguir
 * mayúsculas ni acentos.
 */
final class SqlServer
{
    public static function pgsql(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    public static function qi(string $identifier): string
    {
        if (!self::pgsql()) {
            return $identifier;
        }

        return implode('.', array_map(
            static fn (string $part) => '"'.str_replace('"', '""', $part).'"',
            explode('.', $identifier)
        ));
    }

    public static function yearMonth(string $column): string
    {
        if (self::pgsql()) {
            return 'to_char('.self::qi($column).", 'YYYY-MM')";
        }

        return "FORMAT({$column}, 'yyyy-MM')";
    }

    public static function lowerEquals(string $column): string
    {
        if (self::pgsql()) {
            $quoted = self::qi($column);

            return "unaccent(lower({$quoted}::text)) = unaccent(lower(?::text))";
        }

        return "LOWER({$column}) = LOWER(?)";
    }

    /**
     * Unicidad con el mismo criterio que SQL Server CI_AI.
     *
     * @param  array<string, mixed>  $equals
     */
    public static function uniqueRule(
        string $table,
        string $column,
        mixed $ignore = null,
        ?string $ignoreColumn = null,
        array $equals = [],
    ): Unique|Closure {
        if (!self::pgsql()) {
            $rule = Rule::unique($table, $column);
            foreach ($equals as $col => $value) {
                $rule = $rule->where(fn ($query) => $query->where($col, $value));
            }
            if ($ignore !== null && $ignore !== '') {
                $rule = $rule->ignore($ignore, $ignoreColumn);
            }

            return $rule;
        }

        return function (string $attribute, mixed $value, Closure $fail) use ($table, $column, $ignore, $ignoreColumn, $equals) {
            if ($value === null || $value === '') {
                return;
            }

            $grammar = DB::connection()->getQueryGrammar();
            $query = DB::table($table)->whereRaw(
                'unaccent(lower('.$grammar->wrap($column).'::text)) = unaccent(lower(?::text))',
                [(string) $value]
            );

            foreach ($equals as $col => $expected) {
                if ($expected === null || $expected === '') {
                    $query->where(function ($inner) use ($col) {
                        $inner->whereNull($col)->orWhere($col, '');
                    });
                    continue;
                }
                $query->whereRaw(
                    'unaccent(lower('.$grammar->wrap($col).'::text)) = unaccent(lower(?::text))',
                    [(string) $expected]
                );
            }

            if ($ignore !== null && $ignore !== '') {
                $query->where($ignoreColumn ?? 'id', '!=', $ignore);
            }

            if ($query->exists()) {
                $fail('El :attribute ya está registrado.');
            }
        };
    }
}

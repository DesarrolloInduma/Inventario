<?php

namespace App\Support;

/**
 * Expresiones SQL reutilizables para Microsoft SQL Server.
 */
final class SqlServer
{
    public static function yearMonth(string $column): string
    {
        return "FORMAT({$column}, 'yyyy-MM')";
    }

    public static function lowerEquals(string $column): string
    {
        return "LOWER({$column}) = LOWER(?)";
    }
}

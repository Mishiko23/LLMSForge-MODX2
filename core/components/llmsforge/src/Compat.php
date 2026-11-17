<?php
declare(strict_types=1);
namespace LLMSForge;

/** PHP 7.4 helpers scoped to this component; no global polyfills. */
final class Compat
{
    public static function contains(string $text, string $needle): bool
    {
        return $needle === '' || strpos($text, $needle) !== false;
    }

    public static function startsWith(string $text, string $prefix): bool
    {
        return $prefix === '' || strpos($text, $prefix) === 0;
    }

    public static function isList(array $items): bool
    {
        $index = 0;
        foreach ($items as $key => $value) {
            if ($key !== $index++) { return false; }
        }
        return true;
    }
}

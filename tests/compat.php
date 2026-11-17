<?php
declare(strict_types=1);
require dirname(__DIR__) . '/core/components/llmsforge/src/Compat.php';
use LLMSForge\Compat;
foreach ([['', '', true], ['abc', '', true], ['abc', 'b', true], ['abc', 'd', false]] as [$text, $needle, $expected]) {
    if (Compat::contains($text, $needle) !== $expected) { throw new RuntimeException('contains failed'); }
}
foreach ([['', '', true], ['abc', '', true], ['abc', 'ab', true], ['abc', 'b', false], ['a', 'ab', false]] as [$text, $prefix, $expected]) {
    if (Compat::startsWith($text, $prefix) !== $expected) { throw new RuntimeException('startsWith failed'); }
}
foreach ([[[], true], [['a', 'b'], true], [[1 => 'a'], false], [[0 => 'a', 2 => 'b'], false], [['a' => 1], false]] as [$items, $expected]) {
    if (Compat::isList($items) !== $expected) { throw new RuntimeException('isList failed'); }
}
echo "PHP 7.4 compatibility helper checks passed\n";

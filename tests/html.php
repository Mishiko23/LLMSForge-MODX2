<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/core/components/llmsforge/src/Compat.php';
require dirname(__DIR__) . '/core/components/llmsforge/src/HtmlToMarkdown.php';
$html = '<h2>Features</h2><p>Useful <strong>text</strong> and <a href="https://example.com/a(b)">link</a>.</p><ul><li>First</li><li>Second</li></ul><script>secret()</script>[[!Private]]';
$text = (new \LLMSForge\HtmlToMarkdown())->convert($html);
foreach (['## Features', '**text**', '[link](https://example.com/a%28b%29)', '- First', '- Second'] as $expected) {
    if (!\LLMSForge\Compat::contains($text, $expected)) { throw new RuntimeException('Missing Markdown: ' . $expected . "\n" . $text); }
}
if (\LLMSForge\Compat::contains($text, 'secret') || \LLMSForge\Compat::contains($text, 'Private')) { throw new RuntimeException('Executable content leaked'); }
echo "HTML to Markdown checks passed\n";

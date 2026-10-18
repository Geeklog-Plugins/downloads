<?php
require_once dirname(__DIR__) . '/include/github_markdown.php';
$source = "# Release\n\n**Important** *update*\n- first item\n- second item\n\n[More](https://github.com/Geeklog-Plugins/downloads)\n\n```php\n<script>alert(1)</script>\n```";
$html = DLM_renderGithubMarkdown($source);
$expect = array('<h2>Release</h2>', '<strong>Important</strong>', '<em>update</em>',
    '<ul>', '<li>first item</li>', 'href="https://github.com/Geeklog-Plugins/downloads"',
    '&lt;script&gt;', '<pre><code>');
foreach ($expect as $text) {
    if (strpos($html, $text) === false) {
        fwrite(STDERR, "Missing expected Markdown output: " . $text . "\n");
        exit(1);
    }
}
if (strpos($html, '<script>') !== false || strpos(DLM_renderGithubMarkdown('[x](javascript:alert(1))'), 'href=') !== false) {
    fwrite(STDERR, "Unsafe Markdown HTML generated\n");
    exit(1);
}
echo "GitHub Markdown rendering checks passed\n";

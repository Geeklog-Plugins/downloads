<?php
/**
 * Safe, server-side rendering of GitHub release Markdown.
 *
 * This is deliberately a restricted Markdown renderer: no raw HTML, images,
 * reference links, tables or embedded scripts. Source Markdown remains intact.
 * PHP 5.6 compatible.
 */
function DLM_renderGithubMarkdownInline($source)
{
    $text = htmlspecialchars((string) $source, ENT_QUOTES, 'UTF-8');
    // Link URLs are restricted to HTTPS and HTTP. HTML has already been escaped.
    $text = preg_replace_callback('~\\[([^\\]\\r\\n]+)\\]\\((https?://[^\\s)<>]+)\\)~i',
        function ($m) {
            $url = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
            $parts = parse_url($url);
            if (!is_array($parts) || empty($parts['host']) ||
                !in_array(strtolower($parts['scheme']), array('https', 'http'), true) ||
                isset($parts['user']) || isset($parts['pass'])) {
                return $m[0];
            }
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') .
                '" rel="nofollow noopener noreferrer" target="_blank">' . $m[1] . '</a>';
        }, $text);
    $text = preg_replace('~`([^`]+)`~', '<code>$1</code>', $text);
    $text = preg_replace('~\\*\\*(.+?)\\*\\*~', '<strong>$1</strong>', $text);
    $text = preg_replace('~(?<!\\*)\\*([^*\\r\\n]+)\\*(?!\\*)~', '<em>$1</em>', $text);
    return $text;
}

/**
 * @param string $markdown Unmodified GitHub notes stored in Downloads.
 * @return string Restricted, escaped HTML suitable for public output.
 */
function DLM_renderGithubMarkdown($markdown)
{
    $lines = preg_split('/\\r\\n|\\r|\\n/', (string) $markdown);
    $html = '';
    $list = '';
    $code = false;
    $codeLines = array();
    foreach ($lines as $line) {
        if (preg_match('/^\\s*```/', $line)) {
            if ($list !== '') {
                $html .= '</' . $list . '>';
                $list = '';
            }
            if ($code) {
                $html .= '<pre><code>' . htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES, 'UTF-8') . '</code></pre>';
                $codeLines = array();
            }
            $code = !$code;
            continue;
        }
        if ($code) {
            $codeLines[] = $line;
            continue;
        }
        if (preg_match('/^\\s*([-*+] |[0-9]+\\. )(.+)$/', $line, $m)) {
            $tag = preg_match('/^[0-9]/', $m[1]) ? 'ol' : 'ul';
            if ($list !== $tag) {
                if ($list !== '') $html .= '</' . $list . '>';
                $list = $tag;
                $html .= '<' . $list . '>';
            }
            $html .= '<li>' . DLM_renderGithubMarkdownInline($m[2]) . '</li>';
            continue;
        }
        if ($list !== '') {
            $html .= '</' . $list . '>';
            $list = '';
        }
        if (preg_match('/^(#{1,6})\\s+(.+)$/', $line, $m)) {
            $level = min(6, strlen($m[1]) + 1);
            $html .= '<h' . $level . '>' . DLM_renderGithubMarkdownInline($m[2]) . '</h' . $level . '>';
        } elseif (trim($line) !== '') {
            $html .= '<p>' . DLM_renderGithubMarkdownInline($line) . '</p>';
        }
    }
    if ($code) $html .= '<pre><code>' . htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES, 'UTF-8') . '</code></pre>';
    if ($list !== '') $html .= '</' . $list . '>';
    return '<div class="dlm-github-markdown">' . $html . '</div>';
}

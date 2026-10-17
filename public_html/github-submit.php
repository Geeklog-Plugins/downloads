<?php
/**
 * Member-facing GitHub Release proposal preview.
 * Read-only first step. This page never creates a Downloads item or downloads a ZIP.
 */
require_once '../lib-common.php';

if (!in_array('downloads', $_PLUGINS)) {
    COM_redirect($_CONF['site_url'] . '/index.php');
}
if (COM_isAnonUser()) {
    COM_output(COM_createHTMLDocument(SEC_loginRequiredForm()));
    exit;
}

$escape = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$pageUrl = $_CONF['site_url'] . '/downloads/github-submit.php';
$display = '<h2>Propose a GitHub Release</h2>'
         . '<p>Preview a public release before proposing it to the Downloads moderators. '
         . 'This page does not submit or publish an item, and no archive is downloaded.</p>'
         . '<form action="' . $escape($pageUrl) . '" method="post">'
         . '<label for="release_url">Public GitHub Release URL</label> '
         . '<input id="release_url" name="release_url" type="url" size="70" maxlength="500" required '
         . 'placeholder="https://github.com/owner/repository/releases/tag/v1.0.0">'
         . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $escape(SEC_createToken()) . '">'
         . '<button type="submit">Preview release</button></form>';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        $display .= '<p>Security token expired. Reload this page and try again.</p>';
    } else {
        $url = isset($_POST['release_url']) && is_string($_POST['release_url'])
             ? trim($_POST['release_url']) : '';
        if (strlen($url) > 500) {
            $display .= '<p>The GitHub Release URL is too long.</p>';
        } else {
            require_once $_CONF['path'] . 'plugins/downloads/include/github_release.php';
            $error = '';
            $release = DLM_githubReleaseDetails($url, $error);
            if ($release === false) {
                $display .= '<p>' . $escape($error) . '</p>';
            } else {
                $display .= '<h3>' . $escape($release['name'] !== '' ? $release['name'] : $release['tag']) . '</h3>'
                         . '<p>Repository: ' . $escape($release['repository']) .
                           ' — Tag: ' . $escape($release['tag']) . '</p>'
                         . '<p>Published: ' . $escape($release['published_at']) . '</p>'
                         . '<h4>Release notes</h4><pre class="dlm-github-notes">'
                         . $escape(substr($release['notes'], 0, 8000)) . '</pre>'
                         . '<h4>Available ZIP assets</h4>';
                if (empty($release['assets'])) {
                    $display .= '<p>No ZIP archive is attached to this release.</p>';
                } else {
                    $display .= '<ul>';
                    foreach ($release['assets'] as $asset) {
                        $display .= '<li>' . $escape($asset['name']) . ' (' .
                            (int) $asset['size'] . ' bytes)</li>';
                    }
                    $display .= '</ul><p>A moderated submission form will be added next. '
                             . 'Until then, use the standard Downloads submission form to upload a ZIP manually.</p>';
                }
            }
        }
    }
}
$display .= '<p><a href="' . $escape($_CONF['site_url'] . '/submit.php?type=downloads') .
            '">Standard Downloads submission</a></p>';
COM_output(COM_createHTMLDocument($display, array('pagetitle' => 'GitHub Release proposal')));

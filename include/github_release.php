<?php
/**
 * GitHub Releases metadata preview for Downloads 1.3.2.
 * No remote archive is downloaded and no Downloads record is created.
 * PHP 5.6 compatible.
 */
function DLM_githubReleaseDetails($releaseUrl, &$error)
{
    $error = '';
    $url = trim((string) $releaseUrl);
    if (!preg_match('~^https://github\\.com/([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)/releases/tag/([^/?#]+)$~D', $url, $m)) {
        $error = 'Enter a public GitHub release URL in the form https://github.com/owner/repo/releases/tag/tag';
        return false;
    }
    if ($m[1] === '.' || $m[1] === '..' || $m[2] === '.' || $m[2] === '..' || !function_exists('curl_init')) {
        $error = 'Invalid repository name or unavailable HTTP client.';
        return false;
    }
    $tag = rawurldecode($m[3]);
    if ($tag === '' || strlen($tag) > 200 || strpos($tag, '/') !== false || strpos($tag, '\\') !== false || preg_match('/[\\x00-\\x1F\\x7F]/', $tag)) {
        $error = 'Invalid release tag.';
        return false;
    }
    $api = 'https://api.github.com/repos/' . rawurlencode($m[1]) . '/' . rawurlencode($m[2])
         . '/releases/tags/' . rawurlencode($tag);
    $ch = curl_init($api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Geeklog-Downloads/1.3.2');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/vnd.github+json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $networkError = curl_error($ch);
    curl_close($ch);
    if ($body === false || $status !== 200 || strlen($body) > 1048576) {
        $error = $status === 403 ? 'GitHub API rate limit or access denied.' :
                 ($status === 404 ? 'Release not found or repository is private.' : 'GitHub API request failed.');
        if ($networkError !== '') {
            $error = 'GitHub API network error.';
        }
        return false;
    }
    $json = json_decode($body, true);
    if (!is_array($json) || empty($json['id']) || empty($json['tag_name']) || empty($json['html_url'])) {
        $error = 'Invalid GitHub release response.';
        return false;
    }
    $assets = array();
    foreach (isset($json['assets']) && is_array($json['assets']) ? $json['assets'] : array() as $asset) {
        if (!empty($asset['name']) && preg_match('/\\.zip$/i', $asset['name']) &&
            isset($asset['size']) && !empty($asset['id'])) {
            $assets[] = array('id' => (int) $asset['id'], 'name' => (string) $asset['name'],
                              'size' => (int) $asset['size']);
        }
    }
    return array('repository' => $m[1] . '/' . $m[2], 'id' => (int) $json['id'],
                 'name' => isset($json['name']) ? (string) $json['name'] : '',
                 'tag' => (string) $json['tag_name'],
                 'published_at' => isset($json['published_at']) ? (string) $json['published_at'] : '',
                 'notes' => isset($json['body']) ? (string) $json['body'] : '',
                 'url' => (string) $json['html_url'], 'assets' => $assets);
}

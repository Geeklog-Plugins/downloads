<?php
/**
 * GitHub Releases metadata preview for Downloads 1.3.2.
 * No remote archive is downloaded and no Downloads record is created.
 * PHP 5.6 compatible.
 */
function DLM_githubReleaseDetails($releaseUrl, &$error)
{
    $error = '';
    $url = trim(html_entity_decode((string) $releaseUrl, ENT_QUOTES, 'UTF-8'));
    // Copy/paste from Markdown and browsers may carry escaped slashes,
    // tracking query parameters or a final slash.
    $url = str_replace('\\/', '/', $url);
    $parts = parse_url($url);
    $path = isset($parts['path']) ? rtrim($parts['path'], '/') : '';
    // GitHub's releases index uses anchors like #release-v1.2.3.
    // Resolve these to the same tag-based API lookup as direct release links.
    $releaseMatch = preg_match('~^/([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)/releases/tag/([^/?#]+)$~D', $path, $m);
    if (!$releaseMatch && preg_match('~^/([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)/releases$~D', $path, $indexMatch) &&
        isset($parts['fragment']) && strpos($parts['fragment'], 'release-') === 0) {
        $anchorTag = substr($parts['fragment'], strlen('release-'));
        if ($anchorTag !== '') {
            $m = array('', $indexMatch[1], $indexMatch[2], $anchorTag);
            $releaseMatch = true;
        }
    }
    if (!is_array($parts) || !isset($parts['scheme'], $parts['host']) ||
        strtolower($parts['scheme']) !== 'https' ||
        !in_array(strtolower($parts['host']), array('github.com', 'www.github.com'), true) ||
        isset($parts['user']) || isset($parts['pass']) ||
        (isset($parts['port']) && (int) $parts['port'] !== 443) ||
        !$releaseMatch) {
        // Show only the parsed host and path; never echo query strings,
        // credentials, fragments or other potentially sensitive URL parts.
        $receivedHost = is_array($parts) && isset($parts['host']) ? $parts['host'] : '(none)';
        $receivedPath = is_array($parts) && isset($parts['path']) ? $parts['path'] : '(none)';
        $error = 'Expected https://github.com/owner/repo/releases/tag/v1.0.0. '
               . 'Received host: ' . substr($receivedHost, 0, 100)
               . '; path: ' . substr($receivedPath, 0, 240);
        return false;
    }
    if ($m[1] === '.' || $m[1] === '..' || $m[2] === '.' || $m[2] === '..' || !function_exists('curl_init')) {
        $error = 'Invalid repository name or unavailable HTTP client.';
        return false;
    }
    $tag = rawurldecode($m[3]);
    if ($tag === '' || strlen($tag) > 200 || strpos($tag, '/') !== false || strpos($tag, '\\') !== false || preg_match('/[\x00-\x1F\x7F]/', $tag)) {
        $error = 'Invalid release tag.';
        return false;
    }
    $api = 'https://api.github.com/repos/' . rawurlencode($m[1]) . '/' . rawurlencode($m[2])
         . '/releases/tags/' . rawurlencode($tag);
    $ch = curl_init($api);
    // Bound API responses during transfer, not after buffering them in memory.
    $body = '';
    $tooLarge = false;
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $chunk) use (&$body, &$tooLarge) {
        if (strlen($body) + strlen($chunk) > 1048576) {
            $tooLarge = true;
            return 0;
        }
        $body .= $chunk;
        return strlen($chunk);
    });
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Geeklog-Downloads/1.3.2');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/vnd.github+json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $received = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $networkError = curl_error($ch);
    curl_close($ch);
    if ($received === false || $status !== 200 || $tooLarge) {
        $error = $tooLarge ? 'GitHub API response is too large.' : ($status === 403 ? 'GitHub API rate limit or access denied.' :
                 ($status === 404 ? 'Release not found or repository is private.' : 'GitHub API request failed.'));
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
        if (!empty($asset['name']) && preg_match('/\.zip$/i', $asset['name']) &&
            isset($asset['size']) && !empty($asset['id'])) {
            $assets[] = array('id' => (int) $asset['id'], 'name' => (string) $asset['name'],
                              'size' => (int) $asset['size'],
                              'digest' => isset($asset['digest']) ? (string) $asset['digest'] : '');
        }
    }
    return array('repository' => $m[1] . '/' . $m[2], 'id' => (int) $json['id'],
                 'name' => isset($json['name']) ? (string) $json['name'] : '',
                 'tag' => (string) $json['tag_name'],
                 'published_at' => isset($json['published_at']) ? (string) $json['published_at'] : '',
                 'notes' => isset($json['body']) ? (string) $json['body'] : '',
                 'url' => (string) $json['html_url'], 'assets' => $assets);
}

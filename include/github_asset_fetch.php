<?php
/**
 * Bounded GitHub Release ZIP fetch. This helper does not publish or install a
 * download: the caller owns the temporary file and must always remove it.
 * PHP 5.6 compatible.
 */
function DLM_githubFetchReleaseZip($repository, $asset, $destination, $limitBytes, &$error)
{
    $error = '';
    if (!is_array($asset) || empty($asset['id']) || empty($asset['name']) ||
        !preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/D', (string) $repository) ||
        !preg_match('/^[^\\\/\x00-\x1f]+\\.zip$/iD', (string) $asset['name']) ||
        !isset($asset['size']) || (int) $asset['size'] < 1 ||
        (int) $asset['size'] > $limitBytes || $limitBytes < 1 ||
        file_exists($destination) ||
        !function_exists('curl_init')) {
        $error = 'Invalid ZIP selection, size, or empty staging destination.';
        return false;
    }
    $parts = explode('/', $repository);
    $url = 'https://api.github.com/repos/' . rawurlencode($parts[0]) . '/'
         . rawurlencode($parts[1]) . '/releases/assets/' . (int) $asset['id'];
    // GitHub's release asset API can redirect to GitHub-controlled CDN hosts.
    // Resolve the redirect explicitly, never allowing arbitrary destination URLs.
    $probe = curl_init($url);
    // Keep response headers bounded: never buffer an untrusted header block.
    $headers = '';
    $headerTooLarge = false;
    curl_setopt($probe, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($probe, CURLOPT_HEADER, false);
    curl_setopt($probe, CURLOPT_NOBODY, true);
    curl_setopt($probe, CURLOPT_HEADERFUNCTION, function ($curl, $line) use (&$headers, &$headerTooLarge) {
        if (strlen($headers) + strlen($line) > 16384) {
            $headerTooLarge = true;
            return 0;
        }
        $headers .= $line;
        return strlen($line);
    });
    curl_setopt($probe, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($probe, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
    curl_setopt($probe, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
    curl_setopt($probe, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($probe, CURLOPT_TIMEOUT, 12);
    curl_setopt($probe, CURLOPT_USERAGENT, 'Geeklog-Downloads/1.3.2');
    curl_setopt($probe, CURLOPT_HTTPHEADER, array('Accept: application/octet-stream'));
    curl_setopt($probe, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($probe, CURLOPT_SSL_VERIFYHOST, 2);
    $probeOk = curl_exec($probe);
    $probeStatus = (int) curl_getinfo($probe, CURLINFO_HTTP_CODE);
    curl_close($probe);
    // GitHub documents both 200 (direct binary) and 302 (CDN redirect).
    // For a 200 response, retry with a bounded GET against the original
    // allowlisted api.github.com endpoint; ZIP signature validation remains mandatory.
    if ($probeOk === false || $headerTooLarge ||
        !in_array($probeStatus, array(200, 302, 303, 307, 308), true)) {
        $error = 'Unable to resolve GitHub asset safely.';
        return false;
    }
    if ($probeStatus !== 200) {
        if (!preg_match('/^Location:\\s*(\\S+)\\s*$/im', $headers, $location)) {
            $error = 'GitHub asset redirect has no usable location.';
            return false;
        }
        $target = parse_url(trim($location[1]));
        $allowedHosts = array('release-assets.githubusercontent.com', 'objects.githubusercontent.com');
        if (!is_array($target) || !isset($target['scheme'], $target['host']) ||
            strtolower($target['scheme']) !== 'https' ||
            !in_array(strtolower($target['host']), $allowedHosts, true) ||
            isset($target['user']) || isset($target['pass']) ||
            (isset($target['port']) && (int) $target['port'] !== 443)) {
            $error = 'GitHub asset redirect destination is not allowed.';
            return false;
        }
        $url = trim($location[1]);
    }
    // Resolve names immediately before transfer, and never overwrite existing files.
    $fp = @fopen($destination, 'xb');
    if (!$fp) {
        $error = 'Unable to write temporary archive.';
        return false;
    }
    $bytes = 0;
    $tooLarge = false;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
    curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Geeklog-Downloads/1.3.2');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/octet-stream'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $chunk) use ($fp, &$bytes, &$tooLarge, $limitBytes) {
        $length = strlen($chunk);
        if ($bytes + $length > $limitBytes) {
            $tooLarge = true;
            return 0;
        }
        // A short fwrite is not a truncated network response: finish the
        // chunk explicitly, or abort and discard the incomplete archive.
        $offset = 0;
        while ($offset < $length) {
            $written = fwrite($fp, substr($chunk, $offset));
            if ($written === false || $written === 0) {
                return 0;
            }
            $offset += $written;
        }
        $bytes += $length;
        return $length;
    });
    $ok = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    // GitHub asset requests may redirect to an external object host. Do not
    // follow those redirects without explicit destination validation.
    if ($ok === false || $http !== 200 || $tooLarge || $bytes !== (int) $asset['size']) {
        @unlink($destination);
        $error = $http >= 300 && $http < 400
               ? 'GitHub returned a redirect. External asset hosts are not enabled.'
               : 'Archive transfer failed or returned an unexpected size.';
        return false;
    }
    $check = @fopen($destination, 'rb');
    $signature = $check ? fread($check, 4) : '';
    if ($check) {
        fclose($check);
    }
    if ($signature !== "PK\x03\x04" && $signature !== "PK\x05\x06") {
        @unlink($destination);
        $error = 'Downloaded content is not a ZIP archive.';
        return false;
    }
    // Optional published GitHub digest (sha256:HEX) is compared by the
    // caller after metadata validation; this helper never trusts URL hashes.
    if (!empty($asset['digest'])) {
        if (!preg_match('/^sha256:([a-f0-9]{64})$/iD', $asset['digest'], $match) ||
            !hash_equals(strtolower($match[1]), strtolower(hash_file('sha256', $destination)))) {
            @unlink($destination);
            $error = 'ZIP SHA-256 digest does not match GitHub release metadata.';
            return false;
        }
    }
    return true;
}

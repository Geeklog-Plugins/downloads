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
        !is_file($destination) || filesize($destination) !== 0) {
        $error = 'Invalid ZIP selection, size, or empty staging destination.';
        return false;
    }
    $parts = explode('/', $repository);
    $url = 'https://api.github.com/repos/' . rawurlencode($parts[0]) . '/'
         . rawurlencode($parts[1]) . '/releases/assets/' . (int) $asset['id'];
    $fp = @fopen($destination, 'wb');
    if (!$fp) {
        $error = 'Unable to write temporary archive.';
        return false;
    }
    $bytes = 0;
    $tooLarge = false;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
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
        $written = fwrite($fp, $chunk);
        if ($written === false) {
            return 0;
        }
        $bytes += $written;
        return $written;
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
    return true;
}

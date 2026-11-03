<?php
/**
 * Regression guard: Item Info can be called without prior Downloads helper loads.
 * Compatible with the project's PHP 5.6 baseline.
 */
$source = file_get_contents(dirname(__DIR__) . '/functions.inc');
if ($source === false) {
    fwrite(STDERR, "Cannot read functions.inc\n");
    exit(1);
}
$start = strpos($source, 'function DLM_getInteropItem(');
$end = strpos($source, 'function plugin_collectSitemapItems_downloads(', $start);
if ($start === false || $end === false) {
    fwrite(STDERR, "Cannot locate Item Info implementation\n");
    exit(1);
}
$body = substr($source, $start, $end - $start);
$include = 'require_once ' . '$_CONF' . "['path'] . 'plugins/downloads/include/functions.php';";
$includePos = strpos($body, $include);
$callPos = strpos($body, 'DLM_createSafeFileName(');
if ($includePos === false || $callPos === false || $includePos > $callPos) {
    fwrite(STDERR, "Downloads filename helpers must load before Item Info uses them\n");
    exit(1);
}
echo "Item Info filename helper dependency check passed\n";

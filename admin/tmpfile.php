<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/admin/plugins/downloads/tmpfile.php                           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2010-2014 dengen - taharaxp AT gmail DOT com                |
// |                                                                           |
// | Downloads Plugin is based on Filemgmt plugin                              |
// | Copyright (C) 2004 by Consult4Hire Inc.                                   |
// | Author:                                                                   |
// | Blaine Lang               - blaine AT portalparts DOT com                 |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';

if (!SEC_hasRights('downloads.edit')) {
    $display = COM_showMessageText($MESSAGE[29], $MESSAGE[30]);
    $display = DLM_createHTMLDocument($display, array('pagetitle' => $MESSAGE[30]));
    COM_accessLog("User {$_USER['username']} tried to illegally access the downloads temporary file.");
    COM_output($display);
    exit;
}

COM_setArgNames(array('id'));
$lid = addslashes(COM_applyFilter(COM_getArgument('id')));
$result = DB_query("SELECT url, date FROM {$_TABLES['downloadsubmission']} WHERE lid='$lid'");
list($url, $date) = DB_fetchArray($result);
$filepath = $_DLM_CONF['path_filestore'] . 'tmp' . date('YmdHis', $date) . DLM_createSafeFileName($url);
if (file_exists($filepath)) {
    header('Content-Disposition: attachment; filename="' . $url . '"');
    header('Content-Type: application/octet-stream');
    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    ob_clean();
    flush();
    @readfile($filepath);
}
?>
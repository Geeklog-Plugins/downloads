<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/downloads/visit.php                                           |
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

require_once '../lib-common.php';

if (!in_array('downloads', $_PLUGINS)) {
    echo COM_refresh($_CONF['site_url'] . '/index.php');
    exit;
}

require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';

if (COM_isAnonUser() && ($_CONF['loginrequired'] == 1 || $_DLM_CONF['loginrequired'] == 1)) {
    $display = SEC_loginRequiredForm();
    $display = DLM_createHTMLDocument($display);
    COM_output($display);
    exit;
}

$uid = (isset($_USER['uid'])) ? $_USER['uid'] : 1;
COM_setArgNames(array('id'));
$lid = addslashes(COM_applyFilter(COM_getArgument('id')));

$sql = "SELECT COUNT(*) FROM {$_TABLES['downloads']} a "
     . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
     . "WHERE a.lid='$lid' " . COM_getPermSQL('AND', 0, 2, 'b');

list($count) = DB_fetchArray(DB_query($sql));
if ($count == 0 || DB_count($_TABLES['downloads'], "lid", $lid) == 0) {
    COM_errorLog("Downloads: invalid attempt to download a file. "
               . "User:{$_USER['username']}, IP:{$_SERVER['REMOTE_ADDR']}, File ID:{$lid}");
    echo COM_refresh($_CONF['site_url'] . '/downloads/index.php');
    exit;
}

$result = DB_query("SELECT url, secret_id, owner_id FROM {$_TABLES['downloads']} WHERE lid='$lid'");
list($url, $secret_id, $owner_id) = DB_fetchArray($result);

if ($uid !== $owner_id || ($uid == $owner_id && $_DLM_CONF['cut_own_download'] == 0)) {
    DB_query("INSERT INTO {$_TABLES['downloadhistories']} (uid, lid, remote_ip, date) "
           . "VALUES ($uid, '$lid', '{$_SERVER['REMOTE_ADDR']}', NOW())");

    DB_query("UPDATE {$_TABLES['downloads']} SET hits=hits+1 "
           . "WHERE lid='$lid'");
}

$filename = $secret_id . '_' . DLM_encodeFileName($url);
$filepath = $_DLM_CONF['path_filestore'] . $filename;
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
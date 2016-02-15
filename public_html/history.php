<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/downloads/history.php                                         |
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

if (!DLM_hasAccess_history()) {
    $display .= COM_siteHeader('menu', $MESSAGE[30])
             . COM_showMessageText($MESSAGE[29], $MESSAGE[30])
             . COM_siteFooter();
    COM_accessLog("User {$_USER['username']} tried to illegally access "
                . "the downloads history screen.");
    COM_output($display);
    exit;
}

require_once ($_CONF['path_system'] . 'lib-admin.php');

$pagetitle = $LANG_DLM['DownloadReport'];
$display = '';

COM_setArgNames(array('lid'));
$lid = addslashes(COM_applyFilter(COM_getArgument('lid')));
$title = DB_getItem($_TABLES['downloads'], 'title', "lid = '$lid'");
$result = DB_query("SELECT date, uid, remote_ip "
                 . "FROM {$_TABLES['downloadhistories']} WHERE lid = '$lid'");

$header_arr = array(
    array('text' => $LANG_DLM['votedate'], 'field' => 'date'),
    array('text' => $LANG_DLM['user'],     'field' => 'user'),
    array('text' => $LANG_DLM['ip'],       'field' => 'remote_ip'));
$data_arr = array();
$text_arr = array('has_menu' => false,
                  'title'    => $LANG_DLM['DownloadReport'] . ': ' . $title);

while ($A = DB_fetchArray($result)) {
    $data_arr[] = array('date'      => $A['date'],
                        'user'      => COM_getDisplayName($A['uid']),
                        'remote_ip' => $A['remote_ip'] );
}

$display .= ADMIN_simpleList('', $header_arr, $text_arr, $data_arr);
$display = DLM_createHTMLDocument($display, array('pagetitle' => $pagetitle));

COM_output($display);
?>
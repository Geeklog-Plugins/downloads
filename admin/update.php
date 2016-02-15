<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/admin/plugins/downloads/update.php                            |
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

switch ($_CONF['language']) {
case 'japanese_utf-8':
    $_LANG_UPDATE = array(
        'title'               => 'Downloadsプラグイン アップデート',
        'submit'              => $LANG_ACCESS['submit'],
        'cancel'              => $LANG_ACCESS['cancel'],
        'description1'        => 'Downloadsプラグインは最新です。アップデートの必要はありません。',
        'description2'        => '実行ボタンをクリックすると、処理を開始します。（元に戻すことはできません。）',
        'description_001'     => '#001: Geeklog1.8.0から新しくなったコンフィギュレーションUIに対応します。',
        'dm_not_installed'    => 'Downloads Plugin はインストールされていないか、または無効になっています。',
        'db_error'            => 'データベースアクセス中にエラーが発生: ',
        'process_canceled'    => 'アップデート処理 %s はキャンセルされました。',
        'process_interrupted' => 'アップデート処理 %s は中断されました。',
        'process_completed'   => 'アップデート処理 %s は正常に終了しました!',
    );
    break;
case 'english':
case 'english_utf-8':
default:
    $_LANG_UPDATE = array(
        'title'               => 'Downloads Plugin Update',
        'submit'              => $LANG_ACCESS['submit'],
        'cancel'              => $LANG_ACCESS['cancel'],
        'description1'        => 'Downloads plugin is already up to date. There is no need to update.',
        'description2'        => 'Click the Submit button to start the process. (You cannot undo.)',
        'description_001'     => '#001: Respond to the new Configuration UI has been introduced with Geeklog version 1.8.0.',
        'dm_not_installed'    => 'Downloads Plugin are not installed or disabled.',
        'db_error'            => 'During a database access error occurred: ',
        'process_canceled'    => 'Update process %s was canceled.',
        'process_interrupted' => 'Update process %s was interrupted.',
        'process_completed'   => 'Update process %s was completed successfully!',
    );
    break;
}

if (!in_array('downloads', $_PLUGINS)) {
    $display = COM_showMessageText($_LANG_UPDATE['dm_not_installed'], $MESSAGE[40]);
    $display = DLM_createHTMLDocument($display, array('menu' => $MESSAGE[40]));
    COM_output($display);
    exit;
}

if (!SEC_hasRights('downloads.edit')) {
    $display = COM_showMessageText($MESSAGE[29], $MESSAGE[30]);
    $display = DLM_createHTMLDocument($display, array('menu' => $MESSAGE[30]));
    COM_accessLog("User {$_USER['username']} tried to illegally access "
                . "the downloads administration screen.");
    COM_output($display);
    exit;
}

function DLM_check_001()
{
    global $_CONF, $_TABLES, $_LANG_UPDATE;

    $retval = '';
    if (version_compare(VERSION, '1.8.0') >= 0) {
        $n = DB_getItem($_TABLES['conf_values'], 'COUNT(name)',
            "group_name = 'downloads' AND type = 'tab'");
        if ($n == 0) {
            $retval .= $_LANG_UPDATE['description_001'];
        }
    }

    return $retval;
}

function DLM_update_001()
{
    global $_CONF, $_LANG_UPDATE;

    $retval = '';
    $pnum = '#001';
    $desc = DLM_check_001();
    if (!empty($desc)) {
        require_once $_CONF['path'] . 'plugins/downloads/install_defaults.php';
        require_once $_CONF['path_system'] . 'classes/config.class.php';
        if (function_exists('COM_versionCompare')) {
            DLM_update_ConfValues_addTabs();
        }
        DLM_updateSortOrder();
        $msg = sprintf($_LANG_UPDATE['process_completed'], $pnum);
        $retval .= '<p style="color:green">' . $msg . '</p>' . LB;
    } else {
        $msg = sprintf($_LANG_UPDATE['process_canceled'], $pnum);
        $retval .= '<p style="color:red">' . $msg . '</p>' . LB;
    }

    return $retval;
}


// MAIN

$mode = (!empty($_POST['mode'])) ? COM_applyFilter($_POST['mode']) : '';
$display = '';
$action = $_CONF['site_admin_url'] . '/plugins/downloads/update.php';
$title = $_LANG_UPDATE['title'];
switch ($mode) {
    case $_LANG_UPDATE['submit']:
        $display .= '<h1>' . $title . '</h1>' . LB;
        $display .= DLM_update_001();
        $display = DLM_createHTMLDocument($display, array('menu' => $title));
        break;

    case $_LANG_UPDATE['cancel']:
        $display = COM_refresh($_CONF['site_url'] . '/index.php');
        break;

    default:
        $display .= '<h1>' . $title . '</h1>' . LB;
        $list = '';
        $desc = DLM_check_001();
        if (!empty($desc)) {
            $list .= '<li>' . $desc . '</li>' . LB;
        }

        if (empty($list)) {
            $display .= '<p>' . $_LANG_UPDATE['description1'] . '</p>' . LB;
        } else {
            $display .= '<ul>' . LB . $list . '</ul>' . LB;
            $display .= '<p>' . $_LANG_UPDATE['description2'] . '</p>' . LB;
        }
        $display .= '<form action="'. $action .'" method="post"><div>' . LB;
        if (!empty($list)) {
            $display .= '<input type="submit" name="mode" value="'
                      . $_LANG_UPDATE['submit'] . '" class="button"' . XHTML . '>' . LB;
        }
        $display .= '<input type="submit" name="mode" value="'
                  . $_LANG_UPDATE['cancel'] . '" class="button"' . XHTML . '>' . LB;
        $display .= '</div></form>' . LB;
        $display = DLM_createHTMLDocument($display, array('menu' => $title));
        break;
}
COM_output($display);
?>
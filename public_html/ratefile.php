<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/downloads/ratefile.php                                        |
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

use Geeklog\Input;

// The lowest and highest of the rating
define('RATING_LOWEST', 1);
define('RATING_HIGHEST', 10);

require_once '../lib-common.php';

if (!in_array('downloads', $_PLUGINS)) {
    COM_redirect($_CONF['site_url'] . '/index.php');
}

require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';

if (COM_isAnonUser() && ($_CONF['loginrequired'] == 1 || $_DLM_CONF['loginrequired'] == 1)) {
    $display = SEC_loginRequiredForm();
    $display = COM_createHTMLDocument($display);
    COM_output($display);
    exit;
}

$uid = (isset($_USER['uid'])) ? $_USER['uid'] : 1;

//if ($_POST['submit'] && SEC_checkToken()) {
if (isset($_POST['submit'])) {
    //Make sure only 1 anonymous from an IP in a single day.
    $anonwaitdays = 1;
    $ip = Input::server('REMOTE_ADDR', '');
    $lid = Input::fPost('lid');
    $rating = (int) Input::fPost('rating', RATING_LOWEST - 1);
    // Check if Rating is valid
    if (empty($rating) || ($rating < RATING_LOWEST) || ($rating > RATING_HIGHEST)) {
        echo DLM_showErrorMessage('norating');
        exit();
    }

    if ($uid != 1) {
        // Check if Download POSTER is voting (UNLESS Anonymous users allowed to post)
        $result = DB_query("SELECT owner_id FROM {$_TABLES['downloads']} WHERE lid='" . DB_escapeString($lid) . "'");
        while (list($ratinguserDB) = DB_fetchArray($result)) {
            if ($ratinguserDB == $uid) {
                echo DLM_showErrorMessage('cantvoteown');
                exit();
            }
        }

        // Check if REG user is trying to vote twice.
        $result = DB_query("SELECT ratinguser FROM {$_TABLES['downloadvotes']} WHERE lid='" . DB_escapeString($lid) . "'");
        while (list($ratinguserDB) = DB_fetchArray($result)) {
            if ($ratinguserDB == $uid) {
                echo DLM_showErrorMessage('voteonce');
                exit();
            }
        }
    }

    // Check if ANONYMOUS user is trying to vote more than once per day.
    if ($uid == 1){
        $yesterday = (time() - (86400 * $anonwaitdays));
        $result=DB_query("SELECT COUNT(*) FROM {$_TABLES['downloadvotes']} "
                       . "WHERE lid = '" . DB_escapeString($lid) . "' "
                       . "AND ratinguser = 1 AND ratinghostname = '$ip' AND ratingtimestamp > $yesterday");
        list($anonvotecount) = DB_fetchArray($result);
        if ($anonvotecount >= 1) {
            echo DLM_showErrorMessage('voteonce');
            exit();
        }
    }

    //All is well.  Add to Line Item Rate to DB.
    $datetime = time();
    DB_query("INSERT INTO {$_TABLES['downloadvotes']} "
           . "(lid, ratinguser, rating, ratinghostname, ratingtimestamp) "
           . "VALUES ('" . DB_escapeString($lid) . "', $uid, $rating, '$ip', $datetime)");
    //All is well.  Calculate Score & Add to Summary (for quick retrieval & sorting) to DB.
    DLM_updaterating($lid);
    echo PLG_afterSaveSwitch('home', '', 'downloads', 113);
    exit;
}

$lid = Input::fGet('lid');
$result = DB_query("SELECT title FROM {$_TABLES['downloads']} WHERE lid='" . DB_escapeString($lid) . "'");
list($title) = DB_fetchArray($result);
$title = DLM_htmlspecialchars($title);

$pagetitle = $LANG_DLM['plugin_name'];
$display = '';
$display .= COM_startBlock($LANG_DLM['plugin_name']);
$T = COM_newTemplate(CTL_plugin_templatePath('downloads'));
$T->set_file(array('t_vote' => 'vote.thtml'));
DLM_setDefaultTemplateVars($T);
$T->set_var('val_lid',          $lid);
$T->set_var('lang_file',        $LANG_DLM['file']);
$T->set_var('val_title',        $title);
$T->set_var('lang_ratefiletitle', $LANG_DLM['ratefiletitle']);
$T->set_var('lang_voteonce',    $LANG_DLM['voteonce']);
$T->set_var('lang_ratingscale', $LANG_DLM['ratingscale']);
$T->set_var('lang_beobjective', $LANG_DLM['beobjective']);
$T->set_var('lang_donotvote',   $LANG_DLM['donotvote']);

$option_list = '<option>--</option>' . PHP_EOL;
for ($i = RATING_HIGHEST; $i >= RATING_LOWEST; $i--) {
	$option_list .=  '<option value="' . $i . '">' . $i . '</option>' . PHP_EOL;
}

$T->set_var('option_list',      $option_list);
$T->set_var('lang_rateit',      $LANG_DLM['rateit']);
$T->set_var('lang_cancel',      $LANG_DLM['cancel']);
//    $T->set_var('gltoken_name',     CSRF_TOKEN);
//    $T->set_var('gltoken',          SEC_createToken());
$T->parse('output', 't_vote');
$display .= $T->finish($T->get_var('output'));
$display .= COM_endBlock();
$display = COM_createHTMLDocument($display, array('pagetitle' => $pagetitle));
COM_output($display);

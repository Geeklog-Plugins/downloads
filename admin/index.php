<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/admin/plugins/downloads/index.php                             |
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
    COM_accessLog("User {$_USER['username']} tried to illegally access the downloads administration screen.");
    COM_output($display);
    exit;
}

$installed_version = DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name = 'downloads'");
if (version_compare($installed_version, $_DLM_CONF['version']) < 0) {
    $display = COM_showMessageText($LANG_DLM['please_update']);
    $display = DLM_createHTMLDocument($display, array('pagetitle' => $LANG_DLM['manager']));
    COM_output($display);
    exit;
}

function listDownloads()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_DLM;

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $retval = '';

    $is_root_user = SEC_inGroup('Root');

    $admin_url = $_CONF['site_admin_url'] . '/plugins/downloads/index.php';

    $field_category = $LANG_DLM['category'];
    if (isset($_CONF['languages'])) {
        $field_category .= ' (' . $LANG_DLM['language'] . ')';
    }

    $header_arr   = array(
                    array('text' => $LANG_ADMIN['edit'],     'field' => 'edit',    'sort' => false),
                    array('text' => $LANG_ADMIN['title'],    'field' => 'title',   'sort' => true),
                    array('text' => $field_category,         'field' => 'cid',     'sort' => true),
                    array('text' => $LANG_DLM['ver'],        'field' => 'version', 'sort' => true),
                    array('text' => $LANG_DLM['size'],       'field' => 'size',    'sort' => true),
                    array('text' => $LANG_DLM['submitdate'], 'field' => 'date',    'sort' => true),
    );

    $defsort_arr  = array('field' => 'date', 'direction' => 'desc');

    $menu_arr = array();
    if ($is_root_user) {
        $menu_arr[] = array('url'  => $admin_url . '?op=listCategories',
                            'text' => $LANG_DLM['nav_categories']);

        $menu_arr[] = array('url'  => $admin_url . '?op=newCategory',
                            'text' => $LANG_DLM['nav_addcategory']);
    }

    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloadcategories']} WHERE cid != ''";
    list($count) = DB_fetchArray(DB_query($sql));
    if ($count > 0) {
        $menu_arr[] = array('url'  => $admin_url . '?op=uploadFile',
                            'text' => $LANG_DLM['nav_addfile']);
    }

    $menu_arr[] = array('url'  => $_CONF['site_admin_url'],
                        'text' => $LANG_ADMIN['admin_home']);

    $retval .= COM_startBlock($LANG_DLM['manager'], '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, 
                                ($is_root_user ? $LANG_DLM['instructions'] : $LANG_DLM['instructions2']),
                                plugin_geticon_downloads());

    $text_arr  = array('has_extras'     => true,
                       'form_url'       => $admin_url);

    $sql  = "SELECT lid, url, a.title, a.cid, date, version, size, "
          . "b.owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon "
          . "FROM {$_TABLES['downloads']} a "
          . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
          . "WHERE lid != '' "
          . COM_getPermSQL('AND', 0, 2, 'b');

    $query_arr = array('table'          => 'downloads',
                       'sql'            => $sql,
                       'query_fields'   => array('title'),
                       'default_filter' => '');

    $retval .= ADMIN_list('downloads', 'downloads_getListField_Files', $header_arr, $text_arr,
                           $query_arr, $defsort_arr);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}


function downloads_getListField_Files($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $MESSAGE, $DLM_CSRF_TOKEN;
    
    $retval = false;

    $access = SEC_hasAccess($A['owner_id'],$A['group_id'],$A['perm_owner'],
                            $A['perm_group'],$A['perm_members'],$A['perm_anon']);

    $token  = "&amp;" . CSRF_TOKEN . "=" . $DLM_CSRF_TOKEN;

    switch($fieldname) {
        case "edit":
            $retval = $LANG_ACCESS['readonly'];
            if ($access == 3) {
                $retval = "<div style=\"white-space:nowrap;\"><a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php"
                        . "?lid={$A['lid']}&amp;op=modify&amp;p=list\" title=\"{$LANG_ADMIN['edit']}\">{$icon_arr['edit']}</a>" . LB

                        . "<a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php"
                        . "?lid={$A['lid']}&amp;op=clone\" title=\"{$LANG_ADMIN['copy']}\">{$icon_arr['copy']}</a>" . LB;

                //$icon = $icon_arr['deleteitem'];
                $icon   = "<img src=\"{$_CONF['site_url']}/downloads/images/delete.png\" "
                        . "alt=\"\" title=\"{$LANG_ADMIN['delete']}\">";

                $retval .= "<a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php"
                        . "?lid={$A['lid']}&amp;op=delete"
                        . $token . "\" onclick=\"return confirm('{$MESSAGE[76]}');\">$icon</a></div>" . LB;
            }
            break;

        case "title":
            $url = COM_buildUrl($_CONF['site_url'] . '/downloads/index.php?id=' . $A['lid']);
            $retval = "<a href=\"$url\" title=\"{$A['url']}\">{$A['title']}</a>" . LB;
            break;

        case "cid":
            $retval = DB_getItem($_TABLES['downloadcategories'], 'title', "cid='" . addslashes($A['cid']) . "'");
            $retval .= getCatName_by_language($A['cid']);
            break;

        case "date":
            $retval = strftime('%Y-%m-%d', $A['date']); // Fixed format
            break;

        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}


/**
* Re-orders all category in steps of 10
*/
function DLM_reorderCategories()
{
    global $_TABLES, $mytree;

    $A = $mytree->getChildTreeArray(ROOTID, 'corder');
    foreach ($A as $B) {
        $corder += 10;
        if ($B['corder'] != $corder) {
            DB_query("UPDATE {$_TABLES['downloadcategories']} SET corder = $corder "
                   . "WHERE cid = '" . addslashes($B['cid']) . "'");
        }
    }
}


/**
* Move category UP and Down
*/
function DLM_moveCategory()
{
    global $_TABLES, $mytree;

    $cid   = addslashes(COM_applyFilter($_GET['cid']));
    $where = COM_applyFilter($_GET['where']);
    $corder = DB_getItem($_TABLES['downloadcategories'], 'corder', "cid = '$cid'");
    $pid    = DB_getItem($_TABLES['downloadcategories'], 'pid',    "cid = '$cid'");

    if ($where == 'up') {
        $A = $mytree->getChildTreeArray('', 'corder DESC');
        foreach ($A as $B) {
            $order = $B['corder'] - 1;
            if (($B['corder'] < $corder) && ($B['pid'] == $pid)) break;
        }
    } else {
        $A = $mytree->getChildTreeArray();
        foreach ($A as $B) {
            $order = $B['corder'] + 1;
            if (($B['corder'] > $corder) && ($B['pid'] == $pid)) break;
        }
    }

    DB_query("UPDATE {$_TABLES['downloadcategories']} SET corder = $order WHERE cid = '$cid'");
}

/**
* Enable and Disable menuitem
*/
function DLM_changeMenuitemStatus($itemenable)
{
    global $_TABLES;
    
    DB_query("UPDATE {$_TABLES['downloadcategories']} SET is_enabled = 0");
    foreach ($itemenable as $index => $value) {
        $index = COM_applyFilter($index, true);
        $order = $index * 10;
        DB_query("UPDATE {$_TABLES['downloadcategories']} SET is_enabled = 1 WHERE corder = $order");
    }
}

/**
* 
*/
function DLM_getCatLevel($cid)
{
    global $_TABLES;

    $pid = DB_getItem($_TABLES['downloadcategories'], 'pid', "cid = '" . addslashes($cid) . "'");
    if ($pid != ROOTID) {
        return 1 + DLM_getCatLevel($pid);
    }
    return 0;
}

function listCategories()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_DLM;

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $retval = '';

    DLM_reorderCategories();

    $field_title = $LANG_ADMIN['title'];
    if (isset($_CONF['languages'])) {
        $field_title .= ' (' . $LANG_DLM['language'] . ')';
    }

    $header_arr   = array(
                    array('text' => $LANG_ADMIN['edit'],  'field' => 'edit',   'sort' => false),
                    array('text' => $LANG_DLM['corder'],  'field' => 'corder', 'sort' => true),
                    array('text' => $field_title,         'field' => 'title',  'sort' => true),
                    array('text' => $LANG_DLM['catid'],   'field' => 'cid',    'sort' => true),
    );

    $defsort_arr = array('field' => 'corder', 'direction' => 'asc');

    $menu_arr = array (
                    array('url'  => $_CONF['site_admin_url'] . '/plugins/downloads/index.php',
                          'text' => $LANG_DLM['nav_files']),

                    array('url'  => $_CONF['site_admin_url'] . '/plugins/downloads/index.php?op=newCategory',
                          'text' => $LANG_DLM['nav_addcategory']),
    );

    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloadcategories']} WHERE cid != ''";
    list($count) = DB_fetchArray(DB_query($sql));
    if ($count > 0) {
        $menu_arr = array_merge($menu_arr, array(
                        array('url'  => $_CONF['site_admin_url'] . '/plugins/downloads/index.php?op=uploadFile',
                              'text' => $LANG_DLM['nav_addfile']),
        ));
    }
    $menu_arr = array_merge($menu_arr, array(
                    array('url'  => $_CONF['site_admin_url'],
                          'text' => $LANG_ADMIN['admin_home'])
    ));

    $retval .= COM_startBlock($LANG_DLM['manager'], '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_DLM['instructions'], plugin_geticon_downloads());

    $text_arr = array('has_extras' => true,
                      'form_url'   => $_CONF['site_admin_url'] . "/plugins/downloads/index.php?op=listCategories");

    $sql  = "SELECT * FROM {$_TABLES['downloadcategories']} WHERE cid != '' "
          . COM_getPermSQL('AND', 0, 2);

    $query_arr = array('table'          => 'downloadcategories',
                       'sql'            => $sql,
                       'query_fields'   => array('title'),
                       'default_filter' => '');

    $retval .= ADMIN_list("downloadcategories", "downloads_getListField_Categories", $header_arr, $text_arr,
                           $query_arr, $defsort_arr);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function downloads_getListField_Categories($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_ADMIN, $MESSAGE, $LANG_DLM, $DLM_CSRF_TOKEN;
    
    $retval = false;

    $token  = "&amp;" . CSRF_TOKEN . "=" . $DLM_CSRF_TOKEN;

    switch($fieldname) {
        case 'edit':
            $retval = "<a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php"
                    . "?cid={$A['cid']}&amp;op=modCat\" title=\"{$LANG_ADMIN['edit']}\">{$icon_arr['edit']}</a>" . LB

                    . "<a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php"
                    . "?cid={$A['cid']}&amp;op=cloneCat\" title=\"{$LANG_ADMIN['copy']}\">{$icon_arr['copy']}</a>" . LB;

            //$icon = $icon_arr['deleteitem'];
            $icon   = "<img src=\"{$_CONF['site_url']}/downloads/images/delete.png\" "
                    . "alt=\"\" title=\"{$LANG_ADMIN['delete']}\">";

            $retval .= "<a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php"
                    . "?cid={$A['cid']}&amp;op=delCat"
                    . $token . "\" onclick=\"return confirm('{$MESSAGE[76]}');\">$icon</a>" . LB;
            break;

        case 'title':
            $retval = $fieldvalue . getCatName_by_language($A['cid']);
            $switch = ($A['is_enabled'] == 1) ? UC_CHECKED : '';
            $val = ($A['is_enabled'] == 1) ? 1 : 0;
            $order = intval($A['corder'] / 10);
            $retval = "<input type=\"checkbox\" name=\"itemenable[$order]\" onclick=\"submit()\" value=\"$val\" $switch>" . $retval;
            $retval .= "<input type=\"hidden\" name=\"" . CSRF_TOKEN . "\" value=\"$DLM_CSRF_TOKEN\"" . XHTML . ">";
            break;

        case 'corder':
            $iconup  = "<img src=\"{$_CONF['site_url']}/downloads/images/arrow-up.png\" "
                     . "alt=\"{$LANG_DLM['move_up']}\" title=\"{$LANG_DLM['move_up']}\">";

            $retval .= "<a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php"
                     . "?op=move&amp;cid={$A['cid']}&amp;where=up" . $token . "\">$iconup</a>";

            $icondn  = "<img src=\"{$_CONF['site_url']}/downloads/images/arrow-dn.png\" "
                     . "alt=\"{$LANG_DLM['move_down']}\" title=\"{$LANG_DLM['move_down']}\">";

            $retval .= "<a href=\"{$_CONF['site_admin_url']}/plugins/downloads/index.php?"
                     . "op=move&amp;cid={$A['cid']}&amp;where=dn" . $token . "\">$icondn</a>";

            $retval .= "&nbsp;";
            $retval .= $A['corder'];
            break;

        case 'cid':
            $level = DLM_getCatLevel($A['cid']);
            $retval = '';
            for ($i = 0 ; $i < $level; $i++) {
                $retval .= '&nbsp;&nbsp;&nbsp;';
            }
            $retval .= $A['cid'];
            break;

        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}


function getCatName_by_language($catid, $name='')
{
    global $_CONF;
    if (!isset($_CONF['languages'])) return $name;
    foreach ($_CONF['languages'] as $langid => $lang) {
        $len = strlen($langid) + 1;
        if (substr($catid, -$len) == ('_' . $langid)) {
            return $name . ' (' . $lang . ')';
        }
    }
    return $name;
}

// Update downloads plugin if necessary
function DLM_updatePlugin()
{
    global $_CONF, $_TABLES, $LANG_DLM, $LANG32;

    $retval = '';

    $need = false;
    if (version_compare(VERSION, '1.8.0') >= 0) {
        $n = DB_getItem($_TABLES['conf_values'], 'COUNT(name)',
            "group_name = 'downloads' AND type = 'tab'");
        if ($n == 0) {
            $need = true;
        }
    }
    if ($need == true) {
        $content = $LANG32[42];
        $imgurl = $_CONF['layout_url'] . '/images/update.png';
        $attr = array('style' => 'vertical-align: middle;float: none');
        $img = COM_createImage($imgurl, $content, $attr);
        $url = $_CONF['site_admin_url'] . '/plugins/downloads/update.php';
        $link = COM_createLink($img . $content , $url, array('title' => $content));
        $msg = $LANG_DLM['please_update'] . ' ' . $link;
        $retval .= COM_showMessageText($msg);
    }

    return $retval;
}

// Show message
function showMessage()
{
    $msg = COM_applyFilter($_REQUEST['msg'], true);
    return ($msg > 0) ? COM_showMessage($msg, 'downloads') : '';
}

// MAIN

$op = COM_applyFilter($_REQUEST['op']);
$_page = COM_applyFilter($_REQUEST['page']);
$listing_cid = COM_applyFilter($_REQUEST['listing_cid']);
$display = '';

require_once $_CONF['path'] . 'plugins/downloads/include/gltree.class.php';
$mytree = new GLTree($_TABLES['downloadcategories'], 'cid', 'pid', 'title', '', ROOTID); // Not set $_DLM_CONF['lang_id']
$mytree->setRoot($LANG_DLM['main']);

$mode = (!empty($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';
$cid  = (!empty($_REQUEST['cid'])) ? COM_sanitizeID(trim($_REQUEST['cid'])) : '';
$lid  = (!empty($_REQUEST['lid'])) ? COM_sanitizeID(trim($_REQUEST['lid'])) : '';

$itemenable = array();
$itemenable = $_POST['itemenable'];
if (isset($itemenable) && SEC_checkToken()) {
    DLM_changeMenuitemStatus($itemenable);
}

$op = ($mode == 'editsubmission') ? $mode : $op;
$op = ($mode == 'edit') ? 'uploadFile' : $op;

if (in_array($op, array('newCategory', 'modCat', 'cloneCat', 'addCategory', 'delCat', 'saveCategory'))) {

    // only users who belong to the Root group can access
    if (!SEC_inGroup('Root')) {
        $display = COM_showMessageText($MESSAGE[29], $MESSAGE[30]);
        $display = DLM_createHTMLDocument($display, array('pagetitle' => $MESSAGE[30]));
        COM_accessLog("User {$_USER['username']} tried to illegally access the downloads administration screen.");
        COM_output($display);
        exit;
    }

    require_once $_CONF['path'] . 'plugins/downloads/include/category.class.php';
    $dlcat = new DLCategory();

    $op = ($mode == $LANG_DLM['cancel']) ? 'listCategories' : $op;
}
if (in_array($op, array('uploadFile', 'modify', 'clone', 'editsubmission', 'add', 'delete', 'saveDownload', 'approve'))) {
    require_once $_CONF['path'] . 'plugins/downloads/include/gltext.class.php';
    require_once $_CONF['path'] . 'plugins/downloads/include/download.class.php';
    $dldl = new DLDownload();
    $dldl->initCatTree($mytree);

    if ($mode == $LANG_DLM['cancel']) {
        switch ($_page) {
            case 'item':
                $url = "{$_CONF['site_url']}/downloads/index.php?id=$lid";
                echo PLG_afterSaveSwitch('item', $url, 'downloads');
                exit;
                break;
            case 'flist':
                $url = "{$_CONF['site_url']}/downloads/index.php";
                if (!empty($listing_cid) && $listing_cid != ROOTID) {
                    $url .= '?cid=' . $listing_cid;
                }
                echo PLG_afterSaveSwitch('item', $url, 'downloads');
                exit;
                break;
        }
        $op = 'listDownloads';
    }

    if ($mode == $LANG_DLM['preview']) {
        $editor_mode = (!empty($_POST['editor_mode'])) ? COM_applyFilter($_POST['editor_mode']) : '';
        if (in_array($editor_mode, array('edit', 'create', 'clone', 'editsubmission'))) {
            $dldl->showPreview($editor_mode);
        }
        $op = 'listDownloads';
    }
}

switch ($op) {
    case "uploadFile":
        $display = $dldl->showEditor('create');
        break;

    case "modify":
        $display = $dldl->showEditor('edit');
        break;

    case "clone":
        $display = $dldl->showEditor('clone');
        break;

    case "editsubmission":
        $display = $dldl->showEditor('editsubmission');
        break;

    case "add":
        if ($mode == $LANG_DLM['add'] && SEC_checkToken()) {
            $display = $dldl->addDownload();
        }
        break;

    case "delete":
        if (SEC_checkToken()) {
            $display = $dldl->delDownload($lid);
        }
        break;

    case "saveDownload":
        if ($mode == $LANG_DLM['submit'] && SEC_checkToken()) {
            $display = $dldl->saveDownload();
        }
        if ($mode == $LANG_DLM['delete'] && SEC_checkToken()) {
            $display = $dldl->delDownload();
        }
        break;

    case "approve":
        if ($mode == $LANG_DLM['approve']) {
            $display = $dldl->approve();
        }
        if ($mode == $LANG_DLM['delete']) {
            $display = $dldl->delNewDownload();
        }
        break;

    case "newCategory":
        $display = $dlcat->showEditor($cid, 'create');
        break;

    case "modCat":
        $display = $dlcat->showEditor($cid, 'edit');
        break;

    case "cloneCat":
        $display = $dlcat->showEditor($cid, 'clone');
        break;

    case "addCategory":
        if ($mode == $LANG_DLM['add'] || $mode == $LANG_DLM['save']) {
            $display = $dlcat->addCategory();
        }
        break;

    case "delCat":
        if (SEC_checkToken()) {
            $display = $dlcat->deleteCategory($cid);
        }
        break;

    case "saveCategory":
        if ($mode == $LANG_DLM['save'] && SEC_checkToken()) {
            $display = $dlcat->saveCategory();
        }
        if ($mode == $LANG_DLM['delete'] && SEC_checkToken()) {
            $display = $dlcat->deleteCategory($cid);
        }
        break;

    case "listCategories":
        $DLM_CSRF_TOKEN = SEC_createToken();
        $display .= showMessage();
        $display .= listCategories();
        $display = DLM_createHTMLDocument($display, array('pagetitle' => $LANG_DLM['manager']));
        break;

    case "move":
        DLM_moveCategory();
        $DLM_CSRF_TOKEN = SEC_createToken();
        $display .= showMessage();
        $display .= listCategories();
        $display = DLM_createHTMLDocument($display, array('pagetitle' => $LANG_DLM['manager']));
        break;

    case "listDownloads":
    default:
        $DLM_CSRF_TOKEN = SEC_createToken();
        $display .= DLM_updatePlugin();
        $display .= showMessage();
        $display .= listDownloads();
        $display = DLM_createHTMLDocument($display, array('pagetitle' => $LANG_DLM['manager']));
        break;
}

COM_output($display);
?>
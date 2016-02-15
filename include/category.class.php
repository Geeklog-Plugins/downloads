<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/include/category.class.php                              |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'category.class.php') !== false) {
    die('This file can not be used on its own.');
}

class DLCategory
{
    /**
    * Vars
    *
    * @access  private
    */
    var $_cid;
    var $_pid;
    var $_title;
    var $_imgurl;
    var $_corder;
    var $_is_enabled;
    var $_owner_id;
    var $_group_id;
    var $_perm_owner;
    var $_perm_group;
    var $_perm_members;
    var $_perm_anon;
    var $_old_cid;
    var $_imgurlold;

    var $_deleteimg;

    var $_editor_mode;
    var $_retry;
    var $_errno;

    /**
    * Constructor
    *
    * @access  public
    */
    function DLCategory()
    {
        $this->_errno = array();
        $this->_retry = false;
    }

    /**
    * Create category id
    *
    * @access  private
    */
    function _createID($str = 'category_')
    {
        return $str . uniqid();
    }

    function _loadFromArgs(&$array)
    {
        $corder = trim($array['corder']);

        $this->_owner_id   = COM_applyFilter($array['owner_id'], true);
        $this->_group_id   = COM_applyFilter($array['group_id'], true);
        $this->_cid        = COM_applyFilter(trim($array['cid']));
        $this->_old_cid    = COM_applyFilter(trim($array['old_cid']));
        $this->_pid        = COM_applyFilter(trim($array['pid']));
        $this->_corder     = empty($corder) ? 0 : COM_applyFilter($corder, true);
        $this->_imgurl     = COM_applyFilter($array['imgurl']);
        $this->_imgurlold  = COM_applyFilter($array['imgurlold']);
        $this->_title      = COM_checkHTML(COM_checkWords(trim($array['title'])));
        $this->_is_enabled = ($array['is_enabled'] == 'on') ? 1 : 0;
        $this->_deleteimg  = ($array['deleteimg'] == 'on') ? 1 : 0;

        // Convert array values to numeric permission values
        list($this->_perm_owner, $this->_perm_group, $this->_perm_members, $this->_perm_anon)
               = SEC_getPermissionValues($array['perm_owner'],   $array['perm_group'],
                                         $array['perm_members'], $array['perm_anon']);

        $this->_editor_mode = COM_applyFilter($array['editor_mode']);
    }

    function _loadFromDatabase($cid)
    {
        global $_TABLES;
        
        $result = DB_query("SELECT * FROM {$_TABLES['downloadcategories']} WHERE cid='" . addslashes($cid) . "'");
        $A = DB_fetchArray($result);
        foreach ($A as $key => $val) {
            $this->{'_' . $key} = $val;
        }
        $this->_title  = DLM_htmlspecialchars($this->_title);
        $this->_imgurl = DLM_htmlspecialchars($this->_imgurl);
        $this->_old_cid = $this->_cid;
        $this->_imgurlold = $this->_imgurl;
    }

    function _initVars()
    {
        global $_USER, $_GROUPS, $_DLM_CONF, $mytree;

        $this->_cid        = $this->_createID();
        $this->_old_cid    = '';
        $this->_pid        = $mytree->getRootid();
        $this->_is_enabled = 1;
        $this->_title      = '';
        $this->_imgurl     = '';
        $this->_corder     = 0;
        $this->_owner_id   = $_USER['uid'];
        if (isset($_GROUPS['Downloads Admin'])) {
            $this->_group_id = $_GROUPS['Downloads Admin'];
        } else {
            $this->_group_id = SEC_getFeatureGroup('downloads.edit');
        }
        SEC_setDefaultPermissions($A, $_DLM_CONF['default_permissions']);
        foreach ($A as $key => $val) {
            $this->{'_' . $key} = $val;
        }
    }

    function _checkHasAccess()
    {
        global $_USER, $LANG_DLM;

        // only users who belong to the Root group can full access
        if (!SEC_inGroup('Root')) {
            // deny access
            COM_accessLog("User {$_USER['username']} tried illegally to edit category $this->_cid.");
            $display = COM_showMessage(6, 'downloads');
            $display = DLM_createHTMLDocument($display, array('pagetitle' => $LANG_DLM['manager']));
            COM_output($display);
            exit;
        }
    }

    function _showMessage()
    {
        if (!empty($this->_errno)) {
            return DLM_showMessageArray($this->_errno);
        }
    }

    /**
    * Show the category editor
    */
    function showEditor($cid, $mode='edit')
    {
        global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $_DLM_CONF, $LANG_DLM, $mytree;

        $retval = '';

        if (!empty($this->_editor_mode)) {
            $mode = $this->_editor_mode;
        } else {
            $this->_editor_mode = $mode;
        }

        if ($mode == 'edit' || $mode == 'clone') {
            if ($this->_retry == true) {
                $this->_loadFromArgs($_POST);
            } else {
                $this->_loadFromDatabase($cid);
            }
        }
        if ($mode == 'clone') {
            $this->_cid = $this->_createID($this->_cid . '_');
            $this->_old_cid = $this->_cid;
        }

        if ($mode == 'create') {
            if ($this->_retry == true) {
                $this->_loadFromArgs($_POST);
            } else {
                $this->_initVars();
            }
        }

        $this->_checkHasAccess();

        $blocktitle = ($mode == 'edit') ? $LANG_DLM['modcat'] : $LANG_DLM['addcat'];
        $retval .= $this->_showMessage();
        $retval .= COM_startBlock($blocktitle, '', COM_getBlockTemplate('_admin_block', 'header'));

        $T = new Template($_DLM_CONF['path_layout']);
        $T->set_file(array(
            't_modcategory'         => 'admin_modcategory.thtml',
            't_admin_access'        => 'admin_access.thtml',
            't_admin_submit_delete' => 'admin_submit_delete.thtml',
        ));
        DLM_setDefaultTemplateVars($T);
        $lang = array('title', 'imgurlmain', 'parent', 'save', 'delete', 'cancel',
                      'confirm_delete', 'topic', 'catid', 'is_enabled', 'corder', 'upload');
        foreach ($lang as $v) $T->set_var('lang_' . $v, $LANG_DLM[$v]);

        $T->set_var('preview',         $this->_makeForm_category_image());
        $T->set_var('imgurl',          $this->_imgurl);
        $T->set_var('imgurlold',       $this->_imgurl);
        $T->set_var('cid',             $this->_cid);
        $T->set_var('old_cid',         $this->_old_cid);
        $T->set_var('corder',          $this->_corder);
        $T->set_var('title',           $this->_title);
        $T->set_var('op',              ($mode == 'edit') ? 'saveCategory' : 'addCategory');
        $T->set_var('delete_disabled', ($mode == 'edit') ? ''             : UC_DISABLED);
        $T->set_var('val_is_enabled',  ($this->_is_enabled == 1) ? UC_CHECKED : '');
        $T->set_var('selparents',      $mytree->makeSelBox('title', 'corder', $this->_pid, 1, 'pid', '', $this->_cid));

        // user access info
        $lang = array('accessrights', 'owner', 'group', 'permissions', 'permissionskey', 'permmsg');
        foreach ($lang as $v) $T->set_var('lang_' . $v, $LANG_ACCESS[$v]);
        $this->_owner_id = (int) $this->_owner_id;
        $T->set_var('owner_username',      DB_getItem($_TABLES['users'], 'username', "uid = $this->_owner_id"));
        $ownername = COM_getDisplayName($this->_owner_id);
        $T->set_var('owner_name',         $ownername);
        $T->set_var('owner',              $ownername);
        $T->set_var('ownerid',            $this->_owner_id);
        $T->set_var('group_dropdown',     SEC_getGroupDropdown($this->_group_id, 3));
        $T->set_var('permissions_editor', SEC_getPermissionsHTML($this->_perm_owner, $this->_perm_group,
                                                                 $this->_perm_members, $this->_perm_anon));

        $hidden_values = $this->_makeForm_hidden('editor_mode', $this->_editor_mode);
        $T->set_var('hidden_values', $hidden_values);

        $T->parse('admin_access', 't_admin_access');

        if ($mode == 'edit') {
            $T->parse('admin_submit_delete', 't_admin_submit_delete');
        }

        $T->set_var('gltoken_name', CSRF_TOKEN);
        $T->set_var('gltoken', SEC_createToken());

        $T->parse('output', 't_modcategory');
        $retval .= $T->finish($T->get_var('output'));
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
        $retval = DLM_createHTMLDocument($retval, array('pagetitle' => $blocktitle));

        return $retval;
    }

    function _makeForm_category_image()
    {
        global $_CONF, $_DLM_CONF, $LANG_DLM;

        $imgurl = $_CONF['site_url'] . '/downloads/images/download.png';
        $imgpath = $_CONF['path_html'] . 'downloads/images/download.png';
        $delform = false;
        $safename = DLM_encodeFileName($this->_imgurl);
        if (!empty($this->_imgurl) and file_exists($_DLM_CONF['path_snapcat'] . $safename)) {
            $imgurl = $_DLM_CONF['snapcat_url'] . '/' . $safename;
            $imgpath = $_DLM_CONF['path_snapcat'] . $safename;
            $delform = true;
        }
        list($width, $height) = getimagesize($imgpath);
        if (!empty($width) and !empty($height)) {
            $newwidth  = $_DLM_CONF['download_shotwidth'];
            $newheight = intval($height * $_DLM_CONF['download_shotwidth'] / $width);
            $sizeattributes = 'width="' . $newwidth . '" height="' . $newheight . '"';
        }
        $preview = '<img src="' . $imgurl . '" alt="category image" ' . $sizeattributes . XHTML . '>';
        if ($delform) $preview .= LB . '<input type="checkbox" name="deleteimg"' . XHTML . '>&nbsp;' . $LANG_DLM['delete'];

        return $preview;
    }

    function _makeForm_hidden($name, $value)
    {
        $indent = '            ';
        return $indent . '<input type="hidden" name="' . $name . '" value="' . $value . '"' . XHTML . '>' . LB;
    }

    // Validate the input values
    function _validate()
    {
        global $_TABLES;

        if (empty($this->_title)) {
            $this->_errno[] = '1101';
        }
        if (empty($this->_cid)) {
            $this->_errno[] = '1301';
        } else {
            if ($this->_cid != $this->_old_cid) {
                $count = DB_count($_TABLES['downloadcategories'], 'cid', addslashes($this->_cid));
                if ($count > 0) {
                    $this->_errno[] = '1302';
                }
            }
            if ($this->_cid != COM_sanitizeID($this->_cid)) {
                $this->_errno[] = '1303';
            }
        }
        if (!empty($this->_errno)) {
            $this->_retry = true;
            $this->_reedit('showEditor', array($this->_cid, $this->_editor_mode));
        }
    }

    function _createFilename($name)
    {
        global $_TABLES;

        $name = str_replace(' ', '_', $name);
        $parts = pathinfo($name);
        $extension = $parts['extension'];
        $filename  = $parts['filename'];
        $count = DB_count($_TABLES['downloadcategories'], 'imgurl', addslashes($name));
        $i = 1;
        while ($count > 0) {
            $name = $filename . "_$i." . $extension;
            $count = DB_count($_TABLES['downloadcategories'], 'imgurl', addslashes($name));
            $i++;
        }
        return $name;
    }

    // upload or delete category image
    function _uploadImage()
    {
        global $_TABLES, $_DLM_CONF;

        $newimage_name = COM_applyFilter($_FILES['imgurl']['name']);
        if (!empty($newimage_name)) {
            $name = $this->_createFilename($newimage_name);
            if (DLM_uploadNewFile($_FILES['imgurl'], $_DLM_CONF['path_snapcat'], $name)) {
                $this->_imgurl = $name;
            }
        } else if ($this->_deleteimg) {
            $this->_imgurl = '';
        }
    }

    function addCategory()
    {
        global $_CONF, $_TABLES, $_DLM_CONF;

        $this->_loadFromArgs($_POST);
        $this->_validate();
        $this->_uploadImage();

        $cid          = addslashes($this->_cid);
        $old_cid      = addslashes($this->_old_cid);
        $pid          = addslashes($this->_pid);
        $title        = addslashes($this->_title);
        $imgurl       = addslashes($this->_imgurl);
        $corder       = (int) $this->_corder;
        $is_enabled   = (int) $this->_is_enabled;
        $owner_id     = (int) $this->_owner_id;
        $group_id     = (int) $this->_group_id;
        $perm_owner   = (int) $this->_perm_owner;
        $perm_group   = (int) $this->_perm_group;
        $perm_members = (int) $this->_perm_members;
        $perm_anon    = (int) $this->_perm_anon;

        DB_query("INSERT INTO {$_TABLES['downloadcategories']} "

               . "(cid, pid, title, imgurl, corder, is_enabled, owner_id, group_id, "
               . "perm_owner, perm_group, perm_members, perm_anon) "

               . "VALUES ('$cid', '$pid', '$title', '$imgurl', $corder, $is_enabled, $owner_id, $group_id, "
               . "$perm_owner, $perm_group, $perm_members, $perm_anon)");

        return PLG_afterSaveSwitch('item',
                    "{$_CONF['site_admin_url']}/plugins/downloads/index.php?op=listCategories",
                    'downloads', 106);
    }

    function saveCategory()
    {
        global $_CONF, $_TABLES, $_DLM_CONF;

        $this->_loadFromArgs($_POST);
        $this->_validate();
        $this->_uploadImage();

        $cid          = addslashes($this->_cid);
        $old_cid      = addslashes($this->_old_cid);
        $pid          = addslashes($this->_pid);
        $title        = addslashes($this->_title);
        $imgurl       = addslashes($this->_imgurl);
        $corder       = (int) $this->_corder;
        $is_enabled   = (int) $this->_is_enabled;
        $owner_id     = (int) $this->_owner_id;
        $group_id     = (int) $this->_group_id;
        $perm_owner   = (int) $this->_perm_owner;
        $perm_group   = (int) $this->_perm_group;
        $perm_members = (int) $this->_perm_members;
        $perm_anon    = (int) $this->_perm_anon;

        DB_query("UPDATE {$_TABLES['downloadcategories']} "
               . "SET cid='$cid', pid='$pid', title='$title', "
               . "imgurl='$imgurl', corder=$corder, is_enabled=$is_enabled, "
               . "owner_id=$owner_id, group_id=$group_id, perm_owner=$perm_owner, "
               . "perm_group=$perm_group, perm_members=$perm_members, perm_anon=$perm_anon "
               . "WHERE cid='$old_cid'");

        if ($cid != $old_cid && !empty($old_cid)) {
            DB_query("UPDATE {$_TABLES['downloadcategories']} SET pid='$cid' WHERE pid='$old_cid'");
            DB_query("UPDATE {$_TABLES['downloads']}          SET cid='$cid' WHERE cid='$old_cid'");
            DB_query("UPDATE {$_TABLES['downloadsubmission']} SET cid='$cid' WHERE cid='$old_cid'");
        }

        $this->_unlinkCatImage($this->_imgurlold);

        return PLG_afterSaveSwitch('item',
                    "{$_CONF['site_admin_url']}/plugins/downloads/index.php?op=listCategories",
                    'downloads', 101);
    }

    function deleteCategory($cid)
    {
        global $_CONF, $mytree;

        $this->_loadFromDatabase($cid);
        $this->_checkHasAccess();

        //get all subcategories under the specified category
        $arr = $mytree->getAllChildId($cid);
        for ($i=0; $i<sizeof($arr); $i++) {
            $this->_deleteFile($arr[$i]);
        }
        $this->_deleteFile($cid);

        return PLG_afterSaveSwitch('item',
                    "{$_CONF['site_admin_url']}/plugins/downloads/index.php?op=listCategories",
                    'downloads', 107);
    }

    function _deleteFile($cid)
    {
        global $_TABLES, $_DLM_CONF;

        $cid = addslashes($cid);
        //all subcategory and associated data are deleted, now delete category data and its associated data
        $result = DB_query("SELECT lid, url, logourl, secret_id FROM {$_TABLES['downloads']} WHERE cid='$cid'");
        while (list($lid, $url, $logourl, $secret_id)= DB_fetchArray($result)) {
            $lid = addslashes($lid);
            DB_query("DELETE FROM {$_TABLES['downloadvotes']} WHERE lid='$lid'");
            DB_query("DELETE FROM {$_TABLES['downloads']}     WHERE lid='$lid'");
            $this->_unlinkDlFile($url, $secret_id);
            $this->_unlinkCatImage($logourl);
            $this->_unlinkTnImage($logourl);
        }
        $catimage = DB_getItem($_TABLES['downloadcategories'], 'imgurl', "cid='$cid'");
        DB_query("DELETE FROM {$_TABLES['downloadcategories']} WHERE cid='$cid'");
        $this->_unlinkCatImage($catimage);
    }

    function _unlinkDlFile($name, $secret_id)
    {
        global $_DLM_CONF;

        if (empty($name)) return;
        $target = $_DLM_CONF['path_filestore'] . $secret_id . '_' . DLM_encodeFileName($name);
        $this->_unlink($target);
    }

    function _unlinkSnapImage($name)
    {
        global $_TABLES, $_DLM_CONF;

        if (empty($name)) return;
        $target = $_DLM_CONF['path_snapstore'] . DLM_encodeFileName($name);
        $count = DB_count($_TABLES['downloads'], 'logourl', addslashes($name));
        if ($count == 0) $this->_unlink($target);
    }

    function _unlinkTnImage($name)
    {
        global $_TABLES, $_DLM_CONF;

        if (empty($name)) return;
        $target = $_DLM_CONF['path_tnstore'] . DLM_encodeFileName($name);
        $target = DLM_changeFileExt($target, $_DLM_CONF['tnimage_format']);
        $count = DB_count($_TABLES['downloads'], 'logourl', addslashes($name));
        if ($count == 0) $this->_unlink($target);
    }

    function _unlinkCatImage($name)
    {
        global $_TABLES, $_DLM_CONF;

        if (empty($name)) return;
        $target = $_DLM_CONF['path_snapcat'] . DLM_encodeFileName($name);
        $count = DB_count($_TABLES['downloadcategories'], 'imgurl', addslashes($name));
        if ($count == 0) $this->_unlink($target);
    }

    function _unlink($path)
    {
        if (!empty($path) && file_exists($path) && !is_dir($path)) {
            return @unlink($path);
        }
        return false;
    }

    function _reedit($method, $args = array())
    {
        $display = '';
        if (method_exists($this, $method)) {
            switch (count($args)) {
            case 0:
                $display = $this->$method();
                break;
            case 1:
                $display = $this->$method($args[0]);
                break;
            case 2:
                $display = $this->$method($args[0], $args[1]);
                break;
            case 3:
                $display = $this->$method($args[0], $args[1], $args[2]);
                break;
            default:
                $display = '';
                break;
            }
        }
        COM_output($display);
        exit;
    }
}

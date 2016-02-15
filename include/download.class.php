<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/include/download.class.php                              |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'download.class.php') !== false) {
    die('This file can not be used on its own.');
}

require_once $_CONF['path'] . 'plugins/downloads/include/gltext.class.php';

class DLDownload
{
    /**
    * Vars
    *
    * @access  private
    */
    var $_lid;
    var $_cid;
    var $_title;
    var $_url;
    var $_homepage;
    var $_version;
    var $_size;
    var $_secret_id;
    var $_md5;
    var $_logourl;
    var $_mg_autotag;
    var $_date;
    var $_commentcode;
    var $_project;
    var $_description;
    var $_detail;
    var $_text_version;
    var $_owner_id;
    var $_postmode;
    var $_is_released;
    var $_is_listing;
    var $_createddate;
    var $_tags;

    var $_year;
    var $_month;
    var $_day;
    var $_hour;
    var $_minute;
    var $_second;
    var $_old_lid;
    var $_old_date;

    var $_cat_tree;

    var $_editor_mode;
    var $_retry;
    var $_errno;
    var $_page; // previous page
    var $_listing_cid; // previous listing page category

    /**
    * Constructor
    *
    * @access  public
    */
    function DLDownload()
    {
        $this->_errno = array();
        $this->_retry = false;
        $this->_page = 'admin';
        $this->_listing_cid = ROOTID;
    }

    function initCatTree(&$obj = NULL)
    {
        global $_CONF, $_TABLES, $_DLM_CONF, $LANG_DLM;

        if (is_object($obj) && get_class($obj) == 'GLTree') {
            $this->_cat_tree = $obj;
        } else {
            if (is_object($this->_cat_tree) && get_class($this->_cat_tree) == 'GLTree') {
                return;
            }
            require_once $_CONF['path'] . 'plugins/downloads/include/gltree.class.php';
            $this->_cat_tree = new GLTree($_TABLES['downloadcategories'], 'cid', 'pid', 'title', '', ROOTID);
       //            $mytree = new GLTree($_TABLES['downloadcategories'], 'cid', 'pid', 'title', COM_getPermSQL('AND'), ROOTID, $_DLM_CONF['lang_id']);

            $this->_cat_tree->setRoot($LANG_DLM['main']);
        }
    }

    /**
    * Create download id
    */
    function _createID($str = 'file_')
    {
        return $str . uniqid();
    }

    function _createSecretID()
    {
        return md5(uniqid());
    }

    function _loadFromArgs(&$array)
    {
        global $_CONF;

        $this->_url          = COM_applyFilter(trim($array['url']));
        $this->_postmode     = COM_applyFilter($array['postmode']);
        $this->_version      = COM_applyFilter($array['version']);
        $this->_commentcode  = COM_applyFilter($array['commentcode'], true);
        $this->_is_released  = COM_applyFilter($array['is_released'], true);
        $this->_is_listing   = COM_applyFilter($array['is_listing'],  true);
        $this->_year         = COM_applyFilter($array['release_year'],   true);
        $this->_month        = COM_applyFilter($array['release_month'],  true);
        $this->_day          = COM_applyFilter($array['release_day'],    true);
        $this->_hour         = COM_applyFilter($array['release_hour'],   true);
        $this->_minute       = COM_applyFilter($array['release_minute'], true);
        $this->_second       = COM_applyFilter($array['release_second'], true);
        $this->_owner_id     = COM_applyFilter($array['owner_id'], true);
        $this->_cid          = COM_applyFilter(trim($array['cid']));
        $this->_lid          = COM_applyFilter(trim($array['lid']));
        $this->_old_lid      = COM_applyFilter(trim($array['old_lid']));
        $this->_title        = COM_checkHTML(COM_checkWords(trim($array['title'])));
        $this->_project      = COM_checkHTML(COM_checkWords(trim($array['project'])));
        $this->_homepage     = strip_tags($array['homepage']);
        $this->_size         = intval(COM_applyFilter($array['size'], true));
        $this->_md5          = COM_applyFilter($array['md5']);
        $this->_logourl      = COM_applyFilter(trim($array['logourl']));
        $this->_mg_autotag   = COM_applyFilter(trim($array['mg_autotag']));
        $this->_tags         = COM_applyFilter($this->_modifyTags($array['tags']));
        $this->_old_date     = intval(COM_applyFilter($array['old_date'], true));

        if (version_compare(VERSION, '2.1.0') >= 0) {
            // Now not do anything here to hold the raw text.
            // And do all of the text processing just before display.
            $this->_description = $array['text_description'];
            $this->_detail      = $array['text_detail'];
        } else {
            require_once $_CONF['path'] . 'plugins/downloads/include/gltext.class.php';
            $gltext = new GLPText();
            $this->_description = $gltext->loadTextFromArgs($array['text_description'], $this->_postmode);
            $this->_detail      = $gltext->loadTextFromArgs($array['text_detail'],      $this->_postmode);
        }

        $this->_date = mktime($this->_hour, $this->_minute, $this->_second,
                              $this->_month, $this->_day, $this->_year);
        $this->_createddate = date('Y-m-d H:i:s', $this->_date);

        $this->_is_listing = ($this->_is_released == 0) ? 0 : $this->_is_listing;

        $this->_deletesnap = ($array['deletesnap'] == 'on') ? 1 : 0;
        
        $this->_editor_mode = COM_applyFilter($array['editor_mode']);
        if ($this->_editor_mode == 'submit') {
            $this->_retry = true;
        }

        if (!empty($array['page'])) {
            $this->_page = COM_applyFilter($array['page']);
        }
        if (!empty($array['listing_cid'])) {
            $this->_listing_cid = COM_applyFilter($array['listing_cid']);
        }
    }

    function _loadFromDatabase($lid)
    {
        global $_TABLES;

        $sql  = "SELECT lid, a.cid, a.title, url, homepage, version, size, md5, "
              . "project, description, detail, postmode, logourl, mg_autotag, tags, date, hits, rating, votes, "
              . "commentcode, is_released, is_listing, createddate, a.owner_id, b.owner_id AS cat_owner_id, "
              . "text_version, "
              . "group_id, perm_owner, perm_group, perm_members, perm_anon "
              . "FROM {$_TABLES['downloads']} a "
              . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
              . "WHERE lid='" . addslashes($lid) . "'";
        $result = DB_query($sql);
        if (DB_numRows($result) == 0) {
            return PLG_afterSaveSwitch('home', '', 'downloads', 110);
        }
        $A = DB_fetchArray($result);
        foreach ($A as $key => $val) {
            $this->{'_' . $key} = $val;
        }
    }

    function _loadSubmission($lid)
    {
        global $_TABLES;

        $sql  = "SELECT * "
              . "FROM {$_TABLES['downloadsubmission']} "
              . "WHERE lid='" . addslashes($lid) . "'";
        $result = DB_query($sql);
        if (DB_numRows($result) == 0) {
            return PLG_afterSaveSwitch('home', '', 'downloads', 110);
        }
        $A = DB_fetchArray($result);
        foreach ($A as $key => $val) {
            $this->{'_' . $key} = $val;
        }
    }

    function _initVars()
    {
        global $_CONF, $_USER;

        $this->_lid         = $this->_createID();
        $this->_old_lid     = '';
        $this->_title       = '';
        $this->_url         = '';
        $this->_homepage    = '';
        $this->_version     = '';
        $this->_size        = 0;
        $this->_secret_id   = '';
        $this->_md5         = '';
        $this->_logourl     = '';
        $this->_mg_autotag  = '';
        $this->_tags        = '';
        $this->_is_released = 1;
        $this->_is_listing  = 1;
        $this->_description = '';
        $this->_detail      = '';
        $this->_project     = '';
        $this->_date        = floor(time()/60)*60;
        $this->_owner_id    = $_USER['uid'];
        $this->_commentcode = $_CONF['comment_code'];
        $this->_text_version = 2; // GLTEXT_LATEST_VERSION
    }

    function _checkHasAccess()
    {
        global $_USER, $LANG_DLM;

        $access = SEC_hasAccess($this->_cat_owner_id, $this->_group_id,
                                $this->_perm_owner, $this->_perm_group,
                                $this->_perm_members, $this->_perm_anon);
        if ($access < 3) {
            // deny access
            COM_accessLog("User {$_USER['username']} tried illegally to edit download information $this->_lid.");
            $display = COM_showMessage(7, 'downloads');
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
    * Show the downloads editor
    */
    function showEditor($mode='edit')
    {
        global $_CONF, $_TABLES, $_USER, $_GROUPS, $_SCRIPTS, $LANG01, $LANG12, $LANG24,
               $MESSAGE, $_DLM_CONF, $LANG_DLM, $LANG_ACCESS;

        $retval = '';

        $this->initCatTree();

        $p = COM_applyFilter($_GET['p']);
        if (!empty($p)) $this->_page = $p;
        $lc = COM_applyFilter($_GET['cid']);
        if (!empty($lc)) $this->_listing_cid = $lc;

        if (!empty($this->_editor_mode)) {
            $mode = $this->_editor_mode;
        } else {
            $this->_editor_mode = $mode;
        }

        if ($mode == 'edit' || $mode == 'clone') {
            if ($this->_retry == true) {
                $this->_loadFromArgs($_POST);
            } else {
                $this->_lid = COM_applyFilter($_GET['lid']);
                $this->_loadFromDatabase($this->_lid);
            }
        }

        if ($mode == 'create' || $mode == 'submit') {
            if ($this->_retry == true) {
                $this->_loadFromArgs($_POST);
            } else {
                $this->_initVars();
                $homepage = DB_getItem($_TABLES['users'], 'homepage', "uid = '" . addslashes($this->_owner_id) . "'");
                $this->_homepage = DLM_htmlspecialchars(stripslashes($homepage));
            }
        }

        if ($mode != 'create' && $mode != 'submit') {
            $this->_checkHasAccess();
        }

        if ($mode == 'editsubmission') {
            if ($this->_retry == true) {
                $this->_loadFromArgs($_POST);
            } else {
                $this->_lid = COM_applyFilter($_GET['id']);
                $this->_loadSubmission($this->_lid);
            }
        }

        $ja = ($_CONF['language'] == 'japanese_utf-8');
        $T = new Template($_DLM_CONF['path_layout']);
        $T->set_file(array(
             't_mod_download'      => 'mod_download.thtml',
             't_mod_newfile'       => 'mod_newfile.thtml',
             't_mod_newfile2'      => 'mod_newfile2.thtml',
             't_mod_newfileshot'   => 'mod_newfileshot.thtml',
             't_mod_submit_delete' => 'mod_submit_delete.thtml',
             't_mod_submit_cancel' => 'mod_submit_cancel.thtml',
             't_mod_submit_preview' => 'mod_submit_preview.thtml',
             't_mod_file_id'       => 'mod_file_id.thtml',
             't_mod_dl_file_name'  => 'mod_dl_file_name.thtml',
             't_mod_file_size'     => 'mod_file_size.thtml',
             't_mod_votes'         => 'mod_votes.thtml',
             't_mod_submitter'     => 'mod_submitter.thtml',
             't_mod_tempfile'      => 'mod_tempfile.thtml',
             't_mod_logourl'       => 'mod_logourl.thtml',
             't_mod_tempsnap'      => 'mod_tempsnap.thtml',
             't_mod_date'          => 'mod_date' . ($ja ? '_ja' : '') . '.thtml',
             't_mod_mg_autotag'    => 'mod_mg_autotag.thtml',
        ));
        if ($mode == 'submit') {
            $T->set_file(array(
                 't_mod_newfile'     => 'mod_newfilesubmit.thtml',
                 't_mod_newfileshot' => 'mod_newfileshotsubmit.thtml',
            ));
        }

        DLM_setDefaultTemplateVars($T);
        $lang = array('main', 'fileid', 'filetitle', 'dlfilename', 'replfilename',
                      'homepage', 'ver', 'filesize', 'bytes', 'description', 'detail',
                      'category', 'shotimage', 'addshotimage', 'replshotimage',
                      'released', 'listing', 'yes', 'no',
                      'submit', 'delete', 'cancel', 'confirm_delete', 'submitter',
                      'release_date', 'postmode', 'comment_mode', 'project',
                      'toolbar', 'toolbar1', 'toolbar2', 'toolbar3', 'toolbar5',
                      'md5', 'mg_autotag', 'mg_autotag_info', 'upload', 'tags', 'preview');
        foreach ($lang as $v) $T->set_var('lang_' . $v, $LANG_DLM[$v]);

        $action = 'index.php';

        switch ($mode) {
        case 'edit':
            $op = 'saveDownload';
            break;

        case 'create':
            $T->set_var('lang_submit', $LANG_DLM['add']);
            $T->set_var('lang_replfilename', $LANG_DLM['addfilename']);
            $T->set_var('lang_replshotimage', $LANG_DLM['addshotimage']);
            $op = 'add';
            break;

        case 'clone':
            $T->set_var('lang_submit', $LANG_DLM['add']);
            $op = 'add';
            break;

        case 'editsubmission':
            $T->set_var('lang_submit', $LANG_DLM['approve']);
            $op = 'approve';
            break;

        case 'submit':
            $T->set_var('lang_submit', $LANG12[8]);
            $T->set_var('lang_replfilename', $LANG_DLM['addfilename']);
            $op = 'submit';
            $action = 'submit.php?type=downloads';
            break;
        }

        $categorylist = $this->_cat_tree->makeSelBox('title', 'corder', $this->_cid, 0, 'cid');

        if ($mode == 'edit' || $mode == 'clone' || $mode == 'editsubmission') {
            if (empty($this->_old_lid)) {
                $this->_old_lid = $this->_lid;
            }
            $this->_title       = DLM_htmlspecialchars(stripslashes($this->_title));
            $this->_project     = DLM_htmlspecialchars(stripslashes($this->_project));
            $pathstring         = $this->_cat_tree->getNicePathFromId($cid, "title", "{$_CONF['site_url']}/downloads/index.php?op=");
            $this->_url         = DLM_htmlspecialchars(stripslashes($this->_url));
            $this->_logourl     = DLM_htmlspecialchars(stripslashes($this->_logourl));
            $this->_mg_autotag  = DLM_htmlspecialchars(stripslashes($this->_mg_autotag));
            $this->_tags        = DLM_htmlspecialchars(stripslashes($this->_tags));
            $this->_homepage    = DLM_htmlspecialchars(stripslashes($this->_homepage));
            $this->_version     = DLM_htmlspecialchars($this->_version);
            $this->_size        = DLM_htmlspecialchars($this->_size);
            $this->_md5         = DLM_htmlspecialchars(stripslashes($this->_md5));
        }

        if ($mode == 'editsubmission') {
            $tempfileurl = $_CONF['site_url'] . '/admin/plugins/downloads/tmpfile.php?id=' . $this->_lid;
            $tempsnapurl = '';
            if (!empty($this->_logourl)) {
                $tempsnapurl = $_DLM_CONF['snapstore_url'] . '/tmp' . date('YmdHis', $this->_date) . DLM_createSafeFileName($this->_logourl);
            }
            $T->set_var('lang_tempfile', $LANG_DLM['tempfile']);
            $T->set_var('tempsnapurl', $tempsnapurl);
            $T->set_var('tempfileurl', $tempfileurl);
        }

        if ($mode == 'clone') {
            if ($this->_retry != true && !empty($this->_lid)) {
                $this->_lid = $this->_createID($this->_lid . '_');
                if (strlen($this->_lid) > 40) {
                    $this->_lid = $this->_createID();
                }
            }
        }

        if (version_compare(VERSION, '2.1.0') >= 0) {
            require_once $_CONF['path_system'] . 'classes/gltext.class.php';
            $description      = GLText::getEditText($this->_description, $this->_postmode, 2);
            $detail           = GLText::getEditText($this->_detail,      $this->_postmode, 2);
            $file_description = GLText::getPreviewText($this->_description, $this->_postmode, 'story.edit', 2);
            $file_detail      = GLText::getPreviewText($this->_detail,      $this->_postmode, 'story.edit', 2);
        } else {
            require_once $_CONF['path'] . 'plugins/downloads/include/gltext.class.php';
            $gltext = new GLPText();
            $description      = $gltext->getEditText($this->_description, $this->_postmode);
            $detail           = $gltext->getEditText($this->_detail,      $this->_postmode);
            $file_description = $gltext->getDisplayText($this->_description, $this->_postmode);
            $file_detail      = $gltext->getDisplayText($this->_detail,      $this->_postmode);
        }

        list($year, $month, $day, $hour, $minute, $second) = explode(',', date('Y,m,d,H,i,s', $this->_date));

        $enabled_adv_editor = ($_CONF['advanced_editor'] && $_USER['advanced_editor']);
        $show_texteditor = '';
        $show_htmleditor = 'none';
        if ($enabled_adv_editor && $this->_postmode == 'adveditor') {
            $show_texteditor = 'none';
            $show_htmleditor = '';
        }

        $allowed_html = '';
        if (version_compare(VERSION, '2.1.0') >= 0) {
            $postmode_list = 'plaintext,html';
            if ($enabled_adv_editor) {
                $postmode_list .= ',adveditor';
            }
            if ($_CONF['wikitext_editor']) {
                $postmode_list .= ',wikitext';
            }
            $postmode_array = explode(',', $postmode_list);

            foreach ($postmode_array as $pm) {
                $allowed_html .= COM_allowedHTML('story.edit', false, 1, $pm);
            }
            $allowed_html .= COM_allowedAutotags(false, array('code', 'raw'));
        } else {
            $allowed_html = COM_allowedHTML();
            $allowed_html = str_replace('[page_break], ', '', $allowed_html);  // No support [page_break]
        }
        $T->set_var('lang_allowed_html', $allowed_html);

        if (version_compare(VERSION, '2.1.0') >= 0) {
            // Loads jQuery UI datepicker and timepicker-addon
            $_SCRIPTS->setJavaScriptLibrary('jquery.ui.slider');
            $_SCRIPTS->setJavaScriptLibrary('jquery.ui.datepicker');
            $_SCRIPTS->setJavaScriptLibrary('jquery-ui-i18n');
            $_SCRIPTS->setJavaScriptLibrary('jquery-ui-timepicker-addon');
            $_SCRIPTS->setJavaScriptLibrary('jquery-ui-timepicker-addon-i18n');
            $_SCRIPTS->setJavaScriptFile('datetimepicker', '/javascript/datetimepicker.js');

            $langCode = COM_getLangIso639Code();
            $toolTip  = $MESSAGE[118];
            $imgUrl   = $_CONF['site_url'] . '/images/calendar.png';

            $_SCRIPTS->setJavaScript(
                "jQuery(function () {"
                . "  geeklog.hour_mode = 24;"
                . "  geeklog.datetimepicker.set('release', '{$langCode}', '{$toolTip}', '{$imgUrl}');"
                . "});", TRUE, TRUE
            );
        }

        if ($enabled_adv_editor) {
            // Add JavaScript
            if (version_compare(VERSION, '2.1.0') >= 0) {
                $_SCRIPTS->setJavaScriptFile('postmode_control', '/javascript/postmode_control.js');
                COM_setupAdvancedEditor('/downloads/adveditor.js', 'story.edit');
            } else {
                if (version_compare(VERSION, '2.0.0') < 0) {
                    $js = 'geeklogEditorBasePath = "' . $_CONF['site_url'] . '/fckeditor/";';
                    $_SCRIPTS->setJavaScript($js, true);
                }
                $_SCRIPTS->setJavaScriptFile('fckeditor', '/fckeditor/fckeditor.js');
                $_SCRIPTS->setJavaScriptFile('downloadeditor_fckeditor', '/downloads/downloadeditor_fckeditor.js');
            }
        }

        if (empty($this->_postmode)) {
            $this->_postmode = $_DLM_CONF['postmode'];
        }
        $post_options = COM_optionList($_TABLES['postmodes'], 'code,name', $this->_postmode);
        if ($enabled_adv_editor) {
            if ($this->_postmode == 'adveditor') {
                $post_options .= '<option value="adveditor" ' . UC_SELECTED . '>' . $LANG24[86] . '</option>';
            } else {
                $post_options .= '<option value="adveditor">' . $LANG24[86] . '</option>';
            }
        }
        if ($_CONF['wikitext_editor']) {
            if ($this->_postmode == 'wikitext') {
                $post_options .= '<option value="wikitext" ' . UC_SELECTED . '>' . $LANG24[88] . '</option>';
            } else {
                $post_options .= '<option value="wikitext">' . $LANG24[88] . '</option>';
            }
        }

        $hidden_values  = $this->_makeForm_hidden('owner_id', $this->_owner_id);
        $hidden_values .= $this->_makeForm_hidden('editor_mode', $this->_editor_mode);
        $hidden_values .= $this->_makeForm_hidden('page', $this->_page);
        if (!empty($this->_listing_cid) && $this->_listing_cid != ROOTID) {
            $hidden_values .= $this->_makeForm_hidden('listing_cid', $this->_listing_cid);
        }

        $T->set_var('show_texteditor',      $show_texteditor);
        $T->set_var('show_htmleditor',      $show_htmleditor);
        $T->set_var('post_options',         $post_options);
        $T->set_var('action',               $action);
        $T->set_var('op',                   $op);
        $T->set_var('lid',                  $this->_lid);
        $T->set_var('old_lid',              $this->_old_lid);
        $T->set_var('pathstring',           $pathstring);
        $T->set_var('title',                $this->_title);
        $T->set_var('url',                  $this->_url);
        $T->set_var('homepage',             $this->_homepage);
        $T->set_var('version',              $this->_version);
        $T->set_var('size',                 $this->_size);
        $T->set_var('md5',                  $this->_md5);
        $T->set_var('logourl',              $this->_logourl);
        $T->set_var('mg_autotag',           $this->_mg_autotag);
        $T->set_var('tags',                 $this->_tags);
        $T->set_var('description',          $description);
        $T->set_var('detail',               $detail);
        $T->set_var('project',              $this->_project);
        $T->set_var('snapstore_url',         $_DLM_CONF['snapstore_url']);
        $T->set_var('categorylist',         $categorylist);
        $T->set_var('val_is_released_1',    ($this->_is_released) ? UC_SELECTED : '');
        $T->set_var('val_is_released_0',   (!$this->_is_released) ? UC_SELECTED : '');
        $T->set_var('val_is_listing_1',     ($this->_is_listing) ? UC_SELECTED : '');
        $T->set_var('val_is_listing_0',    (!$this->_is_listing) ? UC_SELECTED : '');
        $T->set_var('shot_autotag',         $this->_makeForm_shot_mg_autotag());
        $T->set_var('shot',                 $this->_makeForm_shot());
        $T->set_var('year_options',         COM_getYearFormOptions($year));
        $T->set_var('month_options',        DLM_getMonthFormOptions($month));
        $T->set_var('day_options',          COM_getDayFormOptions($day));
        $T->set_var('hour_options',         COM_getHourFormOptions($hour, 24));
        $T->set_var('minute_options',       COM_getMinuteFormOptions($minute, 1));
        $T->set_var('second_options',       COM_getMinuteFormOptions($second, 1));
        $T->set_var('old_date',             $this->_date);
        $T->set_var('comment_options',      COM_optionList($_TABLES['commentcodes'], 'code,name', $this->_commentcode));
        $T->set_var('gltoken_name',         CSRF_TOKEN);
        $T->set_var('gltoken',              SEC_createToken());
        $T->set_var('submitter',            $this->_owner_id);
        $T->set_var('displayName',          COM_getDisplayName($this->_owner_id));

        if ($mode == 'submit') {
            $T->set_var('lang_commentoption', $LANG_DLM['commentoption']);
            $T->set_var('val_commentoption_1', UC_SELECTED);
            $T->set_var('val_commentoption_0', '');
        }

        $T->parse('mod_submitter', 't_mod_submitter');

        if ($mode == 'editsubmission' || $mode == 'submit') {
            $T->set_var('mod_mg_autotag','');
            $hidden_values .= $this->_makeForm_hidden('mg_autotag', $this->_mg_autotag);
        } else {
            if ($_DLM_CONF['enabled_mg_autotag'] == 1) {
                $T->parse('mod_mg_autotag', 't_mod_mg_autotag');
            } else {
                $T->set_var('mod_mg_autotag','');
                $hidden_values .= $this->_makeForm_hidden('mg_autotag', $this->_mg_autotag);
            }
        }

        $T->set_var('hidden_values', $hidden_values);

        if ($mode == 'edit' || $mode == 'clone') {
            $T->parse('mod_newfile',       't_mod_newfile');
            $T->parse('mod_newfileshot',   't_mod_newfileshot');
            if ($mode == 'edit') {
                $T->parse('mod_submit_delete', 't_mod_submit_delete');
            }
            $T->parse('mod_submit_cancel', 't_mod_submit_cancel');
            $T->parse('mod_file_size',     't_mod_file_size');
        }

        if ($mode == 'editsubmission') {
            $T->parse('mod_dl_file_name',  't_mod_dl_file_name');
            $T->parse('mod_tempfile',      't_mod_tempfile');
            $T->parse('mod_file_size',     't_mod_file_size');

            $T->set_var('mod_tempsnap', '');
            if ($tempsnapurl != '') {
                $T->parse('mod_tempsnap',  't_mod_tempsnap');
            }

            $T->parse('mod_logourl',       't_mod_logourl');
            $T->parse('mod_submit_delete', 't_mod_submit_delete');
            $T->parse('mod_submit_cancel', 't_mod_submit_cancel');
        }

        if ($mode == 'create') {
            $T->parse('mod_newfile',       't_mod_newfile2');
            $T->parse('mod_newfileshot',   't_mod_newfileshot');
            $T->parse('mod_submit_cancel', 't_mod_submit_cancel');
        }

        if ($mode == 'submit') {
            $T->parse('mod_newfile',     't_mod_newfile');
            $T->parse('mod_newfileshot', 't_mod_newfileshot');
        }

        if ($_DLM_CONF['enabled_preview_on_upload'] === false
                && ($mode == 'create' || $mode == 'submit')) {
            $T->set_var('mod_submit_preview','');
        } else {
            $T->parse('mod_submit_preview', 't_mod_submit_preview');
        }

        $T->parse('mod_file_id', 't_mod_file_id');
        $T->parse('mod_date', 't_mod_date');
        $T->parse('output', 't_mod_download');

        $blocktitle = $LANG_DLM['moddl'];
        if ($mode == 'editsubmission') $blocktitle = $LANG_DLM['dlswaiting'];
        if ($mode == 'create')         $blocktitle = $LANG_DLM['addnewfile'];
        if ($mode == 'clone')          $blocktitle = $LANG_DLM['addnewfile'];
        if ($mode == 'submit')         $blocktitle = $LANG_DLM['uploadtitle'];

        $retval .= $this->_showMessage();
        $retval .= COM_startBlock($blocktitle, '', COM_getBlockTemplate ('_admin_block', 'header'));
        $retval .= $T->finish($T->get_var('output'));
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

        if (!empty($file_description) || !empty($file_detail)) {
            // Display Preview Block
            $T2 = new Template($_DLM_CONF['path_layout']);
            $T2->set_file('t_mod_preview', 'mod_preview.thtml');
            $T2->set_var('file_description', $file_description);
            $T2->set_var('file_detail',      $file_detail);
            $T2->parse('output', 't_mod_preview');
            $blocktitle = $LANG_DLM['preview'];
            $retval .= COM_startBlock($blocktitle, '', COM_getBlockTemplate ('_admin_block', 'header'));
            $retval .= $T2->finish($T2->get_var('output'));
            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
        }

        if ($mode == 'edit') {
            // Display File Voting Information
            $retval .= $this->_showVotes_reguser($this->_lid);
            $retval .= $this->_showVotes_anon($this->_lid);
        }

        if ($mode != 'submit' || $this->_retry == true) {
            $retval = DLM_createHTMLDocument($retval, array('pagetitle' => $blocktitle));
        }

        return $retval;
    }

    function _makeForm_shot_mg_autotag()
    {
        global $_CONF, $_DLM_CONF;

        if (!empty($this->_mg_autotag)) {
            $autotag = str_replace(array('[', ']'), '', $this->_mg_autotag);
            $autotag = '[' . $autotag
                        . ' width:' . $_DLM_CONF['max_tnimage_width']
                        . ' height:' . $_DLM_CONF['max_tnimage_height']
                        . ' align:left]';
            return PLG_replaceTags($autotag, 'mediagallery');
        }
        $tnimgurl = $_CONF['site_url'] . '/downloads/images/blank.png';
        $snapwidth  = $_DLM_CONF['max_tnimage_width'];
        $snapheight = $_DLM_CONF['max_tnimage_height'];
//        $sizeattributes = 'width="' . $snapwidth . '" height="' . $snapheight . '" ';
        return '<img src="' . $tnimgurl . '" ' . $sizeattributes . ' alt=""' . XHTML . '>' . LB;
    }

    function _makeForm_shot()
    {
        global $_CONF, $_DLM_CONF, $LANG_DLM;

        $safename = DLM_createSafeFileName($this->_logourl);
        if (!empty($this->_logourl) && file_exists($_DLM_CONF['path_snapstore'] . $safename)) {
            $imgpath  = $_DLM_CONF['path_tnstore']    . $safename;
            $tnimgurl = $_DLM_CONF['tnstore_url'] . '/' . $safename;
            $imgpath = DLM_modTNPath($imgpath);
            $tnimgurl = substr($tnimgurl, 0, -3) . substr($imgpath, -3); // Align the extension
            if (file_exists($imgpath)) {
                $dimensions = getimagesize($imgpath);
                if (!empty($dimensions[0]) && !empty($dimensions[1])) {
                    if ($dimensions[0]>$_DLM_CONF['max_tnimage_width']) {
                        $snapwidth  = $_DLM_CONF['max_tnimage_width'];
                        $snapheight = intval ($dimensions[1] * $_DLM_CONF['max_tnimage_width'] / $dimensions[0]);
                    } else {
                        $snapwidth  = $dimensions[0];
                        $snapheight = $dimensions[1];
                    }
                }
            } else {
                $tnimgurl = $_CONF['site_url'] . '/downloads/images/blank.png';
                $snapwidth  = $_DLM_CONF['max_tnimage_width'];
                $snapheight = $_DLM_CONF['max_tnimage_height'];
            }
//            $sizeattributes = 'width="' . $snapwidth . '" height="' . $snapheight . '" ';
            $shot = '<div class="dlm_snap_tn"><a href="' . $_DLM_CONF['snapstore_url'] . '/' . $safename . '" title="">'
                  . '<img src="' . $tnimgurl . '" ' . $sizeattributes . ' alt=""' . XHTML . '></a></div>' . LB
                  . '<input type="checkbox" name="deletesnap"' . XHTML . '>&nbsp;'
                  . $LANG_DLM['delete'] . '<br' . XHTML . '>';
        } else {
            $tnimgurl = $_CONF['site_url'] . '/downloads/images/blank.png';
            $snapwidth  = $_DLM_CONF['max_tnimage_width'];
            $snapheight = $_DLM_CONF['max_tnimage_height'];
//            $sizeattributes = 'width="' . $snapwidth . '" height="' . $snapheight . '" ';
            $shot ='<div class="dlm_snap_tn"><img src="' . $tnimgurl . '" ' . $sizeattributes . ' alt=""' . XHTML . '></div>' . LB;
        }
        return $shot;
    }

    function _makeForm_hidden($name, $value)
    {
        $indent = '            ';
        return $indent . '<input type="hidden" name="' . $name . '" value="' . $value . '"' . XHTML . '>' . LB;
    }

    /**
    * Display file voting information
    */
    function _showVotes_reguser($lid)
    {
        global $_CONF, $_TABLES, $_USER, $LANG_DLM;

        require_once $_CONF['path_system'] . 'lib-admin.php';

        // Show Registered Users Votes
        $sql = "SELECT ratingid, ratinguser, rating, ratinghostname, ratingtimestamp "
             . "FROM {$_TABLES['downloadvotes']} "
             . "WHERE lid='" . addslashes($lid) . "' AND ratinguser > 1 "
             . "ORDER BY ratingtimestamp DESC";
        $result = DB_query($sql);
        $votes = DB_numRows($result);

        $header_arr = array(
            array('text' => $LANG_DLM['user'],      'field' => 'ratinguser'    ),
            array('text' => $LANG_DLM['ip'],        'field' => 'ratinghostname'),
            array('text' => $LANG_DLM['rating'],    'field' => 'rating'        ),
            array('text' => $LANG_DLM['useravg'],   'field' => 'useravgrating' ),
            array('text' => $LANG_DLM['totalrate'], 'field' => 'uservotes'     ),
            array('text' => $LANG_DLM['votedate'],  'field' => 'date'          ),
        );
        $data_arr = array();
        $text_arr = array('has_menu' => false,
                          'title'    => sprintf($LANG_DLM['reguservotes'], $votes),
        );

        while ($A = DB_fetchArray($result)) {

            $ratinguser = (int) $A['ratinguser'];
            //Individual user information
            $result2 = DB_query("SELECT rating FROM {$_TABLES['downloadvotes']} "
                              . "WHERE ratinguser = $ratinguser");
            $uservotes = DB_numRows($result2);
            $useravgrating = 0;
            while (list($rating2) = DB_fetchArray($result2)) {
                 $useravgrating = $useravgrating + $rating2;
            }
            $useravgrating = $useravgrating / $uservotes;
            $useravgrating = number_format($useravgrating, 1);

            $formatted_date = COM_getUserDateTimeFormat($A['ratingtimestamp']);

            $data_arr[] = array('ratinguser'     => COM_getDisplayName($A['ratinguser']),
                                'ratinghostname' => $A['ratinghostname'],
                                'rating'         => $A['rating'],
                                'useravgrating'  => $useravgrating,
                                'uservotes'      => $uservotes,
                                'date'           => $formatted_date[0] );
        }

        return ADMIN_simpleList('', $header_arr, $text_arr, $data_arr);
    }

    /**
    * Display file voting information
    */
    function _showVotes_anon($lid)
    {
        global $_CONF, $_TABLES, $_USER, $LANG_DLM;

        require_once $_CONF['path_system'] . 'lib-admin.php';

        // Show Registered Users Votes
        $sql = "SELECT ratingid, ratinguser, rating, ratinghostname, ratingtimestamp "
             . "FROM {$_TABLES['downloadvotes']} "
             . "WHERE lid = '" . addslashes($lid) . "' AND ratinguser = 1 "
             . "ORDER BY ratingtimestamp DESC";
        $result = DB_query($sql);
        $votes = DB_numRows($result);

        $header_arr = array(
            array('text' => $LANG_DLM['ip'],        'field' => 'ratinghostname'),
            array('text' => $LANG_DLM['rating'],    'field' => 'rating'        ),
            array('text' => $LANG_DLM['votedate'],  'field' => 'date'          ),
        );
        $data_arr = array();
        $text_arr = array('has_menu' => false,
                          'title'    => sprintf($LANG_DLM['anonuservotes'], $votes),
        );

        while ($A = DB_fetchArray($result)) {
            $formatted_date = COM_getUserDateTimeFormat($A['ratingtimestamp']);
            $data_arr[] = array('ratinghostname' => $A['ratinghostname'],
                                'rating'         => $A['rating'],
                                'date'           => $formatted_date[0]);
        }

        return ADMIN_simpleList('', $header_arr, $text_arr, $data_arr);
    }

    /**
    * Add download file information to the database
    */
    function addDownload($args='')
    {
        global $_CONF, $_USER, $_TABLES, $_DLM_CONF;

        if (!empty($args) && is_array($args)) {
            $this->_loadFromArgs($args);
        } else {
            $this->_loadFromArgs($_POST);
        }

        $this->_secret_id = $this->_createSecretID();
        $this->_old_url   = $this->_url;
        $this->_url       = $_FILES['newfile']['name'];
        $this->_owner_id  = $_USER['uid'];
        if (empty($this->_lid)) $this->_lid = $this->_old_lid;

        // Validate the input values ----------------------->
        if (empty($this->_title)) {
            $this->_errno[] = '1101';
        }
        if (empty($this->_lid)) {
            $this->_errno[] = '1201';
        } else {
            if ($this->_lid != COM_sanitizeID($this->_lid)) {
                $this->_errno[] = '1203';
            } else if (strlen($this->_lid) > 40) {
                $this->_errno[] = '1203';
            } else {
                $count = DB_count($_TABLES['downloads'], 'lid', addslashes($this->_lid));
                if ($count > 0) {
                    $this->_errno[] = '1202';
                }
            }
        }
        if (empty($this->_old_url) && empty($this->_url)) {
            $this->_errno[] = '1102';
        }
        if (empty($this->_description)) {
            $this->_errno[] = '1103';
        }
        if (!empty($this->_errno)) {
            $this->_retry = true;
            $this->_reedit('showEditor', array($this->_editor_mode));
        }
        // Validate the input values -----------------------<

        $success = false;
        if (!empty($this->_url)) {
            $safename = DLM_createSafeFileName($this->_url, $this->_secret_id);
            $success = DLM_uploadNewFile($_FILES['newfile'], $_DLM_CONF['path_filestore'], $safename);
        } else if (!empty($this->_old_url)) {
            $old_secret_id = DB_getItem($_TABLES['downloads'], 'secret_id', "lid = '" . addslashes($this->_old_lid) . "'");
            $old_safeurl = DLM_createSafeFileName($this->_old_url, $old_secret_id);
            $success = file_exists($_DLM_CONF['path_filestore'] . $old_safeurl);
            if ($success) {
                $safename = DLM_createSafeFileName($this->_old_url, $this->_secret_id);
                $success = copy($_DLM_CONF['path_filestore'] . $old_safeurl,
                                $_DLM_CONF['path_filestore'] . $safename);
                if ($success) {
                    $this->_url = $this->_old_url;
                }
            }
        }
        if ($success) {
            $this->_size = filesize($_DLM_CONF['path_filestore'] . $safename);
            $this->_md5  = md5_file($_DLM_CONF['path_filestore'] . $safename);
            $this->_uploadSnapImage();
            DLM_makeThumbnail(DLM_createSafeFileName($this->_logourl));
            $this->_addToDatabase();

            switch ($this->_page) {
                case 'item':
                    $url = COM_buildURL("{$_CONF['site_url']}/downloads/index.php?id=$this->_lid");
                    break;
                case 'flist':
                    $url = "{$_CONF['site_url']}/downloads/index.php";
                    break;
                case 'list':
                default:
                    $url = "{$_CONF['site_url']}/admin/plugins/downloads/index.php";
                    break;
            }
            return PLG_afterSaveSwitch('item', $url, 'downloads', 102);
        } else {
            return PLG_afterSaveSwitch('admin', '', 'downloads', 108);
        }
    }


    function _addToDatabase($mode='')
    {
        global $_CONF, $_TABLES;

        $sql_var_additions = '';
        $sql_val_additions = '';

        if (version_compare(VERSION, '2.1.0') >= 0) {

            $this->_text_version = GLTEXT_LATEST_VERSION;
            $text_version = $this->_text_version;
            $sql_var_additions = "text_version, ";
            $sql_val_additions = "$text_version, ";

            // Apply HTML filter to the text just before save
            // with the permissions of current editor
            require_once $_CONF['path_system'] . 'classes/gltext.class.php';
            $description = GLText::applyHTMLFilter(
                    $this->_description,
                    $this->_postmode,
                    'story.edit',
                    $this->_text_version);
            $detail      = GLText::applyHTMLFilter(
                    $this->_detail,
                    $this->_postmode,
                    'story.edit',
                    $this->_text_version);
        } else {
            $description = $this->_description;
            $detail      = $this->_detail;
        }

        $lid         = addslashes($this->_lid);
        $cid         = addslashes($this->_cid);
        $title       = addslashes($this->_title);
        $url         = addslashes($this->_url);
        $homepage    = addslashes($this->_homepage);
        $version     = addslashes($this->_version);
        $size        = (int) $this->_size;
        $secret_id   = addslashes($this->_secret_id);
        $md5         = addslashes($this->_md5);
        $logourl     = addslashes($this->_logourl);
        $mg_autotag  = addslashes($this->_mg_autotag);
        $tags        = addslashes($this->_tags);
        $date        = (int) $this->_date;
        $commentcode = (int) $this->_commentcode;
        $project     = addslashes($this->_project);
        $description = addslashes($description);
        $detail      = addslashes($detail);
        $owner_id    = (int) $this->_owner_id;
        $postmode    = addslashes($this->_postmode);
        $is_released = (int) $this->_is_released;
        $is_listing  = (int) $this->_is_listing;
        $createddate = addslashes($this->_createddate);

        $table = empty($mode) ? $_TABLES['downloads'] : $_TABLES['downloadsubmission'];
        DB_query("INSERT INTO $table "

               . "(lid, cid, title, url, homepage, version, size, secret_id, md5, logourl, mg_autotag, tags, "
               . "date, hits, rating, votes, commentcode, project, description, detail, owner_id, "
               . $sql_var_additions
               . "postmode, is_released, is_listing, createddate) "

               . "VALUES ('$lid', '$cid', '$title', '$url', '$homepage', '$version', $size, '$secret_id', '$md5', '$logourl', '$mg_autotag', '$tags', "
               . "$date, 0, 0, 0, $commentcode, '$project', '$description', '$detail', $owner_id, "
               . $sql_val_additions
               . "'$postmode', $is_released, $is_listing, '$createddate')");

        if ($mode != 'submission') {
            PLG_itemSaved($this->_lid, 'downloads');
            COM_rdfUpToDateCheck('downloads', $this->_cid, $this->_lid);
        }
    }

    function _saveToDatabase($mode='')
    {
        global $_CONF, $_TABLES;

        $sql_additions = '';

        if (version_compare(VERSION, '2.1.0') >= 0) {

            $this->_text_version = GLTEXT_LATEST_VERSION;
            $text_version = $this->_text_version;
            $sql_additions = "text_version='$text_version', ";

            // Apply HTML filter to the text just before save
            // with the permissions of current editor
            require_once $_CONF['path_system'] . 'classes/gltext.class.php';
            $description = GLText::applyHTMLFilter(
                    $this->_description,
                    $this->_postmode,
                    'story.edit',
                    $this->_text_version);
            $detail      = GLText::applyHTMLFilter(
                    $this->_detail,
                    $this->_postmode,
                    'story.edit',
                    $this->_text_version);
        } else {
            $description = $this->_description;
            $detail      = $this->_detail;
        }

        $lid         = addslashes($this->_lid);
        $cid         = addslashes($this->_cid);
        $title       = addslashes($this->_title);
        $url         = addslashes($this->_url);
        $homepage    = addslashes($this->_homepage);
        $version     = addslashes($this->_version);
        $size        = (int) $this->_size;
        $md5         = addslashes($this->_md5);
        $logourl     = addslashes($this->_logourl);
        $mg_autotag  = addslashes($this->_mg_autotag);
        $tags        = addslashes($this->_tags);
        $date        = (int) $this->_date;
        $commentcode = (int) $this->_commentcode;
        $project     = addslashes($this->_project);
        $description = addslashes($description);
        $detail      = addslashes($detail);
        $owner_id    = (int) $this->_owner_id;
        $postmode    = addslashes($this->_postmode);
        $is_released = (int) $this->_is_released;
        $is_listing  = (int) $this->_is_listing;
        $createddate = addslashes($this->_createddate);

        $table = empty($mode) ? $_TABLES['downloads'] : $_TABLES['downloadsubmission'];
        DB_query("UPDATE $table "
               . "SET lid='$lid', cid='$cid', title='$title', url='$url', mg_autotag='$mg_autotag', tags='$tags', "
               . "homepage='$homepage', project='$project', description='$description', detail='$detail', "
               . "version='$version', size=$size, md5='$md5', commentcode=$commentcode, owner_id=$owner_id, "
               . "postmode='$postmode', logourl='$logourl', is_released=$is_released, is_listing=$is_listing, "
               . $sql_additions
               . "date=$date, createddate='$createddate' "
               . "WHERE lid='$this->_old_lid'");

        if ($this->_old_lid == $this->_lid) {
            PLG_itemSaved($this->_lid, 'downloads');
        } else {
            DB_change($_TABLES['comments'], 'sid', addslashes($this->_lid),
                      array('sid', 'type'), array(addslashes($this->_old_lid), 'downloads'));
            PLG_itemSaved($this->_lid, 'downloads', $this->_old_lid);
        }
        COM_rdfUpToDateCheck('downloads', $this->_cid, $this->_lid);
    }

    function _unlink($path)
    {
        if (!empty($path) && file_exists($path) && !is_dir($path)) {
            return @unlink($path);
        }
        return false;
    }

    /**
    * Save download file information to the database
    */
    function saveDownload()
    {
        global $_CONF, $_TABLES, $_DLM_CONF;

        $this->_loadFromArgs($_POST);
        $newfile_name = $_FILES['newfile']['name'];

        // Validate the input values ----------------------->
        if (empty($this->_title)) {
            $this->_errno[] = '1101';
        }
        if (empty($this->_lid)) {
            $this->_errno[] = '1201';
        } else {
            if ($this->_lid != $this->_old_lid) {
                if ($this->_lid != COM_sanitizeID($this->_lid)) {
                    $this->_errno[] = '1203';
                } else if (strlen($this->_lid) > 40) {
                    $this->_errno[] = '1203';
                } else {
                    $count = DB_count($_TABLES['downloads'], 'lid', addslashes($this->_lid));
                    if ($count > 0) {
                        $this->_errno[] = '1202';
                    }
                }
            }
        }
        if (empty($this->_url) && empty($newfile_name)) {
            $this->_errno[] = '1102';
        }
        if (empty($this->_description)) {
            $this->_errno[] = '1103';
        }
        if (!empty($this->_errno)) {
            $this->_retry = true;
            $this->_reedit('showEditor', array($this->_editor_mode));
        }
        // Validate the input values -----------------------<

        // The download file
        $old_filename  = DB_getItem($_TABLES['downloads'], 'url',       "lid='" . addslashes($this->_old_lid) . "'");
        $old_secret_id = DB_getItem($_TABLES['downloads'], 'secret_id', "lid='" . addslashes($this->_old_lid) . "'");
        $safename = DLM_createSafeFileName($old_filename, $old_secret_id);
        $old_filepath = $_DLM_CONF['path_filestore'] . $safename;
        if (!empty($newfile_name)) {
            $this->_unlink($old_filepath);
            $safename = DLM_createSafeFileName($newfile_name, $old_secret_id);
            if (DLM_uploadNewFile($_FILES['newfile'], $_DLM_CONF['path_filestore'], $safename)) {
                $this->_url = $newfile_name;
            }
        }

        if (file_exists($_DLM_CONF['path_filestore'] . $safename)) {
          $this->_size = filesize($_DLM_CONF['path_filestore'] . $safename);
          $this->_md5  = md5_file($_DLM_CONF['path_filestore'] . $safename);
        }

        // The snapshot file
        $logourl_old = DB_getItem($_TABLES['downloads'], 'logourl', "lid='" . addslashes($this->_old_lid) . "'");
        $this->_uploadSnapImage();
        DLM_makeThumbnail(DLM_createSafeFileName($this->_logourl));

        $this->_saveToDatabase();

        $this->_unlinkSnapImage($logourl_old);
        $this->_unlinkTnImage($logourl_old);

        switch ($this->_page) {
            case 'item':
                $url = COM_buildURL("{$_CONF['site_url']}/downloads/index.php?id=$this->_lid");
                break;
            case 'flist':
                $url = "{$_CONF['site_url']}/downloads/index.php";
                break;
            case 'list':
            default:
                $url = "{$_CONF['site_url']}/admin/plugins/downloads/index.php";
                break;
        }
        return PLG_afterSaveSwitch('item', $url, 'downloads', 101);
    }

    function _createFilename($name, $table, $field)
    {
        $parts = pathinfo($name);
        $extension = $parts['extension'];
        $filename  = $parts['filename'];
        $count = DB_count($table, $field, addslashes($name));
        $i = 1;
        while ($count > 0) {
            $name = $filename . "_$i." . $extension;
            $count = DB_count($table, $field, addslashes($name));
            $i++;
        }
        return $name;
    }

    // upload or delete snap image
    function _uploadSnapImage()
    {
        global $_TABLES, $_DLM_CONF;

        $newimage_name = COM_applyFilter($_FILES['newfileshot']['name']);
        if (!empty($newimage_name)) {
            $name = $this->_createFilename($newimage_name, $_TABLES['downloads'], 'logourl');
            if (DLM_uploadNewFile($_FILES['newfileshot'], $_DLM_CONF['path_snapstore'], $name)) {
                $this->_logourl = $name;
            }
        } else if ($this->_deletesnap) {
            $this->_logourl = '';
        }
    }

    function _unlinkSnapImage($name)
    {
        global $_TABLES, $_DLM_CONF;

        if (empty($name)) return;
        $target = $_DLM_CONF['path_snapstore'] . DLM_createSafeFileName($name);
        $count = DB_count($_TABLES['downloads'], 'logourl', addslashes($name));
        if ($count == 0) $this->_unlink($target);
    }

    function _unlinkTnImage($name)
    {
        global $_TABLES, $_DLM_CONF;

        if (empty($name)) return;
        $target = $_DLM_CONF['path_tnstore'] . DLM_createSafeFileName($name);
        $target = DLM_changeFileExt($target, $_DLM_CONF['tnimage_format']);
        $count = DB_count($_TABLES['downloads'], 'logourl', addslashes($name));
        if ($count == 0) $this->_unlink($target);
    }

    /**
    * Delete download file information from the database and the file repository
    */
    function delDownload($id='', $switch=true)
    {
        global $_CONF, $_TABLES, $_DLM_CONF;

        $this->_checkHasAccess();

        if (!empty($id)) {
            $lid = addslashes(COM_applyFilter($id));
            $name = DB_getItem($_TABLES['downloads'], 'url', "lid = '$lid'");
        } else {
            $lid = addslashes(COM_applyFilter($_POST['old_lid']));
            $name = COM_applyFilter($_POST['url']);
        }

        $secret_id = DB_getItem($_TABLES['downloads'], 'secret_id', "lid = '$lid'");
        $safename = DLM_createSafeFileName($name, $secret_id);
        $tmpfile = $_DLM_CONF['path_filestore'] . $safename;

        $tmpsnapfile = DB_getItem($_TABLES['downloads'], 'logourl', "lid = '$lid'");

        DB_query("DELETE FROM {$_TABLES['downloads']}     WHERE lid = '$lid'");
        DB_query("DELETE FROM {$_TABLES['downloadvotes']} WHERE lid = '$lid'");

        PLG_itemDeleted($lid, 'downloads');

        $this->_unlink($tmpfile);
        $this->_unlinkSnapImage($tmpsnapfile);
        $this->_unlinkTnImage($tmpsnapfile);

        if ($switch == true) {
            $this->_page = COM_applyFilter($_POST['page']);
            if ($this->_page == 'flist') {
                $url = "{$_CONF['site_url']}/downloads/index.php";
            } else {
                $url = "{$_CONF['site_url']}/admin/plugins/downloads/index.php";
            }
            return PLG_afterSaveSwitch('item', $url, 'downloads', 105);

        } else {
            return;
        }
    }

    /**
    * Delete submitted download file information
    */
    function delNewDownload()
    {
        global $_CONF, $_TABLES, $_DLM_CONF;

        $lid = addslashes(COM_applyFilter($_POST['old_lid']));

        if (DB_count($_TABLES['downloadsubmission'], 'lid', $lid) != 1) {
            return COM_refresh($_CONF['site_admin_url'] . '/moderation.php');
        }

        $result = DB_query("SELECT url, logourl, date "
                         . "FROM {$_TABLES['downloadsubmission']} WHERE lid = '$lid'");
        list($url, $logourl, $date) = DB_fetchArray($result);
        $tmpfilename = $_DLM_CONF['path_filestore'] . 'tmp' . date('YmdHis', $date) . DLM_createSafeFileName($url);
        $tmpshotname = '';
        if (!empty($logourl)) {
            $tmpshotname = $_DLM_CONF['path_snapstore'] . 'tmp' . date('YmdHis', $date) . DLM_createSafeFileName($logourl);
        }

        DB_query("DELETE FROM {$_TABLES['downloadsubmission']} WHERE lid='$lid'");
        $this->_unlink($tmpfilename);
        $this->_unlink($tmpshotname);

        return PLG_afterSaveSwitch('admin', '', 'downloads', 102);
    }

    /**
    * Approve submitted download file information
    */
    function approve()
    {
        global $_TABLES, $_CONF, $_DLM_CONF, $LANG_DLM;

        $this->initCatTree();

        $this->_loadFromArgs($_POST);
        if (empty($this->_lid)) $this->_lid = $this->_old_lid;
        if (empty($this->_cid)) $this->_cid = $this->_cat_tree->getRootid();

        // Move file from tmp directory under the document filestore to the main file directory
        $result = DB_query("SELECT url, logourl, secret_id FROM {$_TABLES['downloadsubmission']} "
                         . "WHERE lid = '" . addslashes($this->_old_lid) . "'");
        list($url, $logourl, $secret_id) = DB_fetchArray($result);
        $this->_secret_id = $secret_id;

        $success = false;
        if (!empty($url)) {
            $tmpfile = $_DLM_CONF['path_filestore'] . 'tmp' . date('YmdHis', $this->_old_date) . DLM_createSafeFileName($url);
            $newfile = $_DLM_CONF['path_filestore'] . DLM_createSafeFileName($url, $secret_id);
            $success = $this->_moveNewFile($tmpfile, $newfile);
            if (!$success) {
                $this->_retry = true;
                $this->_reedit('showEditor', array($this->_editor_mode));
            }
        }

        if ($success && !empty($logourl)) {
            $safename = DLM_createSafeFileName($logourl);
            $tmpfile = $_DLM_CONF['path_snapstore'] . 'tmp' . date('YmdHis', $this->_old_date) . $safename;
            $newfile = $_DLM_CONF['path_snapstore'] . $safename;
            $success = $this->_moveNewFile($tmpfile, $newfile);
            if (!$success) {
                $this->_retry = true;
                $this->_reedit('showEditor', array($this->_editor_mode));
            }
            DLM_makeThumbnail($safename);
        }

        if ($success) {
            $this->_addToDatabase();
            DB_delete($_TABLES['downloadsubmission'], "lid", addslashes($this->_old_lid));

            // Send an email to submitter notifying them that file was approved
            if ($_DLM_CONF['download_emailoption']) {
                DLM_sendNotification($this->_lid);
            }
        }

        return PLG_afterSaveSwitch('admin', '', 'downloads', 102);
    }

    function showPreview($editor_mode)
    {
        $this->_retry = true;
        $this->_reedit('showEditor', array($editor_mode));
    }

    function _moveNewFile($tmpfile, $newfile)
    {
        global $_DLM_CONF;

        if (!file_exists($tmpfile) || is_dir($tmpfile)) {
            COM_errorLog("Downloads: upload approve error: "
                       . "Temporary file does not exist: '" . $tmpfile . "'");
            $this->_errno[] = '1001';
            return false;
        }

        $rename = @rename($tmpfile, $newfile);
        $chown = @chmod($newfile, intval((string)$_DLM_CONF['filepermissions'], 8));

        if (!file_exists($newfile)) {
            COM_errorLog("Downloads: upload approve error: "
                       . "New file does not exist after move of tmp file: '" . $newfile . "'");
            $this->_errno[] = '1002';
            return false;
        }
        return true;
    }

    function _reedit($method, $args = array())
    {
        if ($this->_editor_mode == 'submit') {
            COM_resetSpeedlimit('submit');
        }

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

    // $A  Data for that submission
    function submitNewDownload(&$A)
    {
        global $_CONF, $_TABLES, $_DLM_CONF;

        $this->_initVars();
        $this->_loadFromArgs($A);
        $this->_secret_id = $this->_createSecretID();
        $this->_url     = $_FILES['newfile']['name'];
//        $this->_logourl = $_FILES['newfileshot']['name'];
        if (empty($this->_lid)) $this->_lid = $this->_old_lid;

        // Validate the input values ----------------------->
        if (empty($this->_title)) {
            $this->_errno[] = '1101';
        }
        if (empty($this->_url)) {
            $this->_errno[] = '1102';
        } else {
            if ($this->_lid != $this->_old_lid) {
                $count = DB_count($_TABLES['downloads'], 'lid', addslashes($this->_lid));
                if ($count > 0) {
                    $this->_errno[] = '1202';
                }
            }
        }
        if (empty($this->_description)) {
            $this->_errno[] = '1103';
        }

        if (!empty($this->_errno)) {
            $this->_retry = true;
            $this->_reedit('showEditor', array($this->_editor_mode));
        }
        // Validate the input values -----------------------<

        if (empty($this->_cid)) $this->_cid = ROOTID;

        $success = false;
        if (!SEC_hasRights('downloads.submit')) {

            // Upload New file
            if (!empty($this->_url)) {
                $tmpfilename = 'tmp' . date('YmdHis', $this->_date) . DLM_createSafeFileName($this->_url);
                $success = DLM_uploadNewFile($_FILES['newfile'], $_DLM_CONF['path_filestore'], $tmpfilename);
                if ($success) {
                    $this->_size = filesize($_DLM_CONF['path_filestore'] . $tmpfilename);
                    $this->_md5  = md5_file($_DLM_CONF['path_filestore'] . $tmpfilename);
                }
            }

            // Upload New file snapshot image
            if ($success && !empty($_FILES['newfileshot']['name'])) {
                $this->_logourl = $_FILES['newfileshot']['name'];
                $tmpshotname = 'tmp' . date('YmdHis', $this->_date) . DLM_createSafeFileName($this->_logourl);
                $success = DLM_uploadNewFile($_FILES['newfileshot'], $_DLM_CONF['path_snapstore'], $tmpshotname);
            }

            $mode = 'submission';

        } else {

            // Upload New file
            if (!empty($this->_url)) {
                $safename = DLM_createSafeFileName($this->_url, $this->_secret_id);
                $success = DLM_uploadNewFile($_FILES['newfile'], $_DLM_CONF['path_filestore'], $safename);
                if ($success) {
                    $this->_size = filesize($_DLM_CONF['path_filestore'] . $safename);
                    $this->_md5  = md5_file($_DLM_CONF['path_filestore'] . $safename);
                }
            }

            // Upload New file snapshot image
            if ($success) {
                $this->_uploadSnapImage();
                DLM_makeThumbnail(DLM_createSafeFileName($this->_logourl));
            }

            $mode = '';
        }

        if ($success) {
            $this->_addToDatabase($mode);
            $msg = $_DLM_CONF['download_emailoption'] ? 109 : 115;
            echo PLG_afterSaveSwitch('home', '', 'downloads', $msg);
        } else {
            echo PLG_afterSaveSwitch('home', '', 'downloads', 108);
        }
        exit();
    }

    function _modifyTags($tags)
    {
        $tags = trim($tags);
        $tags = str_replace(array("'", '"', "`", ";", ":", ",", "\\") , ' ', $tags);
        $tags = preg_replace('/\s+/', ' ', $tags);
        $ta = explode(' ', $tags);
        $ta = array_unique($ta);
        $tags = implode(' ', $ta);

        return $tags;
    }
}

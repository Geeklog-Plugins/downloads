<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/functions.inc                                           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2010-2020 dengen - taharaxp AT gmail DOT com                |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die('This file can not be used on its own.');
}

if (!defined ('UC_SELECTED')) define('UC_SELECTED', (XHTML == '') ? 'selected' : 'selected="selected"');
if (!defined ('UC_CHECKED'))  define('UC_CHECKED',  (XHTML == '') ? 'checked'  : 'checked="checked"'  );
if (!defined ('UC_READONLY')) define('UC_READONLY', (XHTML == '') ? 'readonly' : 'readonly="readonly"');
if (!defined ('UC_DISABLED')) define('UC_DISABLED', (XHTML == '') ? 'disabled' : 'disabled="disabled"');

define('DLM_ROOTID', 'root');
define('DOWNLOADS_VERSION', '1.3.0');

/**
* Include language file
*/
global $LANG24, $LANG32, $LANG_postmodes;

$dlm_plugin_path = $_CONF['path'] . 'plugins/downloads/';
$dlm_langfile = $dlm_plugin_path . 'language/' . $_CONF['language'] . '.php';

if (file_exists($dlm_langfile)) {
    require_once $dlm_langfile;
} else {
    require_once $dlm_plugin_path . 'language/english.php';
}
unset($dlm_plugin_path, $dlm_langfile);

global $_DB_table_prefix, $_TABLES; // Needed when enabling plugin

$_TABLES['downloadcategories'] = $_DB_table_prefix . 'downloadcategories';
$_TABLES['downloads']          = $_DB_table_prefix . 'downloads';
$_TABLES['downloadvotes']      = $_DB_table_prefix . 'downloadvotes';
$_TABLES['downloadhistories']  = $_DB_table_prefix . 'downloadhistories';
$_TABLES['downloadsubmission'] = $_DB_table_prefix . 'downloadsubmission';
$_TABLES['downloadsubmissionhistory'] = $_DB_table_prefix . 'downloadsubmissionhistory';


/**
* Check and see if we need to load the plugin configuration
*/
$_DLM_CONF = array();
require_once $_CONF['path_system'] . 'classes/config.class.php';

/*
 * Load Downloads runtime configuration.
 *
 * Keep temporary variables plugin-prefixed and remove them immediately after
 * use. This preserves the long-standing activation-safe bootstrap behaviour
 * while avoiding leakage into Geeklog's global namespace.
 */
$dlm_config_instance = config::get_instance();
$dlm_loaded_config = $dlm_config_instance->get_config('downloads');
if (is_array($dlm_loaded_config)) {
    $_DLM_CONF = array_merge($_DLM_CONF, $dlm_loaded_config);
}
unset($dlm_config_instance, $dlm_loaded_config);

$_DLM_CONF['lang_id'] = COM_getLanguageId();

$_DLM_CONF['date_format'] = '%Y-%m-%d %H:%M';

$_DLM_CONF['enabled_preview_on_upload'] = false;

// +---------------------------------------------------------------------------+
// | Geeklog Plugin API Implementation                                         |
// +---------------------------------------------------------------------------+

/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
* @return   mixed   menu entry, or boolean false if disabled / hidden
*
*/
function plugin_getmenuitems_downloads()
{
    global $_CONF, $_USER, $_DLM_CONF, $LANG_DLM;

    if (($_DLM_CONF['hidemenu'] == 1) ||
        (COM_isAnonUser() && ($_CONF['loginrequired'] || $_DLM_CONF['loginrequired']))) {
        return false;
    }

    $menuitems[$LANG_DLM['downloads']] = $_CONF['site_url'] . '/downloads/index.php';

    return $menuitems;
}


/**
* Return headlines for New Downloads section in the What's New block, if enabled
*
* @return   mixed       array(headline, byline), or boolean false is disabled
*
*/
function plugin_whatsnewsupported_downloads()
{
    global $_CONF, $LANG_DLM, $_DLM_CONF, $LANG_WHATSNEW;

    if ($_DLM_CONF['download_whatsnew'] == 1) {
        return array($LANG_DLM['WhatsNewLabel'],
                     COM_formatTimeString($LANG_WHATSNEW['new_last'],
                     $_DLM_CONF['whatsnew_perioddays'] * 60 * 60 * 24));
    }

    return false;
}

function DLM_helper_getLangSQL($category_alias = 'b')
{
    global $_DLM_CONF;

    if (empty($_DLM_CONF['lang_id'])) {
        return '';
    }

    // Alias is always supplied internally; keep it restricted to an SQL identifier.
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $category_alias)) {
        $category_alias = 'b';
    }

    $len = strlen($_DLM_CONF['lang_id']) + 1;
    return "AND RIGHT(" . $category_alias . ".cid, $len)='_{$_DLM_CONF['lang_id']}' ";
}

/**
 * Log an entry
 *
 * @param  string  $entry
 */
function DLM_errorLog($entry)
{
	if (COM_versionCompare(VERSION, '2.2.1', '>=')) {
        Geeklog\Log::downloads($entry, '');
    } else {
        COM_errorLog($entry);
    }
}

/**
* Return new downloads for the What's New block
*
* @return   string  HTML list of new downloads
*
*/
function plugin_getwhatsnew_downloads()
{
    global $_CONF, $_TABLES, $_DLM_CONF, $LANG_DLM;

    $retval = LB;

    $sql = "SELECT a.lid, a.title FROM {$_TABLES['downloads']} a "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
         . "WHERE date >= UNIX_TIMESTAMP( DATE_SUB( NOW(), INTERVAL {$_DLM_CONF['whatsnew_perioddays']} DAY )) "
         . "AND (is_released = 1) "
         . DLM_helper_getLangSQL()
         . COM_getPermSQL('AND', 0, 2, 'b')
         . " ORDER BY date DESC LIMIT " . $_DLM_CONF['download_newdownloads'];
    $result = DB_query($sql);
    $nrows1 = DB_numRows($result);

    if ($nrows1 == 0) {
        $retval .= $LANG_DLM['no_new_files'] . '<br' . XHTML . '>' . LB;
    } else {
        $items = array();
        while ($A = DB_fetchArray($result)) {
            $title = COM_truncate(stripslashes($A['title']), $_CONF['title_trim_length'], '...');
            $url = COM_buildUrl($_CONF['site_url'] . '/downloads/index.php?id=' . $A['lid']);
            $items[] = COM_createLink($title, $url);
        }
        $retval .= COM_makeList($items, 'list-new-downloads');
    }

    return $retval;
}


/**
* Return new Downloads comments for the What's New block
*
* @param    string  $numreturn  If 0 will return results for What's New Block. 
*                               If > 0 will return last X new comments for User Profile.
* @param    string  $uid        ID of the user to return results for. 0 = all users.
* @return   array list of new comments (dups, type, title, sid, lastdate) or (sid, title, cid, unixdate)
*
*/
function plugin_getwhatsnewcomment_downloads($numreturn = 0, $uid = 0)
{
    global $_CONF, $_TABLES, $_DLM_CONF;

    $stwhere = ($uid > 0) ? "AND (c.uid = $uid) " : ' ';

    if ($numreturn == 0) {
        $select = "SELECT DISTINCT COUNT(*) AS dups, type, c.title, sid, max(c.date) AS lastdate ";
		$onlyNew = " AND (c.date >= (DATE_SUB(NOW(), INTERVAL {$_CONF['newcommentsinterval']} SECOND))) ";
        $order  = "GROUP BY c.sid, type, d.title ORDER BY 5 DESC LIMIT 15";
    } else {
        $select = "SELECT c.sid, c.title, c.cid, UNIX_TIMESTAMP(c.date) AS unixdate ";
		$onlyNew = " ";
        $order  = "ORDER BY unixdate DESC LIMIT $numreturn";
    }

    $sql = $select . "FROM {$_TABLES['comments']} c "
         . "LEFT JOIN {$_TABLES['downloads']} d ON (d.lid = c.sid) "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON (d.cid = b.cid) "
         . "WHERE (d.commentcode >= 0) "
         . "AND (is_released = 1) "
         . DLM_helper_getLangSQL()
         . COM_getPermSQL('AND', 0, 2, 'b')
         . $onlyNew 
         . $stwhere . $order;

    $result = DB_query($sql);
    $nrows = DB_numRows($result);
    if ($nrows > 0) {
        for ($x = 0; $x < $nrows; $x++) {
            $A[] = DB_fetchArray($result);
        }
        
        return $A;
    }
}

/**
 * Did user submit any downloads
 *
 * @param    int     $uid
 * @return   string        number of pages user contributed. If nothing leave blank
 */
function plugin_usercontributed_downloads($uid)
{
    global $_TABLES, $LANG_DLM;

    $retval = '';

    // Include downloads and downloads submissions
    $count = DB_getItem($_TABLES['downloads'], 'COUNT(owner_id)', "owner_id = {$uid}") + DB_getItem($_TABLES['downloadsubmission'], 'COUNT(owner_id)', "owner_id = {$uid}");

    if ($count > 0) {
        $retval = str_replace('%s', $count, $LANG_DLM['num_downloads']);
    }

    return $retval;
}

/**
* Implements the [download:] autotag.
*
* @param    string  $op         operation to perform
* @param    string  $content    item (e.g. story text), including the autotag
* @param    array   $autotag    parameters used in the autotag
* @param    mixed               tag names (for $op='tagname') or formatted content
*
*/
function plugin_autotags_downloads($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES;

    if ($op == 'tagname') {
        return 'download';
    } else if ($op == 'parse') {
        $file_id = COM_applyFilter($autotag['parm1']);
        $url = COM_buildUrl($_CONF['site_url'] . '/downloads/index.php?id=' . $file_id);
        if (empty($autotag['parm2'])) {
            $linktext = stripslashes(DB_getItem($_TABLES['downloads'],
                                     'title', "lid = '" . DB_escapeString($file_id) . "'"));
        } else {
            $linktext = $autotag['parm2'];
        }
        $link = COM_createLink($linktext, $url) . LB;
        $content = str_replace ($autotag['tagstr'], $link, $content);

        return $content;
    }
}


/**
* Called by the plugin Editor to display the current plugin code version
* This may be different then the version installed and registered currently.
* If newer then you may want to run the update
*/
function plugin_chkVersion_downloads()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/downloads/autoinstall.php';

    $inst_parms = plugin_autoinstall_downloads('downloads');

    return $inst_parms['info']['pi_version'];    
}


/**
* Shows the statistics for the Downloads Plugin on stats.php.
* If $showsitestats is 1 then we are to only print the overall stats in the
* 'site statistics box' otherwise we show the detailed stats
*
* @param    int     $showsitestate  Flag to let us know which stats to get
* @param    string                  HTML for the stats section
*
*/
function plugin_showstats_downloads($showsitestats)
{
    global $_CONF, $_TABLES, $LANG_DLM, $_DLM_CONF;

    require_once ($_CONF['path_system'] . 'lib-admin.php');

    $retval = '';

    $sql = "SELECT a.lid, a.title, a.url, hits FROM {$_TABLES['downloads']} a "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
         . "WHERE hits > 0 "
         . DLM_helper_getLangSQL()
         . COM_getPermSQL('AND', 0, 2, 'b')
         . " ORDER BY hits DESC LIMIT 10";
    $result = DB_query($sql);
    $nrows  = DB_numRows($result);
    if ($nrows > 0) {
        $header_arr = array(
            array('text'         => $LANG_DLM['stats_page_title'],
                  'field'        => 'title',
                  'header_class' => 'stats-header-title'
            ),
            array('text'         => $LANG_DLM['stats_hits'],
                  'field'        => 'hits',
                  'header_class' => 'stats-header-count',
                  'field_class'  => 'stats-list-count'
            )
        );
        $data_arr = array();
        $text_arr = array('has_menu' => false,
                          'title'    => $LANG_DLM['stats_headline'],
        );
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result);
            $title = stripslashes(str_replace('$', '&#36;', $A['title']));
            $url = COM_buildUrl($_CONF['site_url'] . '/downloads/index.php?id=' . $A['lid']);
            $title = COM_createLink($title, $url, array('title' => $A['url']));
            $hits = COM_numberFormat($A['hits']);
            $data_arr[] = array('title' => $title,
                                'hits'  => $hits
                          );
        }
        $retval .= ADMIN_simpleList ('', $header_arr, $text_arr, $data_arr);
    } else {
        $retval .= COM_startBlock($LANG_DLM['stats_headline']);
        $retval .= $LANG_DLM['stats_no_hits'];
        $retval .= COM_endBlock();
    }

    return $retval;
}


/**
* New stats plugin API function for proper integration with the site stats
*
* @return   array(item text, item count);
*
*/
function plugin_statssummary_downloads()
{
    global $_TABLES, $LANG_DLM;

    $sql = "SELECT COUNT(*), SUM(hits) FROM {$_TABLES['downloads']} a "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
         . COM_getPermSQL('WHERE', 0, 2, 'b');
    list($total_files, $total_hits) = DB_fetchArray(DB_query($sql));

    $item_count = COM_numberFormat($total_files)
                . ' (' . COM_numberFormat($total_hits) . ')';

    return array($LANG_DLM['stats_download'], $item_count);
}


/**
* Geeklog is asking us to provide any items that show up in the type
* drop-down on search.php.  Let's users search for downloads.
*
* @return   array   (plugin name/entry title) pair for the dropdown
*
*/
function plugin_searchtypes_downloads()
{
    global $LANG_DLM;

    $tmp['downloads'] = $LANG_DLM['searchlabel'];

    return $tmp;
}


/**
* This searches for downloads matching the user query and returns an array for the
* header and table rows back to search.php where it will be formated and printed
*
* @param    string  $query      Keywords user is looking for
* @param    date    $datestart  Start date to get results for
* @param    date    $dateend    End date to get results for
* @param    string  $topic      The topic they were searching in
* @param    string  $type       Type of items they are searching, or 'all' (deprecated)
* @param    int     $author     Get all results by this author
* @param    string  $keyType    search key type: 'all', 'phrase', 'any'
* @param    int     $page       page number of current search (deprecated)
* @param    int     $perpage    number of results per page (deprecated)
* @return   object              search result object
*
*/
function plugin_dopluginsearch_downloads($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_TABLES, $LANG_DLM;

    // Make sure the query is SQL safe
    $query = trim(DB_escapeString($query));

    // downloads search
    $sql  = "SELECT lid AS id, a.title AS title, date, a.owner_id AS uid, hits, "
          . "CONCAT(description, ' ', detail) AS description, "
          . "CONCAT('/downloads/index.php?id=', lid) AS url "
          . "FROM {$_TABLES['downloads']} AS a "
          . "LEFT JOIN {$_TABLES['downloadcategories']} AS b ON b.cid=a.cid "
          . "WHERE is_released=1 "
          . "AND date<=UNIX_TIMESTAMP(NOW()) "
          . COM_getPermSQL('AND', 0, 2, 'b'). ' ';
	
	if (!empty($datestart) || !empty($dateend)) {
		// Do some date checking and fill in missing dates
		$delim = '-';
		if (empty($datestart) || (strtotime($datestart) == false)) {
			$datestart = "0000-00-00";
		} else {
			$datestart = date('Y-m-d', strtotime($datestart));
		}		if (empty($dateend) || (strtotime($dateend) == false)) {
			$dateend = date('Y-m-d');
		} else {
			$dateend = date('Y-m-d', strtotime($dateend));
		}

		$DS = explode($delim, $datestart);
		$DE = explode($delim, $dateend);
		$startdate = mktime(0,0,0,$DS[1],$DS[2],$DS[0]);
		$enddate = mktime(23,59,59,$DE[1],$DE[2],$DE[0]);

		$sql .= "AND (date BETWEEN '$startdate' AND '$enddate') ";
	}	

    if (!empty($author)) {
        $sql .= "AND (a.owner_id = '$author') ";
    }

    $search = new SearchCriteria('downloads', $LANG_DLM['plugin_name']);
    $columns = array('title' => 'a.title', 'description');
    list($sql_tmp,$ftsql_tmp) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql_tmp);
    $search->setFTSQL($ftsql_tmp);
    $search->setRank(3);
    $search->setAppendQuery(false);
    $search->setURLRewrite(true);

    return $search;
}


/**
* This will put an option for downloads in the command and control block on
* moderation.php
*
* @return   mixed   array(title, url, icon), or boolean false when not allowed
*
*/
function plugin_cclabel_downloads()
{
    global $_CONF, $LANG_DLM;

    if (SEC_hasRights('downloads.edit')) {
        return array($LANG_DLM['plugin_name'],
                     $_CONF['site_admin_url']  . '/plugins/downloads/index.php',
                     plugin_geticon_downloads());
    }

    return false;
}


/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_downloads()
{
    global $_CONF;

    return $_CONF['site_url'] . '/downloads/images/download_icon.png';
}


/**
* returns the administrative option for this plugin
*
* @return   mixed   array(title, url, num. downloads), or void when not allowed
*
*/
function plugin_getadminoption_downloads()
{
    global $_CONF, $_TABLES, $LANG_DLM;

    if (SEC_hasRights('downloads.edit')) {
        $sql  = "SELECT COUNT(*) FROM {$_TABLES['downloads']} a "
              . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
              . "WHERE lid != '' "
              . COM_getPermSQL('AND', 0, 2, 'b');
        $result = DB_query($sql);

        list($total) = DB_fetchArray($result);

        return array($LANG_DLM['admin_menu'],
                     $_CONF['site_admin_url'] . '/plugins/downloads/index.php',
                     $total);
    }
}


/**
* Setup the user menu options for this plugin
*
*/
/**
 * Provide concise native configuration tooltips.
 *
 * @param string $id
 * @return string
 */
function plugin_getconfigtooltip_downloads($id)
{
    global $LANG_DLM;

    $key = 'config_tooltip_' . $id;
    return isset($LANG_DLM[$key]) ? $LANG_DLM[$key] : '';
}

function plugin_getuseroption_downloads()
{
    global $_CONF, $LANG_DLM, $_DLM_CONF;

    if (COM_isAnonUser()) {
        return false;
    }

    if (SEC_hasRights('downloads.upload') || $_DLM_CONF['download_uploadselect'] == 1) {
        return array($LANG_DLM['my_downloads'],
                     $_CONF['site_url'] . '/downloads/submissions.php');
    }

    return false;
}


/**
* Check to see if this Plugin uses comments
*
* Geeklog is checking to see if this plugin supports comments, tell it no!
* NOTE: to support comments you must use the same date/time based ID for your
* widget.  In other words, to make primary keys for your plugin you should call
* makesid().  Comments are keyed off of that...it is a limitation on how geeklog
* does comments.
*
* @return   boolean true or false
*
*/
function plugin_commentsupport_downloads()
{
    return true;
}

/**
 * Provide URL for the link to a comment's parent item.
 * NOTE: The Plugin API does not support $_CONF['url_rewrite'] here,
 *       so we'll end up with a non-rewritten URL ...
 *
 * @return   string   string of URL
 */
function plugin_getcommenturlid_downloads($id)
{
    global $_CONF;

	return $_CONF['site_url'] . "/downloads/index.php?id=$id";
}

/**
 * Return the comment code to this plugin item. This is based not only the code of the actual plugin item but the access the user has to the item
 *
 * @param   string $id   Item id to which $cid belongs
 * @param   int    $uid  user id or 0 = current user
 * @return  int    Return a CommentCode: COMMENT_CODE_ENABLED (0), COMMENT_CODE_DISABLED (-1), COMMENT_CODE_CLOSED (1)
 */
function plugin_commentenabled_downloads($id, $uid = 0)
{
    global $_CONF, $_TABLES;

    // CommentCode: COMMENT_CODE_ENABLED (0), COMMENT_CODE_DISABLED (-1), COMMENT_CODE_CLOSED (1)
    $commentCode = COMMENT_CODE_DISABLED;

	// Grab comment code from download file and rest of permission from downlaod category (since download file only has owner and no other permission settings)
    $sql = "SELECT a.commentcode, b.owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon "
                     . "FROM {$_TABLES['downloads']} a "
                     . "LEFT JOIN {$_TABLES['downloadcategories']} b ON b.cid=a.cid "
                     . "WHERE lid = '" . DB_escapeString($id) . "'" . COM_getPermSQL('AND', $uid, 2, 'b');					 
					 
    $result = DB_query($sql);
    $A = DB_fetchArray($result);
    if (DB_numRows($result) == 1) {
        if ($A['commentcode'] == COMMENT_CODE_ENABLED) {
            $commentCode = COMMENT_CODE_ENABLED;
        } elseif ($A['commentcode'] == COMMENT_CODE_CLOSED) { // Closed but still visible so give admins access
            if (SEC_hasRights('downloads.edit', 'AND', $uid) &&
                (SEC_hasAccess($A['owner_id'], $A['group_id'],
                        $A['perm_owner'], $A['perm_group'], $A['perm_members'],
                        $A['perm_anon'], $uid) == 3)) {
                $commentCode = COMMENT_CODE_ENABLED; // If Admin then treat comment like enabled
            } else {
                $commentCode = COMMENT_CODE_CLOSED;
            }
        }
    }

    return $commentCode;
}

/**
 * Plugin function that is called after comment form is submitted.
 * Needs to atleast save the comment and check return value.
 * Add any additional logic your plugin may need to perform on comments.
 *
 * @author Blaine Lang blaine@portalparts.com
 * @param   string  $title   Comment title field in comment form
 * @param   string  $comment comment text
 * @param   string  $id     Item id to which $cid belongs
 * @param   int     $pid    comment parent
 * @param   string  $postmode 'html' or 'text'
 * @return  mixed   HTML string (redirect?) for success or comment form if failure.
 */
function plugin_savecomment_downloads($title, $comment, $id, $pid, $postmode)
{
    global $_CONF, $_TABLES, $LANG03;

    $retval = '';
	
    // Use plugin_commentenabled_foo permission check to determine if user has permissions to save a comment for this item
    // CommentCode: COMMENT_CODE_ENABLED (0), COMMENT_CODE_DISABLED (-1), COMMENT_CODE_CLOSED (1)
    if (plugin_commentenabled_downloads($id) != COMMENT_CODE_ENABLED) {
        COM_handle404($_CONF['site_url'] . '/index.php');
    }

    $ret = CMT_saveComment($title, $comment, $id, $pid, 'downloads', $postmode);
    if ($ret > 0) { // failure //FIXME: some failures should not return to comment form
        $retval = CMT_commentForm($title, $comment, $id, $pid, 'downloads',
                                  $LANG03[14], $postmode);
        if (!defined('COMMENT_ON_SAME_PAGE') || !COMMENT_ON_SAME_PAGE) {
            $retval = COM_createHTMLDocument($retval, array('pagetitle' => $LANG03[1]));
        }
    } else { // success
        COM_redirect(COM_buildURL($_CONF['site_url'] . "/downloads/index.php?id=$id"));
    }

    return $retval;
}

/**
 * Comment Submission approved
 *
 * @param   string $id       Item id to which $cid belongs
 * @param   int     $cid    Approved Comment id
 */
function plugin_moderationcommentapprove_downloads($id, $cid)
{
    // Nothing needed to do on approval of comment to plugin item
    
    return true;
}

/**
 * Delete a comment
 *
 * @param   int    $cid Comment to be deleted
 * @param   string $id  Item id to which $cid belongs
 * @param   boolean $returnOption  Either return a boolean on success or not, or redirect
 * @return  mixed        Based on $returnOption. false for failure or true for success, else a redirect for success or failure
 */
function plugin_deletecomment_downloads($cid, $id, $returnBoolean)
{
    global $_CONF, $_TABLES, $_USER;

    $retval = false;

    $has_editPermissions = SEC_hasRights('downloads.edit');
    $result = DB_query("SELECT a.owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon "
                     . "FROM {$_TABLES['downloads']} a "
                     . "LEFT JOIN {$_TABLES['downloadcategories']} b ON b.cid=a.cid "
                     . "WHERE lid = '" . DB_escapeString($id) . "'");
    $A = DB_fetchArray($result);
    $access = SEC_hasAccess($A['owner_id'], $A['group_id'],
                            $A['perm_owner'], $A['perm_group'],
                            $A['perm_members'], $A['perm_anon']);

    if ($has_editPermissions && $access == 3) {
        CMT_deleteComment($cid, $id, 'downloads');
        if ($returnBoolean) {
            $retval = true;
        } else {		
			COM_redirect(COM_buildURL($_CONF['site_url'] . "/downloads/index.php?id=$id"));
		}
    } else {
        DLM_errorLog("User {$_USER['username']} (IP: {$_SERVER['REMOTE_ADDR']}) "
                   . "tried to illegally delete comment $cid from downloads $id");
        if ($returnBoolean) {
            $retval = false;
        } else {				   
			COM_redirect($_CONF['site_url'] . '/index.php');
		}
    }

    return $retval;
}


/**
* Plugin should display [a] comment[s]
*
* @param   string  $id     Unique idenifier for item comment belongs to
* @param   int     $commentid    Comment id to display (possibly including sub-comments)
* @param   string  $title  Page/comment title
* @param   string  $order  'ASC' or 'DESC' or blank
* @param   string  $format 'threaded', 'nested', or 'flat'
* @param   int     $page   Page number of comments to display
* @param   boolean $view   True to view comment (by cid), false to display (by $pid)
* @return  mixed   results of calling the plugin_displaycomment_ function
*/
function plugin_displaycomment_downloads($id, $commentid, $title, $order, $format, $page, $view)
{
    global $_TABLES, $LANG_DLM, $LANG_ACCESS;

    $retval = '';

    $sql = "SELECT COUNT(*) AS count, commentcode, a.owner_id, group_id, perm_owner, "
         . "perm_group, perm_members, perm_anon "
         . "FROM {$_TABLES['downloads']} a "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON b.cid=a.cid "
         . "WHERE lid = '" . DB_escapeString($id) . "' " . COM_getPermSQL('AND', 0, 2, 'b')
         . " GROUP BY lid";

    $result = DB_query($sql);
    $allowed = 0;
    $A = array();
    if (DB_numRows($result) == 1) {
        $A = DB_fetchArray($result);
        $allowed = (int) $A['count'];
    }

    if ($allowed == 1) {
        $delete_option = (SEC_hasRights('downloads.edit') &&
                (SEC_hasAccess($A['owner_id'], $A['group_id'],
                    $A['perm_owner'], $A['perm_group'], $A['perm_members'],
                    $A['perm_anon']) == 3));
        $retval .= CMT_userComments($id, $title, 'downloads', $order, $format,
                                    $commentid, $page, $view, $delete_option,
                                    $A['commentcode']);
    } else {
        $retval .= COM_startBlock($LANG_ACCESS['accessdenied'], '',
                        COM_getBlockTemplate('_msg_block', 'header'))
                . $LANG_DLM['deny_msg']
                . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    }

    return $retval;
}


/**
* Automatic uninstall function for plugins
*
* @return   array
*
* This code is automatically uninstalling the plugin.
* It passes an array to the core code function that removes
* tables, groups, features and php blocks from the tables.
* Additionally, this code can perform special actions that cannot be
* foreseen by the core code (interactions with other plugins for example)
*
*/
function plugin_autouninstall_downloads()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/downloads/autoinstall.php';

    return DLM_autouninstall();
}


/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_downloads()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/downloads/autoinstall.php';

    return DLM_upgrade();
}


/**
* A user is about to be deleted. Update ownership of any downloads owned
* by that user or delete them.
*
* @param    int     $uid    User id of deleted user
* @return   void
*
*/
function plugin_user_delete_downloads($uid)
{
    global $_CONF, $_TABLES, $_DLM_CONF;

    if (DB_count($_TABLES['downloads'], 'owner_id', $uid) == 0) return;

    if ($_DLM_CONF['delete_download'] == 1) {
        // delete the downloads
        require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';
        require_once $_CONF['path'] . 'plugins/downloads/include/download.class.php';
        $dldl = new DLDownload();
        $result = DB_query("SELECT lid FROM {$_TABLES['downloads']} WHERE owner_id = $uid");
        while (list($lid) = DB_fetchArray($result)) {
            $dldl->delDownload($lid, false);
        }
        return;
    }
    // assign ownership to a user from the Root group
    $rootgroup = DB_getItem($_TABLES['groups'], 'grp_id', "grp_name = 'Root'");
    $result = DB_query("SELECT DISTINCT ug_uid FROM {$_TABLES['group_assignments']} "
                     . "WHERE ug_main_grp_id = $rootgroup AND ug_uid IS NOT NULL "
                     . "ORDER BY ug_uid LIMIT 1");
    if (DB_numRows($result) == 1) {
        list($rootuser) = DB_fetchArray($result);
        DB_query("UPDATE {$_TABLES['downloads']} SET owner_id = " . (int) $rootuser
               . " WHERE owner_id = " . (int) $uid);
    } else {
        DLM_errorLog("Downloads: user deletion warning: no Root user available for ownership reassignment.");
    }
}


/**
* Get header code for inclusion
*
* @return   string
*
*/
/**
 * Return deterministic cache version for a shipped asset.
 *
 * @param string $file Absolute filesystem path
 * @return string
 */
function DLM_getAssetVersion($file)
{
    $version = DOWNLOADS_VERSION;
    if (is_file($file)) {
        $mtime = @filemtime($file);
        if ($mtime !== false) {
            $version .= '-' . (int) $mtime;
        }
    }

    return $version;
}

/**
 * Return a public URL for a plugin asset with deterministic cache versioning.
 *
 * @param string $url
 * @param string $file
 * @return string
 */
function DLM_getVersionedAssetUrl($url, $file)
{
    global $_CONF;

    if (stripos($url, 'http://') !== 0
        && stripos($url, 'https://') !== 0
        && strpos($url, '//') !== 0
    ) {
        $url = rtrim($_CONF['site_url'], '/') . '/' . ltrim($url, '/');
    }

    return $url . '?v=' . rawurlencode(DLM_getAssetVersion($file));
}

function plugin_getheadercode_downloads()
{
    global $_CONF;

    $retval = '';

    // Use the CSS only if we are on the plugin's pages.
    if (substr_count($_SERVER['PHP_SELF'], '/downloads/') > 0) {
        $css_url = CTL_plugin_themeFindFile('downloads', 'css', 'downloads.css', true);
        $css_file = CTL_plugin_themeFindFile('downloads', 'css', 'downloads.css', false);
        if (!empty($css_url) && !empty($css_file)) {
            $href = DLM_getVersionedAssetUrl($css_url, $css_file);
            $retval = '<link rel="stylesheet" href="'
                    . htmlspecialchars($href, ENT_QUOTES, 'UTF-8')
                    . '" type="text/css"' . XHTML . '>' . LB;
        }
    }

    return $retval;
}


function DLM_helper_changeCatName_by_language($catid, $name)
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


/**
* Do we support feeds?
*
* @return   array   id/name pairs of all supported feeds
*
*/
function plugin_getfeednames_downloads()
{
    global $_CONF, $_TABLES, $LANG_DLM;

    $feeds = array();
    $result = DB_query("SELECT cid, title "
                     . "FROM {$_TABLES['downloadcategories']} "
                     . COM_getPermSQL() . " ORDER BY title ASC");
    $num = DB_numRows($result);

    if ($num > 0) {
        $feeds[] = array('id' => DLM_ROOTID, 'name' => $LANG_DLM['main']);

        if (isset($_CONF['languages'])) {
            foreach ($_CONF['languages'] as $langid => $lang) {
                $feeds[] = array('id'   => DLM_ROOTID . '_' . $langid ,
                                 'name' => $LANG_DLM['main'] . ' (' . $lang . ')');            }
        }

        for ($i = 0; $i < $num; $i++) {
            $A = DB_fetchArray($result);
            $A['title'] = DLM_helper_changeCatName_by_language($A['cid'], $A['title']);
            $feeds[] = array('id' => $A['cid'], 'name' => $A['title']);
        }
    }

    return $feeds;
}


/**
* Helper function: Build part of an SQL request
*
* @param    string  $cid        category id
* @param    string  $limits     limit (number of entries or number of hours)
* @return   string              part of an SQL request
*
*/
function DLM_helper_buildSQL($cid, $limits)
{
    global $_CONF, $_TABLES, $LANG_DLM;

    require_once $_CONF['path'] . 'plugins/downloads/include/gltree.class.php';
    $mytree = new GLTree($_TABLES['downloadcategories'], 'cid', 'pid', 'title', '', DLM_ROOTID);

    $lang_id = '';
    if (isset($_CONF['languages'])) {
        foreach ($_CONF['languages'] as $langid => $lang) {
            if ($cid == DLM_ROOTID . '_' . $langid) {
                $cid = DLM_ROOTID;
                $lang_id = $langid;
                break;
            }
        }
    }

    $where = '';
    if ($cid != DLM_ROOTID) {
        $carr = $mytree->getAllChildId($cid);
        $carr = array_merge(array($cid), $carr);
        $where = " cid IN ('" . implode("','", $carr) . "')";
    } else if (!empty($lang_id)) {
        $mytree->setLangid($lang_id);
        $carr = $mytree->getAllChildId(DLM_ROOTID);
        $where = " cid IN ('" . implode("','", $carr) . "')";
    }

    $limitsql = ' LIMIT 10';
    if (!empty($limits)) {
        $limitsql = ' LIMIT ' . $limits;
        if (substr($limits, -1) == 'h') { // last xx hours
            $limitsql = '';
            $hours = substr($limits, 0, -1);
            if (!empty($where)) {
                $where .= ' AND';
            }
            $where .= " date >= DATE_SUB(NOW(),INTERVAL $hours HOUR)";
        }
    }

    if (!empty($where)) {
        $where = ' WHERE' . $where;
    }

    $sql = $where . ' ORDER BY date DESC' . $limitsql;

    return $sql;
}

/**
* Provide feed data
*
* @param    int     $feed       feed ID
* @param    ref     $link
* @param    ref     $update
* @return   array               feed entries
*
*/
function plugin_getfeedcontent_downloads($feed, &$link, &$update)
{
    global $_CONF, $_TABLES;

    $result = DB_query("SELECT topic, limits FROM {$_TABLES['syndication']} WHERE fid = $feed");
    $F = DB_fetchArray($result);

    $sql = "SELECT lid, title, owner_id, description, date "
         . "FROM {$_TABLES['downloads']}"
         . DLM_helper_buildSQL($F['topic'], $F['limits']);
    $result = DB_query($sql);

    $content = array();
    $lids = array();
    $num = DB_numRows($result);
    for ($i = 0; $i < $num; $i++)
    {
        $A = DB_fetchArray($result);
        $lids[] = $A['lid'];
        $title = stripslashes($A['title']);
        $desc = PLG_replaceTags(stripslashes($A['description']));
        $filelink = COM_buildURL($_CONF['site_url'] . '/downloads/index.php?id=' . $A['lid']);
        $content[] = array('title'   => $title,
                           'summary' => $desc,
                           'link'    => $filelink,
                           'uid'     => $A['owner_id'],
                           'author'  => COM_getDisplayName( $A['owner_id'] ),
                           'date'    => $A['date'],
                           'format'  => 'plaintext'
                          );
    }

    $link = $_CONF['site_url'] . '/downloads/index.php';
    $update = implode(',', $lids);

    return $content;
}

/**
* Checking if downloads feeds are up to date
*
* @param    int     $feed           id of feed to be checked
* @param    string  $topic          topic (actually: category)
* @param    string  $update_data    data describing current feed contents
* @param    string  $limit          number of entries or number of hours
* @param    string  $updated_type   (optional) type of feed to be updated
* @param    string  $updated_topic  (optional) feed's "topic" to be updated
* @param    string  $updated_id     (optional) id of entry that has changed
* @return   boolean                 true: feed data is up to date; false: isn't
*
*/
function plugin_feedupdatecheck_downloads($feed, $topic, $update_data, $limit,
                    $updated_type = '', $updated_topic = '', $updated_id = '')
{
    global $_TABLES;

    $is_current = true;

    if ($updated_type != 'downloads') {
        // we're not interested
        $updated_type = '';
        $updated_topic = '';
        $updated_id = '';
    }

    $sql = "SELECT lid "
         . "FROM {$_TABLES['downloads']}"
         . DLM_helper_buildSQL($topic, $limit);
    $result = DB_query($sql);
    $num = DB_numRows($result);

    $lids = array();
    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray($result);

        if ($A['lid'] == $updated_id) {
            // this feed has to be updated - no further checks needed
            return false;
        }

        $lids[] = $A['lid'];
    }
    $current = implode(',', $lids);

    return ($current != $update_data) ? false : true;
}


/**
* Counts the items that are submitted
*
* @return   int     number of items in submission queue
*
*/
function plugin_submissioncount_downloads()
{
    global $_TABLES;

    $retval = 0;

    if (plugin_ismoderator_downloads()) {
        $retval = DB_count($_TABLES['downloadsubmission']);
    }

    return $retval;
}

/**
* Checks that the current user has the rights to moderate the
* plugin, returns true if this is the case, false otherwise
*
* @return        boolean       Returns true if moderator
*
*/
function plugin_ismoderator_downloads()
{
    return SEC_hasRights('downloads.moderate');
}


/**
* Returns SQL & Language texts to moderation.php
*
* @return   mixed   plugin object or void if not allowed
*
*/
function plugin_itemlist_downloads()
{
    global $_CONF, $_TABLES, $LANG_DLM;

    if (plugin_ismoderator_downloads()) {
        $plugin = new Plugin();
        $plugin->submissionlabel = $LANG_DLM['download_submissions'];
        if (COM_VersionCompare(VERSION, '2.1.2')) { // updated help feature for multiple languages only support in 2.1.2+
            if (file_exists($_CONF['path_html'] . 'help/ccdownloadsubmission.html')) {
                $plugin->submissionhelpfile = 'ccdownloadsubmission.html';
            }
        } else {
            $plugin->submissionhelpfile = 'ccdownloadsubmission.html';
        }
        $plugin->getsubmissionssql
            = "SELECT a.lid AS id, a.title, a.version, b.title AS category "
            . "FROM {$_TABLES['downloadsubmission']} a "
            . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
            . "ORDER BY a.title ASC";
        $plugin->addSubmissionHeading($LANG_DLM['title']);
        $plugin->addSubmissionHeading($LANG_DLM['ver']);
        $plugin->addSubmissionHeading($LANG_DLM['category']);

        return $plugin;
    }
}

/**
* returns list of moderation values
*
* The array returned contains (in order): the row 'id' label, main plugin
* table, moderation fields (comma seperated), and plugin submission table
*
* @return       array        Returns array of useful moderation values
*
*/
function plugin_moderationvalues_downloads()
{
    global $_TABLES;

    return array (
        'lid',
        $_TABLES['downloads'],
        'lid, cid, title, url, homepage, version, size, secret_id, md5, project, '
        . 'meta_description, meta_keywords, description, detail, postmode, logourl, mg_autotag, tags, date, hits, rating, '
        . 'votes, commentcode, is_released, is_listing, createddate, owner_id',
        $_TABLES['downloadsubmission']);
}


/**
* Performs plugin exclusive work for items deleted by moderation
*
* While moderation.php handles the actual removal from downloadsubmission
* table, within this function we handle all other deletion
* related tasks
*
* @param    string  $id     Identifying string
* @return   string          Any wanted HTML output
*
*/
function plugin_moderationdelete_downloads($id)
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';
    DLM_delNewDownload($id);

    return '';
}


/**
* Performs plugin exclusive work for items approved by moderation
*
* While moderation.php handles the actual move from downloadsubmission
* to downloads tables, within the function we handle all other
* approval relate tasks
*
* @param    string  $id     Identifying string
* @return   string          Any wanted HTML output
*
*/
function plugin_moderationapprove_downloads($id)
{
    global $_CONF, $LANG_DLM;

    require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';

    if (!DLM_approveNewDownload($id)) {
        return COM_showMessageText($LANG_DLM['approval_failed_pending']);
    }

    return '';
}

/**
* Shows download submission form
*
* @return   string  HTML for the download submission form
*
*/
function plugin_submit_downloads()
{
    global $_CONF, $_DLM_CONF, $LANG_DLM;

    require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';
    require_once $_CONF['path'] . 'plugins/downloads/include/download.class.php';
    $dldl = new DLDownload();

    if ($_DLM_CONF['enabled_preview_on_upload'] === true) {
        $mode = (!empty($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';
        if ($mode == $LANG_DLM['preview']) {
            $dldl->showPreview('submit');
        }
    }

    return $dldl->showEditor('submit');
}

/**
* Save submission form values if OK. Else show form again
*
* @param    array   $A  The download record
* @return   string      Any wanted HTML output
*
*/
function plugin_savesubmission_downloads($A)
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';
    require_once $_CONF['path'] . 'plugins/downloads/include/download.class.php';
    $dldl = new DLDownload();
    return $dldl->submitNewDownload($A);
}

/**
 * Resolve a stable Downloads interoperability identity.
 *
 * Supported public identities:
 * - root
 * - category:<cid>
 * - raw download id (legacy-compatible)
 *
 * @param string $id
 * @return array
 */
function DLM_parseInteropId($id)
{
    $id = trim((string) $id);

    if ($id === 'root') {
        return array('subtype' => 'root', 'id' => 'root');
    }

    if (strpos($id, 'category:') === 0) {
        return array(
            'subtype' => 'category',
            'id' => substr($id, strlen('category:')),
        );
    }

    if (strpos($id, 'download:') === 0) {
        return array(
            'subtype' => 'download',
            'id' => substr($id, strlen('download:')),
        );
    }

    return array('subtype' => 'download', 'id' => $id);
}

/**
 * Build normalized Item Info properties in request order.
 *
 * @param array $properties
 * @param array $record
 * @param bool  $indexed
 * @return array
 */
function DLM_buildItemInfoResult($properties, $record, $indexed = false)
{
    $result = array();

    foreach ($properties as $property) {
        $value = isset($record[$property]) ? $record[$property] : '';
        if ($indexed) {
            $result[] = $value;
        } elseif ($value !== '') {
            $result[$property] = $value;
        }
    }

    return $result;
}

/**
 * Return normalized information for one Downloads-owned public resource.
 *
 * @param string $id
 * @param array  $properties
 * @param int    $uid
 * @return array
 */
function DLM_getInteropItem($id, $properties, $uid = 0)
{
    global $_CONF, $_TABLES, $_DLM_CONF, $LANG_DLM;

    $identity = DLM_parseInteropId($id);
    $uid = (int) $uid;

    if ($identity['subtype'] === 'root') {
        $record = array(
            'id' => 'root',
            'title' => $LANG_DLM['downloads'],
            'url' => $_CONF['site_url'] . '/downloads/index.php',
            'description' => '',
            'excerpt' => '',
            'date-created' => '',
            'date-modified' => '',
            'type' => 'downloads',
            'subtype' => 'root',
            'is-container' => 1,
            'parent-id' => '',
            'parent-subtype' => '',
            'hits' => '',
            'image' => '',
            'category' => '',
        );

        return DLM_buildItemInfoResult($properties, $record, true);
    }

    if ($identity['subtype'] === 'category') {
        $cid = DB_escapeString($identity['id']);
        $sql = "SELECT c.cid, c.pid, c.title, c.meta_description, c.imgurl "
             . "FROM {$_TABLES['downloadcategories']} c "
             . "WHERE c.cid='$cid' AND c.is_enabled=1 "
             . COM_getPermSQL('AND', $uid, 2, 'c')
             . " LIMIT 1";
        $result = DB_query($sql);
        if (DB_numRows($result) !== 1) {
            return array();
        }

        $A = DB_fetchArray($result);
        $parent_id = ($A['pid'] === '' || $A['pid'] === DLM_ROOTID)
            ? 'root' : 'category:' . $A['pid'];

        $image = '';
        if (!empty($A['imgurl'])) {
            $image = rtrim($_DLM_CONF['snapcat_url'], '/')
                   . '/' . DLM_encodeFileName($A['imgurl']);
        }

        $record = array(
            'id' => 'category:' . $A['cid'],
            'title' => stripslashes($A['title']),
            'url' => $_CONF['site_url'] . '/downloads/index.php?cid='
                   . rawurlencode($A['cid']),
            'description' => stripslashes($A['meta_description']),
            'excerpt' => stripslashes($A['meta_description']),
            'date-created' => '',
            'date-modified' => '',
            'type' => 'downloads',
            'subtype' => 'category',
            'is-container' => 1,
            'parent-id' => $parent_id,
            'parent-subtype' => ($parent_id === 'root') ? 'root' : 'category',
            'hits' => '',
            'image' => $image,
            'category' => stripslashes($A['title']),
        );

        return DLM_buildItemInfoResult($properties, $record, true);
    }

    $lid = DB_escapeString($identity['id']);
    $now = time();
    $sql = "SELECT d.lid, d.cid, d.title, d.description, d.detail, d.date, "
         . "d.createddate, d.hits, d.logourl, c.title AS category_title "
         . "FROM {$_TABLES['downloads']} d "
         . "LEFT JOIN {$_TABLES['downloadcategories']} c ON d.cid=c.cid "
         . "WHERE d.lid='$lid' AND d.is_released=1 AND d.date<=$now "
         . "AND c.is_enabled=1 "
         . COM_getPermSQL('AND', $uid, 2, 'c')
         . DLM_helper_getLangSQL('c')
         . " LIMIT 1";
    $result = DB_query($sql);
    if (DB_numRows($result) !== 1) {
        return array();
    }

    $A = DB_fetchArray($result);
    $description = stripslashes($A['description']);
    if (!empty($A['detail'])) {
        $description .= '<br' . XHTML . '>' . stripslashes($A['detail']);
    }

    $created = '';
    if (!empty($A['createddate']) && $A['createddate'] !== '0000-00-00 00:00:00') {
        $created = strtotime($A['createddate']);
        if ($created === false) {
            $created = '';
        }
    }

    $image = '';
    if (!empty($A['logourl'])) {
        $image = rtrim($_DLM_CONF['snapstore_url'], '/')
               . '/' . DLM_createSafeFileName($A['logourl']);
    }

    $record = array(
        'id' => $A['lid'],
        'title' => stripslashes($A['title']),
        'url' => COM_buildURL(
            $_CONF['site_url'] . '/downloads/index.php?id=' . rawurlencode($A['lid'])
        ),
        'description' => PLG_replaceTags($description),
        'excerpt' => PLG_replaceTags($description),
        'date-created' => $created,
        'date-modified' => (int) $A['date'],
        'type' => 'downloads',
        'subtype' => 'download',
        'is-container' => 0,
        'parent-id' => 'category:' . $A['cid'],
        'parent-subtype' => 'category',
        'hits' => (int) $A['hits'],
        'image' => $image,
        'category' => stripslashes($A['category_title']),
    );

    return DLM_buildItemInfoResult($properties, $record, true);
}

/**
 * Collect public Downloads URLs for XMLSitemap.
 *
 * @param int $uid User id used for permission checks
 * @param int $limit Maximum number of items, 0 = unlimited
 * @return array
 */
function plugin_collectSitemapItems_downloads($uid = 1, $limit = 0)
{
    $uid = (int) $uid;
    $limit = (int) $limit;
    $options = array('subtype' => 'all');
    $items = plugin_getiteminfo_downloads(
        '*',
        'id,url,date-modified,subtype,is-container',
        $uid,
        $options
    );

    $result = array();
    $seen = array();

    foreach ($items as $item) {
        if (empty($item['url']) || isset($seen[$item['url']])) {
            continue;
        }

        $seen[$item['url']] = true;
        $entry = array(
            'url' => $item['url'],
            'change-freq' => ($item['subtype'] === 'download') ? 'monthly' : 'weekly',
            'priority' => ($item['subtype'] === 'download') ? 0.5 : 0.6,
        );

        if (!empty($item['date-modified'])) {
            $entry['date-modified'] = (int) $item['date-modified'];
        }

        $result[] = $entry;

        if ($limit > 0 && count($result) >= $limit) {
            break;
        }
    }

    return $result;
}

/**
 * Return normalized information for Downloads-owned public resources.
 *
 * Legacy raw download ids remain supported. Additional stable ids are:
 * root and category:<cid>.
 *
 * @param string $id
 * @param string $what
 * @param int    $uid
 * @param array  $options
 * @return mixed
 */
function plugin_getiteminfo_downloads($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $_DLM_CONF, $LANG_DLM;

    $properties = array_map('trim', explode(',', $what));
    $uid = (int) $uid;

    if ($id !== '*') {
        return DLM_getInteropItem($id, $properties, $uid);
    }

    // Preserve legacy collection behavior unless a subtype is explicitly requested.
    $subtype = isset($options['subtype']) ? strtolower($options['subtype']) : 'download';
    $allowed = array('download', 'category', 'root', 'all');
    if (!in_array($subtype, $allowed, true)) {
        $subtype = 'download';
    }

    $records = array();

    if ($subtype === 'root' || $subtype === 'all') {
        $root = DLM_getInteropItem('root', $properties, $uid);
        if (!empty($root)) {
            $records[] = DLM_buildItemInfoResult(
                $properties,
                array_combine($properties, $root),
                false
            );
        }
    }

    if ($subtype === 'category' || $subtype === 'all') {
        $sql = "SELECT c.cid FROM {$_TABLES['downloadcategories']} c "
             . "WHERE c.cid<>'' AND c.is_enabled=1 "
             . COM_getPermSQL('AND', $uid, 2, 'c')
             . " ORDER BY c.corder ASC, c.title ASC";
        $result = DB_query($sql);
        while ($A = DB_fetchArray($result)) {
            if (!matchLanguage($A['cid'])) {
                continue;
            }
            $row = DLM_getInteropItem('category:' . $A['cid'], $properties, $uid);
            if (!empty($row)) {
                $records[] = DLM_buildItemInfoResult(
                    $properties,
                    array_combine($properties, $row),
                    false
                );
            }
        }
    }

    if ($subtype === 'download' || $subtype === 'all') {
        $now = time();
        $where = "d.is_released=1 AND d.is_listing=1 "
               . "AND d.date<=$now AND c.is_enabled=1 ";
        if (isset($options['since']) && is_numeric($options['since'])) {
            $where .= "AND d.date>=" . (int) $options['since'] . " ";
        }

        $sql = "SELECT d.lid FROM {$_TABLES['downloads']} d "
             . "LEFT JOIN {$_TABLES['downloadcategories']} c ON d.cid=c.cid "
             . "WHERE $where"
             . COM_getPermSQL('AND', $uid, 2, 'c')
             . DLM_helper_getLangSQL('c');

        $order = 'd.date DESC';
        if (isset($options['order'])) {
            switch ($options['order']) {
            case 'title':
            case 'title-asc':
                $order = 'd.title ASC';
                break;
            case 'title-desc':
                $order = 'd.title DESC';
                break;
            case 'date-asc':
            case 'created-asc':
            case 'modified-asc':
                $order = 'd.date ASC';
                break;
            case 'hits-desc':
                $order = 'd.hits DESC, d.date DESC';
                break;
            case 'date':
            case 'date-desc':
            case 'created-desc':
            case 'modified-desc':
            default:
                $order = 'd.date DESC';
                break;
            }
        }
        $sql .= ' ORDER BY ' . $order;

        $result = DB_query($sql);
        while ($A = DB_fetchArray($result)) {
            $row = DLM_getInteropItem($A['lid'], $properties, $uid);
            if (!empty($row)) {
                $records[] = DLM_buildItemInfoResult(
                    $properties,
                    array_combine($properties, $row),
                    false
                );
            }
        }
    }

    if (isset($options['limit'])) {
        $limit = (int) $options['limit'];
        if ($limit > 0 && count($records) > $limit) {
            $records = array_slice($records, 0, $limit);
        }
    }

    return $records;
}

/**
 * Convert a Downloads identity to its canonical public URL.
 *
 * Geeklog 2.2.2 calls this with ($sub_type, $item_id). The second parameter
 * remains optional so older direct one-argument calls continue to work.
 *
 * @param string $sub_type
 * @param string $item_id
 * @return string
 */
function plugin_idToURL_downloads($sub_type, $item_id = '')
{
    global $_CONF;

    if ($item_id === '') {
        $item_id = $sub_type;
        $sub_type = '';
    }

    $identity = DLM_parseInteropId($item_id);
    if ($sub_type !== '') {
        $normalized_subtype = strtolower((string) $sub_type);
        if (in_array($normalized_subtype, array('root', 'category', 'download'), true)) {
            $identity['subtype'] = $normalized_subtype;
            if ($normalized_subtype === 'category'
                && strpos($item_id, 'category:') === 0
            ) {
                $identity['id'] = substr($item_id, strlen('category:'));
            }
        }
    }

    if ($identity['subtype'] === 'root') {
        return $_CONF['site_url'] . '/downloads/index.php';
    }

    if ($identity['subtype'] === 'category') {
        $cid = COM_sanitizeID($identity['id'], false);
        if ($cid === '') {
            return '';
        }

        return $_CONF['site_url'] . '/downloads/index.php?cid=' . rawurlencode($cid);
    }

    $lid = COM_sanitizeID($identity['id'], false);
    if ($lid === '') {
        return '';
    }

    return COM_buildURL(
        $_CONF['site_url'] . '/downloads/index.php?id=' . rawurlencode($lid)
    );
}

/**
* Scans a download item for tags
*
*/
function plugin_tag_scantag_downloads($id)
{
    global $_TABLES, $_TAG_CONF;

    $retval = array();

    $tag_name = 'tag';
    if (!empty($_TAG_CONF['tag_name'])) {
        $tag_name = $_TAG_CONF['tag_name'];
    }

    $tags = DB_getItem($_TABLES['downloads'], 'tags',
        "lid='" . DB_escapeString($id) . "'");
    if (!empty($tags)) {
        $content = '[' . $tag_name . ':' . stripslashes($tags) . ']';
        $retval = TAG_scanTag($content);
        $retval = array_unique($retval);
    }

    return $retval;
}

/**
* Scans all download items for tags
*
*/
function plugin_tag_scanall_downloads()
{
    global $_TABLES, $_TAG_CONF;

    $tag_name = 'tag';
    if (!empty($_TAG_CONF['tag_name'])) {
        $tag_name = $_TAG_CONF['tag_name'];
    }

    $sql = "SELECT lid, tags FROM {$_TABLES['downloads']}";
    $result = DB_query($sql);
    while ($A = DB_fetchArray($result)) {
        if (!empty($A['tags'])) {
            $content = '[' . $tag_name . ':' . stripslashes($A['tags']) . ']';
            $tags = TAG_scanTag($content);
            foreach ($tags as $tag) {
                TAG_saveTagToList($tag);
                TAG_saveTagToMap($tag, $A['lid'], 'downloads');
            }
        }
    }
}

/**
* Returns information for a tagged download item
*
*/
function plugin_tag_gettaggeditems_downloads($id)
{
    global $LANG_DLM, $_TABLES, $_TAG_CONF;

    $retval = array();
    $item = plugin_getiteminfo_downloads($id, 'title,url');
    if (empty($item)) return $retval;

    $tags = DB_getItem($_TABLES['downloads'], 'tags',
        "lid='" . DB_escapeString($id) . "'");
    if (empty($tags)) return $retval;

    $tag_name = 'tag';
    if (!empty($_TAG_CONF['tag_name'])) {
        $tag_name = $_TAG_CONF['tag_name'];
    }

    $content = '[' . $tag_name . ':' . stripslashes($tags) . ']';
    $tags = TAG_scanTag($content);
    $retval = array(
        'title'     => $item[0],
        'url'       => $item[1],
        'all_tags'  => $tags,
        'type_name' => $LANG_DLM['downloads']
    );

    return $retval;
}
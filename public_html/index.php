<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/downloads/index.php                                           |
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

require_once $_CONF['path'] . 'plugins/downloads/include/gltree.class.php';

define('BCSEPALATOR', '&nbsp;:&nbsp;');

//returns the total number of items in items table that are accociated with a given table $table id
function getTotalItems($sel_id)
{
    global $_TABLES, $_DLM_CONF, $mytree, $now;

    if (!is_array($sel_id)) {
        $arr = $mytree->getAllChildId($sel_id);
        $arr = array_merge(array($sel_id), $arr);
    } else {
        $arr = $sel_id;
    }
    $sql_cid_list = "('" . implode("','", $arr) . "') ";
    $permsql = $_DLM_CONF['has_edit_rights'] ? '' : COM_getPermSQL('AND', 0, 2, 'b');
    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloads']} a "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
         . "WHERE a.cid IN " . $sql_cid_list
         . $permsql
         . "AND a.is_released=1 "
         . "AND a.is_listing=1 "
         . "AND date<=$now ";
    list($count) = DB_fetchArray(DB_query($sql));
    return $count;
}

function matchLanguage($cid)
{
    global $_DLM_CONF;
    if (empty($_DLM_CONF['lang_id'])) return true;
    $len = strlen($_DLM_CONF['lang_id']) + 1;
    return (substr($cid, -$len) == ('_' . $_DLM_CONF['lang_id']));
}

function makeProjectFileList($lid) {

    global $_CONF, $_TABLES, $LANG01, $_DLM_CONF, $LANG_DLM, $LANG_ADMIN;

    require_once $_CONF['path_system'] . 'lib-admin.php';

    $retval = '';
    $project = DB_getItem($_TABLES['downloads'], 'project', "lid = '" . addslashes($lid) . "'");
    if ($project == false) return '';
    $permsql = $_DLM_CONF['has_edit_rights'] ? '' : COM_getPermSQL('AND', 0, 2, 'b');
    $result = DB_query("SELECT a.lid, a.title, a.url, a.version, a.size, a.date, a.cid "
                     . "FROM {$_TABLES['downloads']} a "
                     . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
                     . "WHERE a.project='" . addslashes($project) . "' "
                     . "AND a.project<>'' "
                     . "AND a.is_released=1 "
                     . $permsql
                     . " ORDER BY a.date DESC LIMIT 10");

    $header_arr = array(
        array('text' => $LANG_ADMIN['title'],    'field' => 'title'  ),
        array('text' => $LANG_DLM['file'],       'field' => 'url'    ),
        array('text' => $LANG_DLM['ver'],        'field' => 'version'),
        array('text' => $LANG_DLM['size'],       'field' => 'size'   ),
        array('text' => $LANG_DLM['submitdate'], 'field' => 'date'   ));

    $data_arr = array();

    $text_arr = array('has_menu' => false,
                      'title'    => sprintf($LANG_DLM['projectfilelist'], $project));

    while ($A = DB_fetchArray($result)) {
        if (!matchLanguage($A['cid'])) continue;
        $data_arr[] = array(
            'title'     => COM_createLink($A['title'],
                           COM_buildURL($_CONF['site_url'] . '/downloads/index.php?id=' . $A['lid'])),
            'url'       => COM_createLink($A['url'],
                           COM_buildURL($_CONF['site_url'] . '/downloads/visit.php?id=' . $A['lid'])),
            'version'   => $A['version'],
            'size'      => $A['size'],
            'date'      => strftime($_DLM_CONF['date_format'], $A['date']));
    }

    $retval .= ADMIN_simpleList('', $header_arr, $text_arr, $data_arr);
    return $retval;
}


/**
* Gets tag list
*
*/
function getTagList($tags)
{
    global $_PLUGINS, $_CONF, $_TABLES, $_TAG_CONF;

    if (!in_array('tag', $_PLUGINS)) {
        return '';
    }

    $temp = str_replace(' ', "','", $tags);
    $sql_tag_list = "('" . $temp . "') ";
    $sql = "SELECT t.tag "
         . "FROM {$_TABLES['tag_map']} AS m "
         . "LEFT JOIN {$_TABLES['tag_list']} AS t ON t.tag_id = m.tag_id "
         . "WHERE t.tag IN " . $sql_tag_list
         . "AND m.type = 'downloads' "
         . "GROUP BY t.tag_id "
         . "ORDER BY t.tag ASC";
    $result = DB_query($sql);
    if (DB_error()) return '';

    $retval = '';
    while ($A = DB_fetchArray($result)) {
        $title = $A['tag'];
        $rel = rawurlencode($title);
        if ($_TAG_CONF['replace_underscore'] == true) {
            $title = str_replace('_', ' ', $title);
        }
        $title = TAG_escape($title);
        $url = COM_buildURL($_CONF['site_url'] . '/tag/index.php?tag=' . $rel);
        $retval .= COM_createLink($title, $url, array('rel' => $title)). ', ' . LB;
    }
    $retval = rtrim($retval, ", \r\n");

    return $retval;
}


function dlformat(&$T, &$A, $isListing=false, $cid=ROOTID)
{
    global $_CONF, $_TABLES, $LANG01, $_DLM_CONF, $LANG_DLM, $mytree;

    $A['rating']   = number_format($A['rating'], 2);
    $A['title']    = DLM_htmlspecialchars($A['title']);
    $A['project']  = DLM_htmlspecialchars($A['project']);
    $A['url']      = DLM_htmlspecialchars($A['url']);
    $A['homepage'] = DLM_htmlspecialchars($A['homepage']);
    $A['version']  = DLM_htmlspecialchars($A['version']);
    $A['size']     = DLM_htmlspecialchars($A['size']);
    $A['md5']      = DLM_htmlspecialchars($A['md5']);
    $A['logourl']  = DLM_htmlspecialchars($A['logourl']);
    $A['postmode'] = DLM_htmlspecialchars($A['postmode']);
    $A['tags']     = DLM_htmlspecialchars($A['tags']);

    $A['datetime'] = strftime($_DLM_CONF['date_format'], $A['date']);

    if (version_compare(VERSION, '2.1.0') >= 0) {
        require_once $_CONF['path_system'] . 'classes/gltext.class.php';
        $A['description'] = GLText::getDisplayText($A['description'], $A['postmode'], 2);
        $A['detail']      = GLText::getDisplayText($A['detail'],      $A['postmode'], 2);
    } else {
        require_once $_CONF['path'] . 'plugins/downloads/include/gltext.class.php';
        $gltext = new GLPText();
        $A['description'] = $gltext->getDisplayText($A['description'], $A['postmode']);
        $A['detail']      = $gltext->getDisplayText($A['detail'],      $A['postmode']);
    }

    $filedetail_url = COM_buildURL($_CONF['site_url'] . '/downloads/index.php?id=' . $A['lid']);
    $visitfile_url  = COM_buildURL($_CONF['site_url'] . '/downloads/visit.php?id=' . $A['lid']);

    if ($isListing && !empty($A['detail'])) {
        $A['description'] .= '<p class="download-break">'
                           . COM_createLink($LANG_DLM['more'], $filedetail_url) . '</p>';
    }

    $result = DB_query("SELECT username, fullname, photo "
                     . "FROM {$_TABLES['users']} "
                     . "WHERE uid = {$A['owner_id']}");
    $B = DB_fetchArray($result);

    $submitter_name = COM_getDisplayName($A['owner_id'], $B['username'], $B['fullname']);
    if (empty($submitter_name)) {
        $submitter_name = $LANG_DLM['unknown_uid'];
    } else {
        $submitter_name = COM_createLink($submitter_name, $_CONF['site_url']
                        . '/users.php?mode=profile&amp;uid=' . $A['owner_id']);
    }

    $path = $mytree->getNicePathFromId($A['cid'], 'title', $_CONF['site_url'] . '/downloads/index.php');
    $temp = $mytree->getSepalator();
    $path = substr($path, 0, strlen($path) - strlen($temp));
    $path = str_replace($temp, ' <img src="' . $_CONF['site_url']
                             . '/downloads/images/arrow.gif" alt="arrow"' . XHTML . '> ', $path);

    $tags = '-';
    if (!empty($A['tags'])) {
        $tags = getTagList($A['tags']);
        if (empty($tags)) $tags = '-';
    }
    $notags = ($tags == '-') ? 'dlm_notags' : '';

    $T->set_var('lang_category',  $LANG_DLM['category']);
    $T->set_var('category_path',  $path);
    $T->set_var('lang_tags',      $LANG_DLM['tags']);
    $T->set_var('tags',           $tags);
    $T->set_var('notags',         $notags);
    $T->set_var('lang_submitter', $LANG_DLM['submitter']);
    $T->set_var('submitter_name', $submitter_name);
    $T->set_var('lid',            $A['lid']);
    $T->set_var('cid',            $A['cid']);
    $T->set_var('lang_dlnow',     $LANG_DLM['dlnow']);
    $T->set_var('dtitle',         $A['title']);
    $T->set_var('filedetail_url', $filedetail_url);
    $T->set_var('visitfile_url',  $visitfile_url);
    $T->set_var('listing_cid',    $cid);
    $T->set_var('lang_download_button', $LANG_DLM['download_button']);

    $startdate = (time() - 60 * 60 * 24 * 7);
    if ($startdate < $A['date']) {
        $image_new = COM_createImage($_CONF['site_url'] . '/downloads/images/newred.gif',
                                     $LANG_DLM['newthisweek']);
        $newdownload = '<span class="badgenew">NEW</span>';
    }
    $T->set_var('image_newdownload', $image_new); // Image (New)
    $T->set_var('newdownload', $newdownload);     // Badge (New)

    if ($A['hits'] >= $_DLM_CONF['download_popular']) {
        $image_pop = COM_createImage($_CONF['site_url'] . '/downloads/images/pop.gif',
                                     $LANG_DLM['popular']);
        $popdownload = '<span class="badgepop">POP</span>';
    }
    $T->set_var('image_popular', $image_pop);     // Image (Pop)
    $T->set_var('popdownload', $popdownload);     // Badge (Pop)

    // category image
    $cat_title = DLM_htmlspecialchars($A['cat_title']);
    if ($_DLM_CONF['download_useshots'] && !empty($A['imgurl'])) {
        $imgurl = $_DLM_CONF['snapcat_url'] . '/' . DLM_htmlspecialchars($A['imgurl']);
    } else {
        $imgurl = $_CONF['site_url'] . '/downloads/images/download.png';
    }
    $category_image = COM_createImage($imgurl, $cat_title,
                                      array('width' => $_DLM_CONF['download_shotwidth']));
    $T->set_var('category_image', $category_image);

    $T->set_var('download_title',   $LANG_DLM['click2dl'] . ': ' . $A['url']);
    $T->set_var('url',              $A['url']);
    $T->set_var('file_description', $A['description']);
    $T->set_var('file_detail',      $A['detail']);
    $T->set_var('rating',           $A['rating']);

    if ($A['rating'] != "0" || $A['rating'] != "0.00") {
        $votestring = sprintf($LANG_DLM['numvotes'], $A['votes']);
    }
    $T->set_var('votestring', $votestring);

    if (!empty($A['mg_autotag'])) {

        // use the mediagallery autotag as a snapshot.
        $mg_autotag = str_replace(array('[', ']'), '', $A['mg_autotag']);
        $mg_autotag = '[' . $mg_autotag
                    . ' width:' . $_DLM_CONF['max_tnimage_width']
                    . ' height:' . $_DLM_CONF['max_tnimage_height']
                    . ' align:left]';
        $T->set_var('mg_autotag', PLG_replaceTags($mg_autotag, 'mediagallery'));
        $T->set_var('snapshot', '');
        $T->set_var('snaplinkicon', '');

    } elseif (!empty($A['logourl'])) {

        $safename = DLM_createSafeFileName($A['logourl']);
        $imgpath = $_DLM_CONF['path_tnstore'] . $safename;
        $imgpath = DLM_modTNPath($imgpath);
        $tnimgurl = $_DLM_CONF['tnstore_url'] . '/' . $safename;
        $tnimgurl = substr($tnimgurl, 0, -3) . substr($imgpath, -3); // align the extension
        $sizeattributes = DLM_getImgSizeAttr($imgpath);
        $T->set_var('snapshot_url',         $_DLM_CONF['snapstore_url'] . '/' . $safename);
        $T->set_var('thumbnail_url',        $tnimgurl);
        $T->set_var('snapshot_sizeattr',    $sizeattributes);
        $T->set_var('lang_click2see',       $LANG_DLM['click2see']);
        $T->set_var('show_snapshoticon',    '');
        $T->set_var('show_snapshoticon_na', 'none');
        $T->set_var('mg_autotag',           '');
        
        if ($_DLM_CONF['show_tn_image']) {
            $T->parse('snapshot', 'tsnapshot');
        } else {
            $T->parse('snaplinkicon', 'tsnaplinkicon');
        }
    } else {
        $tnimgurl = $_CONF['site_url'] . '/downloads/images/blank.png';
        $T->set_var('thumbnail_url',        $tnimgurl);
        $T->set_var('snapshot_url',         $_CONF['site_url'] . '/downloads/index.php');
        $T->set_var('snapshot_sizeattr',    'width="200" height="200" ');
        $T->set_var('show_snapshoticon',    'none');
        $T->set_var('show_snapshoticon_na', '');
        $T->parse('snapshot', 'tsnapshot');
        $T->set_var('snaplinkicon', '');
        $T->set_var('mg_autotag',   '');
    }

    $T->set_var('lang_version',    $LANG_DLM['ver']);
    $T->set_var('lang_rating',     $LANG_DLM['ratingc']);
    $T->set_var('lang_submitdate', $LANG_DLM['submitdate']);
    $T->set_var('lang_size',       $LANG_DLM['size']);
    $T->set_var('datetime',        $A['datetime']);
    $T->set_var('version',         $A['version']);

    // Check if restricted access has been enabled for download report to admin's only
    if ($A['hits'] > 0 && DLM_hasAccess_history()) {
        $T->set_var('begin_dlreport_link', '<a href="' . COM_buildURL($_CONF['site_url']
                                         . '/downloads/history.php?lid=' . $A['lid']) . '">');
        $T->set_var('end_dlreport_link',   '</a>');
    } else {
        $T->set_var('begin_dlreport_link', '');
        $T->set_var('end_dlreport_link',   '');
    }
    $T->set_var('download_times',    sprintf($LANG_DLM['dltimes'], $A['hits']));
    $T->set_var('download_count',    $A['hits']);
    $T->set_var('lang_popularity',   $LANG_DLM['popularity']);
    $T->set_var('lang_filesize',     $LANG_DLM['filesize']);
    $T->set_var('file_size',         DLM_PrettySize($A['size']));
    $T->set_var('homepage_url',      $A['homepage']);
    $T->set_var('homepage_link', '-');
    if (!empty($A['homepage'])) {
        $T->set_var('homepage_link', COM_makeClickableLinks($A['homepage']));
    }
    $T->set_var('lang_homepage',     $LANG_DLM['homepage']);
    $T->set_var('lang_download',     $LANG_DLM['download']);
    $T->set_var('lang_filelink',     $LANG_DLM['filelink']);
    $T->set_var('lang_permalink',    $LANG_DLM['permalink']);
    $T->set_var('lang_ratethisfile', $LANG_DLM['ratethisfile']);
    $T->set_var('lang_edit',         $LANG_DLM['edit']);
    $T->set_var('show_editlink',     $_DLM_CONF['has_edit_rights'] ? '' : 'none');
    $T->set_var('lang_md5_checksum', $LANG_DLM['md5_checksum']);
    $T->set_var('md5_checksum',      $A['md5']);

    if ($A['commentcode'] == 0) {
        $commentCount = DB_count($_TABLES['comments'], 'sid', addslashes($A['lid']));
        $recentPostMessage = $LANG_DLM['commentswanted'];
        if ($commentCount > 0) {
            $result4 = DB_query("SELECT cid, UNIX_TIMESTAMP(date) AS day, username "
                              . "FROM {$_TABLES['comments']}, {$_TABLES['users']} "
                              . "WHERE {$_TABLES['users']}.uid = {$_TABLES['comments']}.uid "
                              . "AND sid = '" . addslashes($A['lid']) . "' "
                              . "ORDER BY date DESC LIMIT 1");
            $C = DB_fetchArray($result4);
            $recentPostMessage = $LANG01[27] . ': ' . strftime($_CONF['daytime'], $C['day'])
                               . ' ' . $LANG01[104] . ' ' . $C['username'];

            $comment_link = COM_createLink($commentCount . '&nbsp;' . $LANG01[3],
                                           $filedetail_url, array('title' => $recentPostMessage));
        } else {

            $A['title'] = str_replace('&#039;', "'", $A['title']);
            $A['title'] = str_replace('&amp;',  '&', $A['title']);
            $url = $_CONF['site_url'] . '/comment.php?type=downloads&amp;sid='
                 . $A['lid'] . '&amp;title=' . rawurlencode($A['title']);
            $comment_link = COM_createLink($LANG_DLM['entercomment'],
                                           $url, array('title' => $recentPostMessage));

        }
        $T->set_var('comment_link', $comment_link);
        $T->set_var('show_comments', '');
    } else {
        $T->set_var('show_comments', 'none');
    }
}


function makeCategoryPart($cid)
{
    global $_CONF, $_DLM_CONF, $LANG_DLM, $mytree;

    $T = new Template($_DLM_CONF['path_layout']);
    $T->set_file(array(
        'categorypart'   => 'filelisting_category.thtml',
        'categoryrow'    => 'filelisting_category_row.thtml',
        'categoryitem'   => 'filelisting_category_item.thtml',
    ));
    DLM_setDefaultTemplateVars($T);

    $arr = $mytree->getFirstChild($cid, 'corder'); // all child ID are listed (Affected by the language mode)
    if (count($arr) == 0) return '';

//  if ($_DLM_CONF['numCategoriesPerRow'] < 1) $_DLM_CONF['numCategoriesPerRow'] = 1; // probably no longer necessary
//  if ($_DLM_CONF['numCategoriesPerRow'] > 6) $_DLM_CONF['numCategoriesPerRow'] = 6; // probably no longer necessary

    $count = 0;
    foreach ($arr as $ele) { // Each category
        $chtitle = DLM_htmlspecialchars($ele['title']);
        $T->set_var('cid',           $ele['cid']);
        $T->set_var('chtitle',       $chtitle);
        $T->set_var('totaldownload', getTotalItems($ele['cid']));
        $category_image_link = '&nbsp;';
        if ($_DLM_CONF['download_useshots']) {
            if ($ele['imgurl'] && $ele['imgurl'] != "http://") {
                $imgurl = $_DLM_CONF['snapcat_url'] . '/' . DLM_htmlspecialchars($ele['imgurl']);
            } else {
                $imgurl = $_CONF['site_url'] . '/downloads/images/download.png';
            }
            $category_image_link = COM_createImage($imgurl, $chtitle,
                                                   array('width' => $_DLM_CONF['download_shotwidth']));
            $category_image_link = COM_createLink($category_image_link,
                                                  $_CONF['site_url'] . '/downloads/index.php?cid=' . $ele['cid']);
        }
        $T->set_var('category_link', $category_image_link);
        $T->parse('category_row', 'categoryitem', true);
        $count++;
        if ($count == $_DLM_CONF['numCategoriesPerRow']) {
            $T->parse('category_records', 'categoryrow', true);
            $T->set_var('category_row', '');
            $count = 0;
        }
    }
    if ($count > 0) {
        $T->parse('category_records', 'categoryrow', true);
    }
    return $T->finish($T->parse('category_part', 'categorypart'));
}


function makeSortMenu($cid, $nppage, $orderby, $show)
{
    global $_DLM_CONF, $LANG_DLM;

    $T = new Template($_DLM_CONF['path_layout']);
    $T->set_file('sortmenu', 'filelisting_sortmenu.thtml');
    DLM_setDefaultTemplateVars($T);
    switch ($orderby) {
        case 'dated'   : $orderbyTrans = $LANG_DLM['datenew'];        break;
        case 'titlea'  : $orderbyTrans = $LANG_DLM['titleatoz'];      break;
        case 'hitsd'   : $orderbyTrans = $LANG_DLM['popularitymtol']; break;
        case 'ratingd' : $orderbyTrans = $LANG_DLM['ratinghtol'];     break;
        default:         $orderbyTrans = $LANG_DLM['datenew'];        break;
    }
    $T->set_var(array(
        'cid0'                => $cid,
        'nppage'              => $nppage,
        'lang_datenew'        => $LANG_DLM['datenew'],
        'lang_titleatoz'      => $LANG_DLM['titleatoz'],
        'lang_popularitymtol' => $LANG_DLM['popularitymtol'],
        'lang_ratinghtol'     => $LANG_DLM['ratinghtol'],
        'lang_sortby'         => $LANG_DLM['sortby'],
        'lang_numperpage'     => $LANG_DLM['numperpage'],
        'lang_rating'         => $LANG_DLM['rating'],
        'orderby'             => $orderby,
        'current_ord_d'       => (($orderby == 'dated')   ? 'current' : 'dummy'),
        'current_ord_t'       => (($orderby == 'titlea')  ? 'current' : 'dummy'),
        'current_ord_h'       => (($orderby == 'hitsd')   ? 'current' : 'dummy'),
        'current_ord_r'       => (($orderby == 'ratingd') ? 'current' : 'dummy'),
        'current_num_5'       => (($show == 5)  ? 'current' : 'dummy'),
        'current_num_10'      => (($show == 10) ? 'current' : 'dummy'),
        'current_num_20'      => (($show == 20) ? 'current' : 'dummy'),
        'current_num_50'      => (($show == 50) ? 'current' : 'dummy'),
        'orderbyTrans'        => $orderbyTrans,
    ));
    return $T->finish($T->parse('sort_menu', 'sortmenu'));
}

function DLM_makeForm_hidden($name, $value) {
    return '<input type="hidden" name="' . $name . '" value="' . $value . '"' . XHTML . '>' . LB;
}


// MAIN

$_DLM_CONF['has_edit_rights'] = SEC_hasRights('downloads.edit');
$permsql = $_DLM_CONF['has_edit_rights'] ? '' : COM_getPermSQL('AND');

$mytree = new GLTree($_TABLES['downloadcategories'], 'cid', 'pid', 'title',
                     $permsql . 'AND is_enabled=1 ', ROOTID, $_DLM_CONF['lang_id']);
$mytree->setSepalator(BCSEPALATOR);
$mytree->setRoot($LANG_DLM['main']);

$display = '';
$pagetitle = $LANG_DLM['plugin_name'];

// display message
if (isset($_REQUEST['msg'])) {
    $msg = COM_applyFilter($_REQUEST['msg'], true);
    if ($msg > 0) $display .= COM_showMessage($msg, 'downloads');
}

$T = new Template($_DLM_CONF['path_layout']);
$T->set_file(array(
    'page'            => 'filelisting.thtml',
    'filedetail'      => 'filedetail.thtml',
    'records'         => 'filelisting_record.thtml',
    'tsnapshot'       => 'filelisting_snapshot.thtml',
    'tsnaplinkicon'   => 'filelisting_snaplinkicon.thtml',
    'filedetail_notn' => 'filedetail_no_tn.thtml',
    'records_notn'    => 'filelisting_record_no_tn.thtml',
    'categoryselbox'  => 'filelisting_category_selbox.thtml',
));
if (!$_DLM_CONF['show_tn_image']) {
    $T->set_file(array(
        'filedetail'  => 'filedetail_no_tn.thtml',
        'records'     => 'filelisting_record_no_tn.thtml',
    ));
}

DLM_setDefaultTemplateVars($T);
$T->set_var('block_header', COM_startBlock($LANG_DLM['plugin_name']));
$T->set_var('block_footer', COM_endBlock());
$now = time();


COM_setArgNames(array('id'));
$lid = COM_applyFilter(COM_getArgument('id'));
if (empty($lid)) {  // Check if the script is being called from the commentbar
    $lid = COM_applyFilter($_POST['lid']);
}

if (!empty($lid)) {
    $permsql = $_DLM_CONF['has_edit_rights'] ? '' : COM_getPermSQL('AND', 0, 2, 'b');
    $sql = "SELECT a.lid, a.cid, a.title, url, homepage, version, size, md5, logourl, mg_autotag, tags, a.owner_id, date, "
         . "hits, rating, votes, commentcode, project, description, detail, postmode, "
         . "imgurl, b.title AS cat_title "
         . "FROM {$_TABLES['downloads']} a "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
         . "WHERE a.lid='" . addslashes($lid) . "' "
         . "AND is_released=1 "
         . "AND date<=$now "
         . $permsql;
    $result = DB_query($sql);
    if (DB_numRows($result) == 1) {
        $A = DB_fetchArray($result);
        dlformat($T, $A);

        $pathstring = "<a href=\"{$_CONF['site_url']}/downloads/index.php\">" . $LANG_DLM['main'] . "</a>" . BCSEPALATOR
                    . $mytree->getNicePathFromId($A['cid'], "title", "{$_CONF['site_url']}/downloads/index.php");
        $T->set_var('category_path_link', $pathstring);
        $T->set_var('cssid', 1);
        $T->set_var('project_filelist', makeProjectFileList($lid));

        require_once $_CONF['path_system'] . 'lib-comment.php';
        $A['title'] = str_replace('&#039;', "'", $A['title']);
        $A['title'] = str_replace('&amp;',  '&', $A['title']);
        $T->set_var('comment_records', CMT_userComments($lid, $A['title'], 'downloads',
                    $_POST['order'], $_POST['mode'] ,0 ,1 ,false ,$_DLM_CONF['has_edit_rights'], $A['commentcode']));

        if ($_DLM_CONF['show_tn_only_exists']) {
            if (empty($A['logourl'])) {
                $filedetail = $T->finish($T->parse('filelisting_records', 'filedetail_notn'));
            } else {
                $filedetail = $T->finish($T->parse('filelisting_records', 'filedetail'));
            }
        } else {
            $filedetail = $T->finish($T->parse('filelisting_records', 'filedetail'));
        }
        $display .= PLG_replaceTags($filedetail);

        $pagetitle .= ': ' . $A['title'];
        $display = DLM_createHTMLDocument($display, array('pagetitle' => $pagetitle));
        COM_output($display);

        exit;
    }
}
// ----------------------------------------------------------------------------------------------------------

$T->set_var('tablewidth', $_DLM_CONF['download_shotwidth'] + 10); // probably no longer necessary

$cid = COM_applyFilter($_GET['cid']);
if (empty($cid)) {
    $cid = COM_applyFilter($_POST['selbox_cat']);
}
if (empty($cid)) $cid = ROOTID;

$page = COM_applyFilter($_GET['page'], true);
if (!isset($page)) {
    $page = COM_applyFilter($_POST['selbox_page']);
}
$page = (!isset($page) || $page == 0) ? 1 : $page;

$pathstring = "<a href=\"{$_CONF['site_url']}/downloads/index.php\">" . $LANG_DLM['main'] . "</a>" . BCSEPALATOR
            . $mytree->getNicePathFromId($cid, "title", "{$_CONF['site_url']}/downloads/index.php");
$T->set_var('category_path_link', $pathstring);

// child category objects
$T->set_var('category_part', makeCategoryPart($cid));

$carr = $mytree->getAllChildId($cid);
$carr = array_merge(array($cid), $carr);
$sql_cid_list = "('" . implode("','", $carr) . "') ";
$carr_count = count($carr);

$maxrows = getTotalItems($carr);
$T->set_var('filelisting_info', sprintf($LANG_DLM['listingheading'], $maxrows)); // number of file list
$nppage = COM_applyFilter($_REQUEST['nppage'], true);
if (!isset($nppage)) {
    $nppage = COM_applyFilter($_POST['selbox_nppage'], true);
}

$show = $_DLM_CONF['download_perpage'];
$show = ($nppage > 0) ? $nppage : $show;
$numpages = ceil($maxrows / $show);

$orderby = COM_applyFilter($_GET['orderby']);
if (empty($orderby)) {
    $orderby = COM_applyFilter($_POST['selbox_orderby']);
}
if (empty($orderby)) $orderby = 'dated';

if ($maxrows > 0) {
    $T->set_var('sort_menu', makeSortMenu($cid, $nppage, $orderby, $show)); // sort menu
}

$selbox = $mytree->makeSelBox('title', 'corder', $cid, 1, 'selbox_cat', "javascript:submit()");
$hidden_values  = DLM_makeForm_hidden('selbox_page',    $page);
$hidden_values .= DLM_makeForm_hidden('selbox_nppage',  $show);
$hidden_values .= DLM_makeForm_hidden('selbox_orderby', $orderby);
$T->set_var('lang_go',          $LANG_DLM['go']);
$T->set_var('downloads_selbox', $selbox);
$T->set_var('hidden_values',    $hidden_values);
$T->parse('category_selbox', 'categoryselbox');

switch ($orderby) {
    case 'dated'  : $ordersql = 'date DESC';   break;
    case 'titlea' : $ordersql = 'title ASC';   break;
    case 'hitsd'  : $ordersql = 'hits DESC';   break;
    case 'ratingd': $ordersql = 'rating DESC'; break;
    default:        $ordersql = 'date DESC';   break;
}
$offset = ($page - 1) * $show;
$sql = "SELECT d.lid, d.cid, d.title, url, homepage, version, size, md5, d.owner_id, logourl, mg_autotag, tags, "
     . "date, hits, rating, votes, commentcode, project, description, detail, postmode, "
     . "c.group_id, c.title AS cat_title, c.imgurl "
     . "FROM {$_TABLES['downloads']} d "
     . "LEFT JOIN {$_TABLES['downloadcategories']} c ON d.cid=c.cid "
     . "WHERE is_released=1 "
     . (($carr_count > 0) ? "AND d.cid IN " . $sql_cid_list : " ")
     . "AND is_listing=1 "
     . "AND date<=$now "
     . "ORDER BY $ordersql LIMIT $offset, $show";
$result = DB_query($sql);
if (DB_numRows($result) > 0) {
    $cssid = 1;
    while ($A = DB_fetchArray($result)) {
        dlformat($T, $A, true, $cid);
        $T->set_var('cssid', $cssid);
        if ($_DLM_CONF['show_tn_only_exists']) {
            if (empty($A['logourl'])) {
                $T->parse('filelisting_records', 'records_notn', true);
            } else {
                $T->parse('filelisting_records', 'records', true);
            }
        } else {
            $T->parse('filelisting_records', 'records', true);
        }
        $cssid = ($cssid == 2) ? 1 : 2;
    }

    // Print Google-like paging navigation
    $base_url = $_CONF['site_url'] . '/downloads/index.php?cid=' . $cid . '&amp;nppage=' . $nppage;
    $page_str = 'orderby=' . $orderby . '&amp;page=';
    $T->set_var('page_navigation', COM_printPageNavigation($base_url, $page, $numpages, $page_str));
} else {
    $T->set_var('filelisting_records', '<div class="pluginAlert dlm_alert">' . $LANG_DLM['nofiles'] . '</div>');
}
$display .= PLG_replaceTags($T->finish($T->parse('output', 'page')));

$display = DLM_createHTMLDocument($display, array('pagetitle' => $pagetitle));
COM_output($display);
?>
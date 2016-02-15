<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/include/functions.php                                   |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.php') !== false) {
    die('This file can not be used on its own.');
}

function DLM_PrettySize($size)
{
    global $LANG_DLM;

    $mb = 1024*1024;
    if ($size > $mb) {
        return sprintf("%01.2f", $size/$mb) . " MB";
    } elseif ($size >= 1024) {
        return sprintf("%01.2f", $size/1024) . " KB";
    }
    return sprintf($LANG_DLM['numbytes'], $size);
}

// Updates rating data in itemtable for a given item
function DLM_updaterating($sel_id)
{
    global $_TABLES;

    $sel_id = addslashes($sel_id);
    $voteresult = DB_query("SELECT rating FROM {$_TABLES['downloadvotes']} "
                          ."WHERE lid = '$sel_id'");
    $votesDB = DB_numRows($voteresult);
    $totalrating = 0;
    if ($votesDB > 0) {
        while (list($rating) = DB_fetchArray($voteresult)){
            $totalrating += $rating;
        }
        $finalrating = $totalrating / $votesDB;
    }
    $finalrating = number_format($finalrating, 4);
    DB_query("UPDATE {$_TABLES['downloads']} "
           . "SET rating='$finalrating', votes='$votesDB' "
           . "WHERE lid = '$sel_id'");
}


function DLM_showErrorMessage($e_code, $pages=1)
{
    global $_CONF, $MESSAGE, $_IMAGE_TYPE, $LANG_DLM;

    $e_code = (in_array($e_code, array_keys($LANG_DLM))) ? $e_code : '9999';
    $message = $LANG_DLM[$e_code];
    $timestamp = strftime($_CONF['daytime']);

    $display = COM_siteHeader('menu')
             . COM_startBlock($MESSAGE[40] . ' - ' . $timestamp, '', COM_getBlockTemplate('_msg_block', 'header'))
             . '<p class="sysmessage"><img src="' . $_CONF['layout_url'] . '/images/sysmessage.'
             . $_IMAGE_TYPE . '" alt="" ' . XHTML . '>' . $message . '</p>'
             . '<p class="sysmessage" style="text-align:center;">[ <a href="javascript:history.go(-' . $pages . ')">'
             . $LANG_DLM['goback'] . '</a> ]</p>'
             . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'))
             . COM_siteFooter();

    echo $display;
    exit;
}


function DLM_showMessage($e_code)
{
    global $_CONF, $_IMAGE_TYPE, $LANG_DLM, $MESSAGE;

    $e_code = (in_array($e_code, array_keys($LANG_DLM))) ? $e_code : '9999';
    $message = $LANG_DLM[$e_code];
    $timestamp = strftime($_CONF['daytime']);

    $retval = COM_startBlock($MESSAGE[40] . ' - ' . $timestamp, '', COM_getBlockTemplate('_msg_block', 'header'))
            . '<p class="sysmessage"><img src="' . $_CONF['layout_url'] . '/images/sysmessage.'
            . $_IMAGE_TYPE . '" alt="" ' . XHTML . '>' . $message . '</p>'
            . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));

    return $retval;
}


function DLM_showMessageArray($e_code_array)
{
    global $_CONF, $_IMAGE_TYPE, $LANG_DLM, $MESSAGE;

    $message = '';
    foreach ($e_code_array as $e_code) {
        $e_code = (in_array($e_code, array_keys($LANG_DLM))) ? $e_code : '9999';
        if (!empty($message)) {
            $message .= '<br'. XHTML .'>';
        }
        $message .= $LANG_DLM[$e_code];
    }
    $timestamp = strftime($_CONF['daytime']);
    $retval = COM_startBlock($MESSAGE[40] . ' - ' . $timestamp, '', COM_getBlockTemplate('_msg_block', 'header'))
            . '<p class="sysmessage"><img src="' . $_CONF['layout_url'] . '/images/sysmessage.'
            . $_IMAGE_TYPE . '" alt="" ' . XHTML . '>' . '<div>'.$message.'</div>' . '</p>'
            . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));

    return $retval;
}


/**
* Escape a string for displaying in HTML
*/
function DLM_htmlspecialchars($text)
{
    $text = str_replace( // Unescape a string
        array('&lt;', '&gt;', '&amp;', '&quot;', '&#039;'),
        array(   '<',    '>',     '&',      '"',      "'"),
        $text
    );
    return htmlspecialchars($text, ENT_QUOTES, COM_getCharset());
}


function DLM_nl2br($text)
{
    return preg_replace("/(\015\012)|(\015)|(\012)/", "<br" . XHTML . ">", $text);
}


function DLM_reedit($function, $args = array())
{
    $display = '';
    if (function_exists($function)) {
        switch (count($args)) {
        case 0:
            $display = $function();
            break;
        case 1:
            $display = $function($args[0]);
            break;
        case 2:
            $display = $function($args[0], $args[1]);
            break;
        case 3:
            $display = $function($args[0], $args[1], $args[2]);
            break;
        default:
            $display = '';
            break;
        }
    }
    echo $display;
    exit;
}

// Move file from tmp directory to the main file directory
function DLM_moveNewFile($tmpfile, $newfile)
{
    global $_DLM_CONF;

    if (file_exists($tmpfile) && !is_dir($tmpfile)) {
        $rename = @rename($tmpfile, $newfile);
        $chown = @chmod($newfile, intval((string)$_DLM_CONF['filepermissions'], 8));
        $success = true;
        if (!file_exists($newfile)) {
            COM_errorLog("Downloads: upload approve error: "
                       . "New file does not exist after move of tmp file: '" . $newfile . "'");
            DLM_showErrorMessage('1002');
            $success = false;
        }
    } else {
        COM_errorLog("Downloads: upload approve error: "
                   . "Temporary file does not exist: '" . $tmpfile . "'");
        DLM_showErrorMessage('1001');
        $success = false;
    }

    return $success;
}

// Approve the uploaded file (process after the approval)
function DLM_approveNewDownload($id)
{
    global $_TABLES, $_CONF, $_DLM_CONF;

    $result = DB_query("SELECT url, logourl, date, secret_id "
                     . "FROM {$_TABLES['downloads']} "
                     . "WHERE lid = '" . addslashes($id) . "'");
    list($url, $logourl, $date, $secret_id) = DB_fetchArray($result);

    $safename = DLM_encodeFileName($url);
    $tmpfile = $_DLM_CONF['path_filestore'] . 'tmp' . date('YmdHis', $date) . $safename;
    $newfile = $_DLM_CONF['path_filestore'] . $secret_id . '_' . $safename;
    $success = DLM_moveNewFile($tmpfile, $newfile);

    if ($success && !empty($logourl)) {
        $safename = DLM_encodeFileName($logourl);
        $tmpfile = $_DLM_CONF['path_snapstore'] . 'tmp' . date('YmdHis', $date) . $safename;
        $newfile = $_DLM_CONF['path_snapstore'] . $safename;
        $success = DLM_moveNewFile($tmpfile, $newfile);
        if ($success) {
            DLM_makeThumbnail($safename);
        }
    }

    if ($success) {

        // PLG_itemSaved($lid, 'downloads');

        // COM_rdfUpToDateCheck('downloads', $cid, $lid);

        // Send a email to submitter notifying them that file was approved
        if ($_DLM_CONF['download_emailoption']) {
            DLM_sendNotification($id);
        }
    }
}

function DLM_unlink($path)
{
    if (!empty($path) && file_exists($path) && !is_dir($path)) {
        return @unlink($path);
    }
    return false;
}

function DLM_delNewDownload($id)
{
    global $_CONF, $_TABLES, $_DLM_CONF, $LANG_DLM;

    $result = DB_query("SELECT url, logourl, date "
                     . "FROM {$_TABLES['downloadsubmission']} "
                     . "WHERE lid = '" . addslashes($id) . "'");
    list($url, $logourl, $date) = DB_fetchArray($result);
    if (empty($url)) return;
    $tmpfilename = $_DLM_CONF['path_filestore'] . 'tmp' . date('YmdHis', $date) . DLM_encodeFileName($url);
    $tmpshotname = $_DLM_CONF['path_snapstore'] . 'tmp' . date('YmdHis', $date) . DLM_encodeFileName($logourl);
    DLM_unlink($tmpfilename);
    DLM_unlink($tmpshotname);
    DB_delete($_TABLES['downloadsubmission'], 'lid', addslashes($id));
}

function DLM_changeFileExt($src_path, $ext)
{
    $src_parts = pathinfo($src_path);
    $extension = $src_parts['extension'];
    
    if (!empty($extension)) {
        $dest_path = substr($src_path, 0, strlen($src_path) - strlen($extension) - 1) . '.' . $ext;
    } else {
        $dest_path = $src_path . '.' . $ext;
    }
    return $dest_path;
}

function DLM_makeThumbnail($filename)
{
    global $_DLM_CONF;

    if (empty($filename)) return false;

    $src_path = $_DLM_CONF['path_snapstore'] . $filename;
    if (!file_exists($src_path)) return false;
    $src_parts = pathinfo($src_path);
    $ext  = strtolower($src_parts['extension']);
    $name = $src_parts['filename'];

    switch ($_DLM_CONF['tnimage_format']) {
        case 'jpg': $dst_path = $_DLM_CONF['path_tnstore'] . $name . '.jpg'; break;
        case 'png': $dst_path = $_DLM_CONF['path_tnstore'] . $name . '.png'; break;
    }

    // Get the size of an image
    list($width, $height) = getimagesize($src_path);
    $newwidth  = $_DLM_CONF['max_tnimage_width'];
    $newheight = intval($height * $_DLM_CONF['max_tnimage_width'] / $width);

    // Create a new image from file
    switch ($ext) {
        case 'jepg': $source = imagecreatefromjpeg($src_path); break;
        case 'jpg': $source = imagecreatefromjpeg($src_path); break;
        case 'png': $source = imagecreatefrompng($src_path);  break;
        case 'gif': $source = imagecreatefromgif($src_path);  break;
        default: return false; break;
    }

    if (($width <= $_DLM_CONF['max_tnimage_width']) && ($height <= $_DLM_CONF['max_tnimage_height'])) {
        // Create an image
        $thumb = imagecreatetruecolor($width, $height);
        // Copy
        imagecopy($thumb, $source, 0, 0, 0, 0, $width, $height);
    } else {
        // Create an image
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        // Resize
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        if ($newwidth < $newheight) {
            // Create an image
            $thumb2 = imagecreatetruecolor($newwidth, $newwidth);
            // Trim
            imagecopyresampled($thumb2, $thumb, 0, 0, 0, 0, $newwidth, $newwidth, $newwidth, $newwidth);
            $thumb = $thumb2;
        }
    }

    // Output image to file
    switch ($_DLM_CONF['tnimage_format']) {
        case 'jpg': imagejpeg($thumb, $dst_path, 85); break;
        case 'png': imagepng($thumb,  $dst_path);     break;
    }

    // Frees any memory associated with image
    imagedestroy($thumb);
    if (is_resource($source)) {
        imagedestroy($source);
    }
    if (is_resource($thumb2)) {
        imagedestroy($thumb2);
    }
    return true;
}

function DLM_getImgSizeAttr($imgpath)
{
    global $_DLM_CONF;

    if (!file_exists($imgpath)) return '';
    $dimensions = getimagesize($imgpath);
    if (empty($dimensions[0]) || empty($dimensions[1])) return '';
    $snapwidth  = $dimensions[0];
    $snapheight = $dimensions[1];
    if ($dimensions[0] > $_DLM_CONF['max_tnimage_width']) {
        $snapwidth  = $_DLM_CONF['max_tnimage_width'];
        $snapheight = intval ($dimensions[1] * $_DLM_CONF['max_tnimage_width'] / $dimensions[0]);
    }
    return 'width="' . $snapwidth . '" height="' . $snapheight . '" ';
}


/**
* Gets the <option> values for calendar months
*
* @param        string      $selected       Selected month
* @see function COM_getDayFormOptions
* @see function COM_getYearFormOptions
* @see function COM_getHourFormOptions
* @see function COM_getMinuteFormOptions
* @return   string  HTML Months as option values
*/

function DLM_getMonthFormOptions($selected = '')
{
    $month_options = '';
    for ($i = 1; $i <= 12; $i++) {
        $mval = $i;
        $month_options .= '<option value="' . $mval . '"';
        if ($i == $selected) {
            $month_options .= ' ' . UC_SELECTED;
        }
        $month_options .= '>' . $i . '</option>';
    }
    return $month_options;
}

// If the file name contains double-byte characters, replace the file name to the md5 hash
function DLM_encodeFileName($name)
{
    $name = str_replace(' ', '_', $name);
    $parts = pathinfo($name);
    if (preg_match("/[^-_.a-zA-Z0-9]/", $parts['filename'])) {
        $name = md5($parts['filename']);
        if (!empty($parts['extension'])) {
            $name .= '.' . $parts['extension'];
        }
    }
    return $name;
}

function DLM_createSafeFileName($name, $prefix='')
{
    return $prefix . (!empty($prefix) ? '_' : '') . DLM_encodeFileName($name);
}

// 
function DLM_modTNPath($url)
{
    $parts = pathinfo($url);
    $extary = array('jpg', 'png');
    foreach ($extary as $ext) {
        $len = strlen(ext);
        $modurl = substr($url, 0, -$len) . $ext;
        if (file_exists($modurl)) {
            return $modurl;
        }
    }
    return $url;
}

function DLM_setDefaultTemplateVars(&$T)
{
    global $_CONF;
    $T->set_var(array(
        'site_url'       => $_CONF['site_url'],
        'site_admin_url' => $_CONF['site_admin_url'],
        'layout_url'     => $_CONF['layout_url'],
        'xhtml'          => XHTML
    ));
}


// Moves an uploaded file in temporary directory to data directory
function DLM_uploadNewFile($newfile, $directory, $name = '')
{
    global $_DLM_CONF;

    $tmp = $newfile['tmp_name'];
    if (empty($name)) {
        $name = COM_applyFilter($newfile['name']);
        if (empty($name)) return false;
    }
    $newfilepath = $directory . DLM_encodeFileName($name);

    if (!is_uploaded_file($tmp)) {
        COM_errorLog("Downloads: upload error: Temporary file does not exist: '" . $tmp . "'");
        DLM_showErrorMessage('1003');
        return false;
    }

    if (file_exists($newfilepath)) {
        COM_errorLog("Downloads: warning: Added new filelisting for a file that already exists " . $newfilepath);
        return true; // not uploaded. this OK? or upload and overwrite force.
    }

    if (!move_uploaded_file($tmp, $newfilepath)) {
        COM_errorLog("Downloads: upload error: Could not move an uploaded file: " . $tmp . " to " . $name);
        DLM_showErrorMessage('1004');
        return false;
    }

    @chmod($newfilepath, intval((string)$_DLM_CONF['filepermissions'], 8));
    return true;
}


// Send a email to submitter notifying them that file was approved
function DLM_sendNotification($lid)
{
    global $_CONF, $_TABLES, $LANG_DLM, $LANG08;

    $lid = addslashes($lid);
    $result = DB_query("SELECT username, email, b.url "
                     . "FROM {$_TABLES['users']} a, {$_TABLES['downloads']} b "
                     . "WHERE a.uid = b.owner_id AND b.lid = '$lid'");
    list($username, $email, $url) = DB_fetchArray($result);
    $body  = sprintf($LANG_DLM['hello'], $username). "\n\n"
           . $LANG_DLM['weapproved'] . " " . $url . " \n"
           . $LANG_DLM['thankssubmit'] . "\n\n"
           . "{$_CONF['site_name']}\n"
           . "{$_CONF['site_url']}\n"
           . "\n------------------------------\n"
           . "\n$LANG08[34]\n"
           . "\n------------------------------\n";
    $subject = $_CONF['site_name'] . ' ' . $LANG_DLM['approved'];
    COM_mail($email, $subject, $body);
}

function DLM_hasAccess_history()
{
    global $_DLM_CONF;

    switch ($_DLM_CONF['download_dlreport']) {
    case 'all':
        return true;
        break;
    case 'user':
        return (!COM_isAnonUser());
        break;
    case 'editor':
        return SEC_hasRights('downloads.edit');
        break;
    default:
        return false;
        break;
    }
}

function DLM_createHTMLDocument(&$content, $information = array())
{
    if (function_exists('COM_createHTMLDocument')) {
        return COM_createHTMLDocument($content, $information);
    }

    // Retrieve required variables from information array
    $what = 'menu';
    if (isset($information['what'])) {
        $what = $information['what'];
    }
    $pagetitle = '';
    if (isset($information['pagetitle'])) {
        $pagetitle = $information['pagetitle'];
    }
    $headercode = '';
    if (isset($information['headercode'])) {
        $headercode = $information['headercode'];
    }
    $rightblock = -1;
    if (isset($information['rightblock'])) {
        $rightblock = $information['rightblock'];
    } 
    $custom = '';
    if (isset($information['custom'])) {
        $custom = $information['custom'];
    }

    return COM_siteHeader($what, $pagetitle, $headercode) . $content
         . COM_siteFooter($rightblock, $custom);
}
?>
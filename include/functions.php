<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/include/functions.php                                   |
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

    $sel_id = DB_escapeString($sel_id);
    $voteresult = DB_query("SELECT rating FROM {$_TABLES['downloadvotes']} "
                          ."WHERE lid = '$sel_id'");
    $votesDB = DB_numRows($voteresult);
    $totalrating = 0;
    $finalrating = 0;
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
	if (is_callable('COM_strftime')) {
		$timestamp = COM_strftime($_CONF['daytime']);
	} else {
		$timestamp = strftime($_CONF['daytime']);
	}

    $content = COM_startBlock($MESSAGE[40] . ' - ' . $timestamp, '', COM_getBlockTemplate('_msg_block', 'header'))
             . '<p class="sysmessage"><img src="' . $_CONF['layout_url'] . '/images/sysmessage.'
             . $_IMAGE_TYPE . '" alt="" ' . XHTML . '>' . $message . '</p>'
             . '<p class="sysmessage" style="text-align:center;">[ <a href="javascript:history.go(-' . $pages . ')">'
             . $LANG_DLM['goback'] . '</a> ]</p>'
             . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
    $display = COM_createHTMLDocument($content, array('what' => 'menu'));
    COM_output($display);
    exit;
}


function DLM_showMessage($e_code)
{
    global $_CONF, $_IMAGE_TYPE, $LANG_DLM, $MESSAGE;

    $e_code = (in_array($e_code, array_keys($LANG_DLM))) ? $e_code : '9999';
    $message = $LANG_DLM[$e_code];
	if (is_callable('COM_strftime')) {
		$timestamp = COM_strftime($_CONF['daytime']);
	} else {
		$timestamp = strftime($_CONF['daytime']);
	}	

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
	if (is_callable('COM_strftime')) {
		$timestamp = COM_strftime($_CONF['daytime']);
	} else {
		$timestamp = strftime($_CONF['daytime']);
	}
	
    $retval = COM_startBlock($MESSAGE[40] . ' - ' . $timestamp, '', COM_getBlockTemplate('_msg_block', 'header'))
            . '<p class="sysmessage"><img src="' . $_CONF['layout_url'] . '/images/sysmessage.'
            . $_IMAGE_TYPE . '" alt="" ' . XHTML . '>' . '<div>'.$message.'</div>' . '</p>'
            . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));

    return $retval;
}


/**
* Escape a string for displaying in HTML
*/
/**
 * Check whether a download is publicly readable by a user.
 *
 * @param string $lid
 * @param int    $uid 0 = current user
 * @return bool
 */
function DLM_canViewDownload($lid, $uid = 0)
{
    global $_TABLES;

    $lid = DB_escapeString($lid);
    $now = time();
    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloads']} a "
         . "LEFT JOIN {$_TABLES['downloadcategories']} b ON a.cid=b.cid "
         . "WHERE a.lid='$lid' "
         . "AND a.is_released=1 "
         . "AND a.date<=$now "
         . "AND b.is_enabled=1 "
         . COM_getPermSQL('AND', $uid, 2, 'b');
    list($count) = DB_fetchArray(DB_query($sql));

    return ((int) $count === 1);
}

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

/**
 * Finalize files belonging to a pending download submission.
 *
 * Shared by both the Downloads administration editor and Geeklog moderation.
 */
function DLM_finalizeSubmissionFiles($date, $url, $logourl, $secret_id)
{
    global $_DLM_CONF;

    if (empty($url) || empty($secret_id)) {
        DLM_errorLog("Downloads: approval error: Missing file name or secret id.");
        return false;
    }

    if (!DLM_ensureDirectory($_DLM_CONF['path_filestore'])) {
        return false;
    }
    if (!empty($logourl) && !DLM_ensureDirectory($_DLM_CONF['path_snapstore'])) {
        return false;
    }

    $safeurl = DLM_createSafeFileName($url);
    $tmpfile = rtrim($_DLM_CONF['path_filestore'], "/\\") . DIRECTORY_SEPARATOR
             . 'tmp' . date('YmdHis', $date) . $safeurl;
    $newfile = rtrim($_DLM_CONF['path_filestore'], "/\\") . DIRECTORY_SEPARATOR
             . DLM_createSafeFileName($url, $secret_id);

    if (!is_file($tmpfile) || is_file($newfile) || !rename($tmpfile, $newfile)) {
        DLM_errorLog("Downloads: approval error: Could not finalize pending download file.");
        return false;
    }

    @chmod($newfile, intval((string) $_DLM_CONF['filepermissions'], 8));

    if (!empty($logourl)) {
        $safesnap = DLM_createSafeFileName($logourl);
        $tmpsnap = rtrim($_DLM_CONF['path_snapstore'], "/\\") . DIRECTORY_SEPARATOR
                 . 'tmp' . date('YmdHis', $date) . $safesnap;
        $newsnap = rtrim($_DLM_CONF['path_snapstore'], "/\\") . DIRECTORY_SEPARATOR
                 . $safesnap;

        if (!is_file($tmpsnap) || is_file($newsnap) || !rename($tmpsnap, $newsnap)) {
            rename($newfile, $tmpfile);
            DLM_errorLog("Downloads: approval error: Snapshot finalization failed; main file restored to pending state.");
            return false;
        }

        @chmod($newsnap, intval((string) $_DLM_CONF['filepermissions'], 8));
        DLM_makeThumbnail($safesnap);
    }

    return is_file($newfile);
}

// Approve the uploaded file (process after the approval)
function DLM_approveNewDownload($id)
{
    global $_TABLES, $_DLM_CONF;

    $id = DB_escapeString($id);
    $result = DB_query("SELECT url, logourl, date, secret_id, cid "
                     . "FROM {$_TABLES['downloads']} "
                     . "WHERE lid = '$id'");

    if (DB_numRows($result) != 1) {
        DLM_errorLog("Downloads: approval error: Published moderation row not found for '$id'.");
        return false;
    }

    $A = DB_fetchArray($result);
    if (!DLM_finalizeSubmissionFiles(
        (int) $A['date'],
        $A['url'],
        $A['logourl'],
        $A['secret_id']
    )) {
        if (DB_count($_TABLES['downloadsubmission'], 'lid', $id) == 0) {
            DB_query("INSERT INTO {$_TABLES['downloadsubmission']} "
                   . "SELECT * FROM {$_TABLES['downloads']} WHERE lid = '$id'");
        }
        DB_delete($_TABLES['downloads'], 'lid', $id);
        DLM_errorLog("Downloads: moderation approval rolled back to pending state for '$id'.");
        return false;
    }

    DLM_recordSubmissionStatus($id, 'published', $id);
    PLG_itemSaved($id, 'downloads');
    COM_rdfUpToDateCheck('downloads', $A['cid'], $id);

    if ($_DLM_CONF['download_emailoption']) {
        DLM_sendNotification($id);
    }

    return true;
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

    DLM_recordSubmissionStatus($id, 'rejected');

    $result = DB_query("SELECT url, logourl, date "
                     . "FROM {$_TABLES['downloadsubmission']} "
                     . "WHERE lid = '" . DB_escapeString($id) . "'");
    list($url, $logourl, $date) = DB_fetchArray($result);
    if (empty($url)) return;
    $tmpfilename = $_DLM_CONF['path_filestore'] . 'tmp' . date('YmdHis', $date) . DLM_encodeFileName($url);
    $tmpshotname = $_DLM_CONF['path_snapstore'] . 'tmp' . date('YmdHis', $date) . DLM_encodeFileName($logourl);
    DLM_unlink($tmpfilename);
    DLM_unlink($tmpshotname);
    DB_delete($_TABLES['downloadsubmission'], 'lid', DB_escapeString($id));
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

    if (empty($filename)) {
        return false;
    }

    $src_path = rtrim($_DLM_CONF['path_snapstore'], "/\\")
              . DIRECTORY_SEPARATOR . $filename;
    if (!is_file($src_path)) {
        return false;
    }

    if (!DLM_ensureDirectory($_DLM_CONF['path_tnstore'])) {
        return false;
    }

    $dimensions = @getimagesize($src_path);
    if ($dimensions === false || empty($dimensions[0]) || empty($dimensions[1])) {
        DLM_errorLog("Downloads: thumbnail error: Invalid image source '" . $src_path . "'.");
        return false;
    }

    $src_parts = pathinfo($src_path);
    $ext = isset($src_parts['extension']) ? strtolower($src_parts['extension']) : '';
    $name = $src_parts['filename'];

    switch ($_DLM_CONF['tnimage_format']) {
    case 'jpg':
        $dst_path = rtrim($_DLM_CONF['path_tnstore'], "/\\")
                  . DIRECTORY_SEPARATOR . $name . '.jpg';
        break;
    case 'png':
        $dst_path = rtrim($_DLM_CONF['path_tnstore'], "/\\")
                  . DIRECTORY_SEPARATOR . $name . '.png';
        break;
    default:
        DLM_errorLog("Downloads: thumbnail error: Unsupported thumbnail format.");
        return false;
    }

    switch ($ext) {
    case 'jpeg':
    case 'jpg':
        $source = @imagecreatefromjpeg($src_path);
        break;
    case 'png':
        $source = @imagecreatefrompng($src_path);
        break;
    case 'gif':
        $source = @imagecreatefromgif($src_path);
        break;
    default:
        return false;
    }

    if ($source === false) {
        DLM_errorLog("Downloads: thumbnail error: Could not decode source image.");
        return false;
    }

    $width = (int) $dimensions[0];
    $height = (int) $dimensions[1];
    $max_width = max(1, (int) $_DLM_CONF['max_tnimage_width']);
    $max_height = max(1, (int) $_DLM_CONF['max_tnimage_height']);

    $scale = min(1, $max_width / $width, $max_height / $height);
    $newwidth = max(1, (int) floor($width * $scale));
    $newheight = max(1, (int) floor($height * $scale));

    $thumb = imagecreatetruecolor($newwidth, $newheight);
    if ($thumb === false) {
        if (is_resource($source) || is_object($source)) {
            imagedestroy($source);
        }
        return false;
    }

    if (!imagecopyresampled(
        $thumb,
        $source,
        0,
        0,
        0,
        0,
        $newwidth,
        $newheight,
        $width,
        $height
    )) {
        imagedestroy($thumb);
        if (is_resource($source) || is_object($source)) {
            imagedestroy($source);
        }
        return false;
    }

    if ($_DLM_CONF['tnimage_format'] === 'jpg') {
        $success = imagejpeg($thumb, $dst_path, 85);
    } else {
        $success = imagepng($thumb, $dst_path);
    }

    imagedestroy($thumb);
    if (is_resource($source) || is_object($source)) {
        imagedestroy($source);
    }

    if (!$success) {
        DLM_errorLog("Downloads: thumbnail error: Could not write thumbnail.");
        return false;
    }

    @chmod($dst_path, intval((string) $_DLM_CONF['filepermissions'], 8));
    return true;
}

function DLM_getImgSizeAttr($imgpath)
{
    global $_DLM_CONF;

    if (!file_exists($imgpath)) return '';
    $dimensions = @getimagesize($imgpath);
    if ($dimensions === false || empty($dimensions[0]) || empty($dimensions[1])) return '';
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
        $len = strlen($ext);
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


/**
 * Ensure a configured Downloads storage directory exists and is writable.
 *
 * @param  string $directory
 * @return bool
 */
function DLM_ensureDirectory($directory)
{
    $directory = rtrim((string) $directory, "/\\") . DIRECTORY_SEPARATOR;

    if (is_dir($directory)) {
        return is_writable($directory);
    }

    if (!@mkdir($directory, 0755, true) && !is_dir($directory)) {
        DLM_errorLog("Downloads: storage error: Could not create directory: '" . $directory . "'");
        return false;
    }

    if (!is_writable($directory)) {
        DLM_errorLog("Downloads: storage error: Directory is not writable: '" . $directory . "'");
        return false;
    }

    return true;
}

/**
 * Validate an uploaded image using the actual file content.
 *
 * @param  array $file
 * @return bool
 */
function DLM_isUploadedImage($file)
{
    if (!is_array($file) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || empty($info[2])) {
        return false;
    }

    return in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), true);
}

// Moves an uploaded file in temporary directory to data directory
function DLM_uploadNewFile($newfile, $directory, $name = '')
{
    global $_DLM_CONF;

    if (!is_array($newfile) || empty($newfile['tmp_name'])) {
        DLM_errorLog("Downloads: upload error: Invalid upload data.");
        return false;
    }

    if (!DLM_ensureDirectory($directory)) {
        DLM_showErrorMessage('1004');
        return false;
    }

    $tmp = $newfile['tmp_name'];
    if (empty($name)) {
        $name = isset($newfile['name']) ? COM_applyFilter($newfile['name']) : '';
        if (empty($name)) {
            return false;
        }
    }

    $directory = rtrim($directory, "/\\") . DIRECTORY_SEPARATOR;
    $newfilepath = $directory . DLM_encodeFileName($name);

    if (!is_uploaded_file($tmp)) {
        DLM_errorLog("Downloads: upload error: Temporary file does not exist: '" . $tmp . "'");
        DLM_showErrorMessage('1003');
        return false;
    }

    if (file_exists($newfilepath)) {
        DLM_errorLog("Downloads: warning: Added new filelisting for a file that already exists " . $newfilepath);
        return true;
    }

    if (!move_uploaded_file($tmp, $newfilepath)) {
        DLM_errorLog("Downloads: upload error: Could not move uploaded file to: '" . $newfilepath . "'");
        DLM_showErrorMessage('1004');
        return false;
    }

    @chmod($newfilepath, intval((string)$_DLM_CONF['filepermissions'], 8));
    return true;
}


/**
 * Record a durable submission status transition.
 *
 * @param string $lid
 * @param string $status pending|published|rejected
 * @param string $public_lid
 * @return bool
 */
function DLM_recordSubmissionStatus($lid, $status, $public_lid = '')
{
    global $_TABLES;

    $lid = DB_escapeString($lid);
    $status = DB_escapeString($status);
    $public_lid = DB_escapeString($public_lid);

    $source = $_TABLES['downloadsubmission'];
    if (DB_count($source, 'lid', $lid) != 1) {
        $source = $_TABLES['downloads'];
    }
    if (DB_count($source, 'lid', $lid) != 1) {
        DLM_errorLog("Downloads: submission history error: Source record not found for '$lid'.");
        return false;
    }

    $result = DB_query("SELECT lid, owner_id, cid, title, date FROM $source WHERE lid='$lid'");
    $A = DB_fetchArray($result);
    $owner_id = (int) $A['owner_id'];
    $cid = DB_escapeString($A['cid']);
    $title = DB_escapeString($A['title']);
    $submitted_date = (int) $A['date'];
    $status_date = time();

    $last = DB_query("SELECT status FROM {$_TABLES['downloadsubmissionhistory']} "
                   . "WHERE lid='$lid' AND owner_id=$owner_id "
                   . "ORDER BY history_id DESC LIMIT 1");
    if (DB_numRows($last) == 1) {
        list($last_status) = DB_fetchArray($last);
        if ($last_status === $status) {
            return true;
        }
    }

    DB_query("INSERT INTO {$_TABLES['downloadsubmissionhistory']} "
           . "(lid, owner_id, cid, title, submitted_date, status, status_date, public_lid) "
           . "VALUES ('$lid', $owner_id, '$cid', '$title', $submitted_date, "
           . "'$status', $status_date, '$public_lid')");

    return !DB_error();
}

/**
 * Render matching HTML and plaintext Downloads email templates.
 *
 * @param string $template
 * @param array  $vars
 * @return array
 */
function DLM_renderEmailTemplates($template, $vars)
{
    global $LANG31;

    $T = COM_newTemplate(CTL_plugin_templatePath('downloads', 'emails'));
    $T->set_file(array('email_html' => $template . '-html.thtml'));
    $T->preprocess_fn = 'CTL_removeLineFeeds';
    $T->set_file(array('email_plaintext' => $template . '-plaintext.thtml'));

    $T->set_var('email_divider', $LANG31['email_divider']);
    $T->set_var('email_divider_html', $LANG31['email_divider_html']);
    $T->set_var('LB', LB);

    foreach ($vars as $key => $value) {
        $T->set_var($key, $value);
    }

    return array(
        $T->parse('output', 'email_html'),
        $T->parse('output', 'email_plaintext')
    );
}

/**
 * Notify the configured moderator address about a new submission.
 *
 * @param string $lid
 * @return bool
 */
function DLM_sendSubmissionNotification($lid)
{
    global $_CONF, $_TABLES, $_DLM_CONF, $LANG_DLM;

    if (empty($_DLM_CONF['notify_on_submission'])) {
        return true;
    }

    $email = isset($_DLM_CONF['submission_notify_email'])
        ? trim($_DLM_CONF['submission_notify_email']) : '';
    if ($email === '') {
        $email = isset($_CONF['site_mail']) ? trim($_CONF['site_mail']) : '';
    }
    if ($email === '') {
        DLM_errorLog("Downloads: submission notification skipped: no recipient configured.");
        return false;
    }

    $lid_sql = DB_escapeString($lid);
    $result = DB_query("SELECT s.title, s.owner_id, u.username "
                     . "FROM {$_TABLES['downloadsubmission']} s "
                     . "LEFT JOIN {$_TABLES['users']} u ON u.uid=s.owner_id "
                     . "WHERE s.lid='$lid_sql'");
    if (DB_numRows($result) != 1) {
        DLM_errorLog("Downloads: submission notification skipped: submission '$lid_sql' not found.");
        return false;
    }

    $A = DB_fetchArray($result);
    $subject = $_CONF['site_name'] . ' - ' . $LANG_DLM['submission_notification_subject'];
    $moderation_url = $_CONF['site_admin_url'] . '/moderation.php';
    $message = DLM_renderEmailTemplates('download_submission', array(
        'notification_intro' => $LANG_DLM['submission_notification_intro'],
        'lang_title' => $LANG_DLM['submission_notification_title'],
        'submission_title' => DLM_htmlspecialchars($A['title']),
        'lang_submitter' => $LANG_DLM['submission_notification_submitter'],
        'submission_submitter' => DLM_htmlspecialchars(
            COM_getDisplayName((int) $A['owner_id'], $A['username'])
        ),
        'lang_moderate' => $LANG_DLM['submission_notification_moderate'],
        'moderation_url' => $moderation_url
    ));

    return COM_mail($email, $subject, $message, '', true);
}

/**
 * Notify a submitter after their download is approved.
 *
 * @param string $lid
 * @return bool
 */
function DLM_sendNotification($lid)
{
    global $_CONF, $_TABLES, $LANG_DLM;

    $lid_sql = DB_escapeString($lid);
    $result = DB_query("SELECT u.username, u.email, d.title "
                     . "FROM {$_TABLES['users']} u "
                     . "INNER JOIN {$_TABLES['downloads']} d ON u.uid=d.owner_id "
                     . "WHERE d.lid='$lid_sql'");
    if (DB_numRows($result) != 1) {
        DLM_errorLog("Downloads: approval notification skipped: download '$lid_sql' not found.");
        return false;
    }

    $A = DB_fetchArray($result);
    if (empty($A['email'])) {
        return false;
    }

    $download_url = COM_buildURL(
        $_CONF['site_url'] . '/downloads/index.php?id=' . rawurlencode($lid)
    );
    $message = DLM_renderEmailTemplates('download_approved', array(
        'greeting' => sprintf($LANG_DLM['hello'], DLM_htmlspecialchars($A['username'])),
        'approval_text' => $LANG_DLM['weapproved'],
        'download_title' => DLM_htmlspecialchars($A['title']),
        'download_url' => $download_url,
        'thanks_text' => $LANG_DLM['thankssubmit'],
        'site_name' => DLM_htmlspecialchars($_CONF['site_name']),
        'site_url' => $_CONF['site_url']
    ));

    $subject = $_CONF['site_name'] . ' ' . $LANG_DLM['approved'];
    return COM_mail($A['email'], $subject, $message, '', true);
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

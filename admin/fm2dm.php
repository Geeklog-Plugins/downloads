<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/admin/plugins/downloads/fm2dm.php                             |
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
require_once $_CONF['path_system'] . 'lib-comment.php';
require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';

switch ($_CONF['language']) {
case 'japanese_utf-8':
    $_LANG_CONV = array(
        'title'                   => 'Filemgmt to Downloads Data Converter',
        'submit'                  => $LANG_ACCESS['submit'],
        'cancel'                  => $LANG_ACCESS['cancel'],
        'description1'            => 'Filemgmt プラグインのデータから Downloads プラグインのデータへコンバートします。コメントデータもサポートします。',
        'description2'            => 'この処理により Downloads Plugin のデータ及びコメントデータは全て削除されます!',
        'description3'            => 'なお、この処理では Filemgmt Plugin のデータ及びコメントデータを変更しません。',
        'description4'            => '下の実行ボタンをクリックすると、処理を開始します。（元に戻すことはできません。）',
        'dm_not_installed'        => 'Downloads Plugin はインストールされていないか、または無効になっています。',
        'fm_not_installed'        => 'Filemgmt Plugin はインストールされていないか、または無効になっています。',
        'num_dm_cat_data'         => 'Downloads Plugin カテゴリのデータ数: ',
        'del_dm_cat_data'         => 'Downloads Plugin カテゴリのデータを削除します。',
        'num_dm_info_data'        => 'Downloads Plugin ファイル情報のデータ数: ',
        'del_dm_info_data'        => 'Downloads Plugin ファイル情報のデータを削除します。',
        'num_dm_vote_data'        => 'Downloads Plugin ファイル評価のデータ数: ',
        'del_dm_vote_data'        => 'Downloads Plugin ファイル評価のデータを削除します。',
        'num_dm_history_data'     => 'Downloads Plugin ダウンロード履歴のデータ数: ',
        'del_dm_history_data'     => 'Downloads Plugin ダウンロード履歴のデータを削除します。',
        'num_dm_submission_data'  => 'Downloads Plugin ファイル投稿のデータ数: ',
        'del_dm_submission_data'  => 'Downloads Plugin ファイル投稿のデータを削除します。',
        'readable_fm_file_dir'    => 'Filemgmt Plugin ファイル保存ディレクトリは読み込み可能: ',
        'unreadable_fm_file_dir'  => 'Filemgmt Plugin ファイル保存ディレクトリは読み込み不可能: ',
        'writeable_dm_file_dir'   => 'Downloads Plugin ファイル保存ディレクトリは書き込み可能: ',
        'unwriteable_dm_file_dir' => 'Downloads Plugin ファイル保存ディレクトリは書き込み不可能: ',
        'unable_to_read_catimg'   => 'カテゴリ画像ファイルを読み込み不可能: ',
        'unable_to_conv_catimg'   => 'カテゴリ画像ファイルをコンバート（書き込み）不可能: ',
        'unable_to_read_dlfile'   => 'ダウンロードファイルを読み込み不可能: ',
        'unable_to_conv_dlfile'   => 'ダウンロードファイルをコンバート（書き込み）不可能: ',
        'unable_to_read_snapimg'  => 'スナップショット画像ファイルを読み込み不可能: ',
        'unable_to_conv_snapimg'  => 'スナップショット画像ファイルをコンバート（書き込み）不可能: ',
        'unable_to_make_tn'       => 'サムネール画像ファイルの作成失敗: ',
        'failure_delete_comment'  => 'コメントの削除失敗: ',
        'db_error'                => 'データベースアクセス中にエラーが発生: ',
        'process_interrupted'     => 'コンバート処理は中断されました。',
        'process_completed'       => 'コンバート処理は正常に終了しました!',
    );
    break;
case 'english':
case 'english_utf-8':
default:
    $_LANG_CONV = array(
        'title'                   => 'Filemgmt to Downloads Data Converter',
        'submit'                  => $LANG_ACCESS['submit'],
        'cancel'                  => $LANG_ACCESS['cancel'],
        'description1'            => 'Convert from Filemgmt Plugin data to Downloads Plugin data. To support the comments as well.',
        'description2'            => 'This process removes all Downloads Plugin data and comment data!',
        'description3'            => 'However, this process does not change Filemgmt Plugin data and comment data.',
        'description4'            => 'Click the Submit button below to start the process. (You cannot undo.)',
        'dm_not_installed'        => 'Downloads Plugin are not installed or disabled.',
        'fm_not_installed'        => 'Filemgmt Plugin are not installed or disabled.',
        'num_dm_cat_data'         => 'Number of Downloads Plugin category data: ',
        'del_dm_cat_data'         => 'Delete Downloads Plugin category data.',
        'num_dm_info_data'        => 'Number of Downloads Plugin file infomation data: ',
        'del_dm_info_data'        => 'Delete Downloads Plugin file infomation data.',
        'num_dm_vote_data'        => 'Number of Downloads Plugin vote data: ',
        'del_dm_vote_data'        => 'Delete Downloads Plugin vote data.',
        'num_dm_history_data'     => 'Number of Downloads Plugin history data: ',
        'del_dm_history_data'     => 'Delete Downloads Plugin history data.',
        'num_dm_submission_data'  => 'Number of Downloads Plugin submission data: ',
        'del_dm_submission_data'  => 'Delete Downloads Plugin submission data.',
        'readable_fm_file_dir'    => 'Filemgmt Plugin file store directory is readable: ',
        'unreadable_fm_file_dir'  => 'Filemgmt Plugin file store directory is not readable: ',
        'writeable_dm_file_dir'   => 'Downloads Plugin file store directory is writeable: ',
        'unwriteable_dm_file_dir' => 'Downloads Plugin file store directory is not writeable: ',
        'unable_to_read_catimg'   => 'Unable to read the category snap image file: ',
        'unable_to_conv_catimg'   => 'Category snap image file converting (write) impossible: ',
        'unable_to_read_dlfile'   => 'Unable to read the download file: ',
        'unable_to_conv_dlfile'   => 'Download file converting (write) impossible: ',
        'unable_to_read_snapimg'  => 'Unable to read the snapshot image file: ',
        'unable_to_conv_snapimg'  => 'Snapshot image file converting (write) impossible: ',
        'unable_to_make_tn'       => 'Thumbnail image file creation failure: ',
        'failure_delete_comment'  => 'Failed to remove a comment: ',
        'db_error'                => 'During a database access error occurred: ',
        'process_interrupted'     => 'Conversion process was interrupted.',
        'process_completed'       => 'Conversion process was completed successfully!',
    );
    break;
}

if (!in_array('downloads', $_PLUGINS)) {
    $display .= COM_siteHeader('menu', $MESSAGE[40])
             . COM_showMessageText($_LANG_CONV['dm_not_installed'], $MESSAGE[40])
             . COM_siteFooter();
    COM_output($display);
    exit;
}

if (!in_array('filemgmt', $_PLUGINS)) {
    $display .= COM_siteHeader('menu', $MESSAGE[40])
             . COM_showMessageText($_LANG_CONV['fm_not_installed'], $MESSAGE[40])
             . COM_siteFooter();
    COM_output($display);
    exit;
}

if (!SEC_hasRights('downloads.edit')) {
    $display .= COM_siteHeader('menu', $MESSAGE[30])
             . COM_showMessageText($MESSAGE[29], $MESSAGE[30])
             . COM_siteFooter();
    COM_accessLog("User {$_USER['username']} tried to illegally access the downloads administration screen.");
    COM_output($display);
    exit;
}

if (!SEC_hasRights('filemgmt.edit')) {
    $display .= COM_siteHeader('menu', $MESSAGE[30])
             . COM_showMessageText($MESSAGE[29], $MESSAGE[30])
             . COM_siteFooter();
    COM_accessLog("User {$_USER['username']} tried to illegally access the downloads administration screen.");
    COM_output($display);
    exit;
}

function DLM_convertData()
{
    global $_PLUGINS, $_DLM_CONF, $_GROUPS, $_CONF, $_TABLES, $_USER, $_FM_TABLES, $_FM_CONF, $_LANG_CONV, $_SUCCESS;

    $retval = '';

    if (is_readable($_FM_CONF['filemgmt_FileStore'])) {
        $retval .= '<p>' . $_LANG_CONV['readable_fm_file_dir'] . $_FM_CONF['filemgmt_FileStore'] . '</p>' . LB;
    } else {
        $retval .= '<p>' . $_LANG_CONV['unreadable_fm_file_dir'] . $_FM_CONF['filemgmt_FileStore'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }

    if (is_writeable($_DLM_CONF['path_filestore'])) {
        $retval .= '<p>' . $_LANG_CONV['writeable_dm_file_dir'] . $_DLM_CONF['path_filestore'] . '</p>' . LB;
    } else {
        $retval .= '<p>' . $_LANG_CONV['unwriteable_dm_file_dir'] . $_DLM_CONF['path_filestore'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }

    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloadcategories']}";
    list($count) = DB_fetchArray(DB_query($sql));
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    $retval .= '<p>' . $_LANG_CONV['num_dm_cat_data'] . $count . '</p>' . LB;
    if ($count > 0) {
        $retval .= '<p>' . $_LANG_CONV['del_dm_cat_data'] . '</p>' . LB;
        DB_query("DELETE FROM {$_TABLES['downloadcategories']}");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }

    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloads']}";
    list($count) = DB_fetchArray(DB_query($sql));
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    $retval .= '<p>' . $_LANG_CONV['num_dm_info_data'] . $count . '</p>' . LB;
    if ($count > 0) {
        $retval .= '<p>' . $_LANG_CONV['del_dm_info_data'] . '</p>' . LB;
        DB_query("DELETE FROM {$_TABLES['downloads']}");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }

    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloadvotes']}";
    list($count) = DB_fetchArray(DB_query($sql));
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    $retval .= '<p>' . $_LANG_CONV['num_dm_vote_data'] . $count . '</p>' . LB;
    if ($count > 0) {
        $retval .= '<p>' . $_LANG_CONV['del_dm_vote_data'] . '</p>' . LB;
        DB_query("DELETE FROM {$_TABLES['downloadvotes']}");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }

    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloadhistories']}";
    list($count) = DB_fetchArray(DB_query($sql));
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    $retval .= '<p>' . $_LANG_CONV['num_dm_history_data'] . $count . '</p>' . LB;
    if ($count > 0) {
        $retval .= '<p>' . $_LANG_CONV['del_dm_history_data'] . '</p>' . LB;
        DB_query("DELETE FROM {$_TABLES['downloadhistories']}");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }

    $sql = "SELECT COUNT(*) FROM {$_TABLES['downloadsubmission']}";
    list($count) = DB_fetchArray(DB_query($sql));
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    $retval .= '<p>' . $_LANG_CONV['num_dm_submission_data'] . $count . '</p>' . LB;
    if ($count > 0) {
        $retval .= '<p>' . $_LANG_CONV['del_dm_submission_data'] . '</p>' . LB;
        DB_query("DELETE FROM {$_TABLES['downloadsubmission']}");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }

    if (!$_SUCCESS) return $retval;

    $owner_id = $_USER['uid'];
    if (isset($_GROUPS['Downloads Admin'])) {
        $group_id = $_GROUPS['Downloads Admin'];
    } else {
        $group_id = SEC_getFeatureGroup('downloads.edit');
    }
    SEC_setDefaultPermissions($P, $_DLM_CONF['default_permissions']);
    foreach ($P as $key => $val) $$key = $val;


    $sql = "SELECT * FROM {$_FM_TABLES['filemgmt_cat']}";
    $result = DB_query($sql);
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    $corder = 0;
    while ($A = DB_fetchArray($result)) {
        foreach ($A as $key => $val) $$key = $val;
        if ($pid == 0) $pid = ROOTID;
        $corder += 10;
        $is_enabled = 1;

        if (!empty($imgurl)) {
            $imgurl = rawurldecode($imgurl);
            $catimg_src  = $_FM_CONF['filemgmt_SnapCat'] . $imgurl;
            if (!is_readable($catimg_src)) {
                $retval .= '<p>' . $_LANG_CONV['unable_to_read_catimg'] . $catimg_src . '</p>' . LB;
                $_SUCCESS = false;
                return $retval;
            }
            $catimg_dest = $_DLM_CONF['path_snapcat'] . DLM_createSafeFileName(DLM_createCatImgFilename($imgurl));
            $_SUCCESS = DLM_copyFile_fm2dm($catimg_src, $catimg_dest);
            if (!$_SUCCESS) {
                $retval .= '<p>' . $_LANG_CONV['unable_to_conv_catimg'] . $catimg_src . '</p>' . LB;
                return $retval;
            }
        }

        DB_query("INSERT INTO {$_TABLES['downloadcategories']} "

               . "(cid, pid, title, imgurl, corder, is_enabled, owner_id, group_id, "
               . "perm_owner, perm_group, perm_members, perm_anon) "

               . "VALUES ('$cid', '$pid', '$title', '$imgurl', $corder, $is_enabled, $owner_id, $group_id, "
               . "$perm_owner, $perm_group, $perm_members, $perm_anon)");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }


    $sql = "SELECT * FROM {$_FM_TABLES['filemgmt_filedetail']}";
    $result = DB_query($sql);
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    while ($A = DB_fetchArray($result)) {
        foreach ($A as $key => $val) $$key = $val;
        
        $project = '';
        $description = '';
        $detail = '';
        $postmode = 'plaintext';
        $commentcode = ($comments==1) ? 0 : -1;
        $is_released = $status;
        $is_listing = $status;
        $createddate = date('Y-m-d H:i:s', $date);
        $owner_id = $submitter;
        $secret_id = md5(uniqid());
        $mg_autotag = '';
        $tags = '';

        $url = rawurldecode($url);
        $src_url = $_FM_CONF['filemgmt_FileStore'] . $url;
        if (!is_readable($src_url)) {
            $retval .= '<p>' . $_LANG_CONV['unable_to_read_dlfile'] . $src_url . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
        $size = filesize($src_url);
        $md5  = md5_file($src_url);

        $dest_url = $_DLM_CONF['path_filestore'] . DLM_createSafeFileName($url, $secret_id);
        $_SUCCESS = DLM_copyFile_fm2dm($src_url, $dest_url);
        if (!$_SUCCESS) {
            $retval .= '<p>' . $_LANG_CONV['unable_to_conv_dlfile'] . $src_url . '</p>' . LB;
            return $retval;
        }

        if (!empty($logourl)) {
            $logourl = rawurldecode($logourl);
            $snap_src = $_FM_CONF['filemgmt_SnapStore'] . $logourl;
            if (!is_readable($snap_src)) {
                $retval .= '<p>' . $_LANG_CONV['unable_to_read_snapimg'] . $snap_src . '</p>' . LB;
                $_SUCCESS = false;
                return $retval;
            }
            $logourl = DLM_createSnapFilename($logourl, $_TABLES['downloads'], 'logourl');
            $snap_dest = $_DLM_CONF['path_snapstore'] . DLM_createSafeFileName($logourl);
            $_SUCCESS = DLM_copyFile_fm2dm($snap_src, $snap_dest);
            if (!$_SUCCESS) {
                $retval .= '<p>' . $_LANG_CONV['unable_to_conv_snapimg'] . $snap_src . '</p>' . LB;
                return $retval;
            }
            $_SUCCESS = DLM_makeThumbnail(DLM_createSafeFileName($logourl));
            if (!$_SUCCESS) {
                $retval .= '<p>' . $_LANG_CONV['unable_to_make_tn'] . $snap_src . '</p>' . LB;
                return $retval;
            }
        }

        DB_query("INSERT INTO {$_TABLES['downloads']} "

               . "(lid, cid, title, url, homepage, version, size, secret_id, md5, "
               . "project, description, detail, postmode, logourl, mg_autotag, tags, "
               . "date, hits, rating, votes, commentcode, is_released, is_listing, createddate, owner_id"
               . ") "

               . "VALUES ('$lid', '$cid', '$title', '$url', '$homepage', '$version', '$size', '$secret_id', '$md5', "
               . "'$project', '$description', '$detail', '$postmode', '$logourl', '$mg_autotag', '$tags', "
               . "$date, $hits, $rating, $votes, '$commentcode', $is_released, $is_listing, '$createddate', '$owner_id'"
               . ")");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }


    $sql = "SELECT * FROM {$_FM_TABLES['filemgmt_filedesc']}";
    $result = DB_query($sql);
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    while ($A = DB_fetchArray($result)) {
        foreach ($A as $key => $val) $$key = $val;

        $description = addslashes(stripslashes($description));
        $descri = '';
        $detail = '';

        // Search break page position
        $breakPosition = strpos($description, "\r\n\r\n");
        if (($breakPosition > 0) AND ($breakPosition < strlen($description))) {
            $descri = substr($description, 0, $breakPosition);
            $detail = substr($description, $breakPosition + 4, (strlen($description) - $breakPosition - 4));
        } else {
            $breakPosition = strpos($description, "\n\n");
            if (($breakPosition > 0) AND ($breakPosition < strlen($description))) {
                $descri = substr($description, 0, $breakPosition);
                $detail = substr($description, $breakPosition + 2, (strlen($description) - $breakPosition - 2));
            } else {
                $breakPosition = strpos($description, "\r\r");
                if (($breakPosition > 0) AND ($breakPosition < strlen($description))) {
                    $descri = substr($description, 0, $breakPosition);
                    $detail = substr($description, $breakPosition + 2, (strlen($description) - $breakPosition - 2));
                } else {
                    $descri = $description;
                }
            }
        }

        DB_query("UPDATE {$_TABLES['downloads']} "
               . "SET description='$descri', detail='$detail' "
               . "WHERE lid='$lid'");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }


    $sql = "SELECT * FROM {$_FM_TABLES['filemgmt_votedata']}";
    $result = DB_query($sql);
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    while ($A = DB_fetchArray($result)) {
        foreach ($A as $key => $val) $$key = $val;
        DB_query("INSERT INTO {$_TABLES['downloadvotes']} "
               . "(ratingid, lid, ratinguser, rating, ratinghostname, ratingtimestamp) "
               . "VALUES ('$ratingid', '$lid', '$ratinguser', '$rating', '$ratinghostname', '$ratingtimestamp')");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }


    $sql = "SELECT * FROM {$_FM_TABLES['filemgmt_history']}";
    $result = DB_query($sql);
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    while ($A = DB_fetchArray($result)) {
        foreach ($A as $key => $val) $$key = $val;
        DB_query("INSERT INTO {$_TABLES['downloadhistories']} "
               . "(uid, lid, remote_ip, date) "
               . "VALUES ('$uid', '$lid', '$remote_ip', '$date')");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
    }

    return $retval;
}

function DLM_copyFile_fm2dm($src_path, $dest_path)
{
    if (!is_readable($src_path)) return false;

    $fsrc  = fopen($src_path, 'r');
    $fdest = fopen($dest_path,'w+');
    $len = stream_copy_to_stream($fsrc, $fdest);
    fclose($fsrc);
    fclose($fdest);
    return ($len > 0);
}

function DLM_createCatImgFilename($name)
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

function DLM_createSnapFilename($name, $table, $field)
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


function DLM_saveComment(&$C)
{
    global $_CONF, $_TABLES, $_USER, $_LANG_CONV, $_SUCCESS;

    $retval = '';

    $title     = addslashes($C['title']);
    $comment   = addslashes($C['comment']);
    $sid       = addslashes(str_replace('fileid_', '', $C['sid']));
    $pid       = (int) $C['pid'];
    $type      = 'downloads';
    $name      = addslashes($C['name']);
    $ipaddress = addslashes($C['ipaddress']);
    $uid       = (int) $C['uid'];

    if ($pid > 0) {
        DB_lockTable ($_TABLES['comments']);

        $result = DB_query("SELECT rht, indent FROM {$_TABLES['comments']} "
                         . "WHERE cid = $pid AND sid = '$sid'");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
        list($rht, $indent) = DB_fetchArray($result);
        $rht2 = $rht + 1;
        $indent += 1;
        DB_query("UPDATE {$_TABLES['comments']} SET lft = lft + 2 "
               . "WHERE sid = '$sid' AND type = '$type' AND lft >= $rht");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
        DB_query("UPDATE {$_TABLES['comments']} SET rht = rht + 2 "
               . "WHERE sid = '$sid' AND type = '$type' AND rht >= $rht");
        if (DB_error()) {
            $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
            $_SUCCESS = false;
            return $retval;
        }
        if (isset($name)) {
            DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress,name',
                "'$sid',$uid,'$comment',now(),'$title',$pid,$rht,$rht2,$indent,'$type','$ipaddress','$name'");
        } else {
            DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress',
                "'$sid',$uid,'$comment',now(),'$title',$pid,$rht,$rht2,$indent,'$type','$ipaddress'");
        }
    } else {
        $rht = DB_getItem($_TABLES['comments'], 'MAX(rht)', "sid = '$sid'");
        if (DB_error()) {
            $rht = 0;
        }
        $rht2 = $rht + 1;
        $rht3 = $rht + 2;
        if (isset($name)) {
            DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress,name',
                "'$sid',$uid,'$comment',now(),'$title',$pid,$rht2,$rht3,0,'$type','$ipaddress','$name'");
        } else {
            DB_save ($_TABLES['comments'], 'sid,uid,comment,date,title,pid,lft,rht,indent,type,ipaddress',
                "'$sid',$uid,'$comment',now(),'$title',$pid,$rht2,$rht3,0,'$type','$ipaddress'");
        }
    }
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }

    $result = DB_query("SELECT LAST_INSERT_ID()");
    list($last_cid) = DB_fetchArray($result);
    $C['new_cid'] = $last_cid;

    DB_unlockTable($_TABLES['comments']);

    $cid     = (int) $C['new_cid'];
    $date    = addslashes($C['date']);
    $name    = addslashes($C['name']);
    $score   = (int) $C['score'];
    $reason  = (int) $C['reason'];
    DB_query("UPDATE {$_TABLES['comments']} SET "
           . "date='$date', "
           . (!empty($name) ? "name='$name', " : "name=NULL, ")
           . "score=$score, "
           . "reason=$reason "
           . "WHERE cid=$cid");
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }

    return $retval;
}


function DLM_updatePid(&$C, $cid)
{
    foreach ($C as $key => $val)
    {
        if ($C[$key]['pid'] == $cid) {
            $C[$key]['pid'] = $C[$cid]['new_cid'];
        }
    }
}

function DLM_deleteComments()
{
    global $_TABLES, $_LANG_CONV, $_SUCCESS;

    $retval = '';
    $result = DB_query("SELECT cid, sid FROM {$_TABLES['comments']} WHERE type = 'downloads' ORDER BY date ASC");
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    while (list($cid, $sid) = DB_fetchArray($result)) {
        $val = CMT_deleteComment($cid, $sid, 'downloads');
        if ($val > 0) {
            $_SUCCESS = false;
        }
        if (!$_SUCCESS) {
            $retval .= '<p>' . $_LANG_CONV['failure_delete_comment'] . "cid: $cid" . '</p>' . LB;
            return $retval;
        }
    }
    return $retval;
}

function DLM_convertComment()
{
    global $_TABLES, $_LANG_CONV, $_SUCCESS;

    $retval = '';
    $C = array();
    $result = DB_query("SELECT * FROM {$_TABLES['comments']} WHERE type = 'filemgmt' ORDER BY cid ASC");
    if (DB_error()) {
        $retval .= '<p>' . $_LANG_CONV['db_error'] . '</p>' . LB;
        $_SUCCESS = false;
        return $retval;
    }
    while ($A = DB_fetchArray($result)) {
        $C[ $A['cid'] ] = array(
            'cid'       => $A['cid'],
            'sid'       => $A['sid'],
            'date'      => $A['date'],
            'title'     => $A['title'],
            'comment'   => $A['comment'],
            'score'     => $A['score'],
            'reason'    => $A['reason'],
            'pid'       => $A['pid'],
            'name'      => $A['name'],
            'uid'       => $A['uid'],
            'ipaddress' => $A['ipaddress'],
        // ---------------
            'new_cid'   => NULL,
        );
    }
    $retval .= DLM_deleteComments();
    if (!$_SUCCESS) return $retval;
    foreach ($C as $key => $val) {
        $retval .= DLM_saveComment($C[$key]);
        if (!$_SUCCESS) return $retval;
        DLM_updatePid($C, $key);
    }
    return $retval;
}

function DL_convert()
{
    global $_LANG_CONV, $_SUCCESS;

    $retval = DLM_convertData();
    if (!$_SUCCESS) {
        $retval .= '<p style="color:red; font-weight:bold">' . $_LANG_CONV['process_interrupted'] . '</p>' . LB;
        return $retval;
    }
    $retval .= DLM_convertComment();
    if (!$_SUCCESS) {
        $retval .= '<p style="color:red; font-weight:bold">' . $_LANG_CONV['process_interrupted'] . '</p>' . LB;
        return $retval;
    }
    $retval .= '<p style="color:green; font-weight:bold">' . $_LANG_CONV['process_completed'] . '</p>' . LB;
    return $retval;
}


// MAIN

$op = COM_applyFilter($_REQUEST['mode']);
$display = '';
$action = $_CONF['site_admin_url'].'/plugins/downloads/fm2dm.php';
$_SUCCESS = true;

switch ($op) {
    case $_LANG_CONV['submit']:
        $display = COM_siteHeader('menu', $_LANG_CONV['title']);
        $display .= DL_convert();
        $display .= COM_siteFooter();
        break;

    case $_LANG_CONV['cancel']:
        $display = COM_refresh($_CONF['site_url'] . '/index.php');
        break;

    default:
        $display = COM_siteHeader('menu', $_LANG_CONV['title']);
        $display .= '<h1>' . $_LANG_CONV['title'] . '</h1>' . LB;
        $display .= '<p>' . $_LANG_CONV['description1'] . '</p>' . LB;
        $display .= '<p style="color:red; font-weight:bold">' . $_LANG_CONV['description2'] . '</p>' . LB;
        $display .= '<p>' . $_LANG_CONV['description3'] . '</p>' . LB;
        $display .= '<p>' . $_LANG_CONV['description4'] . '</p>' . LB;
        $display .= '<form action="'. $action .'" method="post"><div>' . LB;
        $display .= '<input type="submit" name="mode" value="'. $_LANG_CONV['submit'] .'" class="button">' . LB;
        $display .= '<input type="submit" name="mode" value="'. $_LANG_CONV['cancel'] .'" class="button">' . LB;
        $display .= '</div></form>' . LB;
        $display .= COM_siteFooter();
        break;
}
COM_output($display);
?>
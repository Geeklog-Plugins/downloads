<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/language/japanese_utf-8.php                             |
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

global $LANG_postmodes;

$LANG_DLM = array(
    'nav_addfile'       => 'ファイルの追加',
    'nav_addcategory'   => 'カテゴリの追加',
    'nav_categories'    => 'カテゴリのリスト',
    'nav_files'         => 'ファイルのリスト',
    'warning'           => 'アンインストール前の警告',
    'WhatsNewLabel'     => 'ダウンロード',
    'plugin_name'       => 'ダウンロード',
    'more'              => '続きを読む...',
    'DownloadReport'    => 'ダウンロードの履歴',
    'instructions'      => 'データを修正、削除する場合は各データの「編集」アイコンをクリックしてください。新規作成は「カテゴリの追加」または「ファイルの追加」をクリックしてください。',
    'instructions2'     => 'データを修正、削除する場合は各データの「編集」アイコンをクリックしてください。新規作成は「ファイルの追加」をクリックしてください。',
    'downloads'         => 'ダウンロード',
    'stats_download'    => 'ダウンロードファイル数（ヒット数）',
    'stats_headline'    => 'ダウンロード(上位10件)',
    'stats_no_hits'     => 'このサイトにはダウンロード用ファイルがないか、ダウンロードした人がいないようです。',
    'stats_page_title'  => 'タイトル',
    'stats_hits'        => 'ヒット',
    'search_results'    => 'ダウンロードの検索結果',
    'search_title'      => 'タイトル',
    'search_date'       => '公開日',
    'search_author'     => '提供者',
    'search_hits'       => 'ヒット数',
    'search_description'=> '説明',
    'searchlabel'       => 'ダウンロード',
    'search_downloads'  => 'ダウンロードを検索',
    'search_placeholder'=> 'タイトル、説明、プロジェクト、バージョン、タグ',
    'search'            => '検索',
    'clear'             => 'クリア',
    'search_results_for'=> '「%s」の検索結果: %d 件',
    'searchlabel_results' => 'ファイルリスト結果',
    'user_menu'         => 'ファイルアップロード',
    'my_downloads'      => '自分のダウンロード',
    'my_downloads_help' => '送信したダウンロードと現在の状態を確認できます。',
    'submit_new_download' => 'ダウンロードを送信',
    'submission_state'  => '状態',
    'submission_state_pending' => '承認待ち',
    'submission_state_published' => '公開済み',
    'no_user_submissions' => '送信したダウンロードはまだありません。',
    'approval_failed_pending' => 'ダウンロードを確定できなかったため、承認待ちキューに戻しました。',
    'config_tooltip_download_emailoption' => '承認待ちのダウンロードが承認されたときに送信者へメールを送信します。',
    'config_tooltip_notify_on_submission' => '新しいダウンロードが承認待ちキューに追加されたときに通知します。',
    'config_tooltip_submission_notify_email' => '新規申請通知の送信先メールアドレス。既定値はサイトのメールアドレスです。',
    'config_tooltip_download_uploadselect' => '直接公開権限のないログインユーザーが承認待ちキューへファイルを送信できるようにします。',
    'config_tooltip_path_filestore' => '公開済みおよび承認待ちのダウンロードファイルを保存するディレクトリです。Web サーバーの書き込み権限が必要です。',
    'config_tooltip_path_snapstore' => 'ダウンロードのスナップショット画像を保存するディレクトリです。',
    'config_tooltip_path_tnstore' => '生成されたサムネイルを保存するディレクトリです。',
    'config_tooltip_filepermissions' => '保存後のアップロードファイルに適用する 8 進数のパーミッションです。',
    'submission_state_rejected' => '却下',
    'submission_notification_subject' => '新しいダウンロード申請',
    'submission_notification_intro' => '新しいダウンロードが送信され、承認待ちです。',
    'submission_notification_title' => 'タイトル',
    'submission_notification_submitter' => '送信者',
    'submission_notification_moderate' => '申請を確認',
    'admin_menu'        => 'ダウンロード',
    'no_new_files'      => '-',
    'no_comments'       => '-',
    'main'              => 'トップ',
    'popular'           => '人気!',
    'newthisweek'       => '今週の新規ファイル',
    'datenew'           => '新着順',
    'titleatoz'         => 'タイトル',
    'popularitymtol'    => 'ダウンロード数',
    'ratinghtol'        => '評価',
    'numperpage'        => '表示件数',
    'listingheading'    => 'ファイルリスト:  %s 件',
    'description'       => '説明',
    'detail'            => '詳細',
    'permalink'         => '固定リンク',
    'dlnow'             => 'ダウンロード開始',
    'ver'               => 'バージョン',
    'submitdate'        => '公開日',
    'dltimes'           => 'ダウンロード %s 回',
    'filesize'          => 'ファイルサイズ',
    'size'              => 'サイズ',
    'homepage'          => 'Web',
    'ratingc'           => '評価',
    'numvotes'          => '(%s)',
    'entercomment'      => 'コメント投稿',
    'ratethisfile'      => '評価',
    'modify'            => '編集',
    'edit'              => '編集',
    'addnew'            => '新規追加',
    'file'              => 'ファイル',
    'fileid'            => 'ファイルID',
    'filetitle'         => 'タイトル',
    'numbytes'          => '%s バイト',
    'shotimage'         => 'ファイル画像',
    'addshotimage'      => '新規追加ファイル画像',
    'replshotimage'     => '取替え用ファイル画像',
    'project'           => 'プロジェクト名',
    'projectfilelist'   => 'プロジェクト: %s のファイルリスト',
    'voteonce'          => '同じファイルには1回しか投票できません。',
    'ratingscale'       => '評価基準は 1 (低い)から 10 (高い)までです。',
    'beobjective'       => '客観的にお願いします。全員が 1 か 10 の評価しか受けないなら、評価はあまり役に立ちません。',
    'donotvote'         => '自分自身が提供したファイルには投票できません。',
    'rateit'            => '投票',
    'approved'          => 'あなたのファイルは承認されました。',
    'category'          => 'カテゴリ',
    'catid'             => 'カテゴリID',
    'rating'            => '評価',
    'sortby'            => 'ソート',
    'title'             => 'タイトル',
    'date'              => '公開日',
    'submit'            => '実行',
    'go'                => '表示',
    'cancel'            => 'キャンセル',
    'bytes'             => 'Bytes',
    'norating'          => '評価がなされていません。',
    'cantvoteown'       => '自分自身が提供したファイルには投票できません。<br' . XHTML . '>投票は全て記録され、検討されています。',
    'deny_msg'          => 'このファイルへアクセスできません。(このファイルは移動したか削除されているか、あるいはアクセス権がありません。)',
    'ratefiletitle'     => 'ファイルへの評価を投票してください。',
    'admintitle'        => 'ダウンロード管理',
    'uploadtitle'       => 'ダウンロード - ファイルのアップロード',
    'categorytitle'     => 'リスト - カテゴリ',
    'generalset'        => 'ダウンロードの設定',
    'addcategorysnap'   => '画像: <small>(オプション、トップレベルカテゴリのみ)</small>',
    'addimagenote'      => '(画像の高さ制限 50)',
    'dlswaiting'        => 'ダウンロード承認待ち',
    'submitter'         => '提供者',
    'download'          => 'ダウンロード',
    'filelink'          => '詳細',
    'approve'           => '承認',
    'delete'            => '削除',
    'nosubmitted'       => '新たに提供されたダウンロードファイルはありません',
    'addmain'           => '主カテゴリ追加',
    'add'               => '追加',
    'addsub'            => 'サブカテゴリ追加',
    'in'                => 'in',
    'addnewfile'        => 'ダウンロード情報の新規作成',
    'modcat'            => 'カテゴリの編集',
    'addcat'            => 'カテゴリの追加',
    'moddl'             => 'ダウンロード情報の編集',
    'user'              => 'ユーザ',
    'ip'                => 'IPアドレス',
    'useravg'           => 'ユーザ評価の平均',
    'totalrate'         => '全評価',
    'noregvotes'        => '登録ユーザによる投票はありません',
    'nounregvotes'      => 'ゲストユーザによる投票はありません',
    'nobroken'          => '破損ファイルはありません。',
    'nomodreq'          => 'ダウンロード修正要求はありません。',
    'modreqdeleted'     => '修正要求は削除されました。',
    'imgurlmain'        => 'カテゴリ画像',
    'parent'            => '上位カテゴリ',
    'save'              => '保存',
    'yes'               => 'はい',
    'no'                => 'いいえ',
    'configupdated'     => '設定が保存されました。',
    'errornofile'       => 'エラー: ファイルがデータベースの記録にありません。',
    'hello'             => 'こんにちは、%s さん',
    'weapproved'        => '提供していただいたダウンロードファイルは承認されました。ファイル名: ',
    'thankssubmit'      => 'ご提供ありがとうございました。',
    'uploadapproved'    => 'あなたがアップロードしたファイルは承認されました。',
    'nofiles'           => 'ファイルがありません。',
    'toolbar'           => $LANG24[70],
    'toolbar1'          => $LANG24[71],
    'toolbar2'          => $LANG24[72],
    'toolbar3'          => $LANG24[73],
    'toolbar4'          => $LANG24[74],
    'toolbar5'          => $LANG24[75],
    'dlfilename'        => 'ファイル名',
    'replfilename'      => '取替え用ファイル',
    'addfilename'       => '新規追加ファイル',
    'commentswanted'    => 'コメント歓迎',
    'click2see'         => 'クリックで拡大表示',
    'click2dl'          => 'クリックでダウンロード開始',
    'confirm_delete'    => 'このファイルを削除しますか ？',
    'goback'            => '戻る',
    'topic'             => '話題',
    'all'               => 'すべて',
    'download_submissions' => 'ダウンロードの登録申請',
    'pending_submissions' => '承認待ちの申請',
    'status'             => '状態',
    'status_published'   => '公開済み',
    'status_unreleased'  => '未公開',
    'status_hidden'      => '一覧非表示',
    'file_health'        => 'ファイル',
    'file_health_ok'     => '正常',
    'file_health_missing'=> '見つかりません',
    'storage_warning'    => 'Downloads のファイル保存先が存在しないか、書き込みできません: %s',
    'released'          => '公開',
    'listing'           => 'ファイルリストに掲載',
    'postmode'          => '投稿モード',
    'release_date'      => '公開日',
    'comment_mode'      => 'コメントモード',
    'votedate'          => '日付',
    'reguservotes'      => '登録ユーザによる投票 (投票総数: %s)',
    'anonuservotes'     => 'ゲストユーザによる投票 (投票総数: %s)',
    'unknown_uid'       => 'ゲストユーザ',
    'manager'           => 'ダウンロード管理',
    'tempfile'          => '仮登録ファイル',
    'md5'               => 'MD5値',
    'md5_checksum'      => 'MD5',
    'is_enabled'        => '有効',
    'corder'            => '順序',
    'move_down'         => 'カテゴリを下へ',
    'move_up'           => 'カテゴリを上へ',
    'popularity'        => 'ダウンロード数',
    'mg_autotag'        => 'MG 自動タグ',
    'mg_autotag_info'   => 'メディアギャラリーの自動タグ',
    'language'          => '言語',
    'upload'            => 'アップロード',
    'download_button'   => 'ダウンロード',
    'tags'              => 'タグ',
    'tags_help'         => 'キーワードを空白で区切って入力してください。短く再利用しやすい語を使用し、カンマや特殊な句読点は避けてください。',
    'project_help'      => '既存のプロジェクト名から候補を表示します。新規プロジェクトでは、編集するまでタイトルから自動的に設定されます。',
    'meta_description'  => 'メタディスクリプション',
    'meta_keywords'     => 'メタキーワード',
    'required_field'    => '必須項目',
    'section_general'   => '一般',
    'section_content_seo' => 'コンテンツとSEO',
    'section_media'     => 'メディア',
    'section_publication' => '公開設定',
    'please_update'     => 'ダウンロードプラグインをアップデートしてください。',
    'preview'           => 'プレビュー',
    'commentoption'     => 'コメントオプション',
	'num_downloads' 		=> '%s Download(s)',

    '1001' => 'Upload approval error: Temporary file does not exist. Check error.log',
    '1002' => 'Upload approval error: New file does not exist after move of tmp file. Check error.log',
    '1003' => 'Upload add error: Temporary file does not exist. Check error.log',
    '1004' => 'Upload add error: Could not move an uploaded file. Check error.log',
    '1101' => '「タイトル」を入力してください。',
    '1102' => '「ファイル名」を入力してください。',
    '1103' => '「説明」を入力してください。',
    '1201' => '「ファイルID」を入力してください。',
    '1202' => '「ファイルID」の入力値は既に存在します。変更してください。',
    '1203' => '「ファイルID」の入力値が不適正です。変更してください。',
    '1301' => '「カテゴリID」を入力してください。',
    '1302' => '「カテゴリID」の入力値は既に存在します。「カテゴリID」を変更してください。',
    '1303' => '「カテゴリID」の入力値が不適正です。「カテゴリID」を変更してください。',
    '1401' => 'アップロードファイルのパラメータが不正です。',
    '1402' => 'アップロードファイルが選択されていません。',
    '1403' => 'アップロードファイルが大きすぎます。',
    '1404' => 'アップロードに失敗しました。',
    '1405' => '選択された画像は有効な JPEG、PNG、GIF ファイルではありません。',
    '9999' => 'OOPS! God Knows',
);


// $PLG_downloads_MESSAGE1 = 'ダウンロードプラグインのインストールを中断しました。<br' . XHTML . '>ファイル: plugins/downloads/download.php が書き込み可になっていません。';
$PLG_downloads_MESSAGE3 = 'このプラグインにはGeeklog Version 1.5 以降が必要です。アップグレードを中断しました。';
$PLG_downloads_MESSAGE4 = 'このプラグインの version 1.5 用のコードを検出できません。アップグレードを中断しました。';
$PLG_downloads_MESSAGE5 = 'ダウンロードプラグインのアップグレードを中断しました。<br' . XHTML . '>現在のプラグインのバージョンは 1.3 ではありません。';
$PLG_downloads_MESSAGE6 = 'あなたには、このカテゴリを編集する十分な権利がありません。';
$PLG_downloads_MESSAGE7 = 'あなたには、このダウンロード情報を編集する十分な権利がありません。';

// Messages for the plugin upgrade
$PLG_downloads_MESSAGE3001 = 'プラグインのアップグレードはサポートされていません。';
$PLG_downloads_MESSAGE3002 = $LANG32[9];

$PLG_downloads_MESSAGE101 = 'データベースは更新されました。';
$PLG_downloads_MESSAGE102 = 'ダウンロード情報が新たにデータベースに追加されました。';
$PLG_downloads_MESSAGE103 = '注意: ファイルが重複しています。<br' . XHTML . '>ダウンロード情報が新たにデータベースに追加されました。';
$PLG_downloads_MESSAGE104 = '注意: ファイル画像が重複しています。<br' . XHTML . '>ダウンロード情報が新たにデータベースに追加されました。';
$PLG_downloads_MESSAGE105 = 'ダウンロード情報は削除されました。';
$PLG_downloads_MESSAGE106 = 'カテゴリが新たに追加されました。';
$PLG_downloads_MESSAGE107 = 'カテゴリが削除されました。';
$PLG_downloads_MESSAGE108 = 'アップロードできませんでした。リポジトリのパーミッションを確認してください。';
$PLG_downloads_MESSAGE109 = 'ダウンロード情報を受け取りました。ありがとうございます。<br' . XHTML . '>承認後にメールをお送りします。';
$PLG_downloads_MESSAGE110 = '検索条件に一致するものはありません。';
$PLG_downloads_MESSAGE111 = '投票データが削除されました。';
$PLG_downloads_MESSAGE112 = '記録は削除されましたが、ファイルは削除されませんでした。<br' . XHTML . '>複数のダウンロード情報が同じファイルを指しています。'; //'filenotdeleted'
$PLG_downloads_MESSAGE113 = '投票していただき、ありがとうございました。';
$PLG_downloads_MESSAGE114 = 'あなたには、ダウンロード履歴を見るための十分なアクセス権がありません。';
$PLG_downloads_MESSAGE115 = 'ダウンロード情報を受け取りました。ありがとうございます。';


// Localization of the Admin Configuration UI
$LANG_configsections['downloads'] = array(
    'label' => 'ダウンロード',
    'title' => 'ダウンロードの設定'
);

$LANG_confignames['downloads'] = array(
    'loginrequired'            => 'ログインを要求する',
    'hidemenu'                 => 'メニューに表示しない',
    'delete_download'          => '所有者の削除と共に削除する',
    'default_permissions'      => 'パーミッション',
    'download_perpage'         => '1ページあたりのファイル表示件数',
    'download_popular'         => '人気を判断するヒット数のしきい値',
    'download_newdownloads'    => '新着ファイルの表示件数',
    'download_dlreport'        => 'ダウンロード履歴の閲覧許可',
    'download_whatsnew'        => '新着ファイルを表示する',
    'download_uploadselect'    => '登録ユーザのアップロードを許可する',
    'download_useshots'        => 'カテゴリ画像を表示する',
    'download_shotwidth'       => 'カテゴリ画像の幅',
    'download_emailoption'     => 'ファイル承認をメールで通知する',
    'path_filestore'           => 'ファイル',
    'path_snapstore'           => 'ファイル画像',
    'path_snapcat'             => 'カテゴリ画像',
    'path_tnstore'             => 'サムネール画像',
    'snapstore_url'            => 'ファイル画像',
    'snapcat_url'              => 'カテゴリ画像',
    'tnstore_url'              => 'サムネール画像',
    'show_tn_image'            => 'サムネール画像を表示する',
    'show_tn_only_exists'      => '存在する画像のみ表示する',
    'max_tnimage_width'        => '最大幅',
    'max_tnimage_height'       => '最大高',
    'tnimage_format'           => 'ファイル形式',
    'enabled_mg_autotag'       => 'MG自動タグを有効にする',
    'filepermissions'          => 'ファイルのパーミッション',
    'whatsnew_perioddays'      => '新着ファイルと見なす日数',
    'postmode'                 => 'デフォルトの投稿モード',
    'cut_own_download'         => '所有者自らのダウンロードを無視',
);

$LANG_configsubgroups['downloads'] = array(
    'sg_main'           => 'メイン',
    'sg_Miscellaneous'  => 'その他'
);

$LANG_tab['downloads'] = array(
    'tab_main'           => 'ダウンロードのメイン設定',
    'tab_tnimage'        => 'サムネール画像',
    'tab_category'       => 'カテゴリ',
    'tab_history'        => 'ダウンロード履歴',
    'tab_whatsnew_block' => '新着情報ブロック',
    'tab_path'           => 'リポジトリのパス',
    'tab_url'            => 'リポジトリのURL',
    'tab_permissions'    => 'カテゴリのパーミッション'
);

$LANG_fs['downloads'] = array(
    'fs_main'           => 'ダウンロードのメイン設定',
    'fs_tnimage'        => 'サムネール画像',
    'fs_category'       => 'カテゴリ',
    'fs_history'        => 'ダウンロード履歴',
    'fs_whatsnew_block' => '新着情報ブロック',
    'fs_path'           => 'リポジトリのパス',
    'fs_url'            => 'リポジトリのURL',
    'fs_permissions'    => 'カテゴリのデフォルトパーミッション（[0]所有者 [1]グループ [2]メンバー [3]ゲスト）'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['downloads'] = array(
    0 => array('はい' => 1, 'いいえ' => 0),
    1 => array('はい' => TRUE, 'いいえ' => FALSE),
    2 => array('登録順' => 'submitorder', '得票順' => 'voteorder'),
    //5 => array_flip($LANG_postmodes), // This doesn't work when upgrading Geeklog (error happened when upgrading to v2.2.0) so use hard coded variables below
    5 => array('テキスト' => 'plaintext', 'HTML' => 'html'),
    9 => array('作成したファイルを表示する' => 'item', 'ダウンロード管理を表示する' => 'list', 'ファイル一覧を表示する' => 'plugin', 'Homeを表示する' => 'home', '管理画面TOPを表示する' => 'admin'),
    12 => array('アクセス不可' => 0, '表示' => 2, '表示・編集' => 3),
    20 => array('全てのユーザ' => 'all', '登録ユーザまで' => 'user', 'ダウンロード編集者まで' => 'editor'),
    30 => array('PNG' => 'png', 'JPEG' => 'jpg')
);

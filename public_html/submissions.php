<?php

require_once '../lib-common.php';

if (!in_array('downloads', $_PLUGINS)) {
    COM_redirect($_CONF['site_url'] . '/index.php');
}

if (COM_isAnonUser()) {
    $display = SEC_loginRequiredForm();
    $display = COM_createHTMLDocument($display);
    COM_output($display);
    exit;
}

require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';
require_once $_CONF['path_system'] . 'lib-admin.php';

$uid = (int) $_USER['uid'];
$rows = array();

$sql = "SELECT lid, cid, title, date, 'pending' AS state "
     . "FROM {$_TABLES['downloadsubmission']} "
     . "WHERE owner_id = $uid "
     . "UNION ALL "
     . "SELECT lid, cid, title, date, 'published' AS state "
     . "FROM {$_TABLES['downloads']} "
     . "WHERE owner_id = $uid "
     . "ORDER BY date DESC";
$result = DB_query($sql);

while ($A = DB_fetchArray($result)) {
    $state = $A['state'];
    $title = DLM_htmlspecialchars($A['title']);

    if ($state === 'published') {
        $title = COM_createLink(
            $title,
            COM_buildURL($_CONF['site_url'] . '/downloads/index.php?id=' . rawurlencode($A['lid']))
        );
        $state_label = $LANG_DLM['submission_state_published'];
    } else {
        $state_label = $LANG_DLM['submission_state_pending'];
    }

    $category = DB_getItem(
        $_TABLES['downloadcategories'],
        'title',
        "cid='" . DB_escapeString($A['cid']) . "'"
    );

    $rows[] = array(
        'title' => $title,
        'category' => DLM_htmlspecialchars($category),
        'date' => strftime($_DLM_CONF['date_format'], (int) $A['date']),
        'state' => $state_label
    );
}

$header_arr = array(
    array('text' => $LANG_DLM['title'], 'field' => 'title'),
    array('text' => $LANG_DLM['category'], 'field' => 'category'),
    array('text' => $LANG_DLM['submitdate'], 'field' => 'date'),
    array('text' => $LANG_DLM['submission_state'], 'field' => 'state')
);

$text_arr = array(
    'has_menu' => true,
    'title' => $LANG_DLM['my_downloads'],
    'help_url' => '',
    'form_url' => ''
);

$menu_arr = array(
    array(
        'url' => $_CONF['site_url'] . '/submit.php?type=downloads',
        'text' => $LANG_DLM['submit_new_download']
    )
);

$content = ADMIN_createMenu($menu_arr, $LANG_DLM['my_downloads_help'], plugin_geticon_downloads());

if (!empty($rows)) {
    $content .= ADMIN_simpleList('', $header_arr, $text_arr, $rows);
} else {
    $content .= COM_showMessageText($LANG_DLM['no_user_submissions']);
}

$display = COM_createHTMLDocument($content, array('pagetitle' => $LANG_DLM['my_downloads']));
COM_output($display);

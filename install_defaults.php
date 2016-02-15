<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/install_defaults.php                                    |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'install_defaults.php') !== false) {
    die('This file can not be used on its own!');
}

/*
 * Downloads default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records. These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 *
 */

global $_DLM_DEFAULT;

$_DLM_DEFAULT['loginrequired']         = 0;
$_DLM_DEFAULT['hidemenu']              = 0;
$_DLM_DEFAULT['delete_download']       = 0;
$_DLM_DEFAULT['download_perpage']      = 5;
$_DLM_DEFAULT['download_popular']      = 20;
$_DLM_DEFAULT['download_uploadselect'] = 0;
$_DLM_DEFAULT['download_emailoption']  = 1;
$_DLM_DEFAULT['filepermissions']       = 755;
$_DLM_DEFAULT['postmode']              = 'plaintext';
$_DLM_DEFAULT['enabled_mg_autotag']    = 0;
$_DLM_DEFAULT['show_tn_image']         = 1;
$_DLM_DEFAULT['show_tn_only_exists']   = 0;
$_DLM_DEFAULT['max_tnimage_width']     = 200;
$_DLM_DEFAULT['max_tnimage_height']    = 200;
$_DLM_DEFAULT['tnimage_format']        = 'png';
$_DLM_DEFAULT['download_useshots']     = 1;
$_DLM_DEFAULT['download_shotwidth']    = 32;
$_DLM_DEFAULT['download_whatsnew']     = 1;
$_DLM_DEFAULT['download_newdownloads'] = 10;
$_DLM_DEFAULT['whatsnew_perioddays']   = 14;
$_DLM_DEFAULT['download_dlreport']     = 'editor';
$_DLM_DEFAULT['cut_own_download']      = 1;
$_DLM_DEFAULT['path_filestore']        = $_CONF['path_html'] . 'downloads_data/files/';
$_DLM_DEFAULT['path_snapstore']        = $_CONF['path_html'] . 'downloads_data/snaps/';
$_DLM_DEFAULT['path_snapcat']          = $_CONF['path_html'] . 'downloads_data/category_snaps/';
$_DLM_DEFAULT['path_tnstore']          = $_CONF['path_html'] . 'downloads_data/tn/';
$_DLM_DEFAULT['snapstore_url']         = $_CONF['site_url'] . '/downloads_data/snaps';
$_DLM_DEFAULT['snapcat_url']           = $_CONF['site_url'] . '/downloads_data/category_snaps';
$_DLM_DEFAULT['tnstore_url']           = $_CONF['site_url'] . '/downloads_data/tn';
$_DLM_DEFAULT['default_permissions']   = array (3, 2, 2, 2);

/**
* Initialize Downloads Plugin configuration
*
* Creates the database entries for the configuation if they don't already
* exist. Initial values will be taken from $_DLM_CONF if available (e.g. from
* an old config.php), uses $_DLM_DEFAULT otherwise.
*
* @return   boolean     true: success; false: an error occurred
*
*/
function plugin_initconfig_downloads()
{
    global $_CONF, $_DLM_CONF, $_DLM_DEFAULT;

    if (is_array($_DLM_CONF) && (count($_DLM_CONF) > 1)) {
        $_DLM_DEFAULT = array_merge($_DLM_DEFAULT, $_DLM_CONF);
    }

    $c = config::get_instance();
    $n = 'downloads';
    $o = 1;
    if ($c->group_exists($n)) return true;
    $c->add('sg_main',               NULL,                                   'subgroup', 0, 0, NULL, 0,    true, $n);
    // ----------------------------------
    $c->add('fs_main',               NULL,                                   'fieldset', 0, 0, NULL, 0,    true, $n);
    $c->add('loginrequired',         $_DLM_DEFAULT['loginrequired'],         'select',   0, 0, 0,    $o++, true, $n);
    $c->add('hidemenu',              $_DLM_DEFAULT['hidemenu'],              'select',   0, 0, 0,    $o++, true, $n);
    $c->add('delete_download',       $_DLM_DEFAULT['delete_download'],       'select',   0, 0, 0,    $o++, true, $n);
    $c->add('download_perpage',      $_DLM_DEFAULT['download_perpage'],      'text',     0, 0, 0,    $o++, true, $n);
    $c->add('download_popular',      $_DLM_DEFAULT['download_popular'],      'text',     0, 0, 0,    $o++, true, $n);
    $c->add('download_uploadselect', $_DLM_DEFAULT['download_uploadselect'], 'select',   0, 0, 0,    $o++, true, $n);
    $c->add('download_emailoption',  $_DLM_DEFAULT['download_emailoption'],  'select',   0, 0, 0,    $o++, true, $n);
    $c->add('filepermissions',       $_DLM_DEFAULT['filepermissions'],       'text',     0, 0, 0,    $o++, true, $n);
    $c->add('postmode',              $_DLM_DEFAULT['postmode'],              'select',   0, 0, 5,    $o++, true, $n);
    $c->add('enabled_mg_autotag',    $_DLM_DEFAULT['enabled_mg_autotag'],    'select',   0, 0, 0,    $o++, true, $n);
    // ----------------------------------
    $c->add('fs_tnimage',            NULL,                                   'fieldset', 0, 1, NULL, 0,    true, $n);
    $c->add('show_tn_image',         $_DLM_DEFAULT['show_tn_image'],         'select',   0, 1, 0,    $o++, true, $n);
    $c->add('show_tn_only_exists',   $_DLM_DEFAULT['show_tn_only_exists'],   'select',   0, 1, 0,    $o++, true, $n);
    $c->add('max_tnimage_width',     $_DLM_DEFAULT['max_tnimage_width'],     'text',     0, 1, 0,    $o++, true, $n);
    $c->add('max_tnimage_height',    $_DLM_DEFAULT['max_tnimage_height'],    'text',     0, 1, 0,    $o++, true, $n);
    $c->add('tnimage_format',        $_DLM_DEFAULT['tnimage_format'],        'select',   0, 1, 30,   $o++, true, $n);
    // ----------------------------------
    $c->add('fs_category',           NULL,                                   'fieldset', 0, 2, NULL, 0,    true, $n);
    $c->add('download_useshots',     $_DLM_DEFAULT['download_useshots'],     'select',   0, 2, 0,    $o++, true, $n);
    $c->add('download_shotwidth',    $_DLM_DEFAULT['download_shotwidth'],    'text',     0, 2, 0,    $o++, true, $n);
    // ----------------------------------
    $c->add('fs_whatsnew_block',     NULL,                                   'fieldset', 0, 3, NULL, 0,    true, $n);
    $c->add('download_whatsnew',     $_DLM_DEFAULT['download_whatsnew'],     'select',   0, 3, 0,    $o++, true, $n);
    $c->add('download_newdownloads', $_DLM_DEFAULT['download_newdownloads'], 'text',     0, 3, 0,    $o++, true, $n);
    $c->add('whatsnew_perioddays',   $_DLM_DEFAULT['whatsnew_perioddays'],   'text',     0, 3, 0,    $o++, true, $n);
    // ----------------------------------
    $c->add('fs_history',            NULL,                                   'fieldset', 0, 4, NULL, 0,    true, $n);
    $c->add('download_dlreport',     $_DLM_DEFAULT['download_dlreport'],     'select',   0, 4, 20,   $o++, true, $n);
    $c->add('cut_own_download',      $_DLM_DEFAULT['cut_own_download'],      'select',   0, 4, 0,    $o++, true, $n);


    $c->add('sg_Miscellaneous',      NULL,                                   'subgroup', 1, 0, NULL, 0,    true, $n);
    // ----------------------------------
    $c->add('fs_path',               NULL,                                   'fieldset', 1, 5, NULL, 0,    true, $n);
    $c->add('path_filestore',        $_DLM_DEFAULT['path_filestore'],        'text',     1, 5, 0,    $o++, true, $n);
    $c->add('path_snapstore',        $_DLM_DEFAULT['path_snapstore'],        'text',     1, 5, 0,    $o++, true, $n);
    $c->add('path_snapcat',          $_DLM_DEFAULT['path_snapcat'],          'text',     1, 5, 0,    $o++, true, $n);
    $c->add('path_tnstore',          $_DLM_DEFAULT['path_tnstore'],          'text',     1, 5, 0,    $o++, true, $n);
    // ----------------------------------
    $c->add('fs_url',                NULL,                                   'fieldset', 1, 6, NULL, 0,    true, $n);
    $c->add('snapstore_url',         $_DLM_DEFAULT['snapstore_url'],         'text',     1, 6, 0,    $o++, true, $n);
    $c->add('snapcat_url',           $_DLM_DEFAULT['snapcat_url'],           'text',     1, 6, 0,    $o++, true, $n);
    $c->add('tnstore_url',           $_DLM_DEFAULT['tnstore_url'],           'text',     1, 6, 0,    $o++, true, $n);
    // ----------------------------------
    $c->add('fs_permissions',        NULL,                                   'fieldset', 1, 7, NULL, 0,    true, $n);
    $c->add('default_permissions',   $_DLM_DEFAULT['default_permissions'],   '@select',  1, 7, 12,   $o++, true, $n);

    if (function_exists('COM_versionCompare')) {
        DLM_update_ConfValues_addTabs();
    }

    return true;
}

function DLM_updateSortOrder()
{
    global $_TABLES;

    $conf_vals = array(
        'loginrequired',
        'hidemenu',
        'delete_download',
        'download_perpage',
        'download_popular',
        'download_uploadselect',
        'download_emailoption',
        'filepermissions',
        'postmode',
        'enabled_mg_autotag',
        'show_tn_image',
        'show_tn_only_exists',
        'max_tnimage_width',
        'max_tnimage_height',
        'tnimage_format',
        'download_useshots',
        'download_shotwidth',
        'download_whatsnew',
        'download_newdownloads',
        'whatsnew_perioddays',
        'download_dlreport',
        'cut_own_download',
        'path_filestore',
        'path_snapstore',
        'path_snapcat',
        'path_tnstore',
        'snapstore_url',
        'snapcat_url',
        'tnstore_url',
        'default_permissions',
    );
    $o = 1;
    foreach ($conf_vals as $val) {
        $sql = "UPDATE {$_TABLES['conf_values']} "
             . "SET sort_order = $o "
             . "WHERE name = '$val' AND group_name = 'downloads'";
        DB_query($sql);
        $o++;
    }
}

function DLM_update_ConfValues_addTabs()
{
    global $_TABLES;

    // Add in all the Tabs for the configuration UI
    $c = config::get_instance();
    $n = 'downloads';
    $c->add('tab_main',           NULL, 'tab', 0, 0, NULL, 0, true, $n, 0);
    $c->add('tab_tnimage',        NULL, 'tab', 0, 1, NULL, 0, true, $n, 1);
    $c->add('tab_category',       NULL, 'tab', 0, 2, NULL, 0, true, $n, 2);
    $c->add('tab_whatsnew_block', NULL, 'tab', 0, 3, NULL, 0, true, $n, 3);
    $c->add('tab_history',        NULL, 'tab', 0, 4, NULL, 0, true, $n, 4);
    $c->add('tab_path',           NULL, 'tab', 1, 5, NULL, 0, true, $n, 5);
    $c->add('tab_url',            NULL, 'tab', 1, 6, NULL, 0, true, $n, 6);
    $c->add('tab_permissions',    NULL, 'tab', 1, 7, NULL, 0, true, $n, 7);

    DB_query("UPDATE {$_TABLES['conf_values']} SET tab = fieldset WHERE group_name = '$n'");

    return true;
}
?>
<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/autoinstall.inc                                         |
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

/**
* Autoinstall API functions for the Downloads plugin
*
* @package Downloads
*/

/**
* Plugin autoinstall function
*
* @param    string  $pi_name    Plugin name
* @return   array               Plugin information
*
*/
function plugin_autoinstall_downloads($pi_name)
{
    global $_CONF;

    $pi_name         = 'downloads';
    $pi_display_name = 'Downloads';
    $pi_admin        = $pi_display_name . ' Admin';

    $info = array(
        'pi_name'         => $pi_name,
        'pi_display_name' => $pi_display_name,
        'pi_version'      => '1.2.0',
        'pi_gl_version'   => '1.8.0',
        'pi_homepage'     => 'http://www.trybase.com/~dengen/log/'
    );

    if ($_CONF['language'] == 'japanese_utf-8') { // Japanese
        $groups = array(
            $pi_admin => 'ダウンロードプラグイン管理者'
        );
    } else { // English (default)
        $groups = array(
            $pi_admin => 'Has full access to ' . $pi_display_name . ' features'
        );
    }

    if ($_CONF['language'] == 'japanese_utf-8') { // Japanese
        $features = array(
            $pi_name . '.edit'      => 'ダウンロードを編集する権限',
            $pi_name . '.upload'    => 'ダウンロードを投稿する権限',
            $pi_name . '.moderate'  => '承認待ちのダウンロードを承認・却下する権限',
            $pi_name . '.submit'    => '承認待ちなしでダウンロードを掲載する権限'
        );
    } else { // English (default)
        $features = array(
            $pi_name . '.edit'      => 'Access to downloads editor',
            $pi_name . '.upload'    => 'Downloads file upload rights',
            $pi_name . '.moderate'  => 'Ability to moderate pending downloads',
            $pi_name . '.submit'    => 'May skip the downloads submission queue'
        );
    }

    $mappings = array(
        $pi_name . '.edit'      => array($pi_admin),
        $pi_name . '.upload'    => array($pi_admin),
        $pi_name . '.moderate'  => array($pi_admin),
        $pi_name . '.submit'    => array($pi_admin)
    );

    $tables = array(
        'downloadcategories',
        'downloads',
        'downloadvotes',
        'downloadhistories',
        'downloadsubmission'
    );

    $inst_parms = array(
        'info'      => $info,
        'groups'    => $groups,
        'features'  => $features,
        'mappings'  => $mappings,
        'tables'    => $tables
    );

    return $inst_parms;
}

/**
* Load plugin configuration from database
*
* @param    string  $pi_name    Plugin name
* @return   boolean             true on success, otherwise false
* @see      plugin_initconfig_downloads
*
*/
function plugin_load_configuration_downloads($pi_name)
{
    global $_CONF;

    $base_path = $_CONF['path'] . 'plugins/' . $pi_name . '/';

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $base_path . 'install_defaults.php';

    return plugin_initconfig_downloads();
}

/**
* Plugin postinstall
*
* We're inserting our default data here since it depends on other stuff that
* has to happen first ...
*
* @return   boolean     true = proceed with install, false = an error occured
*
*/
function plugin_postinstall_downloads($pi_name)
{
    return true;
}

/**
* Check if the plugin is compatible with this Geeklog version
*
* @param    string  $pi_name    Plugin name
* @return   boolean             true: plugin compatible; false: not compatible
*
*/
function plugin_compatible_with_this_version_downloads($pi_name)
{
    global $_CONF, $_DB_dbms;

    // check if we support the DBMS the site is running on
    $dbFile = $_CONF['path'] . 'plugins/' . $pi_name . '/sql/'
            . $_DB_dbms . '_install.php';
    if (!file_exists($dbFile)) {
        return false;
    }

    if (function_exists('COM_printUpcomingEvents')) {
        // if this function exists, then someone's trying to install the
        // plugin on Geeklog 1.4.0 or older - sorry, but that won't work
        return false;
    }

    if (!function_exists('MBYTE_strpos')) {
        // the plugin requires the multi-byte functions
        return false; 
    }

    if (!function_exists('SEC_createToken')) {
        return false;
    }

    if (!function_exists('COM_showMessageText')) {
        return false;
    }

    if (!function_exists('SEC_getTokenExpiryNotice')) {
        return false;
    }

    if (!function_exists('SEC_loginRequiredForm')) {
        return false;
    }

    if (!function_exists('COM_newTemplate')) {
        // the plugin requires Geeklog 1.8.0 or older
        return false;
    }

    return true;
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
function DLM_autouninstall()
{
    return array (
        // give the name of the tables, without $_TABLES[]
        'tables' => array('downloadcategories', 'downloads',
                          'downloadvotes', 'downloadhistories', 'downloadsubmission'),
        // give the full name of the group, as in the db
        'groups' => array('Downloads Admin'),
        // give the full name of the feature, as in the db
        'features' => array('downloads.edit', 'downloads.upload',
                            'downloads.moderate', 'downloads.submit'),
        // give the full name of the block, including 'phpblock_', etc
        'php_blocks' => array('phpblock_NewDownloads'),
        // give all vars with their name
        'vars'=> array()
    );
}

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function DLM_upgrade()
{
    global $_CONF, $_TABLES, $_DB_dbms;

    $pi_name = 'downloads';
    $installed_version = DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name = '$pi_name'");
    $func = "plugin_chkVersion_$pi_name";
    $code_version = $func();
    if ($installed_version == $code_version) return true;
    $func = "plugin_compatible_with_this_version_$pi_name";
    if (!$func($pi_name)) return 3002;

    if (version_compare($installed_version, '1.1.0') < 0) {
        require_once $_CONF['path'] . 'plugins/downloads/install_defaults.php';
        require_once $_CONF['path_system'] . 'classes/config.class.php';
        if (function_exists('COM_versionCompare')) {
            DLM_update_ConfValues_addTabs();
        }
        DLM_updateSortOrder();
    }

    $func = "plugin_autoinstall_$pi_name";
    $inst_parms = $func($pi_name);
    $pi_gl_version = $inst_parms['info']['pi_gl_version'];

    require_once $_CONF['path'] . 'plugins/downloads/sql/' . $_DB_dbms . '_updates.php';

    $current_version = $installed_version;
    if (version_compare($current_version, '1.1.0') < 0) {
        $current_version = '1.1.0';
    }
    $done = false;
    while (!$done) {
        switch ($current_version) {
        case '1.1.0':
            if (isset($_UPDATES[$current_version])) {
                $_SQL = $_UPDATES[$current_version];
                foreach ($_SQL as $sql) {
                    DB_query($sql);
                }
            }
            $current_version = '1.2.0';
            break;
        default:
            $done = true;
            break;
        }
    }

    // Update the version numbers
    DB_query("UPDATE {$_TABLES['plugins']} "
           . "SET pi_version = '$code_version', pi_gl_version = '$pi_gl_version' "
           . "WHERE pi_name = '$pi_name'");

    COM_errorLog(ucfirst($pi_name)
        . " plugin was successfully updated to version $code_version.");

    return true;
}

?>

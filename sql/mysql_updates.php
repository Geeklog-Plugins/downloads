<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/sql/mysql_updates.php                                   |
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
* MySQL updates
*
* @package Downloads
*/

$_UPDATES = array(

    '1.1.0' => array(
        "ALTER TABLE {$_TABLES['downloads']} ADD text_version tinyint(2) unsigned NOT NULL default '1' AFTER detail",
        "ALTER TABLE {$_TABLES['downloadsubmission']} ADD text_version tinyint(2) unsigned NOT NULL default '1' AFTER detail"
    ),

    '1.2.3.1' => array(
        "ALTER TABLE {$_TABLES['downloadcategories']} ADD meta_description varchar(320) NOT NULL default '' AFTER title",
        "ALTER TABLE {$_TABLES['downloadcategories']} ADD meta_keywords varchar(255) NOT NULL default '' AFTER meta_description",
        "ALTER TABLE {$_TABLES['downloads']} MODIFY project varchar(150) NOT NULL default ''",
        "ALTER TABLE {$_TABLES['downloads']} ADD meta_description varchar(320) NOT NULL default '' AFTER project",
        "ALTER TABLE {$_TABLES['downloads']} ADD meta_keywords varchar(255) NOT NULL default '' AFTER meta_description",
        "ALTER TABLE {$_TABLES['downloadsubmission']} MODIFY project varchar(150) NOT NULL default ''",
        "ALTER TABLE {$_TABLES['downloadsubmission']} ADD meta_description varchar(320) NOT NULL default '' AFTER project",
        "ALTER TABLE {$_TABLES['downloadsubmission']} ADD meta_keywords varchar(255) NOT NULL default '' AFTER meta_description"
    )
);

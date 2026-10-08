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
        "ALTER TABLE {$_TABLES['downloadsubmission']} ADD meta_keywords varchar(255) NOT NULL default '' AFTER meta_description",
        "CREATE TABLE {$_TABLES['downloadsubmissionhistory']} (
          history_id int(11) unsigned NOT NULL auto_increment,
          lid varchar(40) NOT NULL default '',
          owner_id mediumint(8) unsigned NOT NULL default '1',
          cid varchar(40) NOT NULL default '',
          title varchar(100) NOT NULL default '',
          submitted_date int(10) NOT NULL default '0',
          status varchar(20) NOT NULL default 'pending',
          status_date int(10) NOT NULL default '0',
          public_lid varchar(40) NOT NULL default '',
          PRIMARY KEY (history_id),
          KEY lid (lid),
          KEY owner_id (owner_id),
          KEY status (status),
          KEY submitted_date (submitted_date)
        ) ENGINE=MyISAM",
        "INSERT INTO {$_TABLES['downloadsubmissionhistory']} (lid, owner_id, cid, title, submitted_date, status, status_date)
         SELECT s.lid, s.owner_id, s.cid, s.title, s.date, 'pending', s.date
         FROM {$_TABLES['downloadsubmission']} s
         LEFT JOIN {$_TABLES['downloadsubmissionhistory']} h ON h.lid=s.lid
         WHERE h.history_id IS NULL"
    )
);

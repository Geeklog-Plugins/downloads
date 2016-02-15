<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/sql/mysql_install.php                                   |
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

$_SQL[] = "CREATE TABLE {$_TABLES['downloadcategories']} (
  cid varchar(40) NOT NULL default '',
  pid varchar(40) NOT NULL default '',
  title varchar(50) NOT NULL default '',
  imgurl varchar(150) NOT NULL default '',
  corder smallint(5) unsigned NOT NULL default '1',
  is_enabled tinyint(1) unsigned NOT NULL default '1',
  owner_id mediumint(8) unsigned NOT NULL default '1',
  group_id mediumint(8) unsigned NOT NULL default '1',
  perm_owner tinyint(1) unsigned NOT NULL default '3',
  perm_group tinyint(1) unsigned NOT NULL default '2',
  perm_members tinyint(1) unsigned NOT NULL default '2',
  perm_anon tinyint(1) unsigned NOT NULL default '2',
  PRIMARY KEY (cid),
  KEY pid (pid)
) ENGINE=MyISAM";

$_SQL[] = "CREATE TABLE {$_TABLES['downloads']} (
  lid varchar(40) NOT NULL default '',
  cid varchar(40) NOT NULL default '',
  title varchar(100) NOT NULL default '',
  url varchar(250) NOT NULL default '',
  homepage varchar(100) NOT NULL default '',
  version varchar(10) NOT NULL default '',
  size int(8) NOT NULL default '0',
  secret_id varchar(32) NOT NULL default '',
  md5 varchar(32) NOT NULL default '',
  project varchar(50) NOT NULL default '',
  description text NOT NULL,
  detail text NOT NULL,
  text_version tinyint(2) unsigned NOT NULL default '1',
  postmode varchar(10) NOT NULL default 'plaintext',
  logourl varchar(250) NOT NULL default '',
  mg_autotag varchar(250) NOT NULL default '',
  tags varchar(250) NOT NULL default '',
  date int(10) NOT NULL default '0',
  hits int(11) unsigned NOT NULL default '0',
  rating double(6,4) NOT NULL default '0.0000',
  votes int(11) unsigned NOT NULL default '0',
  commentcode tinyint(4) NOT NULL default '0',
  is_released tinyint(1) NOT NULL default '0',
  is_listing tinyint(1) NOT NULL default '0',
  createddate datetime default NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY (lid),
  KEY cid (cid(40)),
  KEY title (title(40))
) ENGINE=MyISAM";

$_SQL[] = "CREATE TABLE {$_TABLES['downloadsubmission']} (
  lid varchar(40) NOT NULL default '',
  cid varchar(40) NOT NULL default '',
  title varchar(100) NOT NULL default '',
  url varchar(250) NOT NULL default '',
  homepage varchar(100) NOT NULL default '',
  version varchar(10) NOT NULL default '',
  size int(8) NOT NULL default '0',
  secret_id varchar(32) NOT NULL default '',
  md5 varchar(32) NOT NULL default '',
  project varchar(50) NOT NULL default '',
  description text NOT NULL,
  detail text NOT NULL,
  text_version tinyint(2) unsigned NOT NULL default '1',
  postmode varchar(10) NOT NULL default 'plaintext',
  logourl varchar(250) NOT NULL default '',
  mg_autotag varchar(250) NOT NULL default '',
  tags varchar(250) NOT NULL default '',
  date int(10) NOT NULL default '0',
  hits int(11) unsigned NOT NULL default '0',
  rating double(6,4) NOT NULL default '0.0000',
  votes int(11) unsigned NOT NULL default '0',
  commentcode tinyint(4) NOT NULL default '0',
  is_released tinyint(1) NOT NULL default '0',
  is_listing tinyint(1) NOT NULL default '0',
  createddate datetime default NULL,
  owner_id mediumint(8) unsigned NOT NULL default '1',
  PRIMARY KEY (lid)
) ENGINE=MyISAM";

$_SQL[] = "CREATE TABLE {$_TABLES['downloadvotes']} (
  ratingid int(11) unsigned NOT NULL auto_increment,
  lid varchar(40) NOT NULL default '',
  ratinguser int(11) NOT NULL default '0',
  rating tinyint(3) unsigned NOT NULL default '0',
  ratinghostname varchar(60) NOT NULL default '',
  ratingtimestamp int(10) NOT NULL default '0',
  PRIMARY KEY (ratingid),
  KEY ratinguser (ratinguser),
  KEY ratinghostname (ratinghostname),
  KEY lid (lid)
) ENGINE=MyISAM";

$_SQL[] = "CREATE TABLE {$_TABLES['downloadhistories']} (
  uid mediumint(8) NOT NULL default '0',
  lid varchar(40) NOT NULL default '',
  remote_ip varchar(15) NOT NULL default '',
  date datetime NOT NULL default '0000-00-00 00:00:00',
  KEY lid (lid),
  KEY uid (uid)
) ENGINE=MyISAM";

?>
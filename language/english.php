<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/language/english.php                                    |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2010-2014 dengen - taharaxp AT gmail DOT com                |
// |                                                                           |
// | Downloads plugin is based on Filemgmt plugin                              |
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
    'nav_addfile'       => 'Add File',
    'nav_addcategory'   => 'Add Category',
    'nav_categories'    => 'List Categories',
    'nav_files'         => 'List Files',
    'warning'           => 'De-Install Warning',
    'WhatsNewLabel'     => 'Downloads',
    'plugin_name'       => 'Downloads',
    'more'              => '<em>more ...</em>',
    'DownloadReport'    => 'Downloads History for single file',
    'instructions'      => 'To modify or delete a data, click on that data\'s edit icon below.  To create a new data, click on "Add Category" or "Add File" above.',
    'instructions2'     => 'To modify or delete a data, click on that data\'s edit icon below.  To create a new data, click on "Add File" above.',
    'downloads'         => 'Downloads',
    'stats_download'    => 'Number of files in downloads repository (Hits)',
    'stats_headline'    => 'Top Ten Accessed Files in Repository',
    'stats_no_hits'     => 'It appears there are no files defined for the downloads plugin on this site or no one has ever accessed them.',
    'stats_page_title'  => 'Title',
    'stats_hits'        => 'Hits',
    'search_results'    => 'File Listing Results',
    'search_title'      => 'Title',
    'search_date'       => 'Date',
    'search_author'     => 'Author',
    'search_hits'       => 'Hits',
    'search_description'=> 'Description',
    'searchlabel'       => 'Downloads',
    'searchlabel_results' => 'File Listing Results',
    'user_menu'         => 'Upload File',
    'admin_menu'        => 'Downloads',
    'no_new_files'      => 'No new files',
    'no_comments'       => 'No new comments',
    'main'              => 'Root',
    'popular'           => 'Popular!',
    'newthisweek'       => 'New this week',
    'datenew'           => 'Date',
    'titleatoz'         => 'Title',
    'popularitymtol'    => 'Popularity',
    'ratinghtol'        => 'Rating',
    'numperpage'        => 'Num per Page',
    'listingheading'    => 'File Listing: %s files',
    'description'       => 'Description',
    'detail'            => 'Detail',
    'permalink'         => 'Permalink',
    'dlnow'             => 'Download Now!',
    'ver'               => 'Version',
    'submitdate'        => 'Date',
    'dltimes'           => 'Downloaded %s times',
    'filesize'          => 'File Size',
    'size'              => 'Size',
    'homepage'          => 'Web',
    'ratingc'           => 'Rating',
    'numvotes'          => '(%s)',
    'entercomment'      => 'Comment',
    'ratethisfile'      => 'Rating',
    'modify'            => 'Modify',
    'edit'              => 'Edit',
    'addnew'            => 'Add New',
    'file'              => 'File',
    'fileid'            => 'File ID',
    'filetitle'         => 'Title',
    'numbytes'          => '%s bytes',
    'shotimage'         => 'File Image',
    'addshotimage'      => 'Add File Image',
    'replshotimage'     => 'Replacement File Image',
    'project'           => 'Project Name',
    'projectfilelist'   => 'File List for the Project: %s',
    'voteonce'          => 'Please do not vote for the same resource more than once.',
    'ratingscale'       => 'The scale is 1 - 10, with 1 being poor and 10 being excellent.',
    'beobjective'       => 'Please be objective, if everyone receives a 1 or a 10, the ratings aren\'t very useful.',
    'donotvote'         => 'Do not vote for your own resource.',
    'rateit'            => 'Rate It!',
    'approved'          => 'Your file has been approved',
    'category'          => 'Category',
    'catid'             => 'Category ID',
    'rating'            => 'Rating',
    'sortby'            => 'Sort by',
    'title'             => 'Title',
    'date'              => 'Date',
    'submit'            => 'Submit',
    'go'                => 'Go',
    'cancel'            => 'Cancel',
    'bytes'             => 'Bytes',
    'norating'          => 'No rating selected.',
    'cantvoteown'       => 'You cannot vote on the resource you submitted.<br' . XHTML . '>All votes are logged and reviewed.',
    'deny_msg'          => 'Access to this file is denied.  Either the file has been moved/removed or you do not have sufficient permissions.',
    'ratefiletitle'     => 'Record your file rating',
    'admintitle'        => 'Downloads Administration',
    'uploadtitle'       => 'Downloads - Upload new file',
    'categorytitle'     => 'File Listing - Category View',
    'generalset'        => 'Configuration Settings',
    'addcategorysnap'   => 'Optional Image: <small>(Top Level Categories only)</small>',
    'addimagenote'      => '(Image height will be resized to 50)',
    'dlswaiting'        => 'Downloads Waiting for Validation',
    'submitter'         => 'Submitter',
    'download'          => 'Download',
    'filelink'          => 'Detail',
    'approve'           => 'Approve',
    'delete'            => 'Delete',
    'nosubmitted'       => 'No New Submitted Downloads.',
    'addmain'           => 'Add MAIN Category',
    'add'               => 'Add',
    'addsub'            => 'Add SUB-Category',
    'in'                => 'in',
    'addnewfile'        => 'Add New File',
    'modcat'            => 'Category Editor',
    'addcat'            => 'Add Category',
    'moddl'             => 'Downloads Info Editor',
    'user'              => 'User',
    'ip'                => 'IP Address',
    'useravg'           => 'User AVG Rating',
    'totalrate'         => 'Total Ratings',
    'noregvotes'        => 'No Registered User Votes',
    'nounregvotes'      => 'No Unregistered User Votes',
    'nobroken'          => 'No reported broken files.',
    'nomodreq'          => 'No Download Modification Request.',
    'modreqdeleted'     => 'Modification Request Deleted.',
    'imgurlmain'        => 'Category Image',
    'parent'            => 'Parent Category',
    'save'              => 'Save Changes',
    'yes'               => 'Yes',
    'no'                => 'No',
    'configupdated'     => 'New configuration saved',
    'errornofile'       => 'ERROR: You need to enter TITLE!',
    'hello'             => 'Hello %s',
    'weapproved'        => 'We approved your download submission to our downloads section. The file name is: ',
    'thankssubmit'      => 'Thanks for your submission!',
    'uploadapproved'    => 'Your uploaded file was approved',
    'nofiles'           => 'No Files Found',
    'toolbar'           => $LANG24[70],
    'toolbar1'          => $LANG24[71],
    'toolbar2'          => $LANG24[72],
    'toolbar3'          => $LANG24[73],
    'toolbar4'          => $LANG24[74],
    'toolbar5'          => $LANG24[75],
    'dlfilename'        => 'File Name',
    'replfilename'      => 'Replacement File',
    'addfilename'       => 'Add New File',
    'commentswanted'    => 'Comments are appreciated',
    'click2see'         => 'Click to see',
    'click2dl'          => 'Click to download',
    'confirm_delete'    => 'Delete this file ?',
    'goback'            => 'Go Back',
    'topic'             => 'Topic',
    'all'               => 'All',
    'download_submissions' => 'Download Submissions',
    'released'          => 'Released',
    'listing'           => 'Include Files on List',
    'postmode'          => 'Post Mode',
    'release_date'      => 'Release Date',
    'comment_mode'      => 'Comment Mode',
    'votedate'          => 'Date',
    'reguservotes'      => 'Registered User Votes (total votes: %s)',
    'anonuservotes'     => 'Anonymous User Votes (total votes: %s)',
    'unknown_uid'       => 'Gest user',
    'manager'           => 'Downloads Manager',
    'tempfile'          => 'TempFile',
    'md5'               => 'MD5 Checksum',
    'md5_checksum'      => 'MD5',
    'is_enabled'        => 'Enabled',
    'corder'            => 'Order',
    'move_down'         => 'Move Category Down',
    'move_up'           => 'Move Category Up',
    'popularity'        => 'Popularity',
    'mg_autotag'        => 'MG Autotag',
    'mg_autotag_info'   => 'Mediagallery Autotag',
    'language'          => 'Lang',
    'upload'            => 'Upload',
    'download_button'   => 'DOWNLOAD',
    'tags'              => 'Tags',
    'please_update'     => 'Please update Downloads plugin.',
    'preview'           => 'Preview',

    '1001' => 'Upload approval Error: Temporary file does not exist. Check error.log',
    '1002' => 'Upload approval Error: New file does not exist after move of tmp file. Check error.log',
    '1003' => 'Upload Add Error: The temporary file was not found. Check error.log',
    '1004' => 'Upload Add Error: The new file was not created. Check error.log',
    '1101' => 'Please enter value for "Title".',
    '1102' => 'Please enter value for "File Name".',
    '1103' => 'Please enter value for "Description".',
    '1201' => 'Please enter value for "File ID".',
    '1202' => '"File ID" entered value already exists. Please change value.',
    '1203' => '"File ID" entered value is incorrect. Please change value.',
    '1301' => 'Please enter value for "Category ID".',
    '1302' => '"Category ID" entered value already exists. Please change value.',
    '1303' => '"Category ID" entered value is incorrect. Please change value.',
    '9999' => 'OOPS! God Knows',
);


// $PLG_downloads_MESSAGE1 = 'Downloads Plugin Install Aborted<br>File: plugins/downloads/download.php is not writeable';
$PLG_downloads_MESSAGE3 = 'This plugin requires Geeklog Version 1.4 or greater, upgrade aborted.';
$PLG_downloads_MESSAGE4 = 'Plugin version 1.5 code not detected - upgrade aborted.';
$PLG_downloads_MESSAGE5 = 'Downloads Plugin Upgrade Aborted<br' . XHTML . '>Current plugin version is not 1.3';
$PLG_downloads_MESSAGE6 = 'You do not have sufficient rights to edit this category.';
$PLG_downloads_MESSAGE7 = 'You do not have sufficient rights to edit this download information.';

// Messages for the plugin upgrade
$PLG_downloads_MESSAGE3001 = 'Plugin upgrade not supported.';
$PLG_downloads_MESSAGE3002 = $LANG32[9];

$PLG_downloads_MESSAGE101 = 'Database updated successfully!';
$PLG_downloads_MESSAGE102 = 'New download information added to the database.';
$PLG_downloads_MESSAGE103 = 'Warning: Duplicate File. <br' . XHTML . '>New download information added to the database.';
$PLG_downloads_MESSAGE104 = 'Warning: Duplicate Snap Image File. <br' . XHTML . '>New download information added to the database.';
$PLG_downloads_MESSAGE105 = 'Download information was deleted.';
$PLG_downloads_MESSAGE106 = 'New category added successfully!';
$PLG_downloads_MESSAGE107 = 'Category was deleted.';
$PLG_downloads_MESSAGE108 = 'Download: Unable to upload - check permissions for the file store directories.';
$PLG_downloads_MESSAGE109 = 'We received your download information. Thanks!<br' . XHTML . '>You\'ll receive an E-mail when it\'s approved.';
$PLG_downloads_MESSAGE110 = 'No matches found to your query.';
$PLG_downloads_MESSAGE111 = 'Vote data deleted.';
$PLG_downloads_MESSAGE112 = 'Record was removed but File was not Deleted.<br' . XHTML . '>More then 1 record pointing to same file.';
$PLG_downloads_MESSAGE113 = 'Thank you for taking the time to vote here.';
$PLG_downloads_MESSAGE114 = 'You do not have sufficient access rights to view download history.';
$PLG_downloads_MESSAGE115 = 'We received your download information. Thanks!';


// Localization of the Admin Configuration UI
$LANG_configsections['downloads'] = array(
    'label' => 'Downloads',
    'title' => 'Downloads Configuration'
);  

$LANG_confignames['downloads'] = array(
    'loginrequired'            => 'Downloads Login Required',
    'hidemenu'                 => 'Hide Downloads Menu Entry',
    'delete_download'          => 'Delete Downloads with Owner',
    'default_permissions'      => 'Default Permissions',
    'download_perpage'         => 'Downloads per Page',
    'download_popular'         => 'Popularity Threshold of Hits',
    'download_newdownloads'    => 'Max Number of New Downloads',
    'download_dlreport'        => 'Downloads History Permissions',
    'download_whatsnew'        => 'Show New Downloads',
    'download_uploadselect'    => 'Allow Login Users to Upload',
    'download_useshots'        => 'Show Category Images',
    'download_shotwidth'       => 'Category Image Width',
    'download_emailoption'     => 'Email Notification Allowing',
    'path_filestore'           => 'Download File',
    'path_snapstore'           => 'Download File Snapshot',
    'path_snapcat'             => 'Category Image',
    'path_tnstore'             => 'Thumbnail Image',
    'snapstore_url'            => 'Download File Snapshot',
    'snapcat_url'              => 'Category Image',
    'tnstore_url'              => 'Thumbnail Image',
    'show_tn_image'            => 'Show Thumbnail Image',
    'show_tn_only_exists'      => 'Show Only Image Exists',
    'max_tnimage_width'        => 'Max Width',
    'max_tnimage_height'       => 'Max Height',
    'tnimage_format'           => 'Format',
    'enabled_mg_autotag'       => 'Enabled MG Autotag',
    'filepermissions'          => 'File Permissions',
    'whatsnew_perioddays'      => 'New Downloads Interval Date',
    'postmode'                 => 'Default Post Mode',
    'cut_own_download'         => 'Cut Owner\'s Own Download',
);

$LANG_configsubgroups['downloads'] = array(
    'sg_main'           => 'Main Settings',
    'sg_Miscellaneous'  => 'Miscellaneous'
);

$LANG_tab['downloads'] = array(
    'tab_main'           => 'General Downloads Settings',
    'tab_tnimage'        => 'Thumbnail Image',
    'tab_category'       => 'Category',
    'tab_history'        => 'Downloads History',
    'tab_whatsnew_block' => 'What\'s New Block',
    'tab_path'           => 'Repository Paths',
    'tab_url'            => 'Repository URL',
    'tab_permissions'    => 'Category Permissions'
);

$LANG_fs['downloads'] = array(
    'fs_main'           => 'General Downloads Settings',
    'fs_tnimage'        => 'Thumbnail Image',
    'fs_category'       => 'Category',
    'fs_history'        => 'Downloads History',
    'fs_whatsnew_block' => 'What\'s New Block',
    'fs_path'           => 'Repository Paths',
    'fs_url'            => 'Repository URL',
    'fs_permissions'    => 'Category Permissions'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['downloads'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    2 => array('As Submitted' => 'submitorder', 'By Votes' => 'voteorder'),
    5 => array_flip($LANG_postmodes),
    9 => array('Forward to Created File' => 'item', 'Display Admin List' => 'list', 'Display Public List' => 'plugin', 'Display Home' => 'home', 'Display Admin' => 'admin'),
    12 => array('No access' => 0, 'Read-Only' => 2, 'Read-Write' => 3),
    20 => array('All' => 'all', 'Login User' => 'user', 'Downloads Editor' => 'editor'),
    30 => array('PNG' => 'png', 'JPEG' => 'jpg')
);

?>
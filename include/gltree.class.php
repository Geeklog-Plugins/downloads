<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/include/gltree.class.php                                |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'gltree.class.php') !== false) {
    die('This file can not be used on its own.');
}

if (!defined('ROOTID')) define('ROOTID', 'root');
if (!defined('XHTML')) define('XHTML', '');
if (!defined ('UC_SELECTED')) {
    define('UC_SELECTED', (XHTML == '') ? 'selected' : 'selected="selected"');
}

class GLTree {

    /**
    * Vars
    *
    * @access  private
    */
    var $_table;     // table with parent-child structure
    var $_id;        // name of unique id for records in table $_table
    var $_pid;       // name of parent id used in table $_table
    var $_title;     // name of a field in table $_table which will be used when selection box and paths are generated
    var $_order;     // specifies the order of query results
    var $_filtersql; // 
    var $_rootid;    // 
    var $_langid;    // 
    var $_sepalator; // 
    var $_root;      // 

    var $_is_multi_language; // 

    // constructor of class GLTree
    // sets the names of table, unique id, and parend id
    function GLTree($table, $id, $pid, $title, $filtersql='', $rootid='0', $langid='')
    {
        $this->_table     = $table;
        $this->_id        = $id;
        $this->_pid       = $pid;
        $this->_title     = $title;
        $this->_order     = $title;
        $this->_filtersql = $filtersql;
        $this->_rootid    = $rootid;
        $this->_langid    = $langid;
        $this->_sepalator = '&nbsp;:&nbsp;';
        $this->_is_multi_language = !empty($langid);
        $this->_root      = 'Root';
    }

    function setFilter($filter)
    {
        $this->_filtersql = $filter;
    }

    function getFilter()
    {
        return $this->_filtersql;
    }

    function setOrder($order)
    {
        $this->_order = $order;
    }

    function getOrder()
    {
        return $this->_order;
    }

    function setSepalator($sepalator)
    {
        $this->_sepalator = $sepalator;
    }

    function getSepalator()
    {
        return $this->_sepalator;
    }

    function setLangid($langid)
    {
        $this->_langid = $langid;
        $this->_is_multi_language = !empty($langid);
    }

    function getLangid()
    {
        return $this->_langid;
    }

    function setMode_MultiLanguage($mode)
    {
        $this->_is_multi_language = $mode;
    }

    function getMode_MultiLanguage()
    {
        return $this->_is_multi_language;
    }

    function setRootid($rootid)
    {
        $this->_rootid = $rootid;
    }

    function getRootid()
    {
        return $this->_rootid;
    }

    function setRoot($root)
    {
        $this->_root = $root;
    }

    function getRoot()
    {
        return $this->_root;
    }

    private function _getOrderSQL($order)
    {
        if (empty($order)) $order = $this->_order;
        if (empty($order)) return '';
        return " ORDER BY $order";
    }

    private function _matchLanguage($id)
    {
        if ($id === $this->_rootid) return true;
        if (!$this->_is_multi_language) return true;
        $len = strlen($this->_langid) + 1;
        return (substr($id, -$len) == ('_' . $this->_langid));
    }

    // returns an array of first child objects for a given id($sel_id)
    function getFirstChild($sel_id, $order='')
    {
        $arr = array();
        $result = DB_query("SELECT * FROM $this->_table WHERE $this->_pid = '$sel_id' $this->_filtersql"
                           . $this->_getOrderSQL($order));
        if (DB_numRows($result) == 0) return $arr;
        while ($row = DB_fetchArray($result, false)) {
            if (!$this->_matchLanguage($row[$this->_id])) continue;
            $arr[] = $row;
        }
        return $arr;
    }

    // returns an array of all FIRST child ids of a given id($sel_id)
    function getFirstChildId($sel_id)
    {
        $arr =array();
        $result = DB_query("SELECT $this->_id FROM $this->_table WHERE $this->_pid = '$sel_id' $this->_filtersql");
        if (DB_numRows($result) == 0) return $arr;
        while (list($id) = DB_fetchArray($result)) {
            if (!$this->_matchLanguage($id)) continue;
            $arr[] = $id;
        }
        return $arr;
    }

    // returns an array of ALL child ids for a given id($sel_id)
    function getAllChildId($sel_id, $order='', $arr=array())
    {
        $result = DB_query("SELECT $this->_id FROM $this->_table WHERE $this->_pid = '$sel_id' $this->_filtersql"
                           . $this->_getOrderSQL($order));
        if (DB_numRows($result) == 0) return $arr;
        while (list($id) = DB_fetchArray($result)) {
            if (!$this->_matchLanguage($id)) continue;
            $arr[] = $id;
            $arr = $this->getAllChildId($id, $order, $arr);
        }
        return $arr;
    }

    // returns an array of ALL parent ids for a given id($sel_id)
    function getAllParentId($sel_id, $order='', $arr=array())
    {
        $result = DB_query("SELECT $this->_pid FROM $this->_table WHERE $this->_id = '$sel_id' $this->_filtersql"
                           . $this->_getOrderSQL($order));
        list($parentid) = DB_fetchArray($result);
        if ($parentid === $this->_rootid) return $arr;
        if (!$this->_matchLanguage($parentid)) return $arr;
        $arr[] = $parentid;
        $arr = $this->getAllParentId($parentid, $order, $arr);
        return $arr;
    }

    // generates path from the root id to a given id($sel_id)
    // the path is delimetered with "/"
    function getPathFromId($sel_id, $title='', $path='')
    {
        if (empty($title)) $title = $this->_title;
        $result = DB_query("SELECT $this->_pid, $title FROM $this->_table WHERE $this->_id = '$sel_id' $this->_filtersql");
        if (DB_numRows($result) == 0) return $path;
        list($parentid, $name) = DB_fetchArray($result);
        if (!$this->_matchLanguage($parentid)) return $path;
        $path = "/" . $name . $path;
        if ($parentid === $this->_rootid) return $path;
        $path = $this->getPathFromId($parentid, $title, $path);
        return $path;
    }

    private function changeCatName_by_language($catid, $name)
    {
        global $_CONF;
        if (!isset($_CONF['languages'])) return $name;
        if ($this->_is_multi_language) return $name;
        foreach ($_CONF['languages'] as $langid => $lang) {
            $len = strlen($langid) + 1;
            if (substr($catid, -$len) == ('_' . $langid)) {
                return $name . ' (' . $lang . ')';
            }
        }
        return $name;
    }

    // makes a nicely ordered selection box
    // $preset_id is used to specify a preselected item
    // set $none to 1 to add a option with value 0
    function makeSelBox($title, $order='', $preset_id=ROOTID, $none=0, $sel_name='', $onchange='', $current_id='')
    {
        if ($sel_name == '') {
            $sel_name = $this->_id;
        }
        $retval = '<select name="' . $sel_name . '" id="select_' . $sel_name . '"';
        if ($onchange != '') {
            $retval .= ' onchange="' . $onchange . '"';
        }
        $retval .= ">\n";

        $r_prefix = '';
        $selected = ' ' . UC_SELECTED;
        if ($none) {
            $sel = ($this->_rootid === $preset_id) ? $selected : '';
            $retval .= "<option value=\"$this->_rootid\"$sel>$this->_root</option>\n";
            $r_prefix = "&nbsp;&nbsp;&nbsp;";
        }

        $sql = "SELECT $this->_id, $title FROM $this->_table WHERE $this->_pid = '$this->_rootid' $this->_filtersql";
        $sql .= $this->_getOrderSQL($order);
        $result = DB_query($sql);

        while (list($catid, $name) = DB_fetchArray($result)) {

            if (!empty($current_id)) {
                if ($catid === $current_id) continue;
            }
            if (!$this->_matchLanguage($catid)) continue;

            $name = $r_prefix . $this->changeCatName_by_language($catid, $name);
            $sel = ($catid === $preset_id) ? $selected : '';
            $retval .= "<option value=\"$catid\"$sel>$name</option>\n";
            $parray = array();
            $arr = $this->getChildTreeArray($catid, $order, $parray, $r_prefix);
            foreach ($arr as $option) {

                if (!empty($current_id)) {
                    if ($option[$this->_id] === $current_id) continue;
                }

                $option[$title] = $this->changeCatName_by_language($option[$this->_id], $option[$title]);

                $option['prefix'] = str_replace('.', "&nbsp;&nbsp;", $option['prefix']);
                $catpath = $option['prefix'] . "&nbsp;" . $this->_escape($option[$title]);

                $sel = ($option[$this->_id] === $preset_id) ? $selected : '';
                $retval .= '<option value="' . $option[$this->_id] . "\"$sel>$catpath</option>\n";
            }
        }
        $retval .= "</select>\n";
        return $retval;
    }

    // generates nicely formatted linked path from the root id to a given id
    function getNicePathFromId($sel_id, $title, $funcURL, $path='')
    {
        $result = DB_query("SELECT $this->_pid, $title FROM $this->_table WHERE $this->_id = '$sel_id'");
        if (DB_numRows($result) == 0) return $path;
        list($parentid, $name) = DB_fetchArray($result);
        $name = $this->_escape($name);
        $path = "<a href=\"$funcURL" . (strpos($funcURL, '?', 0) ? "&" : "?")
              . "$this->_id=$sel_id\">$name</a>$this->_sepalator" . $path;
        if ($parentid === $this->_rootid) return $path;
        $path = $this->getNicePathFromId($parentid, $title, $funcURL, $path);
        return $path;
    }

    // generates id path from the root id to a given id
    // the path is delimetered with "/"
    function getIdPathFromId($sel_id, $path='')
    {
        $result = DB_query("SELECT $this->_pid FROM $this->_table WHERE $this->_id = '$sel_id'");
        if (DB_numRows($result) == 0) return $path;
        list($parentid) = DB_fetchArray($result);
        if (!$this->_matchLanguage($parentid)) return $path;
        $path = "/" . $sel_id . $path;
        if ($parentid === $this->_rootid) return $path;
        $path = $this->getIdPathFromId($parentid, $path);
        return $path;
    }


    function getAllChild($sel_id='', $order='', $parray=array())
    {
        if (empty($sel_id)) $sel_id = $this->_rootid;
        $result = DB_query("SELECT * FROM $this->_table WHERE $this->_pid = '$sel_id'"
                           . $this->_getOrderSQL($order));
        if (DB_numRows($result) == 0) return $parray;
        while ($row = DB_fetchArray($result)) {
            if (!$this->_matchLanguage($row[$this->_id])) continue;
            $parray[] = $row;
            $parray = $this->getAllChild($row[$this->_id], $order, $parray);
        }
        return $parray;
    }

    function getChildTreeArray($sel_id='', $order='', $parray=array(), $r_prefix='')
    {
        if (empty($sel_id)) $sel_id = $this->_rootid;
        $result = DB_query("SELECT * FROM $this->_table WHERE $this->_pid = '$sel_id' $this->_filtersql "
                           . $this->_getOrderSQL($order));
        if (DB_numRows($result) == 0) return $parray;
        while ($row = DB_fetchArray($result)) {
            if (!$this->_matchLanguage($row[$this->_id])) continue;
            $row['prefix'] = $r_prefix . '.';
            $parray[] = $row;
            $parray = $this->getChildTreeArray($row[$this->_id], $order, $parray, $row['prefix']);
        }
        return $parray;
    }

    /**
    * Escape a string for displaying in HTML
    */
    private function _escape($str)
    {
        static $encoding = NULL;
        if ($encoding === NULL) {
            $encoding = COM_getCharset();
        }

        // Unescape a string
        $str = str_replace(
            array('&lt;', '&gt;', '&amp;', '&quot;', '&#039;'),
            array(   '<',    '>',     '&',      '"',      "'"),
            $str
        );
        return htmlspecialchars($str, ENT_QUOTES, $encoding);
    }

}
?>
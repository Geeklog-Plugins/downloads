<?php

// Reminder: always indent with 4 spaces (no tabs).
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | plugins/downloads/include/gltext.class.php                                |
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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'gltext.class.php') !== false) {
    die('This file can not be used on its own.');
}

class GLPText
{
    // Variables:

    //Private
    var $_text;
    var $_postmode;
    var $_advanced_editor_mode;

    // Public Methods:

    /**
     * Constructor
     */
    function GLPText()
    {
        global $_CONF, $_USER;

        $this->_text = '';
        $this->_advanced_editor_mode = 0;
        $this->_postmode = $_CONF['postmode'];
        if ($_CONF['advanced_editor'] && $_USER['advanced_editor'] && $_CONF['postmode'] != 'plaintext') {
            $this->_advanced_editor_mode = 1;
            $this->_postmode = 'html';
        }
    }

    function loadTextFromArgs($arg, $postmode='')
    {
        if (!empty($postmode)) $this->setPostmode($postmode);
        if ($this->_postmode == 'html' || $this->_postmode == 'wikitext') {
            $this->_text = COM_checkHTML(COM_checkWords($arg), 'story.edit');
        } else {
            $this->_text = COM_makeClickableLinks(htmlspecialchars(COM_checkWords($arg)));
        }
        return $this->_text;
    }

    function setText($text, $postmode='')
    {
        if (!empty($postmode)) $this->setPostmode($postmode);
        $this->_text = $text;
    }

    function getText()
    {
        return $this->_text;
    }

    function setPostmode($postmode)
    {
        $this->_advanced_editor_mode = 0;
        $this->_postmode = 'plaintext';
        if ($postmode == 'html' || $postmode == 'wikitext') {
            $this->_postmode = $postmode;
        }
        if ($postmode == 'adveditor') {
            $this->_advanced_editor_mode = 1;
            $this->_postmode = 'html';
        }
    }

    function getPostmode()
    {
        if ($this->_postmode == 'plaintext' || $this->_postmode == 'wikitext') {
            return $this->_postmode;
        }
        if ($this->_advanced_editor_mode == 1) {
            return 'adveditor';
        }
        return 'html';
    }

    function getEditText($text=NULL, $postmode=NULL)
    {
        if (isset($text)) $this->setText($text);
        if (isset($postmode)) $this->setPostmode($postmode);

        return $this->_editText($this->_text);
    }

    function getDisplayText($text=NULL, $postmode=NULL)
    {
        if (isset($text)) $this->setText($text);
        if (isset($postmode)) $this->setPostmode($postmode);

        if ($this->_postmode == 'plaintext') {
            $return = nl2br($this->_text);
        } elseif ($this->_postmode == 'wikitext') {
            $return = COM_renderWikiText($this->_editUnescape($this->_text));
        } else {
            $return = $this->_text;
        }
        $return = PLG_replaceTags($this->_displayEscape($return));

        return $return;
    }

    // Private Methods:

    /**
     * Escapes certain HTML for nicely encoded HTML.
     *
     * @access Private
     * @param   string     $in      Text to escpae
     * @return  string     escaped string
     */
    function _displayEscape($in)
    {
        $return = str_replace('$', '&#36;', $in);
        $return = str_replace('{', '&#123;', $return);
        $return = str_replace('}', '&#125;', $return);
        return $return;
    }

    /**
     * Returns a story formatted for spam check:
     *
     * @return  string Story formatted for spam check.
     */
    function getSpamCheckFormat()
    {
        return "<p>{$this->_text}</p>";
    }

    /**
     * Unescapes certain HTML for editing again.
     *
     * @access Private
     * @param   string  $in Text escaped to unescape for editing
     * @return  string  Unescaped string
     */
    function _editUnescape($in)
    {
        if (($this->_postmode == 'html') || ($this->_postmode == 'wikitext')) {
            /* Raw and code blocks need entity decoding. Other areas do not.
             * otherwise, annoyingly, &lt; will end up as < on preview 1, on
             * preview 2 it'll be stripped by KSES. Can't beleive I missed that
             * in rewrite phase 1.
             *
             * First, raw
             */
            $inlower = MBYTE_strtolower($in);
            $buffer = $in;
            $start_pos = MBYTE_strpos($inlower, '[raw]');
            if( $start_pos !== false ) {
                $out = '';
                while( $start_pos !== false ) {
                    /* Copy in to start to out */
                    $out .= MBYTE_substr($buffer, 0, $start_pos);
                    /* Find end */
                    $end_pos = MBYTE_strpos($inlower, '[/raw]');
                    if( $end_pos !== false ) {
                        /* Encode body and append to out */
                        $encoded = html_entity_decode(MBYTE_substr($buffer, $start_pos, $end_pos - $start_pos));
                        $out .= $encoded . '[/raw]';
                        /* Nibble in */
                        $inlower = MBYTE_substr($inlower, $end_pos + 6);
                        $buffer = MBYTE_substr($buffer, $end_pos + 6);
                    } else { // missing [/raw]
                        // Treat the remainder as code, but this should have been
                        // checked prior to calling:
                        $out .= html_entity_decode(MBYTE_substr($buffer, $start_pos + 5));
                        $inlower = '';
                    }
                    $start_pos = MBYTE_strpos($inlower, '[raw]');
                }
                // Append remainder:
                if( $buffer != '' ) {
                    $out .= $buffer;
                }
                $in = $out;
            }
            /*
             * Then, code
             */
            $inlower = MBYTE_strtolower($in);
            $buffer = $in;
            $start_pos = MBYTE_strpos($inlower, '[code]');
            if( $start_pos !== false ) {
                $out = '';
                while( $start_pos !== false ) {
                    /* Copy in to start to out */
                    $out .= MBYTE_substr($buffer, 0, $start_pos);
                    /* Find end */
                    $end_pos = MBYTE_strpos($inlower, '[/code]');
                    if( $end_pos !== false ) {
                        /* Encode body and append to out */
                        $encoded = html_entity_decode(MBYTE_substr($buffer, $start_pos, $end_pos - $start_pos));
                        $out .= $encoded . '[/code]';
                        /* Nibble in */
                        $inlower = MBYTE_substr($inlower, $end_pos + 7);
                        $buffer = MBYTE_substr($buffer, $end_pos + 7);
                    } else { // missing [/code]
                        // Treat the remainder as code, but this should have been
                        // checked prior to calling:
                        $out .= html_entity_decode(MBYTE_substr($buffer, $start_pos + 6));
                        $inlower = '';
                    }
                    $start_pos = MBYTE_strpos($inlower, '[code]');
                }
                // Append remainder:
                if( $buffer != '' ) {
                    $out .= $buffer;
                }
                $in = $out;
            }
            return $in;
        } else {
            // advanced editor or plaintext can handle themselves...
            return $in;
        }
    }

    /**
     * Returns text ready for the edit fields.
     *
     * @access Private
     * @param   string  $in Text to prepare for editing
     * @return  string  Escaped String
     */
    function _editText($in)
    {
        $out = '';

//        $out = $this->replaceImages($in);
        
        // Remove any autotags the user doesn't have permission to use
        $out = PLG_replaceTags($in, '', true);

        if ($this->_postmode == 'plaintext') {
            $out = COM_undoClickableLinks($out);
            $out = $this->_displayEscape($out);
        } elseif ($this->_postmode == 'wikitext') {
            $out = $this->_editUnescape($in);
        } else {
            // html
            $out = str_replace('<pre><code>', '[code]', $out);
            $out = str_replace('</code></pre>', '[/code]', $out);
            $out = str_replace('<!--raw--><span class="raw">', '[raw]', $out);
            $out = str_replace('</span><!--/raw-->', '[/raw]', $out);
            $out = $this->_editUnescape($out);
            $out = $this->_displayEscape(htmlspecialchars($out));
        }

        return $out;
    }
}

?>

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Downloads Plugin for Geeklog                                              |
// +---------------------------------------------------------------------------+
// | public_html/downloads/adveditor.js                                        |
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

window.onload = function() {
    AdvancedEditor.newEditor({
        TextareaId:[
            {plain:'text_description', advanced:'html_description'},
            {plain:'text_detail',      advanced:'html_detail'}
        ],
        toolbar:0
    });
}

AdvancedEditor.onchange_editmode = function() {
    if (AdvancedEditor.isAdvancedMode()) {
        document.getElementById('text_editor').style.display = 'none';
        document.getElementById('html_editor').style.display = '';
        document.getElementById('text_editor2').style.display = 'none';
        document.getElementById('html_editor2').style.display = '';
    } else {
        document.getElementById('text_editor').style.display = '';
        document.getElementById('html_editor').style.display = 'none';
        document.getElementById('text_editor2').style.display = '';
        document.getElementById('html_editor2').style.display = 'none';
    }
    AdvancedEditor.swapEditorContent();
}

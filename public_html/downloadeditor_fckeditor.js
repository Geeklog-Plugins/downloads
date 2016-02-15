// +---------------------------------------------------------------------------+
// | Copyright (C) 2003,2004,2005,2006 by the following authors:               |
// | Version 1.1    Date: Jun 4, 2006                                          |
// | Authors:   Blaine Lang - blaine AT portalparts DOT com                    |
// | Modified:  dengen      - taharaxp AT gmail DOT com                        |
// |                                                                           |
// | Javascript functions for FCKEditor Integration into Geeklog               |
// |                                                                           |
// +---------------------------------------------------------------------------+

    window.onload = function() {
        var bar = 1;
        if (navigator.userAgent.match(/iPhone|Android|IEMobile/i)) {
            bar = 0;
        }

        var oFCKeditor1 = new FCKeditor( 'html_description' ) ;
        oFCKeditor1.Config['CustomConfigurationsPath'] = geeklogEditorBaseUrl + '/fckeditor/myconfig.js';
        oFCKeditor1.BasePath = geeklogEditorBasePath;
        oFCKeditor1.ToolbarSet = 'editor-toolbar' + (bar + 1) ;
        oFCKeditor1.Height = 200 ;
        oFCKeditor1.ReplaceTextarea() ;

        var oFCKeditor2 = new FCKeditor( 'html_detail' ) ;
        oFCKeditor2.Config['CustomConfigurationsPath'] = geeklogEditorBaseUrl + '/fckeditor/myconfig.js';
        oFCKeditor2.BasePath = geeklogEditorBasePath;
        oFCKeditor2.ToolbarSet = 'editor-toolbar' + (bar + 1) ;
        oFCKeditor2.Height = 200 ;
        oFCKeditor2.ReplaceTextarea() ;

        document.getElementById('fckeditor_toolbar_selector').options[bar].selected = true;
        document.getElementById('fckeditor_toolbar_selector2').options[bar].selected = true;
    }

    function change_editmode(obj) {
        if (obj.value == 'html') {
        
            document.getElementById('html_editor').style.display='none';
            document.getElementById('text_editor').style.display='';
            swapEditorContent('html','html_description','text_description');
            
            document.getElementById('html_editor2').style.display='none';
            document.getElementById('text_editor2').style.display='';
            swapEditorContent('html','html_detail','text_detail');

        } else if (obj.value == 'adveditor') {
        
            document.getElementById('text_editor').style.display='none';
            document.getElementById('html_editor').style.display='';
            swapEditorContent('adveditor','html_description','text_description');
            
            document.getElementById('text_editor2').style.display='none';
            document.getElementById('html_editor2').style.display='';
            swapEditorContent('adveditor','html_detail','text_detail');
            
        } else {
        
            document.getElementById('html_editor').style.display='none';
            document.getElementById('text_editor').style.display='';
            swapEditorContent('text','html_description','text_description');

            document.getElementById('html_editor2').style.display='none';
            document.getElementById('text_editor2').style.display='';
            swapEditorContent('text','html_detail','text_detail');
        }
    }

    function changeHTMLTextAreaSize(element, option) {
        var currentSize = parseInt(document.getElementById(element + '___Frame').style.height);
        if (option == 'larger') {
            var newsize = currentSize + 50;
        } else if (option == 'smaller') {
            var newsize = currentSize - 50;
        }
        newsize = (newsize >= 150) ? newsize : currentSize;
        document.getElementById(element + '___Frame').style.height = newsize + 'px';
    }

    function changeTextAreaSize(element, option) {
        var size = document.getElementById(element).rows;
        if (option == 'larger') {
            document.getElementById(element).rows = +(size) + 3;
        } else if (option == 'smaller') {
            document.getElementById(element).rows = +(size) - 3;
        }
    }

    function getEditorContent(instanceName) {
        // Get the editor instance that we want to interact with.
        var oEditor = FCKeditorAPI.GetInstance(instanceName) ;
        // return the editor contents in XHTML.
        return oEditor.GetXHTML( true );
    }

    function swapEditorContent(curmode,instanceName,idNomalTextarea) {
        var content = '';
        var oEditor = FCKeditorAPI.GetInstance(instanceName) ;
        // Switching from Text to HTML mode
        if (curmode == 'adveditor') {
            // Get the content from the textarea 'text' content and copy it to the editor
            content = document.getElementById(idNomalTextarea).value;
            oEditor.SetHTML(content);
        } else {
            content = getEditorContent(instanceName);
            if (content != '') {
                document.getElementById(idNomalTextarea).value = content;
            }
        }
    }

    function set_postcontent() { 
        if (document.getElementById('sel_editmode').value == 'adveditor') {
            document.getElementById('text_description').value = getEditorContent('html_description');
            document.getElementById('text_detail').value = getEditorContent('html_detail');
        }
    }

   function changeToolbar(toolbar) {
        var oEditor = FCKeditorAPI.GetInstance('html_description');
        oEditor.ToolbarSet.Load( toolbar ) ;
        oEditor = FCKeditorAPI.GetInstance('html_detail');
        oEditor.ToolbarSet.Load( toolbar ) ;
   }

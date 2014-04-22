/* CNN VAN Dashboard Wordpress Plugin
Copyright (C) 2014 thePlatform for Media Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA. */

tinymce.PluginManager.add('van', function(editor, url) {
    // Add a button that opens a window
    editor.addButton('van', {        
        tooltip: 'Embed VAN Media',        
        image: url.substring(0, url.lastIndexOf('/js')) + '/images/cnn.png',
        onclick: function() {
            // Open window         
            
            tinyMCE.activeEditor = editor;

            if (tinyMCE.majorVersion > 3) {
                editor.windowManager.open({
                    width: 1200,
                    height: 1024,
                    url: ajaxurl + "?action=van_embed"
                });             
            }
            else {
                if (jQuery("#cnn-embed-dialog").length == 0)
                    jQuery('body').append('<div id="cnn-embed-dialog"></div>');
                jQuery("#cnn-embed-dialog").html('<iframe src="' + ajaxurl + '?action=van_embed" height="100%" width="100%">').dialog({dialogClass: "wp-dialog", modal: true, resizable: true, minWidth: 1024, width: 1200, height: 1024}).css("overflow-y","hidden");                           
            }
            
        }
    });

});

tinymce.init({    
    plugins: 'van'   
});
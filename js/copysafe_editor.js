(function() {
   tinymce.create('tinymce.plugins.copysafe', {
      init : function(ed, url) {
         ed.addButton('copysafe', {
            title : 'CopySafe Web',
            image : url+'/images/copysafebutton.png',
            onclick : function() {
                var name = prompt("Name of the class file", "");
                if (name != null && name != '')
                    ed.execCommand('mceInsertContent', false, '[copysafe name="'+name+'"][/copysafe]');
            }
         });
      },
      createControl : function(n, cm) {
         return null;
      },
      getInfo : function() {
         return {
            longname : "CopySafe Web",
            author : 'ArtistScope',
            authorurl : 'https://artistscope.com/',
            infourl : 'https://artistscope.com/copysafe_web_protection_wordpress_plugin.asp',
            version : "0.3"
         };
      }
   });
   tinymce.PluginManager.add('copysafe', tinymce.plugins.copysafe);
})();
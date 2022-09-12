jQuery(document).ready(function($) {
  tinymce.create('tinymce.plugins.practicioner_directory_shortcode_plugin', {
    init : function(ed, url) {
      ed.addCommand('practicioner_directory_insert_shortcode', function() {
        tb_show('Practicioner Directory Shortcode Options', 'admin-ajax.php?action=get_my_form');
      });
      ed.addButton('practicioner_directory_button', {title : 'Insert Practicioner Directory Shortcode', cmd : 'practicioner_directory_insert_shortcode', image: url + '/../images/wp-editor-icon.png' });
    },
  });
  tinymce.PluginManager.add('practicioner_directory_button', tinymce.plugins.practicioner_directory_shortcode_plugin);
});

PracticionerDirectory = {
  formatShortCode: function(){
    var categoryVal = jQuery('[name="practicioner-category"]').val();
    var orderVal = jQuery('[name="practicioner-order"]').val();
    var templateVal = jQuery('[name="practicioner-template"]').val();
    
    var shortcode = '[practicioner-directory';

    if(categoryVal != '') {
      shortcode += ' cat=' + categoryVal;
    }

    if(orderVal != '') {
      shortcode += ' order=' + orderVal;
    }

    if(templateVal != '') {
      shortcode += ' template=' + templateVal;
    }

    shortcode += ']';
    
    tinymce.execCommand('mceInsertContent', false, shortcode);
    tb_remove();
  }
};
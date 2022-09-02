jQuery(document).ready(function()
{

  var pluginPath = "../wp-content/plugins/nested-directory/";
  var table = null;

  jQuery('.nd-table').on('click','.editND',function(){
    var id = jQuery(this).data('id');
    console.log('edit', id)
  });

  jQuery('.nd-table').on('click','.deleteND',function(){
    var id = jQuery(this).data('id');
    console.log('delete', id)
  });

  // tree form
  jQuery('#nd-tree-submit').click(function() {
    var title = jQuery('#nd-tree-title').val();
    var description = jQuery('#nd-tree-description').val();
    var parent = jQuery('#nd-tree-parent').val();

    if (title == "") {
      jQuery('#nd-tree-title').addClass('nd-error');
      setTimeout(function() {
        jQuery('#nd-tree-title').removeClass('nd-error');
      }, 1000);
      return false;
    }

    var postForm = {
      title: title,
      parent: parent,
      description: description,
    };
    
    jQuery.ajax({
      url: pluginPath + "nested-directory-form-tree.php?action=post_tree",
      method:"POST",
      data: postForm,
      dataType: "json",      
      success: function(data) {
        reloadNestedDirectory();
        self.parent.tb_remove();
      }
    });
  });

  //item form
  jQuery('#nd-item-submit').click(function() {
    var title = jQuery('#nd-item-title').val();
    var location = jQuery('#nd-item-location').val();
    var website = jQuery('#nd-item-website').val();
    var description = jQuery('#nd-item-description').val();
    var category = jQuery('#nd-item-category').val();

    if (title == "") {
      jQuery('#nd-item-title').addClass('nd-error');
      setTimeout(function() {
        jQuery('#nd-item-title').removeClass('nd-error');
      }, 1000);
      return false;
    }

    var postForm = {
      title: title,
      location: location,
      website: website,
      category: category,
      description: description,
    };
    
    jQuery.ajax({
      url: pluginPath + "nested-directory-form-item.php?action=post_item",
      method:"POST",
      data: postForm,
      dataType: "json",      
      success: function(data) {
        reloadNestedDirectory();
        self.parent.tb_remove();
      }
    });
  });

  function reloadTableNestedDirectory() {
    table = jQuery('.nd-table').DataTable({
      "processing": true,
      "serverSide": true,
      "bDestroy": true,
      "ajax":{
               "url": pluginPath + "nested-directory-item.php?id=0&action=table_data",
               "dataType": "json",
               "type": "POST"
             },
      "columns": [
          { "data": "no" },
          { "data": "name" },
          { "data": "description" },
          { "data": "actions" }
      ]  
    });
  }

  function reloadNestedDirectory() {
    jQuery.ajax({
      url: pluginPath + "nested-directory-tree.php?action=item_data",
      method:"POST",
      dataType: "json",      
      success: function(data) {
        reloadTableNestedDirectory();
        jQuery('#nd-treeview').treeview({data: data})
        .on('nodeSelected', function(e, node){
            jQuery('#nd-item-category').val(node.id);
            table.ajax.url(pluginPath + 'nested-directory-item.php?id='+node.id+'&action=table_data').load();
        });
      }  
    });
  }


  reloadNestedDirectory();

});
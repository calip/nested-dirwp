jQuery(document).ready(function()
{

  var pluginPath = "../wp-content/plugins/nested-directory/";

  function reloadNestedDirectory() {
    var table = jQuery('.nd-table').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax":{
               "url": pluginPath + "nested-directory-item.php?id=1&action=table_data",
               "dataType": "json",
               "type": "POST"
             },
      "columns": [
          { "data": "no" },
          { "data": "name" },
          { "data": "description" },
          { "data": "actions" },
      ]  

    });

    jQuery.ajax({
      url: pluginPath + "nested-directory-tree.php?action=item_data",
      method:"POST",
      dataType: "json",      
      success: function(data) {
        jQuery('#nd-treeview').treeview({data: data})
        .on('nodeSelected', function(e, node){
            table.ajax.url(pluginPath + 'nested-directory-item.php?id='+node.id+'&action=table_data').load();
            console.log(node.id)
        });
      }  
    });
  }

  reloadNestedDirectory(0);

});
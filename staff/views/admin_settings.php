<script type="text/javascript">
  jQuery(document).ready(function($){

    $('#add-new-field').on('click', function(ev){
      ev.preventDefault();
      var tr = $('<tr/>');
      tr.html($('#new-field-template').html());
      $("#add-new-field-row").before(tr);
    });

    $(document).on('click', '.remove-field', function(ev){
      ev.preventDefault();
      $(this).parent().parent().remove();
    });

    $(document).on('click', '.custom-template-dropdown-arrow', function(ev){
      ev.preventDefault();
      $(this).toggleClass('fa-angle-down');
      $(this).toggleClass('fa-angle-up');

      var customTemplate = $(this).parent().next(); // .custom-template
      customTemplate.slideToggle();
    });

    $(document).on('click', '.delete-template', function(ev){
      ev.preventDefault();

      var templateIndex = $(this).data('template-index');
      if(confirm("Are you sure you want to delete Custom Template " + (templateIndex) + "? This cannot be undone.")) {
        window.location.href = "<?php echo get_admin_url(); ?>edit.php?post_type=practicioner&page=practicioner-directory-settings&delete-template=" + templateIndex;
      }
    });
  });
</script>

<style type="text/css">
  div.updated.practicioner-success-message {
    margin-left: 0px;
    margin-top: 20px;
  }
  #new-field-template {
    display: none;
  }
  .form-group {
    margin-bottom: 50px;
  }
  .custom-template {
    display: none;
    margin-bottom: 40px;
  }
  .custom-template-dropdown-arrow {
    text-decoration: none;
  }
  .practicioner-template-textarea-wrapper {
    float: left;
    width: 40%;
  }
  .practicioner-template-textarea-wrapper textarea {
    height: 170px;
  }
</style>

<?php if($did_update_options): ?>
  <div id="message" class="updated notice notice-success is-dismissible below-h2 practicioner-success-message">
    <p>Settings updated.</p>
  </div>
<?php endif; ?>

<form method="post">

  <div class="form-group">
    <h2>Custom Details Fields</h2>

    <p>
      This allows you to create custom details fields for each Practicioner member.
      Name and bio fields are provided by default, so you don't need to add those here.
    </p>

    <table class="widefat fixed" cellspacing="0" id="practicioner-meta-fields">
      <thead>
        <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Remove</th>
        </tr>
      </thead>

      <tfoot>
        <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Remove</th>
        </tr>
      </tfoot>

      <tbody>
        <?php foreach(get_option('practicioner_meta_fields') as $field): ?>
          <tr class="column-<?php echo $field['slug']; ?>">
            <td>
              <input type="text" name="practicioner_meta_fields_labels[]" value="<?php echo $field['name']; ?>" />
            </td>
            <td>
              <select name="practicioner_meta_fields_types[]">
                <?php if($field['type'] == 'text'): ?>
                  <option value="text" selected>text field</option>
                  <option value="textarea">text area</option>
                <?php elseif($field['type'] == 'textarea'): ?>
                  <option value="text">text field</option>
                  <option value="textarea" selected>text area</option>
                <?php else: ?>
                  <option value="text">text field</option>
                  <option value="textarea">text area</option>
                <?php endif; ?>
              </select>
            </td>
            <td>
              [<?php echo $field['slug']; ?>]
            </td>
            <td>
              <a href="#" class="remove-field">Remove Field</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr id="add-new-field-row" valign="top">
          <td colspan=4>
            <a href="#" id="add-new-field">+ Add New Field</a>
          </td>
        </tr>
        <tr id="new-field-template">
          <td>
            <input type="text" name="practicioner_meta_fields_labels[]" />
          </td>
          <td>
            <select name="practicioner_meta_fields_types[]">
              <option value="text">text field</option>
              <option value="teaxtarea">text area</option>
            </select>
          </td>
          <td></td>
          <td>
            <a href="#" class="remove-field">Remove Field</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="form-group">
    <h2>Templates</h2>

    <p>Template instructions can be found on the <a href="<?php echo get_admin_url(); ?>edit.php?post_type=practicioner&page=practicioner-directory-help#practicioner-template-tags">Practicioner Help page</a></p>

    <p>Templates can be chosen manually with the [practicioner-directory] shortcode (slugs shown in parentheses), or you can choose to set a default template here:</p>

    <p>
      <?php if($current_template == 'list'): ?>
        <input type="radio" name="practicioner_templates[slug]" value="list" checked />
      <?php else: ?>
        <input type="radio" name="practicioner_templates[slug]" value="list" />
      <?php endif; ?>
      List (list)
    </p>

    <p>
      <?php if($current_template == 'grid'): ?>
        <input type="radio" name="practicioner_templates[slug]" value="grid" checked />
      <?php else: ?>
        <input type="radio" name="practicioner_templates[slug]" value="grid" />
      <?php endif; ?>
      Grid (grid)
    </p>

    <?php foreach($custom_templates as $template): ?>
      <?php require(plugin_dir_path(__FILE__) . '/partials/admin_custom_template.php'); ?>
    <?php endforeach; ?>

    <p>
      <input type="radio" name="practicioner_templates[slug]" value="custom_<?php echo count($custom_templates) + 1; ?>" disabled>
      Custom Template <?php echo count($custom_templates) + 1; ?> (save template before you select) <a href="#" class="fa fa-angle-down custom-template-dropdown-arrow"></a>
    </p>

    <div class="custom-template">
      <div class="practicioner-template-textarea-wrapper">
        <label for="custom_practicioner_templates[<?php echo count($custom_templates) + 1; ?>][html]">HTML:</label>
        <p>
          <textarea name="custom_practicioner_templates[<?php echo count($custom_templates) + 1; ?>][html]" class="large-text code"></textarea>
        </p>
      </div>

      <div class="practicioner-template-textarea-wrapper">
        <label for="custom_practicioner_templates[<?php echo count($custom_templates) + 1; ?>][css]">CSS:</label>
        <p>
          <textarea name="custom_practicioner_templates[<?php echo count($custom_templates) + 1; ?>][css]" class="large-text code"></textarea>
        </p>
      </div>
    </div>

  </div>

  <div class="clear"></div>

  <p>
    <input type="submit" class="button button-primary button-large" value="Save">
  </p>
</form>

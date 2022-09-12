<?php

class PracticionerDirectoryAdmin {
  static function register_admin_menu_items() {
    add_action('admin_menu', array('PracticionerDirectoryAdmin', 'add_admin_menu_items'));
  }

  static function add_admin_menu_items() {
    add_submenu_page('edit.php?post_type=practicioner', 'Practicioner Directory Settings', 'Settings', 'publish_posts', 'practicioner-directory-settings', array('PracticionerDirectoryAdmin', 'settings'));
    add_submenu_page('edit.php?post_type=practicioner', 'Practicioner Directory Help', 'Help', 'publish_posts', 'practicioner-directory-help', array('PracticionerDirectoryAdmin', 'help'));
    add_submenu_page('edit.php?post_type=practicioner', 'Practicioner Directory Import', 'Import Old Practicioner', 'publish_posts', 'practicioner-directory-import', array('PracticionerDirectoryAdmin', 'import'));
  }

  static function settings() {

    $practicioner_settings = PracticionerSettings::sharedInstance();

    if(isset($_GET['delete-template'])) {
      $practicioner_settings->deleteCustomTemplate($_GET['delete-template']);
    }

    if (isset($_POST['practicioner_templates']['slug'])) {
      $practicioner_settings->updateDefaultPracticionerTemplateSlug($_POST['practicioner_templates']['slug']);
      $did_update_options = true;
    }

    if (isset($_POST['custom_practicioner_templates'])) {
      $practicioner_settings->updateCustomPracticionerTemplates($_POST['custom_practicioner_templates']);
      $did_update_options = true;
    }

    if (isset($_POST['practicioner_meta_fields_labels'])) {
      $practicioner_settings->updateCustomPracticionerMetaFields($_POST['practicioner_meta_fields_labels'], $_POST['practicioner_meta_fields_types']);
      $did_update_options = true;
    }

    $current_template = $practicioner_settings->getCurrentDefaultPracticionerTemplate();
    $custom_templates = $practicioner_settings->getCustomPracticionerTemplates();

    require_once(plugin_dir_path(__FILE__) . '../views/admin_settings.php');
  }

  static function help() {
    require_once(plugin_dir_path(__FILE__) . '../views/admin_help.php');
  }

  static function import() {
    $did_import_old_practicioner = false;
    if (isset($_GET['import']) && $_GET['import'] == 'true') {
      PracticionerDirectory::import_old_practicioner();
      $did_import_old_practicioner = true;
    }
    if (PracticionerDirectory::has_old_practicioner_table()):
    ?>

      <h2>Practicioner Directory Import</h2>
      <p>
        This tool is provided to import practicioner from an older version of this plugin.
        This will copy old practicioner members over to the new format, but it is advised
        that you backup your database before proceeding. Chances are you won't need
        it, but it's always better to be safe than sorry! WordPress provides some
        <a href="https://codex.wordpress.org/Backing_Up_Your_Database" target="_blank">instructions</a>
        on how to backup your database.
      </p>

      <p>
        Once you're ready to proceed, simply use the button below to import old
        practicioner members to the newer version of the plugin.
      </p>

      <p>
        <a href="<?php echo get_admin_url(); ?>edit.php?post_type=practicioner&page=practicioner-directory-import&import=true" class="button button-primary">Import Old Practicioner</a>
      </p>

    <?php else: ?>

      <?php if ($did_import_old_practicioner): ?>

        <div class="updated">
          <p>
            Old practicioner was successfully imported! You can <a href="<?php echo get_admin_url(); ?>edit.php?post_type=practicioner">view all practicioner here</a>.
          </p>
        </div>

      <?php else: ?>

        <p>
          It doesn't look like you have any practicioner members from an older version of the plugin. You're good to go!
        </p>

      <?php endif; ?>

    <?php

    endif;
  }

  static function register_import_old_practicioner_message() {
    add_action('admin_notices', array('PracticionerDirectoryAdmin', 'show_import_old_practicioner_message'));
  }

  static function show_import_old_practicioner_message() {
    ?>

    <div class="update-nag">
      It looks like you have practicioner from an older version of the Practicioner Directory plugin.
      You can <a href="<?php echo get_admin_url(); ?>edit.php?post_type=practicioner&page=practicioner-directory-import">import them</a> to the newer version if you would like.
    </div>

    <?php
  }
}

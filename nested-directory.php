<?php
 
/*
 
Plugin Name: Nested Directory
 
Plugin URI: https://alipultra.com/
 
Description: Plugin for nested directory
 
Version: 1.0
 
Author: Alip Putra
 
Author URI: https://alipultra.com/
 
License: GPLv2 or later
 
Text Domain: alipultra
 
*/
global $nested_directories_db;
$nested_directories_db = '1.0';

function nested_directories_install()
{
    global $wpdb;
    global $nested_directories_db;

    $table_name = $wpdb->prefix . 'nested_directory_item'; // do not forget about tables prefix

    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email VARCHAR(100) NOT NULL,
        age int(11) NULL,
        PRIMARY KEY  (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('nested_directories_db', $nested_directories_db);

    $installed_ver = get_option('nested_directories_db');
    if ($installed_ver != $nested_directories_db) {
        $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email VARCHAR(200) NOT NULL,
            age int(11) NULL,
            PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('nested_directories_db', $nested_directories_db);
    }
}

register_activation_hook(__FILE__, 'nested_directories_install');

function nested_directories_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'nested_directory_item'; // do not forget about tables prefix

    $wpdb->insert($table_name, array(
        'name' => 'Alex',
        'email' => 'alex@example.com',
        'age' => 25
    ));
    $wpdb->insert($table_name, array(
        'name' => 'Maria',
        'email' => 'maria@example.com',
        'age' => 22
    ));
}

register_activation_hook(__FILE__, 'nested_directories_install_data');

function nested_directories_update_db_check()
{
    global $nested_directories_db;
    if (get_site_option('nested_directories_db') != $nested_directories_db) {
        nested_directories_install();
    }
}

add_action('plugins_loaded', 'nested_directories_update_db_check');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

function nested_directories_admin_menu()
{
    add_menu_page(__('Persons', 'nested_directories'), __('Persons', 'nested_directories'), 'activate_plugins', 'persons', 'nested_directories_persons_page_handler');
    add_submenu_page('persons', __('Persons', 'nested_directories'), __('Persons', 'nested_directories'), 'activate_plugins', 'persons', 'nested_directories_persons_page_handler');
    // add new will be described in next part
    add_submenu_page('persons', __('Add new', 'nested_directories'), __('Add new', 'nested_directories'), 'activate_plugins', 'persons_form', 'nested_directories_persons_form_page_handler');
}

add_action('admin_menu', 'nested_directories_admin_menu');

function buildNestedDirectoryTree(Array $data, $parent = 0) {
    $tree = array();
    foreach ($data as $d) {
        if ($d['parent_id'] == $parent) {
            $children = buildNestedDirectoryTree($data, $d['id']);
            // set a trivial key
            if (!empty($children)) {
                $d['_children'] = $children;
            }
            $tree[] = $d;
        }
    }
    return $tree;
}

function printTree($tree, $r = 0, $p = null) {
    foreach ($tree as $i => $t) {
        $dash = ($t['parent_id'] == 0) ? '' : str_repeat('-', $r) .' ';
        printf("\t<option value='%d'>%s%s</option>\n", $t['id'], $dash, $t['title']);
        if ($t['parent_id'] == $p) {
            // reset $r
            $r = 0;
        }
        if (isset($t['_children'])) {
            printTree($t['_children'], ++$r, $t['parent_id']);
        }
    }
}


function nested_directories_persons_page_handler()
{
    global $wpdb;
    $directory = $wpdb->get_results("SELECT * FROM wp_nested_directory", ARRAY_A);
    $tree = buildNestedDirectoryTree($directory);
    add_thickbox();
    ?>
    <div class="wrap nosubsub">
        <h1 class="wp-heading-inline">Nested Directory</h1>

        <div id="col-container" class="wp-clearfix">

            <div id="col-left">
                <div class="col-wrap">
                    <h2>
                        <?php echo __('Root')?>
                        <a title="Add New Category" class="thickbox add-new-h2" href="#TB_inline?width=400&height=300&inlineId=modal-category-nd"><?php _e('Add new', 'new_category_nested_directories')?></a>
                    </h2>

                    <div id="nd-treeview"></div>

                    <div id="modal-category-nd" style="display:none;">
                        <div class="nd-thickbox">
                            <label><?php echo __('Title')?></label>
                            <input type="text" name="nd-tree-title" id="nd-tree-title" class="input-control">
                        </div>
                        <div class="nd-thickbox">
                            <label><?php echo __('Parent')?></label>
                            <select name="nd-tree-parent" id="nd-tree-parent" class="input-control">
                                <option value="0">-</option>
                                <?php echo printTree($tree); ?>
                            </select>
                        </div>
                        <div class="nd-thickbox">
                            <label><?php echo __('Description')?></label>
                            <textarea name="nd-tree-description" rows="5" id="nd-tree-description" class="input-control"></textarea>
                        </div>
                        <div class="nd-thickbox">
                            <label>&nbsp;</label>
                            <button id="nd-tree-submit"><?php echo __('Save')?></button>
                        </div>
                    </div>
                </div>
            </div> 

            <div id="col-right">
                <div class="col-wrap">
                    <h2>
                        <?php _e('Persons', 'nested_directories')?> 
                        <a title="Add New Item" class="thickbox add-new-h2" href="#TB_inline?width=400&height=300&inlineId=modal-item-nd"><?php _e('Add new', 'nested_directories')?></a>
                    </h2>
                    <div class="table-responsive">
                        <table class="nd-table table-striped">
                            <thead>
                            <tr>
                                <th scope="col"><?php echo __('No')?></th>
                                <th scope="col"><?php echo __('Name')?></th>
                                <th scope="col"><?php echo __('Description')?></th>
                                <th scope="col"><?php echo __('Actions')?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- List Data Menggunakan DataTable -->              
                            </tbody>
                        </table>
                    </div>

                    <div id="modal-item-nd" style="display:none;">
                        <div class="nd-thickbox">
                            <label><?php echo __('Title')?></label>
                            <input type="hidden" name="nd-item-category" id="nd-item-category" class="input-control">
                            <input type="text" name="nd-item-title" id="nd-item-title" class="input-control">
                        </div>
                        <div class="nd-thickbox">
                            <label><?php echo __('Location')?></label>
                            <input type="text" name="nd-item-location" id="nd-item-location" class="input-control">
                        </div>
                        <div class="nd-thickbox">
                            <label><?php echo __('Website')?></label>
                            <input type="text" name="nd-item-website" id="nd-item-website" class="input-control">
                        </div>
                        <div class="nd-thickbox">
                            <label><?php echo __('Description')?></label>
                            <textarea name="nd-item-description" rows="5" id="nd-item-description" class="input-control"></textarea>
                        </div>
                        <div class="nd-thickbox">
                            <label>&nbsp;</label>
                            <button id="nd-item-submit"><?php echo __('Save')?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
}

function nested_directories_plugin()
{
    load_plugin_textdomain('nested_directories', false, dirname(plugin_basename(__FILE__)));
}

function nested_directory_admin_add_resources() {
    wp_register_script('nested_directory_jquery_datatable', plugins_url('scripts/ndj.dataTables.min.js', __FILE__) );
    wp_enqueue_script('nested_directory_jquery_datatable');
    wp_register_script('nested_directory_datatable', plugins_url('scripts/nd.dataTables.min.js', __FILE__) );
    wp_enqueue_script('nested_directory_datatable');
    wp_register_script('nested_directory_treeview', plugins_url('scripts/nd.treeview.min.js', __FILE__) );
    wp_enqueue_script('nested_directory_treeview');


    wp_register_style( 'nested_directory_bootstrap', plugins_url( 'styles/nd.bootstrap.min.css', __FILE__ ) );
    wp_enqueue_style( 'nested_directory_bootstrap' );
    wp_register_style( 'nested_directory_style_datatable', plugins_url( 'styles/nd.dataTables.min.css', __FILE__ ) );
    wp_enqueue_style( 'nested_directory_style_datatable' );
    wp_register_style( 'nested_directory_style_treeview', plugins_url( 'styles/nd.treeview.min.css', __FILE__ ) );
    wp_enqueue_style( 'nested_directory_style_datatable' );

    wp_register_style( 'nested_directory_style_nd', plugins_url( 'styles/nd.css', __FILE__ ) );
    wp_enqueue_style( 'nested_directory_style_nd' );

    wp_register_script('nested_directory_admin', plugins_url('nested_directory_admin.js', __FILE__) );
    wp_enqueue_script('nested_directory_admin');

    wp_enqueue_script( 'nd-thickbox' );
 
    wp_enqueue_style( 'nd-thickbox' );
}

add_action('init', 'nested_directories_plugin');
add_action('admin_enqueue_scripts', 'nested_directory_admin_add_resources');

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

function nested_directories_persons_page_handler()
{
    global $wpdb;
    ?>
    <div class="wrap nosubsub">
        <h1 class="wp-heading-inline">Nested Directory</h1>

        <div id="col-container" class="wp-clearfix">

            <div id="col-left">
                <div class="col-wrap">
                    <h2>Root</h2>
                    <div id="nd-treeview"></div>
                </div>
            </div>

            <div id="col-right">
                <div class="col-wrap">
                    <h2><?php _e('Persons', 'nested_directories')?> <a class="add-new-h2"
                                                    href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons_form');?>"><?php _e('Add new', 'nested_directories')?></a>
                    </h2>
                    <div class="table-responsive">
                        <table class="nd-table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- List Data Menggunakan DataTable -->              
                            </tbody>
                        </table>
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

    wp_register_script('nested_directory_admin', plugins_url('nested_directory_admin.js', __FILE__) );
    wp_enqueue_script('nested_directory_admin');
}

add_action('init', 'nested_directories_plugin');
add_action('admin_enqueue_scripts', 'nested_directory_admin_add_resources');

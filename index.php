<?php
if (! function_exists('add_action'))
{
    echo 'Nothing to see here. Move along now people.';
    exit;
}

global $wpdb;

global $nesteddir_db_version;
$nesteddir_db_version = '1.26.0';

global $nesteddir_table_directory;
$nesteddir_table_directory = $wpdb->prefix . "nesteddir";

global $nesteddir_table_directory_name;
$nesteddir_table_directory_name = $wpdb->prefix . "nesteddir_name";


/* The helpers and the shortcode are responsible for everything that happens on the frontend */
require_once dirname( __FILE__ ) . '/helpers.php';
require_once dirname( __FILE__ ) . '/shortcode.php';


/**
 * Database update check, run provisioning whenever the DB version has a mismatch
 */
function nesteddir_post_update()
{
    global $nesteddir_db_version;

    /* Update the database if there is a new version */
    if (get_option('nesteddir_db_version') != $nesteddir_db_version)
    {
        nesteddir_db_tables();
        nesteddir_db_post_update_actions();

        if(! nesteddir_is_multibyte_supported())
        {
            add_action('admin_notices', 'nesteddir_notice_mb_string_not_installed');
        }
    }
}


/**
 * Register a capability for the popular Members plugin
 */
function nesteddir_register_capabilities()
{
    /* This should not been called without the Members plugin, but better safe than sorry */
    if ( function_exists( 'members_register_cap' ) ) {
        members_register_cap(
            'manage_nesteddir',
            array(
                'label' => __('Can manage Nested Directory', 'nesteddir'),
            )
        );
    }
}
add_action( 'members_register_caps', 'nesteddir_register_capabilities' );


/**
 * We only need admin functionality and database setup when we are in the WP-admin
 */
if ( is_admin() )
{
    require_once dirname( __FILE__ ) . '/admin.php';
    require_once dirname( __FILE__ ) . '/admin_general_settings.php';
    require_once dirname( __FILE__ ) . '/database.php';

    /* These register_activation_hooks run after install */
    register_activation_hook( __FILE__, 'nesteddir_db_tables' );
    register_activation_hook( __FILE__, 'nesteddir_db_install_demo_data' );

    /* This hook is for updates */
    add_action( 'plugins_loaded', 'nesteddir_post_update' );
}


/**
 * Initialize the plugin
 * Ready.. set.. go!
 */
function nesteddir_init()
{
    $plugin_dir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('nesteddir', false, $plugin_dir . '/translation/');
}
add_action('plugins_loaded', 'nesteddir_init');

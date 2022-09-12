<?php
/*

Plugin Name: Practicioners Folders
Plugin URI: https://wickedplugins.com/wicked-folders/
Description: Organize your pages into folders.
Version: 2.18.14
Author: Practicioners Plugins
Author URI: https://wickedplugins.com/
Text Domain: Practicioners-folders
License: GPLv2 or later


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if ( class_exists( 'Wicked_Folders' ) ) return;

global $wpdb;
$staff_directory_table = $wpdb->prefix . 'staff_directory';

define('STAFF_DIRECTORY_TABLE', $wpdb->prefix . 'staff_directory');
define('STAFF_TEMPLATES', $wpdb->prefix . 'staff_directory_templates');
define('STAFF_PHOTOS_DIRECTORY', WP_CONTENT_DIR . "/uploads/staff-photos/");

require_once(dirname(__FILE__) . '/staff/classes/staff_settings.php');

StaffSettings::setupDefaults();

require_once(dirname(__FILE__) . '/staff/classes/staff_directory.php');
require_once(dirname(__FILE__) . '/staff/classes/staff_directory_shortcode.php');
require_once(dirname(__FILE__) . '/staff/classes/staff_directory_admin.php');

StaffDirectory::register_post_types();
StaffDirectory::set_default_meta_fields_if_necessary();
StaffDirectoryAdmin::register_admin_menu_items();
StaffDirectoryShortcode::register_shortcode();

require_once( dirname( __FILE__ ) . '/lib/class-wicked-folders.php' );

register_activation_hook( __FILE__, array( 'Wicked_Folders', 'activate' ) );

Wicked_Folders::get_instance();



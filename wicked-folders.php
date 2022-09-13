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
$practicioner_directory_table = $wpdb->prefix . 'practicioner_directory';

define('PRACTICIONER_DIRECTORY_TABLE', $wpdb->prefix . 'practicioner_directory');
define('PRACTICIONER_TEMPLATES', $wpdb->prefix . 'practicioner_directory_templates');
define('PRACTICIONER_PHOTOS_DIRECTORY', WP_CONTENT_DIR . "/uploads/practicioner-photos/");

require_once(dirname(__FILE__) . '/staff/classes/practicioner_settings.php');

PracticionerSettings::setupDefaults();

require_once(dirname(__FILE__) . '/staff/classes/practicioner_directory.php');
require_once(dirname(__FILE__) . '/staff/classes/practicioner_directory_shortcode.php');
require_once(dirname(__FILE__) . '/staff/classes/practicioner_directory_admin.php');

PracticionerDirectory::register_post_types();
PracticionerDirectory::set_default_meta_fields_if_necessary();
PracticionerDirectoryAdmin::register_admin_menu_items();
PracticionerDirectoryShortcode::register_shortcode();

require_once( dirname( __FILE__ ) . '/lib/class-wicked-folders.php' );

register_activation_hook( __FILE__, array( 'Wicked_Folders', 'activate' ) );

Wicked_Folders::get_instance();



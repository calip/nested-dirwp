<?php
/**
 * Plugin Name: nd-tree
 * Plugin URI: 
 * Description: Plugin for nested directory
 * Version: Version 0.1.1
 * Author: Alip Putra
 * Author URI:  http://www.alipultra.com/
 * License: GPL2 license
 */

if ( ! defined( 'ABSPATH' ) ) exit;



require_once __DIR__.'/class/TS_Tree.class.php';
$tst = new \ndtree\TS_Tree();

register_activation_hook( __FILE__ , 'initTsTreePlugin' );
function initTsTreePlugin(){	
	global $wpdb;
			
	$checkQuery = "SHOW COLUMNS FROM $wpdb->terms";

	if( !in_array('term_order', $wpdb->get_col( $checkQuery ) ) ){
		$query = "ALTER TABLE $wpdb->terms ADD `term_order` INT NOT NULL AFTER `term_group`";
		$result = $wpdb->query( $wpdb->prepare( $query ));
	}
}
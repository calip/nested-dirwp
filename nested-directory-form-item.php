<?php
$pagePath = explode('/wp-content/', dirname(__FILE__));
include_once(str_replace('wp-content/' , '', $pagePath[0] . '/wp-load.php'));

global $wpdb;
$nd_table_directory_item = $wpdb->prefix . "nested_directory_item";
if($_GET['action'] === 'post_item'){
  $db_success = $wpdb->insert($nd_table_directory_item, array(
    'name'        => $_POST['title'],
    'location'    => $_POST['location'],
    'website'     => $_POST['website'],
    'directory'    => $_POST['category']
  ));

  if(! empty($db_success))
  {
    echo $db_success;
  }
  else
  {
    echo 'Failed';
  }
}
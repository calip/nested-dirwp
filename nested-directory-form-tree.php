<?php
$pagePath = explode('/wp-content/', dirname(__FILE__));
include_once(str_replace('wp-content/' , '', $pagePath[0] . '/wp-load.php'));

global $wpdb;
$nd_table_directory = $wpdb->prefix . "nested_directory";
if($_GET['action'] === 'post_tree'){
  $db_success = $wpdb->insert($nd_table_directory, array(
    'title'         => $_POST['title'],
    'parent_id'     => $_POST['parent'],
    'description'   => $_POST['description']
  ));

  // print_r($nd_table_directory);
  if(! empty($db_success))
  {
    print_r($db_success);
  }
  else
  {
    print_r('Failed');
  }
  // echo "success";
}
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

  if(! empty($db_success))
  {
    echo $db_success;
  }
  else
  {
    echo 'Failed';
  }
}
<?php
$pagePath = explode('/wp-content/', dirname(__FILE__));
include_once(str_replace('wp-content/' , '', $pagePath[0] . '/wp-load.php'));

global $wpdb;
if($_GET['action'] === 'item_data'){
  $directory = $wpdb->get_results("SELECT * FROM wp_nested_directory", ARRAY_A);
  // print_r($directory);
  if(!empty($directory)) {
    foreach($directory as $row)
    {
      $sub_data["id"] = $row["id"];

      $sub_data["name"] = $row["title"];

      $sub_data["text"] = $row["title"];

      $sub_data["parent_id"] = $row["parent_id"];

      $data[] = $sub_data;
    }
    
    foreach($data as $key => &$value){

      $output[$value["id"]] = &$value;

    }

    foreach($data as $key => &$value){

        if($value["parent_id"] && isset($output[$value["parent_id"]])){

            $output[$value["parent_id"]]["nodes"][] = &$value;

        }

    }

    foreach($data as $key => &$value){

        if($value["parent_id"] && isset($output[$value["parent_id"]])){

            unset($data[$key]);

        }

    }
    foreach($data as $key => &$value){
      $item[] = $value;

    }
    
    echo json_encode($item);
   
  }
}
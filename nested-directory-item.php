<?php
$pagePath = explode('/wp-content/', dirname(__FILE__));
include_once(str_replace('wp-content/' , '', $pagePath[0] . '/wp-load.php'));

global $wpdb;

if($_GET['action'] === 'table_data'){
  $myquery = '';
  if ($_GET['id'] > 0) {
      $myquery = 'WHERE directory = ' . $_GET['id'];
  }

  $columns = array( 
    0 => 'id', 
    1 => 'name',
    2 => 'description',
    3 => 'id',
  );

  $querycount = $wpdb->get_row("SELECT count(id) as total FROM wp_nested_directory_item $myquery", ARRAY_A);

  $totalData = $querycount['total'];
  $totalFiltered = $totalData; 

  $limit = $_POST['length'];
  $start = $_POST['start'];
  $order = $columns[$_POST['order']['0']['column']];
  $dir = $_POST['order']['0']['dir'];

  if(empty($_POST['search']['value']))
  {            
    $query = $wpdb->get_results("SELECT * FROM wp_nested_directory_item $myquery order by $order $dir 
                                  LIMIT $limit 
                                  OFFSET $start", ARRAY_A);
  }
  else {
    $search = $_POST['search']['value']; 
    $query = $wpdb->get_results("SELECT * FROM wp_nested_directory_item $myquery AND name LIKE '%$search%' 
                                  order by $order $dir 
                                  LIMIT $limit 
                                  OFFSET $start", ARRAY_A);


    $querycount = $wpdb->get_row("SELECT count(id) as total FROM wp_nested_directory_item $myquery ", ARRAY_A);
    $totalFiltered = $querycount['total'];
  }

  $data = array();
  if(!empty($query))
  {
      $no = $start + 1;
      foreach($query as $r)
      {
          $nestedData['no'] = $no;
          $nestedData['name'] = $r['name'];
          $nestedData['description'] = $r['description'];
          $nestedData['actions'] = "<a href='#' data-id='".$r['id']."' class='editND btn-warning btn-sm'>Edit</a>&nbsp; <a href='#'  data-id='".$r['id']."' class='deleteND btn-danger btn-sm'>Delete</a>";
          $data[] = $nestedData;
          $no++;
      }
  }
    
  $json_data = array(
              "draw"            => intval($_POST['draw']),  
              "recordsTotal"    => intval($totalData),  
              "recordsFiltered" => intval($totalFiltered), 
              "data"            => $data   
              );
      
  echo json_encode($json_data); 
}
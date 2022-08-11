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
add_action('admin_menu', 'nested_directory_register_menu');
add_action('admin_enqueue_scripts', 'nested_directory_admin_add_resources');
 
function nested_directory_register_menu(){
    foreach( name_directory_get_capabilities() as $capability)
    {
        if( current_user_can( $capability ) )
        {
            add_menu_page(
                __('Nested Directory', 'nested-directory'),
                __('Nested Directory', 'nested-directory'),
                $capability,
                'nested-directory',
                'nested_directory_init',
                'dashicons-index-card',
                120);
            break;
        }
    }
}

function get_directory($data, $parent = 0) {
  static $i = 1;
  $tab = str_repeat(" ", $i);
  if ($data[$parent]) {
    $html = "$tab<ul class='tree'>";
    $i++;
    foreach ($data as $v) {
      // echo $v->id_menu;
      // $child = get_directory($data, $v->id_menu);
      $html .= "$tab<li>";
      $html .= '<a href="#">'.$v->title.'</a>';
      if (isset($child)) {
        $i--;
        $html .= $child;
        $html .= "$tab";
      }
      $html .= '</li>';
    }
    $html .= "$tab</ul>";
    return $html;
  }
  else {
    return false;
  }
}

function nested_directory_admin_add_resources() {
  wp_enqueue_style('nested_directory_admin', plugins_url('nested_directory_admin.css', __FILE__), '', '1.0');
}
 
function nested_directory_init(){
  $data = array();
  $data[] = (object) array(
    "id_menu" => 1,
    "parent_id"=> 0,
    "title"=> "Main",
  );
  $data[] = (object) array(
    "id_menu"=> 2,
    "parent_id"=> 0,
    "title"=> "Main 2",
  );
  $data[] = (object) array(
    "id_menu"=> 3,
    "parent_id"=> 1,
    "title"=> "Child Main 1",
  );
  $data[] = (object) array(
    "id_menu"=> 4,
    "parent_id"=>1,
    "title"=> "Child Main 2",
  );
  $menu = get_directory($data)
  ?>
	<?php echo $menu ?>
</div>
  <?php
}
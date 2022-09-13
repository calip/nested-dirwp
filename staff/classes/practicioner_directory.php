<?php

class PracticionerDirectory {

  #
  # Init custom post types
  #

  static function register_post_types() {
    add_action('init', array('PracticionerDirectory', 'create_post_types'));
    add_filter("manage_edit-practicioner_columns", array('PracticionerDirectory', 'set_practicioner_admin_columns'));
    add_filter("manage_practicioner_posts_custom_column", array('PracticionerDirectory', 'custom_practicioner_admin_columns'), 10, 3);
    add_filter('enter_title_here', array('PracticionerDirectory', 'practicioner_title_text'));
    add_filter('admin_head', array('PracticionerDirectory', 'remove_media_buttons'));
    add_action('add_meta_boxes_practicioner', array('PracticionerDirectory', 'add_practicioner_custom_meta_boxes'));
    add_action('save_post', array('PracticionerDirectory', 'save_meta_boxes'));
    add_action('wp_enqueue_scripts', array('PracticionerDirectory', 'enqueue_fontawesome'));
    add_action('admin_enqueue_scripts', array('PracticionerDirectory', 'enqueue_fontawesome'));

    add_action('init', array('PracticionerDirectory', 'init_tinymce_button'));
    add_action('wp_ajax_get_my_form', array('PracticionerDirectory', 'thickbox_ajax_form'));
  }

  static function create_post_types() {
    register_post_type( 'practicioner',
      array(
        'labels' => array(
          'name' => __( 'Practicioner' )
        ),
        'supports' => array(
          'title',
          'editor',
          'thumbnail'
        ),
        'public' => true,
        'menu_icon' => 'dashicons-groups',
        'taxonomies' => array('wf_practicioner_folders')
      )
    );
  }

  static function set_practicioner_admin_columns() {
    $new_columns = array(
  	  'cb' => '<input type="checkbox" />',
  	  'title' => __('Title'),
      'id' => __('ID'),
      'featured_image' => __('Featured Image'),
      'date' => __('Date')
  	);
  	return $new_columns;
  }

  static function custom_practicioner_admin_columns($column_name, $post_id) {
    $out = '';
    switch ($column_name) {
      case 'featured_image':
        $attachment_array = wp_get_attachment_image_src(get_post_thumbnail_id($post_id));
        $photo_url = $attachment_array[0];
        $out .= '<img src="' . $photo_url . '" style="max-height: 60px; width: auto;" />';
        break;

      case 'id':
          $out .= $post_id;
          break;

      default:
        break;
    }
    echo $out;
  }

  static function enqueue_fontawesome() {
    wp_enqueue_style('font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css', array(), '4.0.3');
  }

  #
  # Custom post type customizations
  #

  static function practicioner_title_text( $title ){
    $screen = get_current_screen();
    if ($screen->post_type == 'practicioner') {
      $title = "Enter practicioner member's name";
    }

    return $title;
  }

  static function remove_media_buttons() {
    $screen = get_current_screen();
		if($screen->post_type == 'practicioner') {
		    remove_action('media_buttons', 'media_buttons');
    }
	}

  static function add_practicioner_custom_meta_boxes() {
    add_meta_box( 'practicioner-meta-box', __('Practicioner Details'), array('PracticionerDirectory', 'practicioner_meta_box_output'), 'practicioner', 'normal', 'high' );
  }

  static function practicioner_meta_box_output( $post ) {

    wp_nonce_field('practicioner_meta_box_nonce_action', 'practicioner_meta_box_nonce');

    $practicioner_settings = PracticionerSettings::sharedInstance();

    ?>

    <style type="text/css">
      label.practicioner-label {
        float: left;
        line-height: 27px;
        width: 130px;
      }
    </style>

    <?php foreach($practicioner_settings->getPracticionerDetailsFields() as $field): ?>
      <p>
        <label for="practicioner[<?php echo $field['slug'] ?>]" class="practicioner-label"><?php _e($field['name']); ?>:</label>
        <?php if($field['type'] == 'text'): ?>
          <input type="text" name="practicioner_meta[<?php echo $field['slug'] ?>]" value="<?php echo get_post_meta($post->ID, $field['slug'], true); ?>" />
        <?php elseif($field['type'] == 'textarea'): ?>
          <textarea cols=40 rows=5 name="practicioner_meta[<?php echo $field['slug'] ?>]"><?php echo get_post_meta($post->ID, $field['slug'], true); ?></textarea>
        <?php endif; ?>
      </p>
    <?php endforeach; ?>

    <?php
  }

  static function save_meta_boxes($post_id) {
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return;

    if(!isset( $_POST['practicioner_meta_box_nonce'] ) || !wp_verify_nonce($_POST['practicioner_meta_box_nonce'], 'practicioner_meta_box_nonce_action'))
      return;

    if(!current_user_can('edit_post', get_the_id()))
      return;

    foreach(array_keys($_POST['practicioner_meta']) as $meta_field_slug) {
      update_post_meta($post_id, $meta_field_slug, esc_attr($_POST['practicioner_meta'][$meta_field_slug]));
    }
  }

  static function set_default_meta_fields_if_necessary() {
    $current_meta_fields = get_option('practicioner_meta_fields');

    if($current_meta_fields == NULL || $current_meta_fields = '') {
      $default_meta_fields = array(
        array(
          'name' => 'Position',
          'type' => 'text',
          'slug' => 'position'
        ),
        array(
          'name' => 'Email',
          'type' => 'text',
          'slug' => 'email'
        ),
        array(
          'name' => 'Phone Number',
          'type' => 'text',
          'slug' => 'phone_number'
        ),
        array(
          'name' => 'Website',
          'type' => 'text',
          'slug' => 'website'
        )
      );
      update_option('practicioner_meta_fields', $default_meta_fields);
    }
  }

  #
  # Default templates
  #

  static function set_default_templates_if_necessary() {
    if(get_option('practicioner_directory_template_slug') == '') {
      update_option('practicioner_directory_template_slug', 'list');
    }

    if (get_option('practicioner_directory_html_template') == '') {
      $default_html_template = <<<EOT
<div class="practicioner-directory">

  [practicioner_loop]

    [name_header]
    [bio_paragraph]

    <div class="practicioner-directory-divider"></div>

  [/practicioner_loop]

</div>
EOT;
        update_option('practicioner_directory_html_template', $default_html_template);
    }

    if (get_option('practicioner_directory_css_template') == '') {
      $default_css_template = <<<EOT
.practicioner-directory-divider{
  border-top: solid black thin;
  width: 90%;
  margin:15px 0;
}
EOT;
        update_option('practicioner_directory_css_template', $default_css_template);
    }
  }

  #
  # Related to old practicioner members
  #

  static function has_old_practicioner_table() {
    global $wpdb;
    $practicioner_directory_table = $wpdb->prefix . 'practicioner_directory';

    $old_practicioner_sql = "SHOW TABLES LIKE '$practicioner_directory_table'";
    $old_practicioner_table_results = $wpdb->get_results($old_practicioner_sql);

    return count($old_practicioner_table_results) > 0;
  }

  static function show_import_message() {
    if (
      isset($_GET['page'])
      &&
      $_GET['page'] == 'practicioner-directory-import'
      &&
      isset($_GET['import'])
      &&
      $_GET['import'] == 'true'
    )
      return false;

    return PracticionerDirectory::has_old_practicioner_table();
  }

  static function get_old_practicioner($orderby = null, $order = null, $filter = null){
  	global $wpdb;
  	$practicioner_directory_table = $wpdb->prefix . 'practicioner_directory';
  	$practicioner_directory_categories = $wpdb->prefix . 'practicioner_directory_categories';

  	if((isset($orderby) AND $orderby != '') AND (isset($order) AND $order != '') AND (isset($filter) AND $filter != '')){

  		if($orderby == 'name'){

  			$all_practicioner = $wpdb->get_results("SELECT * FROM " . PRACTICIONER_DIRECTORY_TABLE . " WHERE `category` = $filter ORDER BY `name` $order");

  		}

  		if($orderby == 'category'){

  			$categories = $wpdb->get_results("SELECT * FROM $practicioner_directory_categories WHERE `cat_id` = $filter ORDER BY name $order");

  			foreach($categories as $category){
  				$cat_id = $category->cat_id;
  				//echo $cat_id;
  				$practicioner_by_cat = $wpdb->get_results("SELECT * FROM " . PRACTICIONER_DIRECTORY_TABLE . " WHERE `category` = $cat_id");
  				foreach($practicioner_by_cat as $practicioner){
  					$all_practicioner[] = $practicioner;
  				}
  			}
  		}

  		return $all_practicioner;


  	}elseif((isset($orderby) AND $orderby != '') AND (isset($order) AND $order != '')){

  		if($orderby == 'name'){

  			$all_practicioner = $wpdb->get_results("SELECT * FROM " . PRACTICIONER_DIRECTORY_TABLE . " ORDER BY `name` $order");

  		}

  		if($orderby == 'category'){

  			$all_practicioner = $wpdb->get_results("SELECT * FROM " . PRACTICIONER_DIRECTORY_TABLE . " ORDER BY category $order");

  		}


  		return $all_practicioner;

  	}elseif(isset($filter) AND $filter != ''){

  		$all_practicioner = $wpdb->get_results("SELECT * FROM " . PRACTICIONER_DIRECTORY_TABLE . " WHERE `category` = $filter");
  		if(isset($all_practicioner)){
  			return $all_practicioner;
  		}

  	}else{

  		return $wpdb->get_results("SELECT * FROM " . PRACTICIONER_DIRECTORY_TABLE);

  	}
  }

  static function init_tinymce_button() {
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
         return;

    add_filter("mce_external_plugins", array('PracticionerDirectory', 'register_tinymce_plugin'));
    add_filter('mce_buttons', array('PracticionerDirectory', 'add_tinymce_button'));
  }

  static function register_tinymce_plugin($plugin_array) {
    $plugin_array['practicioner_directory_button'] = plugins_url('/../js/shortcode.js', __FILE__);;
    return $plugin_array;
  }

  static function add_tinymce_button($buttons) {
    $buttons[] = "practicioner_directory_button";
    return $buttons;
  }

  static function thickbox_ajax_form(){
    require_once(plugin_dir_path(__FILE__) . '/../views/shortcode_thickbox.php');
    exit;
  }
}

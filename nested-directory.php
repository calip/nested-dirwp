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
global $nested_directories_db;
$nested_directories_db = '1.0';

function nested_directories_install()
{
    global $wpdb;
    global $nested_directories_db;

    $table_name = $wpdb->prefix . 'nested_directory_item'; // do not forget about tables prefix

    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email VARCHAR(100) NOT NULL,
        age int(11) NULL,
        PRIMARY KEY  (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('nested_directories_db', $nested_directories_db);

    $installed_ver = get_option('nested_directories_db');
    if ($installed_ver != $nested_directories_db) {
        $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email VARCHAR(200) NOT NULL,
            age int(11) NULL,
            PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('nested_directories_db', $nested_directories_db);
    }
}

register_activation_hook(__FILE__, 'nested_directories_install');

function nested_directories_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'nested_directory_item'; // do not forget about tables prefix

    $wpdb->insert($table_name, array(
        'name' => 'Alex',
        'email' => 'alex@example.com',
        'age' => 25
    ));
    $wpdb->insert($table_name, array(
        'name' => 'Maria',
        'email' => 'maria@example.com',
        'age' => 22
    ));
}

register_activation_hook(__FILE__, 'nested_directories_install_data');

function nested_directories_update_db_check()
{
    global $nested_directories_db;
    if (get_site_option('nested_directories_db') != $nested_directories_db) {
        nested_directories_install();
    }
}

add_action('plugins_loaded', 'nested_directories_update_db_check');

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
    * Custom_Table_Example_List_Table class that will display our custom table
    * records in nice table
    */
class Nested_Directory_List_Table extends WP_List_Table
{
    /**
        * [REQUIRED] You must declare constructor and give some basic params
        */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'person',
            'plural' => 'persons',
        ));
    }

    /**
        * [REQUIRED] this is a default column renderer
        *
        * @param $item - row (key, value array)
        * @param $column_name - string (key)
        * @return HTML
        */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
        * [OPTIONAL] this is example, how to render specific column
        *
        * method name must be like this: "column_[column_name]"
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_age($item)
    {
        return '<em>' . $item['age'] . '</em>';
    }

    /**
        * [OPTIONAL] this is example, how to render column with actions,
        * when you hover row "Edit | Delete" links showed
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_name($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=persons_form&id=%s">%s</a>', $item['id'], __('Edit', 'nested_directories')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'nested_directories')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    /**
        * [REQUIRED] this is how checkbox column renders
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
        * [REQUIRED] This method return columns to display in table
        * you can skip columns that you do not want to show
        * like content, or description
        *
        * @return array
        */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'name' => __('Name', 'nested_directories'),
            'email' => __('E-Mail', 'nested_directories'),
            'age' => __('Age', 'nested_directories'),
        );
        return $columns;
    }

    /**
        * [OPTIONAL] This method return columns that may be used to sort table
        * all strings in array - is column names
        * notice that true on name column means that its default sort
        *
        * @return array
        */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
            'email' => array('email', false),
            'age' => array('age', false),
        );
        return $sortable_columns;
    }

    /**
        * [OPTIONAL] Return array of bult actions if has any
        *
        * @return array
        */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
        * [OPTIONAL] This method processes bulk actions
        * it can be outside of class
        * it can not use wp_redirect coz there is output already
        * in this example we are processing delete action
        * message about successful deletion will be shown on page in next part
        */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nested_directory_item'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
        * [REQUIRED] This is the most important method
        *
        * It will get rows from database and prepare them to be showed in table
        */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nested_directory_item'; // do not forget about tables prefix

        $per_page = 5; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}

/**
    * PART 3. Admin page
    * ============================================================================
    *
    * In this part you are going to add admin page for custom table
    *
    * http://codex.wordpress.org/Administration_Menus
    */

/**
    * admin_menu hook implementation, will add pages to list persons and to add new one
    */
function nested_directories_admin_menu()
{
    add_menu_page(__('Persons', 'nested_directories'), __('Persons', 'nested_directories'), 'activate_plugins', 'persons', 'nested_directories_persons_page_handler');
    add_submenu_page('persons', __('Persons', 'nested_directories'), __('Persons', 'nested_directories'), 'activate_plugins', 'persons', 'nested_directories_persons_page_handler');
    // add new will be described in next part
    add_submenu_page('persons', __('Add new', 'nested_directories'), __('Add new', 'nested_directories'), 'activate_plugins', 'persons_form', 'nested_directories_persons_form_page_handler');
}

add_action('admin_menu', 'nested_directories_admin_menu');

/**
    * List page handler
    *
    * This function renders our custom table
    * Notice how we display message about successfull deletion
    * Actualy this is very easy, and you can add as many features
    * as you want.
    *
    * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
    */
function nested_directories_persons_page_handler()
{
    global $wpdb;

    $table = new Nested_Directory_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'nested_directories'), count($_REQUEST['id'])) . '</p></div>';
    }
    $directory = $wpdb->get_results("SELECT * FROM wp_nested_directory");
    $menu = build_menu($directory)
    ?>
    <div class="wrap nosubsub">
        <h1 class="wp-heading-inline">Nested Directory</h1>

        <div id="col-container" class="wp-clearfix">

            <div id="col-left">
                <div class="col-wrap">
                    <?php echo $menu ?>
                </div>
            </div>

            <div id="col-right">
                <div class="col-wrap">
                    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                    <h2><?php _e('Persons', 'nested_directories')?> <a class="add-new-h2"
                                                    href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons_form');?>"><?php _e('Add new', 'nested_directories')?></a>
                    </h2>
                    <?php echo $message; ?>

                    <form id="persons-table" method="GET">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                        <?php $table->display() ?>
                    </form>
                </div>
            </div>
        </div>
    </div>


<?php
}

/**
    * PART 4. Form for adding andor editing row
    * ============================================================================
    *
    * In this part you are going to add admin page for adding andor editing items
    * You cant put all form into this function, but in this example form will
    * be placed into meta box, and if you want you can split your form into
    * as many meta boxes as you want
    *
    * http://codex.wordpress.org/Data_Validation
    * http://codex.wordpress.org/Function_Reference/selected
    */

/**
    * Form page handler checks is there some data posted and tries to save it
    * Also it renders basic wrapper in which we are callin meta box render
    */
function nested_directories_persons_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'nested_directory_item'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => '',
        'email' => '',
        'age' => null,
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = nested_directories_validate_person($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'nested_directories');
                } else {
                    $notice = __('There was an error while saving item', 'nested_directories');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'nested_directories');
                } else {
                    $notice = __('There was an error while updating item', 'nested_directories');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'nested_directories');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('persons_form_meta_box', 'Person data', 'nested_directories_persons_form_meta_box_handler', 'person', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Person', 'nested_directories')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=persons');?>"><?php _e('back to list', 'nested_directories')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('person', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'nested_directories')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
    * This function renders our custom meta box
    * $item is row
    *
    * @param $item
    */
function nested_directories_persons_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Name', 'nested_directories')?></label>
        </th>
        <td>
            <input id="name" name="name" type="text" style="width: 95%" value="<?php echo esc_attr($item['name'])?>"
                    size="50" class="code" placeholder="<?php _e('Your name', 'nested_directories')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="email"><?php _e('E-Mail', 'nested_directories')?></label>
        </th>
        <td>
            <input id="email" name="email" type="email" style="width: 95%" value="<?php echo esc_attr($item['email'])?>"
                    size="50" class="code" placeholder="<?php _e('Your E-Mail', 'nested_directories')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="age"><?php _e('Age', 'nested_directories')?></label>
        </th>
        <td>
            <input id="age" name="age" type="number" style="width: 95%" value="<?php echo esc_attr($item['age'])?>"
                    size="50" class="code" placeholder="<?php _e('Your age', 'nested_directories')?>" required>
        </td>
    </tr>
    </tbody>
</table>
<?php
}

/**
    * Simple function that validates data and retrieve bool on success
    * and error message(s) on error
    *
    * @param $item
    * @return bool|string
    */
function nested_directories_validate_person($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'nested_directories');
    if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-Mail is in wrong format', 'nested_directories');
    if (!ctype_digit($item['age'])) $messages[] = __('Age in wrong format', 'nested_directories');
    //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
    //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
    //...

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

function has_children($rows,$id) {
    foreach ($rows as $row) {
      if ($row->parent_id == $id)
        return true;
    }
    return false;
  }
  function build_menu($rows,$parent=0)
  {  
    $tree = '';
    if ($parent <= 0) {
      $tree = 'tree';
    }
    $result = "<ul class='".$tree."'>";
    foreach ($rows as $row)
    { 
      if ($row->parent_id == $parent){
        $has_child = has_children($rows,$row->id);
        
        if($has_child) {
          $result.= "<li class='section'>";
          $result.= "<input type='checkbox' id=".$row->id.">";
          $result.= "<label for=".$row->id.">".$row->title."</label>";
        } else {
          $result.= "<li>". $row->title;
        }
        if ($has_child) {
          $result.= build_menu($rows,$row->id);
        }
        $result.= "</li>";
      }
    }
    $result.= "</ul>";
  
    return $result;
  }

/**
    * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
    * and _e('english string', 'your_uniq_plugin_name') to echo it
    * in this example plugin your_uniq_plugin_name == nested_directories
    *
    * to create translation file, use poedit FileNew catalog...
    * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
    * and on last tab add "__" and "_e"
    *
    * Name your file like this: [my_plugin]-[ru_RU].po
    *
    * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
    * http://codex.wordpress.org/I18n_for_WordPress_Developers
    */
function nested_directories_plugin()
{
    load_plugin_textdomain('nested_directories', false, dirname(plugin_basename(__FILE__)));
}

function nested_directory_admin_add_resources() {
    wp_enqueue_style('nested_directory_admin', plugins_url('nested_directory_admin.css', __FILE__), '', '1.0');
  }

add_action('init', 'nested_directories_plugin');
add_action('admin_enqueue_scripts', 'nested_directory_admin_add_resources');
/*
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

function has_children($rows,$id) {
  foreach ($rows as $row) {
    if ($row->parent_id == $id)
      return true;
  }
  return false;
}
function build_menu($rows,$parent=0)
{  
  $tree = '';
  if ($parent <= 0) {
    $tree = 'tree';
  }
  $result = "<ul class='".$tree."'>";
  foreach ($rows as $row)
  { 
    if ($row->parent_id == $parent){
      $has_child = has_children($rows,$row->id);
      
      if($has_child) {
        $result.= "<li class='section'>";
        $result.= "<input type='checkbox' id=".$row->id.">";
        $result.= "<label for=".$row->id.">".$row->title."</label>";
      } else {
        $result.= "<li>". $row->title;
      }
      if ($has_child) {
        $result.= build_menu($rows,$row->id);
      }
      $result.= "</li>";
    }
  }
  $result.= "</ul>";

  return $result;
}

function nested_directory_admin_add_resources() {
  wp_enqueue_style('nested_directory_admin', plugins_url('nested_directory_admin.css', __FILE__), '', '1.0');
}
 
function nested_directory_init(){
  global $wpdb;
  $directory = $wpdb->get_results("SELECT * FROM wp_nested_directory");
  $menu = build_menu($directory)
  ?>
  <div class="wrap nosubsub">
    <h1 class="wp-heading-inline">Nested Directory</h1>

    <div id="col-container" class="wp-clearfix">
      <div id="col-left">
        <div class="col-wrap">
          <?php echo $menu; ?>
        </div>
      </div>

      <div id="col-right">
        <div class="col-wrap">
          dfsdf
        </div>
      </div>
    </div>
  </div>
  <!-- <div class="nestedir-container">
    <?php echo $menu; ?>
  </div> -->
  <?php
}
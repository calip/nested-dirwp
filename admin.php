<?php

add_action('admin_menu', 'nesteddir_register_menu_entry');
add_action('admin_enqueue_scripts', 'nesteddir_admin_add_resources');
add_action('wp_ajax_nesteddir_ajax_names', 'nesteddir_names');
add_action('wp_ajax_nesteddir_switch_name_published_status', 'nesteddir_ajax_switch_name_published_status');


/**
 * Add a menu entry on options
 */
function nesteddir_register_menu_entry()
{
    foreach( nesteddir_get_capabilities() as $capability)
    {
        if( current_user_can( $capability ) )
        {
            add_menu_page(
                __('Nested Directory', 'nesteddir'),
                __('Nested Directory', 'nesteddir'),
                $capability,
                'nesteddir',
                'nesteddir_options',
                'dashicons-index-card',
                120);

            add_submenu_page(
                'nesteddir',
                __('Add directory', 'nesteddir'),
                __('Add directory', 'nesteddir'),
                $capability,
                'admin.php?page=nesteddir&sub=new-directory');

            add_submenu_page(
                'nesteddir',
                __('Quick import into new directory', 'nesteddir'),
                __('Quick import', 'nesteddir'),
                $capability,
                'admin.php?page=nesteddir&sub=quick-import');

            break;
        }
    }
}


/**
 * This is a little router for the
 * nesteddir plugin
 */
function nesteddir_options()
{
    if ( ! nesteddir_is_control_allowed() )
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'nesteddir') );
    }

    $sub_page = '';
    if( ! empty( $_GET['sub'] ) )
    {
        $sub_page = $_GET['sub'];
    }

    switch( $sub_page )
    {
        case 'manage-directory':
            nesteddir_names();
            break;
        case 'edit-directory':
            nesteddir_edit();
            break;
        case 'new-directory':
            nesteddir_edit('new');
            break;
        case 'quick-import':
            nesteddir_quick_import();
            break;
        case 'import':
            nesteddir_import();
            break;
        case 'export':
            nesteddir_export();
            break;
        default:
            nesteddir_show_list();
            break;
    }

}


/**
 * Show the list of directories and all of the
 * links to manage the directories
 */
function nesteddir_show_list()
{
    global $wpdb;
    global $nesteddir_table_directory;
    global $nesteddir_table_directory_name;

    if(! empty( $_GET['delete_dir'] ) && is_numeric( $_GET['delete_dir'] ) && check_admin_referer('nesteddir-action','secnonce') )
    {
        $name = $wpdb->get_var(sprintf("SELECT `name` FROM %s WHERE id=%d", $nesteddir_table_directory, $_GET['delete_dir']));
        $wpdb->delete($nesteddir_table_directory, array('id' => $_GET['delete_dir']), array('%d'));
        $wpdb->delete($nesteddir_table_directory_name, array('directory' => $_GET['delete_dir']), array('%d'));
        echo "<div class='updated'><p><strong>"
            . sprintf(__('Nested Directory %s and all entries deleted', 'nesteddir'), "<i>" . $name . "</i>")
            . "</strong></p></div>";
    }

    $wp_file = admin_url('admin.php');
    $wp_page = $_GET['page'];
    $wp_url_path = sprintf("%s?page=%s", $wp_file, $wp_page);
    $wp_new_url = sprintf("%s&sub=%s", $wp_url_path, 'new-directory');
    $wp_nonce = wp_create_nonce('nesteddir-action');

    echo '<div class="wrap">';
    echo "<h2>"
        . __('Nested Directory management', 'nesteddir')
        . " <a href='" . $wp_new_url . "' class='add-new-h2'>" . __('Add directory', 'nesteddir') . "</a>"
        . "</h2>";

    if(! empty($_POST['mode']) && ! empty($_POST['dir_id']) && check_admin_referer( 'nesteddir_dirmanagement','nesteddir_adminnonce' ))
    {
        $wpdb->update(
            $nesteddir_table_directory,
            array(
                'name'                    => sanitize_text_field($_POST['name']),
                'description'             => sanitize_text_field($_POST['description']),
                'show_title'              => (int)$_POST['show_title'],
                'show_description'        => (int)$_POST['show_description'],
                'show_submit_form'        => (int)$_POST['show_submit_form'],
                'show_search_form'        => (int)$_POST['show_search_form'],
                'search_in_description'   => (int)$_POST['search_in_description'],
                'search_highlight'        => (int)$_POST['search_highlight'],
                'show_submitter_name'     => (int)$_POST['show_submitter_name'],
                'show_line_between_names' => (int)$_POST['show_line_between_names'],
                'show_character_header'   => (int)$_POST['show_character_header'],
                'show_all_names_on_index' => (int)$_POST['show_all_names_on_index'],
                'show_all_index_letters'  => (int)$_POST['show_all_index_letters'],
                'jump_to_search_results'  => (int)$_POST['jump_to_search_results'],
                'nr_columns'              => (int)$_POST['nr_columns'],
                'nr_most_recent'          => intval($_POST['nr_most_recent']),
                'nr_words_description'    => intval($_POST['nr_words_description']),
                'name_term'               => sanitize_text_field($_POST['name_term']),
                'name_term_singular'      => sanitize_text_field($_POST['name_term_singular']),
            ),
            array('id' => intval($_POST['dir_id']))
        );

        echo "<div class='updated'><p>"
            . sprintf(__('Directory %s updated.', 'nesteddir'), "<i>" . sanitize_text_field($_POST['name']) . "</i>")
            . "</p></div>";

        unset($_GET['dir_id']);
    }
    elseif(! empty($_POST['mode']) && $_POST['mode'] == "new" && check_admin_referer( 'nesteddir_dirmanagement','nesteddir_adminnonce' ))
    {
        $cleaned_name = sanitize_text_field($_POST['name']);

        if( empty($cleaned_name))
        {
            echo "<div class='error'><p><strong>" . __('Please fill in at least a (valid) name to create new Nested Directory', 'nesteddir') . "</strong></p>";
            echo "<a href='#' onclick='javascript:history.back();'>" . __('Click here to go back and try again', 'nesteddir') . "</a></div>";
        }
        else
        {
            $wpdb->insert(
                $nesteddir_table_directory,
                array(
                    'name'                    => $cleaned_name,
                    'description'             => sanitize_text_field($_POST['description']),
                    'show_title'              => (int)$_POST['show_title'],
                    'show_description'        => (int)$_POST['show_description'],
                    'show_submit_form'        => (int)$_POST['show_submit_form'],
                    'show_search_form'        => (int)$_POST['show_search_form'],
                    'search_in_description'   => (int)$_POST['search_in_description'],
                    'search_highlight'        => (int)$_POST['search_highlight'],
                    'show_submitter_name'     => (int)$_POST['show_submitter_name'],
                    'show_line_between_names' => (int)$_POST['show_line_between_names'],
                    'show_character_header'   => (int)$_POST['show_character_header'],
                    'show_all_names_on_index' => (int)$_POST['show_all_names_on_index'],
                    'show_all_index_letters'  => (int)$_POST['show_all_index_letters'],
                    'jump_to_search_results'  => (int)$_POST['jump_to_search_results'],
                    'nr_columns'              => (int)$_POST['nr_columns'],
                    'nr_most_recent'          => intval($_POST['nr_most_recent']),
                    'nr_words_description'    => intval($_POST['nr_words_description']),
                    'name_term'               => sanitize_text_field($_POST['name_term']),
                    'name_term_singular'      => sanitize_text_field($_POST['name_term_singular']),
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
            );

            echo "<div class='updated'><p>"
                . sprintf(__('Directory %s created.', 'nesteddir'), "<i>" . $cleaned_name . "</i>")
                . "</p></div>";
        }
    }

    $directories = $wpdb->get_results(sprintf("
        SELECT nd.* 
        FROM `%s` nd 
        LEFT JOIN `%s` ndn ON nd.id = ndn.directory 
        GROUP by nd.id
        ORDER BY MAX(ndn.id) IS NULL DESC, MAX(ndn.id) DESC", $nesteddir_table_directory, $nesteddir_table_directory_name));
    $num_directories = $wpdb->num_rows;
    $plural = ($num_directories==1)?__('Nested Directory', 'nesteddir'):__('name directories', 'nesteddir');

    echo "<p>"
        . sprintf(__('You currently have %d %s.', 'nesteddir'), $num_directories, $plural)
        . "</p>";
    ?>

    <table class="wp-list-table widefat fixed table-view-list nesteddir" cellspacing="0">
        <thead><?php nesteddir_render_admin_overview_table_headerfooter(); ?></thead>

        <tbody>
        <?php

        $alternate = "";
        foreach ( $directories as $directory )
        {
            $description = substr($directory->description, 0, 70);
            if(strlen($description) == '70')
            {
                $description .= "...";
            }

            $alternate = ( $alternate == "alternate" ) ? "" : "alternate";

            $entries = $wpdb->get_var(sprintf("SELECT COUNT(`id`) FROM %s WHERE directory=%d", $nesteddir_table_directory_name, $directory->id));
            $unpublished = $wpdb->get_var(sprintf("SELECT COUNT(`id`) FROM %s WHERE directory=%d AND `published` = 0", $nesteddir_table_directory_name, $directory->id));
            echo sprintf("
                <tr class='type-page status-publish hentry " . $alternate . " iedit author-self' valign='top'>
                    <th scope='row'>&nbsp;</th>
                    <td class='post-title page-title column-title title column-title has-row-actions column-primary'>
                        <strong><a class='row-title' href='" . $wp_url_path . "&sub=manage-directory&dir=%d' title='%s'>%s</a>
                            <span>&nbsp;%s</span></strong>
                        <div class='locked-info'>&nbsp;</div>
                        <div class='row-actions'>
                               <span class='manage'><a href='" . $wp_url_path . "&sub=manage-directory&dir=%d' title='%s'>%s</a>
                             | </span><span><a href='" . $wp_url_path . "&sub=manage-directory&dir=%d&display_all_names=no#anchor_add_form' title='%s'>%s</a>
                             | </span><span><a href='" . $wp_url_path . "&sub=edit-directory&dir=%d' title='%s'>%s</a>
                             | </span><span><a href='" . $wp_url_path . "&sub=import&dir=%d' title='%s'>%s</a>
                             | </span><span><a href='" . $wp_url_path . "&sub=export&dir=%d' title='%s'>%s</a>
                             | </span><span class='view'><a class='toggle-info' data-id='%s' href='" . $wp_url_path . "&sub=manage-directory&dir=%d#shortcode' title='%s'>%s</a></span>
                             | </span><span class='trash'><a class='nesteddir_confirmdelete submitdelete' href='" . $wp_url_path . "&delete_dir=%d&secnonce=%s' title=%s'>%s</a>
                        </div>
                    </td>
                    <td>
                        &nbsp; <strong title='%s'>%d</strong>
                        <br /><br />&nbsp;
                    </td>
                    <td>%d</td>
                    <td>%d</td>
                    </tr>",

                $directory->id, $directory->name, $directory->name,
                $description,
                $directory->id, __('Add, edit and remove names', 'nesteddir'), __('Manage names', 'nesteddir'),
                $directory->id, __('Go to the add-name-form on the Manage page', 'nesteddir'), __('Add name', 'nesteddir'),
                $directory->id, __('Edit name, description and appearance settings', 'nesteddir'), __('Settings', 'nesteddir'),
                $directory->id, __('Import entries for this directory by uploading a .csv file', 'nesteddir'), __('Import', 'nesteddir'),
                $directory->id, __('Download the contents of this directory as a .csv file', 'nesteddir'), __('Export', 'nesteddir'),
                $directory->id, $directory->id, __('Show the copy-paste shortcode for this directory', 'nesteddir'), __('Shortcode', 'nesteddir'),
                $directory->id, $wp_nonce, __('Permanently remove this Nested Directory', 'nesteddir'), __('Delete', 'nesteddir'),

                __('Number of names in this directory', 'nesteddir'),
                $entries,
                ($entries - $unpublished),
                $unpublished
            );
            echo sprintf("
                    <tr id='embed_code_%s' class='nesteddir_embed_code'>
                        <td>&nbsp;</td>
                        <td align='right'>%s</td>
                        <td colspan='3'>
                            <input value='[nesteddir dir=\"%s\"]' type='text' size='25' />
                        </td>
                    </tr>",
                $directory->id,
                __('To show your directory on your website, use the shortcode on the right.', 'nesteddir') . '<br />' .
                __('Copy the code and paste it in a post or in a page.', 'nesteddir') . '<br /><small>' .
                __('If you want to start with a specific character, like "J", use [nesteddir dir="X" start_with="j"].', 'nesteddir') . '</small>',
                $directory->id);
        }
        ?>
        </tbody>

        <tfoot><?php nesteddir_render_admin_overview_table_headerfooter(); ?></tfoot>
    </table>
    <?php
}


/**
 * A double purpose function for editing a nesteddir and
 * creating a new directory.
 * @param string $mode
 */
function nesteddir_edit($mode = 'edit')
{
    if( ! nesteddir_is_control_allowed() )
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'nesteddir') );
    }

    global $wpdb;
    global $nesteddir_table_directory;

    $wp_file = admin_url('admin.php');
    $wp_page = $_GET['page'];
    $wp_sub  = $_GET['sub'];
    $overview_url = sprintf("%s?page=%s", $wp_file, $wp_page, $wp_sub);
    $wp_url_path = sprintf("%s?page=%s", $wp_file, $wp_page);

    $directory_id = 0;
    if(! empty($_GET['dir']))
    {
        $directory_id = intval($_GET['dir']);
    }
    $directory = $wpdb->get_row("SELECT * FROM " . $nesteddir_table_directory . " WHERE `id` = " . $directory_id, ARRAY_A);

    echo '<div class="wrap">';
    if($mode == "new")
    {
        $table_heading  = __('Create new Nested Directory', 'nesteddir');
        $button_text    = __('Create', 'nesteddir');
        echo "<h2>" . __('Create new Nested Directory', 'nesteddir') . "</h2>";
        echo "<p>" . __('Complete the form below to create a new Nested Directory.', 'nesteddir');
    }
    else
    {
        $table_heading  = __('Edit this directory', 'nesteddir');
        $button_text    = __('Save Changes', 'nesteddir');
        echo "<h2>" . __('Edit Nested Directory', 'nesteddir') . "</h2>";
        echo "<p>"
            . sprintf(__('You are editing the name, description and settings of directory %s', 'nesteddir'),
                $directory['name']);
    }
    echo " <a style='float: right;' href='" . $overview_url . "'>" . __('Back to the directory overview', 'nesteddir') . "</a></p>";
    ?>

    <form name="add_name" method="post" action="<?php echo $wp_url_path; ?>">
        <table class="wp-list-table widefat" cellpadding="0">
            <thead>
            <tr>
                <th colspan="2">
                    <?php echo $table_heading; ?>
                    <input type="hidden" name="dir_id" value="<?php echo $directory_id; ?>">
                    <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="29%"><?php echo __('Title', 'nesteddir'); ?></td>
                <td width="70%"><input type="text" name="name" value="<?php echo (! empty($directory['name']) ? esc_html($directory['name']) : ''); ?>" size="20" class="nesteddir_widest"></td>
            </tr>
            <tr>
                <td><?php echo __('Description', 'nesteddir'); ?><br><small><?php echo __('Just for your own administration, it does not show on the front-end', 'nesteddir'); ?></small></td>
                <td><textarea name="description" rows="5"  class="nesteddir_widest"><?php echo (! empty($directory['description']) ? esc_textarea($directory['description']) : ''); ?></textarea></td>
            </tr>

            <?php
            $dir_boolean_settings = array(
                'show_title' => array(
                    'friendly_name' => __('Show title', 'nesteddir'),
                    'description' => __('Displays the title in a h3-heading', 'nesteddir'),
                ),
                'show_description' => array(
                    'friendly_name' => __('Show description', 'nesteddir'),
                    'description' => __('This is the description of the names on the front-end', 'nesteddir'),
                ),
                'show_submit_form' => array(
                    'friendly_name' => __('Submit form', 'nesteddir'),
                    'description' => __('Visitors can submit suggestions', 'nesteddir'),
                ),
                'show_submitter_name' => array(
                    'friendly_name' => __('Submitter name', 'nesteddir'),
                    'description' => __('Show the name of the submitter', 'nesteddir'),
                ),
                'show_search_form' => array(
                    'friendly_name' => __('Show search form', 'nesteddir'),
                    'description' => false,
                ),
                'search_in_description' => array(
                    'friendly_name' => __('Search in description', 'nesteddir'),
                    'description' => __('If yes, searches will be performed in the name and the description. If no, it will search in the names only', 'nesteddir'),
                ),
                'search_highlight' => array(
                    'friendly_name' => __('Highlight search term', 'nesteddir'),
                    'description' => __('If yes, the search term will be highlighted on the page so users can spot them easier', 'nesteddir'),
                ),
                'show_line_between_names' => array(
                    'friendly_name' => __('Show line between names', 'nesteddir'),
                    'description' => false,
                ),
                'show_character_header' => array(
                    'friendly_name' => __('Show new character heading', 'nesteddir'),
                    'description' => __('Show a B-heading after all words starting with A, which will that there is a new starting letter', 'nesteddir'),
                ),
                'show_all_names_on_index' => array(
                    'friendly_name' => __('Show all names by default', 'nesteddir'),
                    'description' => __('If no, user HAS to use the index before entries are shown', 'nesteddir'),
                ),
                'show_all_index_letters' => array(
                    'friendly_name' => __('Show all letters on index', 'nesteddir'),
                    'description' => __('If no, just A B D E are shown if there are no entries starting with C', 'nesteddir'),
                ),
                'jump_to_search_results' => array(
                    'friendly_name' => __('Jump to Nested Directory when searching', 'nesteddir'),
                    'description' => __('On the front-end, jump to the Nested Directory search box. Particularly useful if you have Nested Directory on a long page or onepage websites', 'nesteddir'),
                ),
            );

            $dir_options_settings = array(
                'nr_most_recent' => array(
                    'friendly_name' => __('Show most recent names', 'nesteddir'),
                    'description' => __('If No, frontend will not show \'Latest\' option.', 'nesteddir'),
                    'options' => array(0 => __('No', 'nesteddir'), 3 => 3, 5 => 5, 10 => 10, 25 => 25, 50 => 50, 100 => 100)
                ),
                'nr_words_description' => array(
                    'friendly_name' => __('Limit amount of words in description', 'nesteddir'),
                    'description' => __('Display a "read-more" link on the website if the description exceeds X characters.', 'nesteddir'),
                    'options' => array(0 => __('No', 'nesteddir'), 10 => 10, 20 => 20, 25 => 25, 50 => 50, 100 => 100)
                ),
                'nr_columns' => array(
                    'friendly_name' => __('Number of columns', 'nesteddir'),
                    'description' => __('The number of (vertical) columns to display the names in', 'nesteddir'),
                    'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4)
                ),
            );

            foreach($dir_boolean_settings as $setting_name => $setting_props)
            {
                nesteddir_render_admin_setting_boolean($directory, $setting_name, $setting_props['friendly_name'], $setting_props['description']);
            }

            foreach($dir_options_settings as $setting_name => $setting_props)
            {
                nesteddir_render_admin_setting_options($directory, $setting_name, $setting_props['friendly_name'], $setting_props['description'], $setting_props['options']);
            }
            ?>
            <tr>
                <td><?php echo __('Name term', 'nesteddir'); ?><br>
                    <small><?php echo __('Alternative (plural) term for "names", i.e. movies', 'nesteddir'); ?></small></td>
                <td><input type="text" name="name_term" value="<?php echo (! empty($directory['name_term']) ? $directory['name_term'] : ''); ?>" size="20">
                    <br>
                    <small><?php echo __('If you provide an alternative term for name, your website will display:', 'nesteddir'); ?>
                        "<i><?php echo sprintf(__('There are currently %d %s in this directory', 'nesteddir'), 1337, __('movies', 'nesteddir')); ?></i>"<br>
                        <?php echo __('When left blank, the word "names" will be displayed.', 'nesteddir'); ?></small></td>
            </tr>
            <tr>
                <td><?php echo __('Name term (singular)', 'nesteddir'); ?><br>
                    <small><?php echo __('The singular term for "names", i.e. movie', 'nesteddir'); ?></small></td>
                <td><input type="text" name="name_term_singular" value="<?php echo (! empty($directory['name_term_singular']) ? $directory['name_term_singular'] : ''); ?>" size="20"></td>
            </tr>
            <tr>
                <td>
                    <?php wp_nonce_field( 'nesteddir_dirmanagement','nesteddir_adminnonce' ); ?>
                </td>
                <td>
                    <input type="submit" name="submit" class="button button-primary button-large"
                           value="<?php echo $button_text; ?>" />

                    <a class='button button-large' href='<?php echo $overview_url; ?>'>
                        <?php echo __('Cancel', 'nesteddir'); ?>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <?php

}


/**
 * Handle the names in the Nested Directory
 *  - Display all names
 *  - Edit names (ajax and 'oldskool' view)
 *  - Create new names
 */
function nesteddir_names()
{
    if(! nesteddir_is_control_allowed() )
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'nesteddir') );
    }

    global $wpdb;
    global $nesteddir_table_directory;
    global $nesteddir_table_directory_name;
    $nesteddir_settings = get_option('nesteddir_general_option');
    $show_all_names = true;

    if(! empty($_GET['delete_name']) && is_numeric($_GET['delete_name']) && check_admin_referer('nesteddir-action','secnonce'))
    {
        $name = $wpdb->get_var(sprintf("SELECT `name` FROM %s WHERE id=%d", $nesteddir_table_directory_name, $_GET['delete_name']));
        $wpdb->delete($nesteddir_table_directory_name, array('id' => $_GET['delete_name']), array('%d'));
        echo "<div class='updated'><p>"
            . sprintf(__('Name %s deleted', 'nesteddir'), "<i>" . esc_html($name) . "</i>")
            . "</p></div>";
    }
    else if(! empty($_POST['name_id']))
    {
        if(empty($_POST['nesteddir-nonce']) || ! wp_verify_nonce( $_POST['nesteddir-nonce'], 'nesteddir-action'))
        {
            echo nesteddir_get_csrf_error_message($_POST['name']);
            exit;
        }

        $description = $_POST['description'];
        if( ! empty( $nesteddir_settings['simple_wysiwyg_editor'] ) )
        {
            $description = nl2br($description);
        }

        $wpdb->update(
            $nesteddir_table_directory_name,
            array(
                'name'          => wp_kses_post($_POST['name']),
                'letter'        => nesteddir_get_first_char($_POST['name']),
                'description'   => wp_kses_post($description),
                'published'     => (int)$_POST['published'],
                'submitted_by'  => wp_kses_post($_POST['submitted_by']),
            ),
            array('id' => intval($_POST['name_id']))
        );

        if($_POST['action'] == "nesteddir_ajax_names")
        {
            $refresh_url = str_replace('edit_name=', '', $_SERVER['HTTP_REFERER']);
            echo '<p>';
            echo sprintf(__('Name %s updated', 'nesteddir'), "<i>" . esc_html($_POST['name']) . "</i>");
            echo '. <small><i>' . __('Will be visible when the page is refreshed.', 'nesteddir') . '</i> ';
            echo ' <a href="' . $refresh_url . '">' . __('Refresh now', 'nesteddir') . '</a></small>';
            echo '</p>';
            exit;
        }

        echo "<div class='updated'><p>"
            . sprintf(__('Name %s updated', 'nesteddir'), "<i>" . esc_html($_POST['name']) . "</i>")
            . "</p></div>";

        unset($_GET['edit_name']);
    }
    else if(! empty($_POST['name']))
    {
        if(! wp_verify_nonce( $_POST['nesteddir-nonce'], 'nesteddir-action'))
        {
            echo nesteddir_get_csrf_error_message($_POST['name']);
            exit;
        }

        $name_exists = nesteddir_name_exists_in_directory($_POST['name'], $_POST['directory']);
        if($name_exists && $_POST['action'] == "nesteddir_ajax_names")
        {
            echo '<p>';
            echo sprintf(__('Name %s was already on the list, so it was not added', 'nesteddir'),
                '<i>' . esc_html($_POST['name']) . '</i>');
            echo '</p>';
            exit;
        }

        $description = $_POST['description'];
        if( ! empty( $nesteddir_settings['simple_wysiwyg_editor'] ) )
        {
            $description = nl2br($description);
        }

        $wpdb->insert(
            $nesteddir_table_directory_name,
            array(
                'directory'     => (int)$_POST['directory'],
                'name'          => wp_kses_post($_POST['name']),
                'letter'        => nesteddir_get_first_char($_POST['name']),
                'description'   => wp_kses_post($description),
                'published'     => (int)$_POST['published'],
                'submitted_by'  => wp_kses_post($_POST['submitted_by']),
            ),
            array('%d', '%s', '%s', '%s', '%d', '%s')
        );

        if($_POST['action'] == "nesteddir_ajax_names")
        {
            echo '<p>';
            printf(__('New name %s added', 'nesteddir'), '<i>' . esc_html($_POST['name']) . '</i> ');
            echo '. <small><i>' . __('Will be visible when the page is refreshed.', 'nesteddir') . '</i> ';
            echo ' <a href="">' . __('Refresh now', 'nesteddir') . '</a></small>';
            echo '</p>';
            exit;
        }

        echo "<div class='updated'><p><strong>"
            . sprintf(__('New name %s added', 'nesteddir'), "<i>" . esc_html($_POST['name']) . "</i> ")
            . "</strong></p></div>";
    }
    else if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if($_POST['action'] == "nesteddir_ajax_names")
        {
            echo '<p>' . __('Please fill in at least a name', 'nesteddir') . '</p>';
            exit;
        }

        echo "<div class='error'><p><strong>"
            . __('Please fill in at least a name', 'nesteddir')
            . "</strong></p></div>";
    }

    $directory_id = intval($_GET['dir']);

    $wp_file = admin_url('admin.php');
    $wp_page = $_GET['page'];
    $wp_sub  = $_GET['sub'];
    $overview_url = sprintf("%s?page=%s", $wp_file, $wp_page);
    $wp_url_path = sprintf("%s?page=%s&sub=%s&dir=%d", $wp_file, $wp_page, $wp_sub, $directory_id);
    $wp_ndir_path = sprintf("%s?page=%s&sub=%s&dir=%d", $wp_file, $wp_page, 'manage-directory', $directory_id);
    $nesteddir_settings = get_option('nesteddir_general_option');
    $wp_nonce = wp_create_nonce('nesteddir-action');

    $published_status = '0,1';
    $emphasis_class = 's_all';
    if(! empty($_GET['status']) && $_GET['status'] == 'published')
    {
        $published_status = '1';
        $emphasis_class = 's_published';
    }
    else if(! empty($_GET['status']) && $_GET['status'] == 'unpublished')
    {
        $published_status = '0';
        $emphasis_class = 's_unpublished';
    }

    $directory = $wpdb->get_row("SELECT * FROM " . $nesteddir_table_directory . " WHERE `id` = " . $directory_id, ARRAY_A);
    $names = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE `directory` = %d AND `published` IN (%s) ORDER BY `name` ASC",
        $nesteddir_table_directory_name, $directory_id, $published_status));

    echo '<div class="wrap">';
    echo "<h2>" . sprintf(__('Manage names for %s', 'nesteddir'), $directory['name']) . "</h2>";
    ?>

    <?php
    if(! empty($_GET['edit_name']))
    {
        $name = $wpdb->get_row(sprintf("SELECT * FROM `%s` WHERE `id` = %d",
            $nesteddir_table_directory_name, $_GET['edit_name']), ARRAY_A);
        $table_heading = __('Edit a name', 'nesteddir');
        $save_button_txt = __('Save name', 'nesteddir');
        $show_all_names = false;
    }
    else
    {
        $table_heading = __('Add a new name', 'nesteddir');
        $save_button_txt = __('Add name', 'nesteddir');
        $name = array('name' => null, 'description' => null, 'submitted_by' => null);
    }

    ?>
    <span style='float: right;'>
        <a href='<?php echo $overview_url; ?>'><?php _e('Back to the directory overview', 'nesteddir'); ?></a>
    </span>

    <p>&nbsp;</p>

    <div class="hidden" id="add_result"></div>

    <a name="anchor_add_form"></a>
    <form name="add_name" id="add_name_ajax" method="post" action="<?php echo $wp_url_path; ?>">
        <table class="wp-list-table widefat" cellpadding="0">
            <thead>
            <tr>
                <th width="18%"><?php echo $table_heading; ?>
                    <input type="hidden" name="directory" value="<?php echo $directory_id; ?>">
                    <input type="hidden" name="nesteddir-nonce" value="<?php echo $wp_nonce; ?>">
                    <?php
                    if(! empty($_GET['edit_name']))
                    {
                        echo '<input type="hidden" name="name_id" id="edit_name_id" value="' . intval($_GET['edit_name']) . '">';
                    }
                    ?>
                    <input type="hidden" name="action" value="0" id="add_form_ajax_submit" />
                </th>
                <th align="right">

                    <label id="input_compact" title="<?php echo __('Show the compact form, showing only the name, always published)', 'nesteddir'); ?>">
                        <input type="radio" name="input_mode" />
                        <?php echo __('Quick add view', 'nesteddir'); ?>
                    </label>
                    <label id="input_extensive" title="<?php echo __('Show the full form, which allows you to enter a description and submitter', 'nesteddir'); ?>">
                        <input type="radio" name="input_mode" />
                        <?php echo __('Full add view', 'nesteddir'); ?>
                    </label>

                </th>
            </tr>
            </thead>
            <tbody>
            <tr id="add_name">
                <td width="18%"><?php echo __('Name', 'nesteddir'); ?></td>
                <td width="82%"><input type="text" name="name" value="<?php echo esc_html($name['name']); ?>" size="20" class="nesteddir_widest"></td>
            </tr>
            <tr id="add_description">
                <td><?php echo __('Description', 'nesteddir'); ?></td>
                <td><?php
                    /* Determine the kind of editor to use */
                    if(empty($nesteddir_settings) || empty($nesteddir_settings['simple_wysiwyg_editor']))
                    {
                        echo '<textarea name="description" rows="5" class="nesteddir_widest">' . esc_textarea($name['description']) . '</textarea>';
                    }
                    else
                    {
                        wp_editor($name['description'], 'description', array('textarea_rows' => 5, 'textarea_name' => 'description'));
                    }
                    ?>
                    <small><strong><?php echo __('Please be careful!', 'nesteddir'); ?></strong>
                        <?php echo __('HTML markup is allowed and will we printed on your website and in the WordPress admin.', 'nesteddir'); ?></small></td>
            </tr>
            <tr id="add_published">
                <td><?php echo __('Published', 'nesteddir'); ?></td>
                <td>
                    <input type="radio" name="published" id="published_yes" value="1" checked="checked">
                    <label for="published_yes"><?php echo __('Yes', 'nesteddir') ?></label>

                    <input type="radio" name="published" id="published_no" value="0"
                        <?php
                        if(isset($name['published']) && empty($name['published']))
                        {
                            echo 'checked="checked"';
                        }?>>
                    <label for="published_no"><?php echo __('No', 'nesteddir') ?></label>
                </td>
            </tr>
            <tr id="add_submitter">
                <td><?php echo __('Submitted by', 'nesteddir'); ?></td>
                <td><input type="text" name="submitted_by" value="<?php echo esc_html($name['submitted_by']); ?>" size="20" class="nesteddir_widest"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" id="add_button" name="Submit" class="button button-primary button-large"
                           value="<?php echo $save_button_txt; ?>" />
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <?php

    /* Don't show all the names when the user explicitly started the adding-mode */
    if(! empty($_GET['display_all_names']) && htmlspecialchars($_GET['display_all_names']) == "no")
    {
        $show_all_names = false;
    }

    if($show_all_names)
    {
        ?>
        <p>
            <a class='s_all' href='<?php echo $wp_url_path; ?>&status=all'><?php _e('all', 'nesteddir'); ?></a> |
            <a class='s_published' href='<?php echo $wp_url_path; ?>&status=published'><?php _e('published', 'nesteddir'); ?></a> |
            <a class='s_unpublished' href='<?php echo $wp_url_path; ?>&status=unpublished'><?php _e('unpublished', 'nesteddir'); ?></a>
        </p>

        <?php
        $name_filter = array();
        $num_names = 0;

        $search_value = '';
        if(! empty($_GET['s']))
        {
            $search_value = htmlspecialchars($_GET['s']);
            $name_filter['containing'] = $search_value;

            $names = nesteddir_get_directory_names($directory, $name_filter);
            $num_names = count($names);
        }

        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $search_get_url = array();
        if(! empty($parsed_url['query']))
        {

            parse_str($parsed_url['query'], $search_get_url);
        }
        unset($search_get_url['s']);

        echo '<form method="get" action="">';
        echo '<p class="search-box">';
        foreach($search_get_url as $key_name=>$value)
        {
            if($key_name == 'nesteddir_startswith')
            {
                continue;
            }
            echo "<input type='hidden' name='" . htmlspecialchars($key_name) . "' value='" . htmlspecialchars($value) . "' />";
        }
        echo "<input type='search' class='tagsdiv newtag' name='s' id='nesteddir-search-input-box' value='" . $search_value . "' placeholder='" . __('Search', 'nesteddir') . "...' />";
        echo "<input type='submit' id='nesteddir-search-input-button' class='button' value='" . __('Search', 'nesteddir') . "' />";


        echo '</p>';
        if(empty($name_filter['character']) && ! empty($search_value))
        {
            echo '<br><br><p class="search-box">';
            if(empty($directory['name_term']))
            {
                echo sprintf(__('There are %d names in this directory containing the search term %s.', 'nesteddir'), $num_names, "<em><strong>" . $search_value . "</strong></em>");
            }
            else
            {
                echo sprintf(__('There are %d %s in this directory containing the search term %s.', 'nesteddir'), $num_names, $directory['name_term'], "<em><strong>" . $search_value . "</strong></em>");
            }
            echo ' <a href="' . $wp_ndir_path . '"><strong><em>' . __('Clear results', 'nesteddir') . '</em></strong></a>';
            echo "</p>";
        }
        else
        {
            echo "<br><br>";
        }
        echo "</form>";

        ?>

        <table class="wp-list-table widefat nesteddir_names fixed" cellpadding="0">
            <thead>
            <tr>
                <th width="18%"><?php echo __('Name', 'nesteddir'); ?></th>
                <th width="52%"><?php echo __('Description', 'nesteddir'); ?></th>
                <th width="12%"><?php echo __('Submitter', 'nesteddir'); ?></th>
                <th width="9%"><?php echo __('Published', 'nesteddir'); ?></th>
                <th width="12%"><?php echo __('Manage', 'nesteddir'); ?></th>
                <th width="5%"><?php echo __('ID', 'nesteddir'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(empty($name))
            {
                echo sprintf("<tr class='empty-directory'><td colspan='5'>%s</td></tr>",
                    __('Currently, there are no names in this directory..', 'nesteddir'));
            }

            foreach($names as $name)
            {
                if(is_array($name))
                {
                    $name = (object)$name;
                }

                echo sprintf("
                <tr>
                    <td>%s</td><td>%s</td><td>%s</td><td><label class='nesteddir_switch'><input class='toggle_published' type='checkbox' id='nid_%d' data-nameid='%d' %s><span class='nesteddir_slider' title='%s'></span></label></td>
                    <td><a class='button button-primary button-small' href='" . $wp_url_path . "&edit_name=%d#anchor_add_form'>%s</a>
                        <a class='button button-small' href='" . $wp_url_path . "&delete_name=%d&secnonce=%s'>%s</a>
                    </td><td>%s</td>
                </tr>",
                    $name->name,
                    html_entity_decode(stripslashes($name->description)),
                    sanitize_text_field(esc_html($name->submitted_by)),
                    $name->id,
                    $name->id,
                    ! empty($name->published)?' checked':'',
                    __('Toggle published status', 'nesteddir'),
                    $name->id, __('Edit', 'nesteddir'),
                    $name->id, $wp_nonce, __('Delete', 'nesteddir'),
                    $name->id);
            }
            ?>
            </tbody>
        </table>

        <p>&nbsp;</p>

        <?php
    } else {
        echo "<p>";
        echo __('You are currently in editing or adding-mode, so not all the names are shown.', 'nesteddir');
        echo " <a class='s_all' href='" . $wp_url_path . "&status=all'>" . __('View the names', 'nesteddir') . "</a>";
        echo "</p>";
    }

    wp_add_inline_script('nesteddir_admin', "jQuery('" . $emphasis_class . "').css('font-weight', 'bold');");

    if(! empty($_GET['edit_name']))
    {
        wp_add_inline_script('nesteddir_admin', "jQuery('#input_extensive').trigger('click');");
    }
}


/**
 * Create a directory with just a name and then go to the import page
 */
function nesteddir_quick_import()
{
    global $wpdb;
    global $nesteddir_table_directory;

    echo '<div class="wrap">';
    echo "<h2>"
        . __('Import names into a new directory', 'nesteddir')
        . "</h2>";

    if(! empty($_POST['mode']) && $_POST['mode'] == "new")
    {
        $cleaned_name = sanitize_text_field($_POST['name']);
        if(empty($cleaned_name)){
            $cleaned_name = __('Quick import', 'nesteddir');
        }

        $wpdb->insert(
            $nesteddir_table_directory,
            array(
                'name'                    => $cleaned_name,
                'description'             => __('Quick import', 'nesteddir'),
                'show_title'              => 1,
                'show_description'        => 1,
                'show_submit_form'        => 0,
                'show_search_form'        => 1,
                'show_submitter_name'     => 0,
                'show_line_between_names' => 0,
                'show_character_header'   => 0,
                'show_all_names_on_index' => 0,
                'show_all_index_letters'  => 1,
                'jump_to_search_results'  => 1,
                'nr_columns'              => 1,
                'nr_most_recent'          => 0,
                'nr_words_description'    => 0,
                'name_term'               => '',
                'name_term_singular'      => '',
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
        );

        $import_url = sprintf("%s?page=%s&sub=%s&dir=%d", admin_url('admin.php'), $_GET['page'], 'import', $wpdb->insert_id);

        echo "<div class='updated'><p>"
            . sprintf(__('Directory %s created.', 'nesteddir'), "<i>" . $cleaned_name . "</i>")
            . "</p></div>";

        echo __('Loading the import page...', 'nesteddir');
        echo "<script>window.location.href = '" . $import_url . "';</script>";
    }
    ?>

    <form name="add_name" method="post" action="<?php echo sprintf("%s?page=%s&sub=quick-import", admin_url('admin.php'), $_GET['page']); ?>">
        <table class="wp-list-table widefat" cellpadding="0">
            <thead>
            <tr>
                <th colspan="2">
                    <?php echo __('What would be the title of your new directory to import items into?', 'nesteddir'); ?>
                    <input type="hidden" name="mode" value="new">
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="29%"><?php echo __('Title', 'nesteddir'); ?></td>
                <td width="70%"><input type="text" name="name" value="" size="20" class="nesteddir_widest"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" name="submit" class="button button-primary button-large"
                           value="<?php echo __('Next step', 'nesteddir') ?>" />
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <?php
}



/**
 * Import names from a csv file into directory
 */
function nesteddir_import()
{
    if(! nesteddir_is_control_allowed() )
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'nesteddir') );
    }

    global $wpdb;
    global $nesteddir_table_directory;
    global $nesteddir_table_directory_name;

    $directory_id = intval($_GET['dir']);
    $import_success = false;
    $use_utf_import = false;

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        check_admin_referer('import-upload');
        $file = wp_import_handle_upload();

        if( isset($file['error']))
        {
            echo $file['error'];
            return;
        }

        if(! empty($_POST['empty_dir_on_import']))
        {
            $wpdb->delete( $nesteddir_table_directory_name, array( 'directory' => $directory_id ) );
        }

        if(! empty($_POST['use_utf8_import']))
        {
            $use_utf_import = true;
        }

        $csv = array_map( 'str_getcsv', file($file['file']) );

        wp_import_cleanup($file['id']);
        array_shift($csv);

        $names_error = 0;
        $names_imported = 0;
        $names_duplicate = 0;
        foreach($csv as $entry)
        {
            if ( ! $prepared_row = nesteddir_prepared_import_row($entry, 1, $use_utf_import) )
            {
                continue;
            }

            if ( nesteddir_name_exists_in_directory($prepared_row['name'], $directory_id) )
            {
                $names_duplicate++;
                continue;
            }

            $db_res = $wpdb->insert(
                $nesteddir_table_directory_name,
                array(
                    'directory'     => $directory_id,
                    'name'          => sanitize_text_field($prepared_row['name']),
                    'letter'        => nesteddir_get_first_char($prepared_row['name']),
                    'description'   => ! empty($prepared_row['description']) ? wp_kses_post($prepared_row['description']) : '',
                    'published'     => ! empty($prepared_row['published']) ? $prepared_row['published'] : '',
                    'submitted_by'  => ! empty($prepared_row['submitted_by']) ? wp_kses_post($prepared_row['submitted_by']) : '',
                ),
                array('%d', '%s', '%s', '%s', '%d', '%s')
            );

            if($db_res === false)
            {
                $names_error++;
            }
            else
            {
                $names_imported++;
            }
        }

        $notice_class = 'updated';
        $import_success = true;
        $import_message = sprintf(__('Imported %d entries in this directory', 'nesteddir'), $names_imported);

        if($names_imported === 0)
        {
            $notice_class = 'error';
            $import_success = false;
            $import_message = __('Could not import any names into Nested Directory', 'nesteddir');
        }

        if($names_error > 0)
        {
            $notice_class = 'error';
            $import_success = false;
            if($names_imported === 0)
            {
                $import_message .= "! ";
            }
            $import_message .= sprintf(__('There were %d names that produces errors with the WordPress database on import', 'nesteddir'), $names_error);
        }

        if($names_duplicate > 0)
        {
            $ignored = (count($csv)==$names_duplicate)?__('all', 'nesteddir'):$names_duplicate;
            echo '<div class="error" style="border-left: 4px solid #ffba00;"><p>'
                . sprintf(__('Ignored %s names, because they were duplicate (already in the directory)', 'nesteddir'), $ignored)
                . '</p></div>';
        }
        elseif($names_imported === 0)
        {
            $import_message .= ', ' . __('please check your .csv-file', 'nesteddir');
        }

        echo '<div class="' . $notice_class . '"><p>' . $import_message . '</p></div>';
    }

    $wp_file = admin_url('admin.php');
    $wp_page = $_GET['page'];
    $wp_sub  = $_GET['sub'];
    $overview_url = sprintf("%s?page=%s", $wp_file, $wp_page);
    $wp_url_path = sprintf("%s?page=%s&sub=%s&dir=%d", $wp_file, $wp_page, $wp_sub, $directory_id);
    $wp_ndir_path = sprintf("%s?page=%s&sub=%s&dir=%d", $wp_file, $wp_page, 'manage-directory', $directory_id);

    $directory = $wpdb->get_row("SELECT * FROM " . $nesteddir_table_directory . " WHERE `id` = " . $directory_id, ARRAY_A);

    echo '<div class="wrap">';
    echo '<h2>' . sprintf(__('Import names for %s', 'nesteddir'), $directory['name']) . '</h2>';
    echo '<div class="narrow nesteddir_import_page"><p>';
    if(! $import_success && empty($names_duplicate))
    {
        echo __('Use the upload form below to upload a .csv-file containing all of your names (in the first column), description and submitter are optional.', 'nesteddir') . ' ';
        echo '<h4>' . __('If you saved it from Excel or OpenOffice, please ensure that:', 'nesteddir') . '</h4> ';
        echo '<ol><li>' . __('There is a header row (this contains the column names, the first row will NOT be imported)', 'nesteddir');
        echo '</li><li>' . __('Fields are encapsulated by double quotes', 'nesteddir');
        echo '</li><li>' . __('Fields are comma-separated', 'nesteddir');
        echo '</li><li>' . __('If such an option presents itself, save as UTF-8 (not ANSI)', 'nesteddir');
        echo '</li></ol>';
        echo '<p>' . sprintf(__('Please check out %s first and ensure your file is formatted the same.', 'nesteddir'),
                '<a href="http://plugins.svn.wordpress.org/nesteddir/assets/nesteddir-import-example.csv" target="_blank" rel="noopener noreferrer">' .
                __('the example import file', 'nesteddir') . '</a>') . '</p>';
        echo '<p><em>' . __('One of the best ways to verify if your file has the right format is opening it in a plain text editor, like Windows Notepad, Geany, SublimeText or Notepad++.', 'nesteddir') . '</em></p>';
        echo '<br>';
        echo '<h4>' . __('If uploading or importing fails, these are your options', 'nesteddir') . '</h4><ol>';
        echo '<li>
                <a href="https://www.freefileconvert.com" target="_blank" rel="noopener noreferrer">' .
                __('Use an online File Convertor', 'nesteddir') . '</a>
              </li>
              <li>
                <a href="https://wiki.openoffice.org/wiki/Documentation/OOo3_User_Guides/Calc_Guide/Saving_spreadsheets#Saving_as_a_CSV_file">OpenOffice csv-export help</a>
              </li>
              <li>
                <a href="https://support.office.com/en-us/article/Import-or-export-text-txt-or-csv-files-e8ab9ff3-be8d-43f1-9d52-b5e8a008ba5c?CorrelationId=fa46399d-2d7a-40bd-b0a5-27b99e96cf68&ui=en-US&rs=en-US&ad=US#bmexport">Excel csv-export help</a>
              </li>
              <li>';
        echo sprintf(__('If everything else fails, you can always ask a question at the %s.', 'nesteddir'),
                '<a href="https://wordpress.org/support/plugin/nesteddir" target="_blank" rel="noopener noreferrer">' .
                __('plugin support forums', 'nesteddir') . '</a>') . ' ' .
                __('Please make sure you include all the steps you did to create the file you are trying to import.', 'nesteddir');
        echo '</li></ol></p>';
        echo '<p><em>' . __('When using the upload function, script-tags are being removed for security reasons.', 'nesteddir') . '</em></p>';
        echo '<br>';

        if(! function_exists('str_getcsv'))
        {
            echo '<div class="error"><p>';
            echo __('Nested Directory Import requires at least PHP 5.3, you seem to have an older version. Importing names will not work for your website.', 'nesteddir');
            echo '</p></div>';
        }

        echo '<h3>' . __('Upload your .csv-file', 'nesteddir') . '</h3>';
        wp_import_upload_form($wp_url_path);
    }
    echo '</div></div>';
    echo '<a href="' . $wp_ndir_path . '">' . sprintf(__('Back to %s', 'nesteddir'), '<i>' . $directory['name'] . '</i>') . '</a>';
    echo ' | ';
    echo '<a href="' . $overview_url . '">' . __('Go to Nested Directory Overview', 'nesteddir') . '</a>';
}


/**
 * Page to export names from a directory file as a .csv-file
 */
function nesteddir_export()
{
    if(! nesteddir_is_control_allowed() )
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'nesteddir') );
    }

    global $wpdb;
    global $nesteddir_table_directory;

    $directory = $wpdb->get_row("SELECT * FROM " . $nesteddir_table_directory . " WHERE `id` = " . intval($_GET['dir']), ARRAY_A);

    $names = nesteddir_get_directory_names($directory);

    echo '<table id="export_names" class="hidden"><thead><tr><th>name</th><th>description</th><th>submitter</th></tr></thead><tbody>';
    foreach($names as $entry)
    {
        $description = str_replace(array("\n", "\r"), '', $entry['description']);
        echo '<tr><td>' . esc_html($entry['name']) . '</td><td>' . esc_html($description) . '</td><td>' . esc_html($entry['submitted_by']) . '</td></tr>';
    }
    echo '</tbody></table>';

    /* Notify the user of possible not-working export functionality */
    if(stripos($_SERVER['HTTP_USER_AGENT'], 'Chrome') === false && stripos($_SERVER['HTTP_USER_AGENT'], 'Firefox') === false)
    {
        echo '<div class="notice notice-warning"><p>';
        echo __('Nested Directory Export works best in Mozilla Firefox, Google Chrome and Internet Explorer 10+.', 'nesteddir') . ' ';
        echo __('If you encounter problems (or it does not export) in Internet Explorer or Microsoft Edge, please try another browser.', 'nesteddir');
        echo '</div>';
    }

    echo '<div class="wrap">';
    echo '<h2>' . sprintf(__('Export directory %s', 'nesteddir'), $directory['name']) . '</h2>';
    echo '<div class="narrow nesteddir_export"><p>';
    echo __('Click the Export button to download a .csv file with the contents of your directory.', 'nesteddir');
    echo '</p><p><a href="#" id="export_nesteddir_names_button" class="button button-primary">' . __('Export', 'nesteddir') . '</a></p>';
    echo '<p><a href="' . admin_url('admin.php') . '?page=nesteddir">' . __('Go to Nested Directory Overview', 'nesteddir') . '</a></p>';
}


/**
 * Proxy for the AJAX request to switch published-statusses
 * No params, assumes POST
 */
function nesteddir_ajax_switch_name_published_status()
{
    $name_id = intval($_POST['name_id']);
    if( ! empty($name_id) )
    {
        echo nesteddir_switch_name_published_status($name_id);
        exit;
    }

    echo 'Error!';
    exit;
}


/**
 * Add the javascript and css resources to the admin
 */
function nesteddir_admin_add_resources()
{
    $admin_js_translation = array(
        'delete_question' => __('Are you sure you want to delete this Nested Directory?', 'nesteddir'),
        'empty_directory_on_import' => __('Remove all the entries of this directory before starting the import', 'nesteddir'),
        'use_utf8_import' => __('Use special import option (use only when importing has failed before and if you are using non-latin characters)', 'nesteddir'),
    );

    wp_register_script('nesteddir_admin', plugins_url('nesteddir_admin.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('nesteddir_admin', 'nesteddir_translation', $admin_js_translation);
    wp_enqueue_script('nesteddir_admin');

    wp_enqueue_style('nesteddir_admin', plugins_url('nesteddir_admin.css', __FILE__), '', '1.0');
}
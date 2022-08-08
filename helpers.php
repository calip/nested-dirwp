<?php
/**
 * This file is part of the NestedDir plugin for WordPress
 */


/**
 * Check whether the mb_string extension is supported on this installation
 * @return bool
 */
function nesteddir_is_multibyte_supported()
{
    if(function_exists('mb_strtoupper'))
    {
        return true;
    }

    return false;
}


/**
 * Return the first character of a word,
 * or hashtag, may the word begin with a number
 * @param $name
 * @return string
 */
function nesteddir_get_first_char($name)
{
    if(nesteddir_is_multibyte_supported())
    {
        $first_char = mb_strtoupper(mb_substr($name, 0, 1));
    }
    else
    {
        $first_char = strtoupper(substr($name, 0, 1));
    }

    if(is_numeric($first_char))
    {
        $first_char = '#';
    }

    return $first_char;
}


/**
 * Prepare an associative array to be used for the csv importer
 * @param array $row (csv-row)
 * @param int $published (optional)
 * @param bool $utf8_encode (optional)
 * @return array|bool
 */
function nesteddir_prepared_import_row($row, $published=1, $utf8_encode = false)
{
    // Don't continue when there is no name to add (first column in csv-row)
    if( empty( $row[0] ) )
    {
        return false;
    }

    $row_props = array( 'name', 'description', 'submitted_by' );
    $prepared_row = array('published' => $published);
    foreach($row_props as $index=>$prop)
    {
        if(! empty($row[$index]))
        {
            if($utf8_encode == true)
            {
                $row[$index] = utf8_encode($row[$index]);
            }

            $prepared_row[$prop] = $row[$index];
        }
    }

    return $prepared_row;
}


/**
 * Return localized yes or no based on a variable
 * @param $var
 * @return string
 */
function nesteddir_yesno($var)
{
    if(! empty($var))
    {
        return __('Yes', 'nesteddir');
    }

    return __('No', 'nesteddir');
}


/**
 * Switches the published state of a name and returns the human readable value
 * @param (numeric) $name_id
 * @return string
 */
function nesteddir_switch_name_published_status($name_id)
{
    global $wpdb;
    global $nesteddir_table_directory_name;

    $wpdb->query($wpdb->prepare("UPDATE `$nesteddir_table_directory_name` SET `published`=1 XOR `published` WHERE id=%d",
        intval($name_id)));

    return $wpdb->get_var(sprintf("SELECT `published` FROM `%s` WHERE id=%d", $nesteddir_table_directory_name, intval($name_id)));
}


/**
 * Check if a given name already exists in a Nested Directory
 * @param $name
 * @param $directory
 * @return bool
 */
function nesteddir_name_exists_in_directory($name, $directory)
{
    global $wpdb;
    global $nesteddir_table_directory_name;
    $nesteddir_settings = get_option('nesteddir_general_option');

    if ( ! empty($nesteddir_settings['disable_duplicate_protection']) ) {
        return false;
    }

    $wpdb->get_results(sprintf("SELECT 1 FROM `%s` WHERE `name` = '%s' AND `directory` = %d",
        $nesteddir_table_directory_name, esc_sql($name), intval($directory)));

    return (bool)$wpdb->num_rows;
}


/**
 * Construct a plugin URL
 * @param string $index
 * @param null $exclude
 * @return string
 */
function nesteddir_make_plugin_url($index = 'nesteddir_startswith', $exclude = null, $directory = null)
{
    $url = array();
    $parsed = parse_url($_SERVER['REQUEST_URI']);
    if(! empty($parsed['query']))
    {
        parse_str($parsed['query'], $url);
    }

    if(! empty($directory))
    {
        $url['dir'] = (int)$directory;
    }

    if(! empty($exclude))
    {
        unset($url[$exclude]);
    }

    unset($url[$index]);
    unset($url['page_id']);
    $paste_char = '?';
    if(strpos(get_permalink(), '?') !== false)
    {
        $paste_char = '&';
    }
    if(! empty($index))
    {
        $url[$index] = '';
    }

    return get_permalink() . $paste_char . http_build_query($url);
}


/**
 * Get the names of given directory, maybe only with the char?
 * @param $directory
 * @param array $name_filter
 * @return mixed
 */
function nesteddir_get_directory_names($directory, $name_filter = array())
{
    if($directory === null)
    {
        return array();
    }

    global $wpdb;
    global $nesteddir_table_directory_name;

    // Init var
    $use_regex = false;
    $sql_prepared_values = array();

    // Begin SQL Query
    $sql_query  = "
        SELECT * 
        FROM `" . $nesteddir_table_directory_name . "` 
        WHERE `directory` = %d 
          AND `published`   = %d ";
    $sql_prepared_values[] = $directory['id'];
    $sql_prepared_values[] = '1';


    if(! empty($name_filter['containing']))
    {
        if(nesteddir_is_multibyte_supported())
        {
            $first_chars = mb_substr($name_filter['containing'], 0, 7);
        }
        else
        {
            $first_chars = substr($name_filter['containing'], 0, 7);
        }

        /* This means: perform an exact search */
        if(in_array($first_chars, array("\&quot;", '\"')))
        {
            $name_filter['containing'] = str_replace(array("\&quot;", '\"'), array('', ''), $name_filter['containing']);
            $use_regex = true;
        }
    }

    if(! empty($name_filter['character']) && $name_filter['character'] != 'latest')
    {
        $query_addition = " AND `letter`= %s ";
        if( empty($directory['show_all_index_letters']) ) {
            $query_addition = " AND BINARY `letter`= %s ";
        }
        $sql_query .= $query_addition;
        $sql_prepared_values[] = $name_filter['character'];
    }

    if(! empty($name_filter['containing']))
    {
        $sql_query .= " AND ( ";

        if(! $use_regex){
            $sql_query .= " `name` LIKE %s";
            $sql_prepared_values[] = '%' . $wpdb->esc_like($name_filter['containing']) . '%';
        } else {
            // Unsure if there is a better way to SQL prepare this REGEX parameter...
            $sql_query .= " `name` REGEXP '^.*[^a-zA-Z0-9]*(" . esc_sql($name_filter['containing']) . ")[^a-zA-Z0-9]'";

            $sql_query .= " OR `name` = %s";
            $sql_prepared_values[] = $name_filter['containing'];
        }

        if(! empty($directory['search_in_description']) && ! empty($directory['show_description']))
        {
            $sql_query .= " OR ";

            if(!$use_regex){
                $sql_query .= " `description` LIKE %s";
                $sql_prepared_values[] = '%' . $wpdb->esc_like($name_filter['containing']) . '%';
            } else {
                // Unsure if there is a better way to SQL prepare this REGEX parameter...
                $sql_query .= " `description` REGEXP '^.*[^a-zA-Z0-9]*(" . esc_sql($name_filter['containing']) . ")[^a-zA-Z0-9]'";

                $sql_query .= " OR `description` = %s";
                $sql_prepared_values[] = $name_filter['containing'];
            }
        }
        $sql_query .= " ) ";
    }

    if(! empty($name_filter['character']) && $name_filter['character'] == 'latest')
    {
        $sql_query .= "ORDER BY `id` DESC";
        $sql_query .= " LIMIT %d";
        $sql_prepared_values[] = $directory['nr_most_recent'];
    } else {
        $sql_query .= "ORDER BY `letter`, name ASC";
    }

    $names = $wpdb->get_results( $wpdb->prepare($sql_query, $sql_prepared_values), ARRAY_A);

    return $names;
}


/**
 * Get directory by search-query
 * @param $search_query
 * @param bool $include_description
 * @param bool $wildcard
 *
 * @return array|null|object
 */
function nesteddir_get_directory_by_search_query($search_query, $include_description = false, $wildcard = false)
{
	global $wpdb;
	global $nesteddir_table_directory_name;

	$sql_filter = "";

	if(! empty($wildcard))
	{
		$search_query = "%" . $search_query . "%";
	}

	if(empty($include_description))
	{
		$sql_filter = " AND (`name` LIKE '" . $search_query . "') ";
	}
	else
	{
		$sql_filter = " AND (`name` LIKE '" . $search_query . "' OR `description` LIKE '" . $search_query . "') ";
	}

	$directories = $wpdb->get_results(sprintf("
		SELECT DISTINCT `directory`
		FROM %s
		WHERE `published` = 1
		%s",
		esc_sql($nesteddir_table_directory_name),
		$sql_filter),
		ARRAY_A
	);

	return $directories;
}


/**
 * Get the directory with the supplied ID
 * @param $id
 * @return mixed
 */
function nesteddir_get_directory_properties($id)
{
    global $wpdb;
    global $nesteddir_table_directory;

    $directory = $wpdb->get_row(sprintf("SELECT * FROM %s WHERE `id` = %d",
        esc_sql($nesteddir_table_directory),
        esc_sql($id)), ARRAY_A);

    return $directory;
}


/**
 * Get names in a specified directory (specified by ID)
 * @param $id
 * @return mixed
 */
function nesteddir_get_directory_start_characters($id)
{
    global $wpdb;
    global $nesteddir_table_directory_name;

    $characters = $wpdb->get_col(sprintf("SELECT `letter` FROM %s WHERE `directory` = %d ORDER BY `letter` ASC",
        esc_sql($nesteddir_table_directory_name),
        esc_sql($id)));

    return array_unique(array_values($characters));
}


/**
 * Get name specified by ID
 * @param $id
 * @return mixed
 */
function nesteddir_get_single_name($id)
{
    global $wpdb;
    global $nesteddir_table_directory_name;

    $name = $wpdb->get_row(sprintf("SELECT * FROM %s WHERE `id` = %d",
        esc_sql($nesteddir_table_directory_name),
        esc_sql($id)), ARRAY_A);

    return $name;
}


/**
 * Send an email to the WordPress admin e-mailaddress
 * Notify the admin that a new name has been submitted to the directory and
 * that this name has to be reviewed first before publishing
 */
function nesteddir_notify_admin_of_new_submission($directory, $input)
{
    $admin_email = get_option('admin_email');
    wp_mail($admin_email,
        __('New submission for Nested Directory', 'nesteddir'),
        __('Howdy,', 'nesteddir') . "\n\n" .
        sprintf(__('There was a new submission to the Nested Directory on %s at %s', 'nesteddir'), get_option('blogname'), get_option('home')) . "\n\n" .
        sprintf("%s: %s", __('Name', 'nesteddir'), sanitize_text_field($input['nesteddir_name'])) . "\n" .
        sprintf("%s: %s", __('Description', 'nesteddir'), sanitize_text_field($input['nesteddir_description'])) . "\n" .
        sprintf("%s: %s", __('Submitted by', 'nesteddir'), sanitize_text_field($input['nesteddir_submitter'])) . "\n\n" .
        __('This new submission does not have the published status.', 'nesteddir') . ' ' .
        __('Please login to your WordPress admin to review and accept the submission.', 'nesteddir') . "\n\n" .
        sprintf("Link: %s/wp-admin/admin.php?page=nesteddir&sub=manage-directory&dir=%d&status=unpublished", get_option('home'), $directory) . "\n\n" .
        sprintf("Your %s WordPress site", get_option('blogname')));
}


/**
 * Get the first X words of the sent description
 * @param $description
 * @param int $words
 * @return string
 */
function nesteddir_get_words($description, $words = 10)
{
    preg_match("/(?:\w+(?:\W+|$)){0,$words}/u", $description, $matches);
    return $matches[0];
}


/**
 * Helper to render an admin options row with just a Yes/No question
 * @param $directory
 * @param $setting_name
 * @param $setting_friendly_name
 * @param $setting_description
 */
function nesteddir_render_admin_setting_boolean($directory, $setting_name, $setting_friendly_name, $setting_description)
{
    echo '<tr>
            <td>' . $setting_friendly_name . (empty($setting_description) ? '' : '<br><small>' . $setting_description . '</small>') . '</td>
            <td>
                <label for="' . $setting_name . '_yes">
                    <input type="radio" name="' . $setting_name . '" id="' . $setting_name . '_yes" value="1" checked="checked" />
                    &nbsp;' . __('Yes', 'nesteddir') . '
                </label>

                &nbsp; &nbsp;

                <label for="' . $setting_name . '_no">
                    <input type="radio" name="' . $setting_name . '" id="' . $setting_name . '_no" value="0"
                        ' . (empty($directory[$setting_name]) ? 'checked="checked"' : '') . '>' .
                    ' &nbsp; ' . __('No', 'nesteddir') . '
                </label>
            </td>
        </tr>';
}


/**
 * Helper to render an admin options row with multiple choice options
 * @param $directory
 * @param $setting_name
 * @param $setting_friendly_name
 * @param $setting_description
 * @param $options
 */
function nesteddir_render_admin_setting_options($directory, $setting_name, $setting_friendly_name, $setting_description, $options)
{
    echo '<tr>
            <td>' . $setting_friendly_name . (empty($setting_description) ? '' : '<br><small>' . $setting_description . '</small>') . '</td>
            <td>
                <select name="' . $setting_name . '">';
                foreach($options as $option => $name)
                {
                    $selected = null;
                    if(! empty($directory[$setting_name]) && $option == $directory[$setting_name])
                    {
                        $selected = " selected";
                    }
                    echo '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
                }
    echo '      </select>
            </td>
        </tr>';
}


/**
 * Render header and footer of the overview-table
 */
function nesteddir_render_admin_overview_table_headerfooter()
{
    echo '<tr>
            <th width="1%" scope="col" class="manage-column column-cb check-column">&nbsp;</th>
            <th width="52%" scope="col" id="title" class="manage-column column-title sortable desc">
                <span>' . __('Title', 'nesteddir') . '</span>
            </th>
            <th width="13%" scope="col">' . __('Entries', 'nesteddir') . '</th>
            <th width="13%" scope="col">' . __('Published', 'nesteddir') . '</th>
            <th width="13%" scope="col">' . __('Unpublished', 'nesteddir') . '</th>
        </tr>';
}


/**
 * Display a message if mb_string is not available
 */
function nesteddir_notice_mb_string_not_installed()
{
    echo '<div class="notice notice-warning is-dismissible"><p><strong>'
        . __('Message from Nested Directory plugin:', 'nesteddir') . '</strong></p><p>'
        . __('The "mb_string" extension of PHP is not installed, which means that there is no support for non-latin characters. If you are not familiar with what this means, it would not be a problem.', 'nesteddir')
        . '</p></div>';
}


/**
 * The capabilities which can gain control of Nested Directory
 */
function nesteddir_get_capabilities()
{
    return array( 'manage_options', 'manage_nesteddir' );
}


/**
 * Check if the user is allowed to administer the Nested Directory plugin
 */
function nesteddir_is_control_allowed()
{
    if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_nesteddir' ) )
    {
        return true;
    }

    return false;
}


/**
 * Get a localized error message when WP nonce verification fails
 * @param $property
 * @return void
 */
function nesteddir_get_csrf_error_message($property)
{
    $message  = '<p>';
    $message .= sprintf(__('Could not update "%s", your session may have expired or the edit form has been tampered with. Please try again.', 'nesteddir'),
        '<i>' . esc_html($property) . '</i>');
    $message .= '</p>';

    return $message;
}
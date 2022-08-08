<?php
add_action('wp_enqueue_scripts', 'nesteddir_add_frontend_assets');

/**
 * Add the CSS file to output
 */
function nesteddir_add_frontend_assets()
{
    wp_register_style('nesteddir-style', plugins_url('nesteddir.css', __FILE__));
    wp_enqueue_style('nesteddir-style');

    /* If Google reCAPTCHA for Nested Directory is enabled, load it too */
    $nesteddir_settings = get_option('nesteddir_general_option');
    if(! empty($nesteddir_settings) && ! empty($nesteddir_settings['enable_recaptcha']))
    {
        wp_enqueue_script( 'nesteddir-recaptcha', 'https://www.google.com/recaptcha/api.js', false, false );

        /* Make sure it's loaded 'defer async' */
        add_filter( 'script_loader_tag', function ( $tag, $handle ) {
            if ( 'nesteddir-recaptcha' !== $handle ) {
                return $tag;
            }
            return str_replace( ' src', ' async defer src', $tag );
        }, 10, 2 );
    }
}


/**
 * Render function to display a namebox for a Nested Directory
 * @param $entry
 * @param $directory
 */
function nesteddir_render_namebox($entry, $directory)
{
    echo '<div class="nesteddir_name_box">';
    echo '<a name="nesteddir_' . sanitize_html_class($entry['name']) . '"></a>';
    echo '<strong>' . $entry['name'] . '</strong>';
    if(! empty($directory['show_description']) && ! empty($entry['description']))
    {
        $print_description = html_entity_decode(stripslashes($entry['description']));

        /* This toggles the read more/less indicators, these need extra html */
        if(! empty($directory['nr_words_description']))
        {
            $num_words = intval($directory['nr_words_description']);
            $short_desc = nesteddir_get_words($print_description, $num_words);
            $print_description = str_replace($short_desc, "", $print_description);
            if(! empty($print_description))
            {
                echo '<br /><div>
                      <input type="checkbox" class="nesteddir_readmore_state" id="name-' . htmlspecialchars($entry['id']) . '" />
                      <span class="nesteddir_readmore_wrap">' . $short_desc . ' <span class="nesteddir_readmore_target">' . $print_description .'</span></span>
                      <label for="name-' . htmlspecialchars($entry['id']) . '" class="nesteddir_readmore_trigger"></label>
                    </div>';
            }
            else
            {
                echo '<br /><div>' . do_shortcode($short_desc) . '</div>';
            }

        }
        else {
            echo '<br /><div>' . do_shortcode($print_description) . '</div>';
        }
    }
    if(! empty($directory['show_submitter_name']) && ! empty($entry['submitted_by']))
    {
        echo "<small>" . __('Submitted by:', 'nesteddir') . " " . $entry['submitted_by'] . "</small>";
    }
    echo '</div>';
}


/**
 * Show and handle the submission form
 * @param $directory
 * @param $overview_url
 * @return string
 */
function nesteddir_show_submit_form($directory, $overview_url)
{
    global $wpdb;
    global $nesteddir_table_directory_name;
    $nesteddir_settings = get_option('nesteddir_general_option');

    /* Prevent a form from rendering if it's not the one to render, when there are multiple directories on a page */
    if(! empty($_GET['dir']) && ($directory != $_GET['dir']))
    {
        return '';
    }

    $recaptcha_enabled = false;
    $recaptcha_html = '';

    /* If WordPress search for Nested Directory is disabled, just return */
    if(! empty($nesteddir_settings) && ! empty($nesteddir_settings['enable_recaptcha']))
    {
        $recaptcha_enabled = true;
        $recaptcha_html = "<div class='nesteddir_forminput'><br><div class='g-recaptcha' data-sitekey='" . $nesteddir_settings['recaptcha_sitekey'] . "'></div>";
    }

    $directory_info = nesteddir_get_directory_properties($directory);

    if(empty($directory_info['name_term_singular']))
    {
        $name = __('Name', 'nesteddir');
        $back_txt = __('Back to Nested Directory', 'nesteddir');
        $error_empty_txt = __('Please fill in at least a name', 'nesteddir');
    }
    else
    {
        $name = ucfirst($directory_info['name_term_singular']);
        $back_txt = sprintf(__('Back to %s directory', 'nesteddir'), $directory_info['name_term_singular']);
        $error_empty_txt = sprintf(__('Fill in at least a %s', 'nesteddir'), $directory_info['name_term_singular']);
    }

    $required = __('Required', 'nesteddir');
    $description = __('Description', 'nesteddir');
    $your_name = __('Your name', 'nesteddir');
    $submit = __('Submit', 'nesteddir');

    $result_class = '';
    $form_result = null;

    /* reCAPTCHA implementation */
    $proceed_submission = true;
    if($recaptcha_enabled === true)
    {
        /* Check whether user even checked the box */
        if(empty($_POST['g-recaptcha-response']))
        {
            if (! empty($error_empty_txt)) {
                $error_empty_txt .= "<br>";
            }
            $error_empty_txt .= __('Please prove you are not a robot', 'nesteddir');
            $proceed_submission = false;
        }
        else
        {
            $recaptcha_verify_response = file_get_contents(sprintf('https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s',
                $nesteddir_settings['recaptcha_secretkey'], $_POST['g-recaptcha-response']));
            $recaptcha_response = json_decode($recaptcha_verify_response);
            if(! empty($recaptcha_response->success))
            {
                $proceed_submission = true;
            }
            else
            {
                /* Place error message because it didnt't work */
                if(! empty($error_empty_txt))
                {
                    $error_empty_txt .= "<br>";
                }
                $error_empty_txt .= __('reCAPTCHA failed, please try again', 'nesteddir');
                $proceed_submission = false;
            }
        }
    }

    if($proceed_submission === true && ! empty($_POST['nesteddir_name']))
    {
        $wpdb->get_results(
            sprintf("SELECT `id` FROM `%s` WHERE `name` = '%s' AND `directory` = %d",
            $nesteddir_table_directory_name,
            esc_sql($_POST['nesteddir_name']),
            esc_sql(intval($directory)))
        );

        if($wpdb->num_rows > 0)
        {
            $result_class = 'form-result-error';
            $form_result = sprintf(__('Sorry, %s was already on the list so your submission was not sent.', 'nesteddir'),
                '<i>' . esc_sql($_POST['nesteddir_name']) . '</i>');
        }
        else
        {
            $db_success = $wpdb->insert(
                $nesteddir_table_directory_name,
                array(
                    'directory'     => intval($directory),
                    'name'          => wp_kses_post($_POST['nesteddir_name']),
                    'letter'        => nesteddir_get_first_char($_POST['nesteddir_name']),
                    'description'   => wp_kses_post($_POST['nesteddir_description']),
                    'published'     => 0,
                    'submitted_by'  => wp_kses_post($_POST['nesteddir_submitter']),
                ),
                array('%d', '%s', '%s', '%s', '%d', '%s')
            );

            if(! empty($db_success))
            {
                $result_class = 'form-result-success';
                $form_result = __('Thank you for your submission! It will be reviewed shortly.', 'nesteddir');

                nesteddir_notify_admin_of_new_submission($directory, $_POST);
            }
            else
            {
                $result_class = 'form-result-error';
                $form_result = __('Something must have gone terribly wrong. Would you please try it again?', 'nesteddir');
            }
        }
    }
    else if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $result_class = 'form-result-error';
        $form_result = $error_empty_txt;
    }

    $form = <<<HTML
        <form method='post' name='nesteddir_submit'>

            <div class='nesteddir_form_result {$result_class}'>{$form_result}</div>

            <p><a href="{$overview_url}">{$back_txt}</a></p>

            <div class='nesteddir_forminput'>
                <label for='nesteddir_name'>{$name} <small>{$required}</small></label>
                <br />
                <input id='nesteddir_name' type='text' name='nesteddir_name' />
            </div>

            <div class='nesteddir_forminput'>
                <label for='nesteddir_description'>{$description}</label>
                <br />
                <textarea id='nesteddir_description' name='nesteddir_description'></textarea>
            </div>

            <div class='nesteddir_forminput'>
                <label for='nesteddir_submitter'>{$your_name}</label>
                <br />
                <input id='nesteddir_submitter' type='text' name='nesteddir_submitter' />
            </div>

            {$recaptcha_html}

            <div class='nesteddir_forminput'>
                <br />
                <button type='submit'>{$submit}</button>
            </div>

        </form>
HTML;

    return $form;
}


/**
 * Function that takes care of displaying.. stuff
 * @param $attributes
 * @return mixed
 */
function nesteddir_show_directory($attributes)
{
    $dir = null;
    $show_all_link = '';
    $show_latest_link = '';
    $jump_location = '';
    extract(shortcode_atts(
        array('dir' => '1'),
        $attributes
    ));

    $name_filter = array();
    if(! empty($_GET['nesteddir_startswith']) && $_GET['nesteddir_startswith'] == "latest")
    {
        $name_filter['character'] = "latest";
    }
    else if(isset($_GET['nesteddir_startswith']) && ($_GET['dir'] == $dir))
    {
        $name_filter['character'] = nesteddir_get_first_char($_GET['nesteddir_startswith']);
    }
    else if(! empty($attributes['start_with']) && empty($_GET['nesteddir-search-value']))
    {
        $name_filter['character'] = $attributes['start_with'];
    }

    $str_all = __('All', 'nesteddir');
    $str_latest = __('Latest', 'nesteddir');
    $highlight_search_term = false;
    $search_value = '';

    $letter_url = nesteddir_make_plugin_url('nesteddir_startswith', 'nesteddir-search-value', $dir);
    $directory = nesteddir_get_directory_properties($dir);
    if($directory === null)
    {
        echo sprintf(__('Error: Nested Directory #%d does not exist (anymore). If you are the webmaster, please change the shortcode.', 'nesteddir'), $dir);
        return false;
    }

    if(! empty($_GET['nesteddir-search-value']) && ! empty($_GET['dir']) && $_GET['dir'] == $dir)
    {
        $search_value = htmlspecialchars($_GET['nesteddir-search-value']);
        $name_filter['containing'] = $search_value;
        if(! empty($directory['search_highlight']))
        {
            $highlight_search_term = true;
        }
    }

    $names = nesteddir_get_directory_names($directory, $name_filter);
    $num_names = count($names);

    if(isset($_GET['show_submitform']))
    {
        return nesteddir_show_submit_form($dir, nesteddir_make_plugin_url('','show_submitform', $dir));
    }

    ob_start();

    if(! empty($directory['jump_to_search_results']))
    {
        $jump_location = "#nesteddir_position" . $dir;
    }

    echo "<a name='nesteddir_position" . $dir . "'></a>";

    if(! empty($directory['show_title']))
    {
        echo "<h3 class='nesteddir_title'>" . $directory['name'] . "</h3>";
    }

    if(! empty($directory['show_all_names_on_index']))
    {
        $show_all_link = '<a class="nesteddir_startswith" href="' . $letter_url . $jump_location . '">' . $str_all . '</a> |';
    }

    if(! empty($directory['nr_most_recent']))
    {
        $show_latest_link = ' <a class="nesteddir_startswith" href="' . $letter_url . 'latest' . $jump_location . '">' . $str_latest . '</a> |';
    }

    /* Prepare and print the index-letters */
    echo '<div class="nesteddir_index">';
    echo $show_all_link;
    echo $show_latest_link;

    $index_letters = range('A', 'Z');
    array_unshift($index_letters, '#');
    $starting_letters = nesteddir_get_directory_start_characters($dir);

    /* User does not want to show all the index characters */
    if(empty($directory['show_all_index_letters']))
    {
        $index_letters = $starting_letters;
    }

    foreach($index_letters as $index_letter)
    {
        $extra_classes = '';
        if(! empty($name_filter['character']) && $name_filter['character'] == $index_letter)
        {
            $extra_classes .= ' nesteddir_active';
        }

        if(! in_array($index_letter, $starting_letters))
        {
            $extra_classes .= ' nesteddir_empty';
        }

        echo ' <a class="nesteddir_startswith ' . $extra_classes . '" href="' . $letter_url . urlencode($index_letter) . $jump_location . '">' . strtoupper($index_letter) . '</a> ';
    }

    if(! empty($directory['show_submit_form']))
    {
        if(empty($directory['name_term_singular']))
        {
            $submit_string = __('Submit a name', 'nesteddir');
        }
        else
        {
            $submit_string =  sprintf(__('Submit a %s', 'nesteddir'), $directory['name_term_singular']);
        }

        echo " | <a href='" . nesteddir_make_plugin_url('','nesteddir_startswith', $dir) . "&show_submitform=true'>" . $submit_string . "</a>";
    }

    if(! empty($directory['show_search_form']))
    {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $search_get_url = array();
        if(! empty($parsed_url['query']))
        {
            parse_str($parsed_url['query'], $search_get_url);
        }
        unset($search_get_url['nesteddir-search-value']);

        echo "<br />";
        echo "<form method='get' action='" . $jump_location . "'>";
        foreach($search_get_url as $key_name => $value)
        {
            if(in_array($key_name, ['nesteddir_startswith', 'dir']) || is_array($key_name) || is_array($value))
            {
                continue;
            }
            echo "<input type='hidden' name='" . htmlspecialchars($key_name) . "' value='" . htmlspecialchars($value) . "' />";
        }
        echo "<input type='text' name='nesteddir-search-value' id='nesteddir-search-input-box' placeholder='" . __('Search for...', 'nesteddir') . "' />";
        echo "<input type='hidden' name='dir' value='" . (int)$directory['id'] . "' />";
        echo "<input type='submit' id='nesteddir-search-input-button' value='" . __('Search', 'nesteddir') . "' />";
        echo "</form>";
    }
    echo '</div>';

    echo '<div class="nesteddir_total">';
    if(empty($name_filter['character']) && empty($search_value))
    {
        if(empty($directory['name_term'])) {
            echo sprintf(__('There are currently %d names in this directory', 'nesteddir'), $num_names);
        } else {
            if( $num_names == 1 ) {
                echo sprintf(__('There is currently %d %s in this directory', 'nesteddir'), $num_names, $directory['name_term_singular']);
            } else {
                echo sprintf(__('There are currently %d %s in this directory', 'nesteddir'), $num_names, $directory['name_term']);
            }
        }
    }
    else if(empty($name_filter['character']) && ! empty($search_value))
    {
        if(empty($directory['name_term'])) {
            echo sprintf(__('There are %d names in this directory containing the search term %s.', 'nesteddir'), $num_names, "<em>" . stripslashes($search_value) . "</em>");
        } else {
            if( $num_names == 1 ) {
                echo sprintf(__('There is currently %d %s in this directory containing the search term %s.', 'nesteddir'), $num_names, $directory['name_term_singular'], "<em>" . stripslashes($search_value) . "</em>");
            } else {
                echo sprintf(__('There are currently %d %s in this directory containing the search term %s.', 'nesteddir'), $num_names, $directory['name_term'], "<em>" . stripslashes($search_value) . "</em>");
            }
        }

        echo " <a href='" . get_permalink() . "'><small>" . __('Clear results', 'nesteddir') . "</small></a>.<br />";
    }
    else if($name_filter['character'] == 'latest')
    {
        if(empty($directory['name_term'])) {
            echo sprintf(__('Showing %d most recent names in this directory', 'nesteddir'), $num_names);
        } else {
            echo sprintf(__('Showing %d most recent %s in this directory', 'nesteddir'), $num_names, $directory['name_term']);
        }
    }
    else
    {
        if(empty($directory['name_term'])) {
            if( $num_names == 1 ) {
                echo sprintf(__('There is currently %d %s in this directory beginning with the letter %s.', 'nesteddir'), $num_names, strtolower(__('Name', 'nesteddir')), $name_filter['character']);
            } else {
                echo sprintf(__('There are currently %d %s in this directory beginning with the letter %s.', 'nesteddir'), $num_names, __('names', 'nesteddir'), $name_filter['character']);
            }
        } else {
            if( $num_names == 1 ) {
                echo sprintf(__('There is currently %d %s in this directory beginning with the letter %s.', 'nesteddir'), $num_names, $directory['name_term_singular'], $name_filter['character']);
            } else {
                echo sprintf(__('There are currently %d %s in this directory beginning with the letter %s.', 'nesteddir'), $num_names, $directory['name_term'], $name_filter['character']);
            }
        }
    }
    echo  '</div>';

    echo '<div class="nesteddir_names">';
    if($num_names === 0 && empty($search_value))
    {
        echo '<p class="nesteddir_entry_message">' . __('There are no entries in this directory at the moment', 'nesteddir') . '</p>';
    }
    else if(isset($directory['show_all_names_on_index']) && $directory['show_all_names_on_index'] != 1 && empty($name_filter))
    {
        echo '<p class="nesteddir_entry_message">' . __('Please select a letter from the index (above) to see entries', 'nesteddir') . '</p>';
    }
    else
    {
        $split_at = null;
        if(! empty($directory['nr_columns']) && $directory['nr_columns'] > 1)
        {
            $split_at = round($num_names/$directory['nr_columns'])+1;
        }

        echo '<div class="nesteddir_column nesteddir_nr' . (int)$directory['nr_columns'] . '">';

        $i = 1;
        $split_i = 1;
        $this_letter = '---';
        foreach($names as $entry)
        {
            /* Show the header of the next starting letter */
            if(! empty($directory['show_character_header']))
            {
                if ($entry['letter'] !== $this_letter) {
                    $this_letter = $entry['letter'];

                    echo '<div class="nesteddir_character_header">' . $this_letter . '</div>';
                    if(! empty($directory['show_line_between_names']))
                    {
                        echo '<hr />';
                    }
                }
            }

            nesteddir_render_namebox($entry, $directory);

            if(! empty($directory['show_line_between_names']) && $num_names != $i)
            {
                echo '<hr />';
            }

            $split_i++;
            $i++;

            if($split_at == $split_i)
            {
                echo '</div><div class="nesteddir_column nesteddir_nr' . (int)$directory['nr_columns'] . '">';
                $split_i = 1;
            }
        }
        echo '</div>';
    }
    echo '</div>';

    if(! empty($directory['nr_columns']) && $directory['nr_columns'] > 1)
    {
        echo '<div class="nesteddir_column_clear"></div>';
    }

    if(! empty($directory['show_submit_form']))
    {
        echo "<br /><br />
              <a href='" . nesteddir_make_plugin_url('','nesteddir_startswith', $dir) . "&show_submitform=true' 
                    class='nesteddir_submit_bottom_link'>" . $submit_string . "</a>";
    }

    /** Sad to print it like this, but this is needed for translating the show more/less buttons */
    echo "<style>
        .nesteddir_readmore_state ~ .nesteddir_readmore_trigger:before {
            content: '... " . __('Show more', 'nesteddir') . "';
        }
       
        .nesteddir_readmore_state:checked ~ .nesteddir_readmore_trigger:before {
            content: '" . __('Show less', 'nesteddir') . "';
        }
        </style>";

    if($highlight_search_term == true)
    {

        wp_enqueue_script( 'nesteddir-highlight', 'https://cdn.jsdelivr.net/mark.js/8.6.0/mark.min.js', array() );
        wp_add_inline_script( 'nesteddir-highlight', 'var markInstance = new Mark(document.querySelector(".nesteddir_names"));
            markInstance.mark("' . $search_value . '");');
    }

	return ob_get_clean();
}

add_shortcode('nesteddir', 'nesteddir_show_directory');


/**
 * Display a random name from a given directory
 *   Thanks to @mastababa
 * @param $attributes
 * @return mixed
 */
function nesteddir_show_random($attributes)
{
    $dir = null;
    extract(shortcode_atts(
        array('dir' => '1'),
        $attributes
    ));

    $directory = nesteddir_get_directory_properties($dir);
    if($directory === null)
    {
        echo sprintf(__('Error: Nested Directory #%d does not exist (anymore). If you are the webmaster, please change the shortcode.', 'nesteddir'), $dir);
        return false;
    }

    $names = nesteddir_get_directory_names($directory);

    if (! count($names))
    {
        echo __('There are no entries in this directory at the moment', 'nesteddir');
    }

    $entry = $names[array_rand($names)];

    ob_start();

    echo '<div class="nesteddir_random_name">';
    nesteddir_render_namebox($entry, $directory);
    echo '</div>';

    return ob_get_clean();

}
add_shortcode('nesteddir_random', 'nesteddir_show_random');


/**
 * Display a single name by ID
 *   -> Mind you, the name does have to be published!
 * @param $attributes
 * @return mixed
 */
function nesteddir_show_single_name($attributes)
{
    $id = null;
    extract(shortcode_atts(
        array('id' => '1'),
        $attributes
    ));

    $name_entry = nesteddir_get_single_name($id);
    $directory = nesteddir_get_directory_properties($name_entry['directory']);

    ob_start();

    echo '<div class="nesteddir_random_name">';
    nesteddir_render_namebox($name_entry, $directory);
    echo '</div>';

    return ob_get_clean();
}
add_shortcode('nesteddir_single', 'nesteddir_show_single_name');


/**
 * Search the Nested Directory entries whenever necessary
 *      Add the search results to the WordPress search results
 * @param $where (is passed by WordPress)
 * @return string
 */
function nesteddir_insert_sitewide_search_results($where)
{
    /* Only perform actions whenever we are on a search page and within the main query */
    if (is_search())
    {

        $nesteddir_settings = get_option('nesteddir_general_option');

        /* If WordPress search for Nested Directory is disabled, just return */
        if(empty($nesteddir_settings) || empty($nesteddir_settings['search_on']))
        {
            return $where;
        }

        $directories = nesteddir_get_directory_by_search_query(
            get_search_query(),
            $nesteddir_settings['search_description'],
            $nesteddir_settings['search_wildcard']
        );

        $page_ids = array();
        global $wpdb;

        /* If directories were found, get the page they are on */
        foreach($directories as $found => $dir)
        {
            /* Use a plain sql query, WP_Query unfortunately didn't work (infinite loop) */
            $host_pages = $wpdb->get_results("
			  SELECT * 
			  FROM `{$wpdb->prefix}posts` 
			  WHERE 
			    `post_type` IN ('page', 'post')
			    AND `post_status` = 'publish'
			    AND (`post_content` LIKE '%[nesteddir dir=\"{$dir['directory']}%' 
			      OR `post_content` LIKE '%[nesteddir dir={$dir['directory']}%'
			      OR `post_content` LIKE '%[nesteddir dir=''{$dir['directory']}%')");

            if ($host_pages)
            {
                /* Add the pages to the results that WordPress will present to the user */
                foreach($host_pages as $host_page)
                {
                    $page_ids[] = $host_page->ID;
                }
            }
        }

        $page_ids = array_unique($page_ids);
        if(! empty($page_ids))
        {
            $where .= " OR {$wpdb->posts}.ID IN (" . implode(",", $page_ids) . ")";
        }

    }

    return $where;
}

add_filter('posts_where', 'nesteddir_insert_sitewide_search_results');


/**
 * Also add our results to Relevanssi
 */
function nesteddir_insert_relevanssi_search_results($where)
{
    $new_where = nesteddir_insert_sitewide_search_results($where);
    if(! empty($new_where)) {

        global $wpdb;
        $new_where = str_replace("OR {$wpdb->posts}.ID IN", " OR doc IN ", $new_where);
    }

    return $new_where;
}
add_filter('relevanssi_where', 'nesteddir_insert_relevanssi_search_results');

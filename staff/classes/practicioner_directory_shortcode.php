<?php

class PracticionerDirectoryShortcode {
  static function register_shortcode() {
    add_shortcode('practicioner-directory', array('PracticionerDirectoryShortcode', 'shortcode'));
  }

  static function shortcode($params) {
    extract(shortcode_atts(array(
  		'id' => '',
  		'cat' => '',
  		'orderby' => '',
  		'order' => ''
  	), $params));

  	$output = '';

    $practicioner_settings = PracticionerSettings::sharedInstance();
    if(isset($params['template'])) {
      $template = $params['template'];
    } else {
      $template = $practicioner_settings->getCurrentDefaultPracticionerTemplate();
    }

  	// get all practicioner
  	$param = "id=$id&cat=$cat&orderby=$orderby&order=$order";
  	return PracticionerDirectoryShortcode::show_practicioner_directory($param, $template);
  }

  static function show_practicioner_directory($param = null, $template = NULL){
  	parse_str($param);
  	global $wpdb;

  	// make sure we aren't calling both id and cat at the same time
  	if(isset($id) && $id != '' && isset($cat) && $cat != ''){
  		return "<strong>ERROR: You cannot set both a single ID and a category ID for your Practicioner Directory</strong>";
  	}

    $query_args = array(
      'post_type' => 'practicioner',
      'posts_per_page' => -1
    );

  	// check if it's a single practicioner member first, since single members won't be ordered
  	if((isset($id) && $id != '') && (!isset($cat) || $cat == '')){
      $query_args['p'] = $id;
  	}
  	// ends single practicioner

  	// check if we're returning a practicioner category
  	if((isset($cat) && $cat != '') && (!isset($id) || $id == '')){
  		$query_args['tax_query'] = array(
        array(
          'taxonomy' => 'wf_practicioner_folders',
          'terms' => array($cat)
        )
      );
  	}

    if(isset($orderby) && $orderby != ''){
      $query_args['orderby'] = $orderby;
    }
    if(isset($order) && $order != ''){
      $query_args['order'] = $order;
    }

    $practicioner_query = new WP_Query($query_args);

    $list_terms = get_terms('wf_practicioner_folders', array( 'parent' => 0 ) );  
    if($list_terms) {
      $output = PracticionerDirectoryShortcode::html_for_child_list_template($list_terms);
    } else {
      switch($template){
        case 'list':
          $output = PracticionerDirectoryShortcode::html_for_list_template($practicioner_query);
          break;
        case 'grid':
          $output = PracticionerDirectoryShortcode::html_for_grid_template($practicioner_query);
          break;
        default:
          $output = PracticionerDirectoryShortcode::html_for_custom_template($template, $practicioner_query);
          break;
      }
    }

    wp_reset_query();

  	return $output;
  }

  static function html_for_child_list_template($wp_query) {
    $output = '';
    foreach ( $wp_query as $list ) {
      $output .= '<li><a href="' . get_term_link( $list ) . '">' . $list->name . '</a></li>';
    }
    return $output;
  }

  static function html_for_list_template($wp_query) {
    $output = <<<EOT
      <style type="text/css">
        .clearfix {
          clear: both;
        }
        .single-practicioner {
          margin-bottom: 50px;
        }
        .single-practicioner .photo {
          float: left;
          margin-right: 15px;
        }
        .single-practicioner .photo img {
          max-width: 100px;
          height: auto;
        }
        .single-practicioner .name {
          font-size: 1em;
          line-height: 1em;
          margin-bottom: 4px;
        }
        .single-practicioner .location {
          font-size: .9em;
          line-height: .9em;
          margin-bottom: 10px;
        }
        .single-practicioner .bio {
          margin-bottom: 8px;
        }
        .single-practicioner .email {
          font-size: .9em;
          line-height: .9em;
          margin-bottom: 10px;
        }
        .single-practicioner .phone {
          font-size: .9em;
          line-height: .9em;
        }
        .single-practicioner .website {
          font-size: .9em;
          line-height: .9em;
        }
      </style>
      <div id="practicioner-directory-wrapper">
EOT;
    while($wp_query->have_posts()) {
      $wp_query->the_post();

      $name = get_the_title();
      $location = get_post_meta(get_the_ID(), 'location', true);
      $bio = get_the_content();

      if(has_post_thumbnail()) {
        $attachment_array = wp_get_attachment_image_src(get_post_thumbnail_id());
        $photo_url = $attachment_array[0];
        $photo_html = '<div class="photo"><img src="' . $photo_url . '" /></div>';
      } else {
        $photo_html = '';
      }

      if(get_post_meta(get_the_ID(), 'profile_text', true) != '') {
        $profile_text_html = get_post_meta(get_the_ID(), 'profile_text', true);
      } else {
        $profile_text_html = '';
      }

      if(get_post_meta(get_the_ID(), 'profile_link', true) != '') {
        $profile_link_html = get_post_meta(get_the_ID(), 'profile_link', true);
      } else {
        $profile_link_html = '';
      }

      if(get_post_meta(get_the_ID(), 'certification', true) != '') {
        $certification_html = get_post_meta(get_the_ID(), 'certification', true);
      } else {
        $certification_html = '';
      }

      if(get_post_meta(get_the_ID(), 'website', true) != '') {
        $website = get_post_meta(get_the_ID(), 'website', true);
        $website_html = '<div class="website">Website: <a href="' . $website . '">' . $website . '</a></div>';
      } else {
        $website_html = '';
      }

      $output .= <<<EOT
        <div class="single-practicioner">
          $photo_html
          <div class="name">$name</div>
          <div class="location">$location</div>
          <div class="bio">$bio</div>
          $website_html
          $profile_text_html
          $profile_link_html
          $certification_html
          <div class="clearfix"></div>
        </div>
EOT;
    }
    $output .= "</div>";
    return $output;
  }

  static function html_for_grid_template($wp_query) {
    $output = <<<EOT
      <style type="text/css">
        .clearfix {
          clear: both;
        }
        .single-practicioner {
          float: left;
          width: 25%;
          text-align: center;
          padding: 0px 10px;
        }
        .single-practicioner .photo {
          margin-bottom: 5px;
        }
        .single-practicioner .photo img {
          max-width: 100px;
          height: auto;
        }
        .single-practicioner .name {
          font-size: 1em;
          line-height: 1em;
          margin-bottom: 4px;
        }
        .single-practicioner .position {
          font-size: .9em;
          line-height: .9em;
          margin-bottom: 10px;
        }
      </style>
      <div id="practicioner-directory-wrapper">
EOT;
    while($wp_query->have_posts()) {
      $wp_query->the_post();

      $name = get_the_title();
      $location = get_post_meta(get_the_ID(), 'location', true);

      if(has_post_thumbnail()) {
        $attachment_array = wp_get_attachment_image_src(get_post_thumbnail_id());
        $photo_url = $attachment_array[0];
        $photo_html = '<div class="photo"><img src="' . $photo_url . '" /></div>';
      } else {
        $photo_html = '';
      }

      $output .= <<<EOT
        <div class="single-practicioner">
          $photo_html
          <div class="name">$name</div>
          <div class="location">$location</div>
        </div>
EOT;
    }
    $output .= "</div>";
    return $output;
  }

  static function html_for_custom_template($template_slug, $wp_query) {
    $practicioner_settings = PracticionerSettings::sharedInstance();

    $output = '';

    $template = $practicioner_settings->getCustomPracticionerTemplateForSlug($template_slug);
    $template_html = stripslashes($template['html']);
  	$template_css = stripslashes($template['css']);

  	$output .= "<style type=\"text/css\">$template_css</style>";

    if(strpos($template_html, '[practicioner_loop]')) {
      $before_loop_markup = substr($template_html, 0, strpos($template_html, "[practicioner_loop]"));
      $after_loop_markup = substr($template_html, strpos($template_html, "[/practicioner_loop]") + strlen("[/practicioner_loop]"), strlen($template_html) - strpos($template_html, "[/practicioner_loop]"));
      $loop_markup = str_replace("[practicioner_loop]", "", substr($template_html, strpos($template_html, "[practicioner_loop]"), strpos($template_html, "[/practicioner_loop]") - strpos($template_html, "[practicioner_loop]")));
      $output .= $before_loop_markup;
    } else {
      $loop_markup = $template_html;
    }

    while($wp_query->have_posts()) {
      $wp_query->the_post();

      $practicioner_name = get_the_title();
      if (has_post_thumbnail()) {
        $attachment_array = wp_get_attachment_image_src(get_post_thumbnail_id());
        $photo_url = $attachment_array[0];
        $photo_tag = '<img src="' . $photo_url . '" />';
      } else {
        $photo_url = "";
        $photo_tag = "";
      }

      $practicioner_location = get_post_meta(get_the_ID(), 'location', true);
      $practicioner_profile_text = get_post_meta(get_the_ID(), 'profile_text', true);
      $practicioner_profile = get_post_meta(get_the_ID(), 'profile_link', true);
      $practicioner_profile_link = $practicioner_profile != '' ? "<a href=\"$practicioner_profile\" target=\"_blank\">View profile</a>" : "";
      $practicioner_certification = get_post_meta(get_the_ID(), 'certification', true);
      // $practicioner_email_link = $practicioner_email != '' ? $practicioner_email : "";
      // $practicioner_phone_number = get_post_meta(get_the_ID(), 'phone_number', true);
      $practicioner_bio = get_the_content();
      $practicioner_website = get_post_meta(get_the_ID(), 'website', true);
      $practicioner_website_link = $practicioner_website != '' ? "<a href=\"$practicioner_website\" target=\"_blank\">View website</a>" : "";

      $accepted_single_tags = array("[name]", "[photo_url]", "[bio]");
  		$replace_single_values = array($practicioner_name, $photo_url, $practicioner_bio);

  		$accepted_formatted_tags = array("[name_header]", "[photo]", "[location]", "[bio_paragraph]", "[profile_text]", "[profile_link]", "[certification]", "[website_link]");
  		$replace_formatted_values = array("<h3>$practicioner_name</h3>", $photo_tag, $practicioner_location, "<p>$practicioner_bio</p>", $practicioner_profile_text, $practicioner_profile_link, $practicioner_certification, $practicioner_website_link);

  		$current_practicioner_markup = str_replace($accepted_single_tags, $replace_single_values, $loop_markup);
  		$current_practicioner_markup = str_replace($accepted_formatted_tags, $replace_formatted_values, $current_practicioner_markup);

      preg_match_all("/\[(.*?)\]/", $current_practicioner_markup, $other_matches);
      $practicioner_meta_fields = get_option('practicioner_meta_fields');

      if($practicioner_meta_fields != '' && count($other_matches[0]) > 0) {
        foreach($other_matches[0] as $match) {
          foreach($practicioner_meta_fields as $field) {
            $meta_key = $field['slug'];
            $shortcode_without_brackets = substr($match, 1, strlen($match) - 2);
            if($meta_key == $shortcode_without_brackets) {
              $meta_value = get_post_meta(get_the_ID(), $meta_key, true);
              $current_practicioner_markup = str_replace($match, $meta_value, $current_practicioner_markup);
            }
          }
        }
      }

  		$output .= $current_practicioner_markup;
    }

    if(isset($after_loop_markup)) {
      $output .= $after_loop_markup;
    }

    return $output;
  }
}

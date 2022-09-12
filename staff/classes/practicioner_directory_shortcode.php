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
      'post_type' => 'practicioner-directory-master',
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
          'taxonomy' => 'practicioner_category',
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

    wp_reset_query();

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
        .single-practicioner .position {
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
      $position = get_post_meta(get_the_ID(), 'position', true);
      $bio = get_the_content();

      if(has_post_thumbnail()) {
        $attachment_array = wp_get_attachment_image_src(get_post_thumbnail_id());
        $photo_url = $attachment_array[0];
        $photo_html = '<div class="photo"><img src="' . $photo_url . '" /></div>';
      } else {
        $photo_html = '';
      }

      if(get_post_meta(get_the_ID(), 'email', true) != '') {
        $email = get_post_meta(get_the_ID(), 'email', true);
        $email_html = '<div class="email">Email: <a href="mailto:' . $email . '">' . $email . '</a></div>';
      } else {
        $email_html = '';
      }

      if(get_post_meta(get_the_ID(), 'phone', true) != '') {
        $phone_html = '<div class="phone">Phone: ' . get_post_meta(get_the_ID(), 'phone', true) . '</div>';
      } else {
        $phone_html = '';
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
          <div class="position">$position</div>
          <div class="bio">$bio</div>
          $email_html
          $phone_html
          $website_html
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
      $position = get_post_meta(get_the_ID(), 'position', true);

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
          <div class="position">$position</div>
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

      $practicioner_email = get_post_meta(get_the_ID(), 'email', true);
      $practicioner_email_link = $practicioner_email != '' ? "<a href=\"mailto:$practicioner_email\">Email $practicioner_name</a>" : "";
      $practicioner_phone_number = get_post_meta(get_the_ID(), 'phone_number', true);
      $practicioner_bio = get_the_content();
      $practicioner_website = get_post_meta(get_the_ID(), 'website', true);
      $practicioner_website_link = $practicioner_website != '' ? "<a href=\"$practicioner_website\" target=\"_blank\">View website</a>" : "";

      $practicioner_categories = wp_get_post_terms(get_the_ID(), 'practicioner_category');
      $all_practicioner_categories = "";

      if (count($practicioner_categories) > 0) {
        $practicioner_category = $practicioner_categories[0]->name;
        foreach($practicioner_categories as $category) {
          $all_practicioner_categories .= $category->name . ", ";
        }
        $all_practicioner_categories = substr($all_practicioner_categories, 0, strlen($all_practicioner_categories) - 2);
      } else {
        $practicioner_category = "";
      }

      $accepted_single_tags = array("[name]", "[photo_url]", "[bio]", "[category]", "[category all=true]");
  		$replace_single_values = array($practicioner_name, $photo_url, $practicioner_bio, $practicioner_category, $all_practicioner_categories);

  		$accepted_formatted_tags = array("[name_header]", "[photo]", "[email_link]", "[bio_paragraph]", "[website_link]");
  		$replace_formatted_values = array("<h3>$practicioner_name</h3>", $photo_tag, $practicioner_email_link, "<p>$practicioner_bio</p>", $practicioner_website_link);

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
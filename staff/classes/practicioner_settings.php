<?php

class PracticionerSettings {
  public static function sharedInstance()
  {
    static $shared_instance = NULL;
    if ($shared_instance === NULL) {
      $shared_instance = new static();
    }
    return $shared_instance;
  }

  public static function setupDefaults() {
    $practicioner_settings = PracticionerSettings::sharedInstance();

    $current_template_slug = $practicioner_settings->getCurrentDefaultPracticionerTemplate();
    if($current_template_slug == '' || $current_template_slug == NULL) {
      
      $practicioner_settings->updateDefaultPracticionerTemplateSlug('list');

    } else if ($current_template_slug == 'custom' || get_option('practicioner_directory_html_template', '') != '') {

      $templates_array = array();
      $templates_array[] = array(
        'html' => get_option('practicioner_directory_html_template'),
        'css' => get_option('practicioner_directory_css_template')
      );
      $practicioner_settings->updateCustomPracticionerTemplates($templates_array);
      $practicioner_settings->updateDefaultPracticionerTemplateSlug('custom_1');

      delete_option('practicioner_directory_html_template');
      delete_option('practicioner_directory_css_template');
      
    }
  }

  #
  # setters
  #

  public function updateDefaultPracticionerTemplateSlug($slug = 'list') {
    update_option('practicioner_directory_template_slug', $slug);
  }

  public function updateCustomPracticionerTemplates($templates = array()) {
    $updated_templates_array = array();
    $index = 1;
    foreach($templates as $template) {
      if($template['html'] != '' || $template['css'] != '') {
        $template['index'] = $index;
        $template['slug'] = 'custom_' . $index;
        $updated_templates_array[] = $template;
        $index++;
      }
    }
    update_option('practicioner_directory_custom_templates', $updated_templates_array);
  }

  public function updateCustomPracticionerMetaFields($labels = array(), $types = array()) {
    $index = 0;
    $meta_fields_array = array();
    foreach($labels as $meta_label) {
      $slug = strtolower($meta_label);
      $slug = str_replace(' ', '_', $slug);
      if($meta_label != '') {
        $meta_fields_array[] = array(
          'name' => $meta_label,
          'slug' => $slug,
          'type' => $types[$index]
        );
      }
      $index++;
    }
    update_option('practicioner_meta_fields', $meta_fields_array);
  }

  #
  # getters
  #

  public function getCurrentDefaultPracticionerTemplate() {
    $current_template = get_option('practicioner_directory_template_slug');

    if($current_template == '' && get_option('practicioner_directory_html_template') != '') {
      update_option('practicioner_directory_template_slug', 'custom');
      $current_template = 'custom';
    } else if($current_template == '') {
      update_option('practicioner_directory_template_slug', 'list');
      $current_template = 'list';
    }

    return $current_template;
  }

  public function getCustomPracticionerTemplates() {
    return get_option('practicioner_directory_custom_templates', array());
  }

  public function getCustomPracticionerTemplateForSlug($slug = '') {
    $templates = $this->getCustomPracticionerTemplates();
    foreach($templates as $template) {
      if($template['slug'] == $slug) {
        return $template;
      }
    }
  }

  public function getPracticionerDetailsFields() {
    return get_option('practicioner_meta_fields', array());
  }

  #
  # delete functions
  #

  public function deleteCustomTemplate($index = NULL) {
    if($index != NULL) {
      $custom_templates = $this->getCustomPracticionerTemplates();
      $new_custom_templates == array();
      foreach($custom_templates as $template) {
        if($template['index'] != $index) {
          $new_custom_templates[] = $template;
        }
      }
      $this->updateCustomPracticionerTemplates($new_custom_templates);
    }
  }
}
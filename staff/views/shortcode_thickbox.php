<?php

  $practicioner_settings = PracticionerSettings::sharedInstance();

?>

<style type="text/css">
  #practicioner-categories-wrapper,
  #practicioner-order-wrapper,
  #practicioner-template-wrapper {
    margin: 20px 0px;
  }
</style>

<div id="practicioner-categories-wrapper">
  <label for="practicioner-category">Practicioner Folders</label>
  <select name="practicioner-category">
    <option value=''>-- Select Folders --</option>
    <?php
    $taxonomy = "wf_practicioner_folders";
    $terms = get_terms($taxonomy, array(
            "orderby"    => "count",
            "hide_empty" => false
        )
    );
    $hierarchy = _get_term_hierarchy($taxonomy);
    foreach($terms as $term) {
      if($term->parent) {
          continue;
      }
      echo '<option value="' . $term->term_id . '">' . $term->name . '</option>';
      if($hierarchy[$term->term_id]) {
          foreach($hierarchy[$term->term_id] as $child) {
              $child = get_term($child, "wf_practicioner_folders");
              echo '<option value="' . $child->term_id . '"> - ' . $child->name . '</option>';
          }
      }
    }
    ?>
    <!-- <?php foreach(get_terms('wf_practicioner_folders') as $cat): ?>

      <option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
    <?php endforeach; ?> -->
  </select>
</div>

<div id="practicioner-order-wrapper">
  <label for="practicioner-order">Practicioner Order</label>
  <select name="practicioner-order">
    <option value=''>-- Use Default --</option>
    <option value="asc">Ascending</option>
    <option value="desc">Descending</option>
  </select>
</div>

<div id="practicioner-template-wrapper">
  <label for="practicioner-template">Practicioner Template</label>
  <select name="practicioner-template">
    <option value=''>-- Use Default --</option>
    <option value='list'>List</option>
    <option value='grid'>Grid</option>
    <?php foreach($practicioner_settings->getCustomPracticionerTemplates() as $template): ?>
      <option value="<?php echo $template['slug'] ?>">Custom Template <?php echo $template['index']; ?></option>
    <?php endforeach; ?>
  </select>
</div>

<a href="javascript:PracticionerDirectory.formatShortCode();" class="button button-primary button-large">Insert Shortcode</a>
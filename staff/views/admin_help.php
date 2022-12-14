<style type="text/css">
  div.help-topic {
    margin-bottom: 40px;
  }
</style>

<div class="help-topic" id="practicioner-shortcodes">
  <h2>Shortcodes</h2>

  <p>
    Use the <code>[practicioner-directory]</code> shortcode in a post or page to display your practicioner.
  </p>

  <p>
    The following parameters are accepted:
    <ul>
      <li><code>cat</code> - the practicioner category ID to use. (Ex: [practicioner-directory cat=1])</li>
      <li><code>id</code> - the ID for a single practicioner member. (Ex: [practicioner-directory id=4])</li>
      <li><code>orderby</code> - the attribute to use for ordering. Supported values are 'name' and 'ID'. (Ex: [practicioner-directory orderby=name])</li>
      <li><code>order</code> - the order in which to arrange the practicioner members. Supported values are 'asc' and 'desc'. (Ex: [practicioner-directory orbder=asc])</li>
    </ul>
    Note - Ordering options can be viewed here - <a href="https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters</a>
  </p>
</div>

<div class="help-topic" id="practicioner-templates">
  <h2>Practicioner Directory Templates</h2>

  <p>
    The the <code>[practicioner-directory]</code> shortcode supports a default template (set in <a href="<?php echo get_admin_url(); ?>edit.php?post_type=practicioner&page=practicioner-directory-settings">Practicioner Settings</a>) or custom templates per use.
  </p>

  <p>
    Each template is identified by a slug. The provided templates are "List" and "Grid", with their slugs being "list" and "grid" respectively. Each custom template uses the slug format "custom_[n]" where [n] is the custom template ID.
    So to use "Custom Template 1" you would use the shortcode like so: <code>[practicioner-directory template=custom_1]</code>.
  </p>
</div>

<div class="help-topic" id="practicioner-template-tags">
  <h2>Practicioner Directory Template Tags</h2>

  <p>
    Custom Shortcodes are listed in the Custom Details Fields table on the <a href="<?php echo get_admin_url(); ?>edit.php?post_type=practicioner&page=practicioner-directory-settings">Practicioner Settings page</a>. All template shortcodes must be contained within the <code>[practicioner_loop]</code> shortcodes.
  </p>

  <p>
    Preformatted shortcodes are listed below. There were more options in this list previously, but due to the addition of the Custom Details Fields above some of them were removed from the suggestions. They will still work for now, but deprecated shortcodes are marked below and will no longer work at some point in the future.
  </p>

  <ul>
    <li><code>[photo_url]</code> - the url to the featured image for the practicioner member</li>
    <li><code>[photo]</code> - an &lt;img&gt; tag with the featured image for the practicioner member</li>
    <li><code>[name]</code> - the practicioner member's name</li>
    <li><code>[name_header]</code> - the practicioner member's name with &lt;h3&gt; tags</li>
    <li><code>[bio]</code> - the practicioner member's bio</li>
    <li><code>[bio_paragraph]</code> - the practicioner member's bio with &lt;p&gt; tags</li>
    <li><code>[category]</code> - the practicioner member's category (first category only)</li>
    <li><code>[category all=true]</code> - all of the practicioner member's categories in a comma-separated list</li>
    <li><code>[email_link]</code> (deprecated, requires and Email field above)</li>
    <li><code>[website_link]</code> (deprecated, requires a Website field above)</li>
  </ul>
</div>

<div class="help-topic" id="practicioner-theme-tags">
  <h2>WordPress Theme Template Tag</h2>

  <p>
    This plugin previsouly supported a custom template function, but it's now
    recommended to use the following if you need to hardcode a practicioner directory
    into a template:
    <br />
    <code>&lt;?php echo do_shortcode( '[practicioner-directory]' ); ?&gt;</code>
  </p>
</div>

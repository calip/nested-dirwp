<?php
class NestedDirGeneralSettingsPage
{
	private $options;

	/**
	 * NestedDirSettingsPage constructor, register the settings page and initialize the options
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_menu_entry'));
		add_action('admin_init', array($this, 'nesteddir_general_settings_page_init'));
	}

	/**
	 * Add options page to the menu
	 */
	public function add_menu_entry()
	{
        foreach( nesteddir_get_capabilities() as $capability)
        {
            if( current_user_can( $capability ) )
            {
                add_submenu_page(
                    'nesteddir',
                    __('General settings', 'nesteddir'),
                    __('General settings', 'nesteddir'),
                    $capability,
                    'nesteddir-general-settings',
                    array($this, 'nesteddir_general_settings_page'));

                break;
            }
        }
	}

	/**
	 * Options page callback (renders settings form and other stuff)
	 */
	public function nesteddir_general_settings_page()
	{
		$this->options = get_option('nesteddir_general_option');
		?>
		<div class="wrap">
            <h1><?php echo __('Nested Directory', 'nesteddir') . ' ' . __('Settings', 'nesteddir'); ?></h1>
            <div  style="width: 50%; float: left;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('nesteddir_general_settings_search');
                    do_settings_sections('nesteddir-general-settings');
                    submit_button();
                    ?>
                </form>
            </div>

            <?php
            $this->print_asidebar();
        echo '</div>';
	}

	/**
	 * Register and add settings to WordPress
	 */
	public function nesteddir_general_settings_page_init()
	{
		register_setting(
			'nesteddir_general_settings_search',
			'nesteddir_general_option',
			array($this, 'sanitize')
		);


        add_settings_section(
            'nesteddir_editing_section_settings',
            __('Nested Directory', 'nesteddir') . ' ' . __('Editing', 'nesteddir'),
            array($this, 'print_editing_info'),
            'nesteddir-general-settings'
        );

        add_settings_field(
            'simple_wysiwyg_editor',
            __('Use visual editor', 'nesteddir'),
            array($this, 'simple_wysiwyg_editor_callback'),
            'nesteddir-general-settings',
            'nesteddir_editing_section_settings'
        );


		add_settings_section(
			'nesteddir_general_section_settings',
			__('Nested Directory', 'nesteddir') . ' ' . __('Search', 'nesteddir'),
			array($this, 'print_search_info'),
			'nesteddir-general-settings'
		);

		add_settings_field(
			'search_on',
			__('Include in sitewide search', 'nesteddir'),
			array($this, 'search_on_callback'),
			'nesteddir-general-settings',
			'nesteddir_general_section_settings'
		);

		add_settings_field(
			'search_description',
			__('Search in description', 'nesteddir'),
			array($this, 'search_description_callback'),
			'nesteddir-general-settings',
			'nesteddir_general_section_settings'
		);

		add_settings_field(
			'search_wildcard',
			__('Enable wildcard search', 'nesteddir'),
			array($this, 'search_wildcard_callback'),
			'nesteddir-general-settings',
			'nesteddir_general_section_settings'
		);

        add_settings_field(
            'disable_duplicate_protection',
            __('Disable duplicate protection', 'nesteddir'),
            array($this, 'duplicate_protection_callback'),
            'nesteddir-general-settings',
            'nesteddir_general_section_settings'
        );


        add_settings_section(
            'nesteddir_recaptcha_section_settings',
            'Google reCAPTCHA (v2)',
            array($this, 'print_recaptcha_info'),
            'nesteddir-general-settings'
        );

        add_settings_field(
            'enable_recaptcha',
            __('Enable Google reCAPTCHA', 'nesteddir'),
            array($this, 'enable_recaptcha_callback'),
            'nesteddir-general-settings',
            'nesteddir_recaptcha_section_settings'
        );

        add_settings_field(
            'recaptcha_sitekey',
            __('reCAPTCHA site key', 'nesteddir'),
            array($this, 'recaptcha_sitekey_callback'),
            'nesteddir-general-settings',
            'nesteddir_recaptcha_section_settings'
        );

        add_settings_field(
            'recaptcha_secretkey',
            __('reCAPTCHA secret key', 'nesteddir'),
            array($this, 'recaptcha_secretkey_callback'),
            'nesteddir-general-settings',
            'nesteddir_recaptcha_section_settings'
        );
	}

	/**
	 * Sanitize each setting field as needed (mostly booleans)
	 *
	 * @param array $input Contains all posted settings fields as array key
	 * @return array
	 */
	public function sanitize($input)
	{
		$boolean_settings = array();
		foreach($input as $key => $value)
        {
            if(strpos($key, 'recaptcha_') === 0)
            {
                $boolean_settings[$key] = sanitize_text_field($value);
                continue;
            }

	        $boolean_settings[$key] = absint($value);
        }
		return $boolean_settings;
	}

	/**
	 * Print the search settings section text
	 */
	public function print_search_info()
	{
        print __('Nested Directory can also be embedded in the search functionality which WordPress offers out of the box. This page allows you to control these settings.', 'nesteddir');
        print '<br>';
        print __('Please note, these settings are for the <em>site-wide</em> search results! Nested Directory also has a built-in search engine to search within a directory. Those settings can be controlled in the specific directory settings.', 'nesteddir');

	}


    /**
     * Print the editing section text
     */
    public function print_editing_info()
    {
        print __('This settings controls which editor you are using in the Nested Directory admin', 'nesteddir');

    }


    /**
     * Print the editing section text
     */
    public function print_recaptcha_info()
    {
        print __('Do you want to enable Google reCAPTCHA on the submit-a-name forms?', 'nesteddir') . ' ' .
            __('If you are getting spammed or simple want to make sure there are no robots submitting on your site, Google reCAPTCHA will help you.', 'nesteddir') . ' ' .
            __('Once you register your site, just copy the site key and secret key in the fields below and your site will be protected!', 'nesteddir');
        print ' <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="nofollow noopener">';
        print __('Register your website for free here and get the keys.', 'nesteddir');
        print '</a>';

    }

	/**
	 * The callbacks for our functions
	 */
    public function simple_wysiwyg_editor_callback()
    {
        echo $this->radio_button_option('simple_wysiwyg_editor');
        echo sprintf("<p><em>%s</em></p>", __('Enable the simple visual editor for editing the description of names in a directory.', 'nesteddir'));
    }
	public function search_on_callback()
	{
		echo $this->radio_button_option('search_on');
		echo sprintf("<p><em>%s</em></p>", __('All entries in Name Directories can be included in the WordPress Search results. When a name is matched, WordPress search will display the page containing the directory in the search results.', 'nesteddir'));
	}
	public function search_description_callback()
	{
		echo $this->radio_button_option('search_description');
		echo sprintf("<p><em>%s</em></p>", __('This will allow WordPress to search in the descriptions too.', 'nesteddir'));
	}
	public function search_wildcard_callback()
	{
		echo $this->radio_button_option('search_wildcard');
		echo sprintf("<p><em>%s</em></p>", __('This enables WordPress to partially match names. A search for "bird" will also return "birdcage".', 'nesteddir'));
	}
    public function duplicate_protection_callback()
    {
        echo $this->radio_button_option('disable_duplicate_protection');
        echo sprintf("<p><em>%s</em></p>", __('Normally, you cannot use a name more than once in a directory. If you want to do this, enable this setting.', 'nesteddir'));
    }
    public function enable_recaptcha_callback()
    {
        echo $this->radio_button_option('enable_recaptcha');
        echo sprintf("<p><em>%s</em></p>", __('Use reCAPTCHA', 'nesteddir'));
    }
    public function recaptcha_sitekey_callback()
    {
        echo $this->input_field_option('recaptcha_sitekey');
        echo sprintf("<p><em>%s</em></p>", __('Google reCAPTCHA sitekey', 'nesteddir'));
    }
    public function recaptcha_secretkey_callback()
    {
        echo $this->input_field_option('recaptcha_secretkey');
        echo sprintf("<p><em>%s</em></p>", __('Google reCAPTCHA secret key', 'nesteddir'));
    }

	/**
	 * Get the settings option array and print one of its values
	 */
	public function radio_button_option($field)
	{
		printf('<label for"' . $field . '_yes"><input type="radio" id="' . $field . '_yes" name="nesteddir_general_option[' . $field . ']" value="1" %s> %s</label> &nbsp;&nbsp; ', empty($this->options[$field]) ? '' : 'checked', __('Yes', 'nesteddir'));
		printf('<label for"' . $field . '_no"><input type="radio" id="' . $field . '_no" name="nesteddir_general_option[' . $field . ']" value="0" %s> %s</label>',  empty($this->options[$field]) ? 'checked' : '', __('No', 'nesteddir'));
	}

    /**
     * Get the settings option array and print one of its values
     */
    public function input_field_option($field)
    {
        printf('<label for"' . $field . '_input"><input type="text" id="' . $field . '_input" name="nesteddir_general_option[' . $field . ']" value="%s"></label>', empty($this->options[$field]) ? '' : $this->options[$field]);
    }

	/**
     * Print some links and a donation link
     */
    public function print_asidebar()
    {
        print '
        <div style="float:right; width: 35%; max-width: 35%;">
            
            <div style="background-color:#333333; padding:8px; color:#eee; font-size:12pt; font-weight:bold;">
                <i class="dashicons dashicons-admin-plugins"></i> Nested Directory plugin
            </div>
            <div style="background-color:#fff;border: 1px solid #E5E5E5;padding:8px;">
        
                <h3 style="font-weight: normal;">' . __('Thank you for using Nested Directory!', 'nesteddir') . '</h3>
        
                <p>' . __('I am honored that you are using my software. Here are a few handy quicklinks.', 'nesteddir') . '</p>
        
                <ul>
                    <li>
                        <i class="dashicons dashicons-welcome-learn-more"></i>
                        <a href="https://wordpress.org/plugins/nesteddir/#faq" target="_blank">' . __('Frequently Asked Questions', 'nesteddir') . '</a>
                    </li>
                    <li>
                        <i class="dashicons dashicons-translation"></i>
                        <a href="https://translate.wordpress.org/projects/wp-plugins/nesteddir" target="_blank">' . __('Help translate this plugin', 'nesteddir') . '</a>
                    </li>
                    <li>
                        <i class="dashicons dashicons-lightbulb"></i>
                        <a href="https://wordpress.org/support/plugin/nesteddir" target="_blank">' . __('Have an idea? Let me know!', 'nesteddir') . '</a>
                    </li>
                    <li>
                        <i class="dashicons dashicons-editor-help"></i>
                        <a href="https://wordpress.org/support/plugin/nesteddir" target="_blank">' . __('Do you want to ask a question?', 'nesteddir') . '</a>
                    </li>
                    <li>
                        <i class="dashicons dashicons-star-filled"></i>
                        <a href="https://wordpress.org/support/plugin/nesteddir/reviews/#new-post" target="_blank">' . __('Rate/review this plugin', 'nesteddir') . '</a>
                    </li>
                </ul>
            </div>
        
            <br>
            <br>
        
            <div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
                <i class="dashicons dashicons-money"></i> ' . __('Donate', 'nesteddir') . '
            </div>
            <div style="background-color:#fff;border: 1px solid #E5E5E5;padding:8px;">

                <p>' . __('If you like the plugin, would you please consider donating a small amount of money to pay for the license of my programming editor? Or for a good cup of coffee', 'nesteddir') . ' :-)</p>
                
                <form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="text-align: center">
                    <input type="hidden" name="cmd" value="_donations">
                    <input type="hidden" name="business" value="mail@jeroenpeters.com">
                    <input type="hidden" name="item_name" value="Nested Directory Plugin development">
                    <input type="hidden" name="currency_code" value="USD">
                    <input type="hidden" name="no_note" value="1">
                    <input type="hidden" name="no_shipping" value="1">
                    <input type="hidden" name="lc" value="EN_US">
                    <input type="hidden" name="bn" value="WPPlugin_SP">
                    <input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" alt="Make your payments with PayPal. It is free, secure, effective." style="border: none;">
                </form>
                
            </div>
        </div>';
    }

}


/* Add this whenever the logged in user is an administrator */
if( is_admin() || current_user_can( 'manage_nesteddir' ) )
{
	new NestedDirGeneralSettingsPage();
}
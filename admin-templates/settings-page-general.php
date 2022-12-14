<form action="" method="post">
    <input type="hidden" name="action" value="wicked_folders_save_settings" />
    <?php wp_nonce_field( 'wicked_folders_save_settings', 'nonce' ); ?>
    <h2><?php _e( 'General', 'wicked-folders' ); ?></h2>
    <table class="form-table">
        <tr>
            <th scope="row">
                <?php _e( 'Enable folders for:', 'wicked-folders' ); ?>
            </th>
            <td>
                <?php foreach ( $post_types as $post_type ) : ?>
                    <?php
                        if ( ! $is_pro_active && in_array( $post_type->name, $pro_post_types ) ) continue;
                        if ( ! $post_type->show_ui ) continue;
                        if ( $post_type->name === 'practicioner') :
                    ?>
                    <label>
                        <input type="checkbox" name="post_type[]" value="<?php echo esc_attr( $post_type->name ); ?>"<?php if ( in_array( $post_type->name, $enabled_posts_types ) ) echo ' checked="checked"'; ?>/>
                        <?php echo esc_html( $post_type->label ); ?>
                    </label>
                    <br />
                    <?php endif ?>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th scope="row">
                &nbsp;
            </th>
            <td>
                <label>
                    <input type="checkbox" name="show_item_counts" value="1"<?php if ( $show_item_counts ) echo ' checked="checked"'; ?>/>
                    <?php _e( 'Show number of items in each folder', 'wicked-folders' ); ?>
                    <span class="dashicons dashicons-editor-help" title="<?php _e( "When checked (default), the number of items assigned to each Location is displayed next to the Location's name.", 'wicked-folders' ); ?>"></span>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                &nbsp;
            </th>
            <td>
                <label>
                    <input type="checkbox" name="show_unassigned_folder" value="1"<?php if ( $show_unassigned_folder ) echo ' checked="checked"'; ?>/>
                    <?php _e( 'Show unassigned items Location', 'wicked-folders' ); ?>
                    <span class="dashicons dashicons-editor-help" title="<?php _e( "When checked (default), the 'Unassigned Items' Location will always be shown in the Location pane.  When left unchecked, the 'Unassigned Items' Location will appear as a child Location within 'Dynamic Locations'.", 'wicked-folders' ); ?>"></span>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                &nbsp;
            </th>
            <td>
                <label>
                    <input type="checkbox" name="show_folder_search" value="1"<?php if ( $show_folder_search ) echo ' checked="checked"'; ?>/>
                    <?php _e( 'Show Location search', 'wicked-folders' ); ?>
                    <span class="dashicons dashicons-editor-help" title="<?php _e( "When checked (default), a search field is displayed above the Location tree that allows you to search Locations by name.", 'wicked-folders' ); ?>"></span>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                &nbsp;
            </th>
            <td>
                <label>
                    <input type="checkbox" name="show_breadcrumbs" value="1"<?php if ( $show_breadcrumbs ) echo ' checked="checked"'; ?>/>
                    <?php _e( 'Show Location breadcrumbs', 'wicked-folders' ); ?>
                    <span class="dashicons dashicons-editor-help" title="<?php _e( 'Displays a breadcrumb trail at the top of post lists.', 'wicked-folders' ); ?>"></span>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                &nbsp;
            </th>
            <td>
                <label>
                    <input type="checkbox" name="show_hierarchy_in_folder_column" value="1"<?php if ( $show_hierarchy_in_folder_column ) echo ' checked="checked"'; ?>/>
                    <?php _e( 'Show Location hierarchy in Location column', 'wicked-folders' ); ?>
                    <span class="dashicons dashicons-editor-help" title="<?php _e( "When unchecked (default), Location will be displayed as a comma-separated list in the Location column that appears in post lists.  When checked, a breadcrumb path will be displayed showing the hierarchy of each Location the item is assigned to.", 'wicked-folders' ); ?>"></span>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                &nbsp;
            </th>
            <td>
                <label>
                    <input type="checkbox" name="include_children" value="1"<?php if ( $include_children ) echo ' checked="checked"'; ?>/>
                    <?php _e( 'Include items from child Locations', 'wicked-folders' ); ?>
                    <span class="dashicons dashicons-editor-help" title="<?php _e( "When unchecked (default) and a Location is selected, only items assigned to that Location will be displayed.  When checked, items in the selected Location *and* items in any of the Location's child Locations will be displayed.  Please note: this setting does not apply to media.", 'wicked-folders' ); ?>"></span>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                &nbsp;
            </th>
            <td>
                <label>
                    <input type="checkbox" name="enable_ajax_nav" value="1"<?php if ( $enable_ajax_nav ) echo ' checked="checked"'; ?>/>
                    <?php _e( "Don't reload page when navigating Locations", 'wicked-folders' ); ?>
                    <span class="dashicons dashicons-editor-help" title="<?php _e( "When checked (default), navigating between Locations will not cause the page to reload.", 'wicked-folders' ); ?>"></span>
                </label>
            </td>
        </tr>
    </table>
    <!-- <h2><?php _e( 'Dynamic Locations', 'wicked-folders' ); ?></h2>
    <p><?php _e( 'Dynamic Locations are generated on the fly based on your content.  They are useful for finding content based on things like date, author, etc.', 'wicked-folders' ); ?></p>
    <table class="form-table">
        <tr>
            <th scope="row">
                <?php _e( 'Enable dynamic Locations for:', 'wicked-folders' ); ?>
            </th>
            <td>
                <?php foreach ( $post_types as $post_type ) : ?>
                    <?php
                        if ( ! $is_pro_active && in_array( $post_type->name, $pro_post_types ) ) continue;
                        if ( in_array( $post_type->name, array( Wicked_Folders::get_plugin_post_type_name(), Wicked_Folders::get_gravity_forms_form_post_type_name(), Wicked_Folders::get_gravity_forms_entry_post_type_name(), 'tablepress_table' ) ) ) continue;
                        if ( ! $post_type->show_ui ) continue;
                        if ( $post_type->name === 'practicioner') :
                    ?>
                    <label>
                        <input type="checkbox" name="dynamic_folder_post_type[]" value="<?php echo esc_attr( $post_type->name ); ?>"<?php if ( in_array( $post_type->name, $dynamic_folders_enabled_posts_types ) ) echo ' checked="checked"'; ?><?php //if ( ! in_array( $post_type->name, $enabled_posts_types ) ) echo ' disabled="disabled"'; ?>/>
                        <?php echo esc_html( $post_type->label ); ?>
                    </label>
                    <br />
                    <?php endif; ?>
                <?php endforeach; ?>
            </td>
        </tr> -->
        <?php /* ?>
        <th scope="row">
            <?php _e( 'Tree View', 'wicked-folders' ); ?>
        </th>
        <td>
            <label>
                <input type="checkbox" name="show_folder_contents_in_tree_view" value="1"<?php if ( $show_folder_contents_in_tree_view ) echo ' checked="checked"'; ?>/>
                <?php _e( 'Show folder contents in tree view', 'wicked-folders' ); ?>
            </label>
            <p class="description"><?php _e( "When checked, the tree view will display each folder's items in addition to its sub folders.", 'wicked-folders' ); ?></p>
        </td>
        <?php */ ?>
    </table>
    <?php if ( $is_pro_active ) : ?>
        <h2><?php _e( 'Media', 'wicked-folders' ); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php //_e( 'Sync folder upload dropdown', 'wicked-folders' ); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="sync_upload_folder_dropdown" value="1"<?php if ( $sync_upload_folder_dropdown ) echo ' checked="checked"'; ?>/>
                        <?php _e( 'Sync Location upload dropdown', 'wicked-folders' ); ?>
                        <span class="dashicons dashicons-editor-help" title="<?php _e( 'When checked, the dropdown that lets you to choose which Location to assign new uploads to will change as you browse folders and default to the currently selected Location. If left unchecked, the dropdown will default to no Location selected.', 'wicked-folders' ); ?>"></span>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    &nbsp;
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="include_attachment_children" value="1"<?php if ( $include_attachment_children ) echo ' checked="checked"'; ?>/>
                        <?php _e( 'Include media from child Locations', 'wicked-folders' ); ?>
                        <span class="dashicons dashicons-editor-help" title="<?php _e( "When unchecked (default) and a Location is selected, only media assigned to that Location will be displayed.  When checked, media in the selected Location *and* media in any of the Location's child Locations will be displayed.", 'wicked-folders' ); ?>"></span>
                    </label>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <p class="submit">
        <input name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>" type="submit" />
    </p>
</form>

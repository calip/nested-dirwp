<div class="wrap">
    <h1><?php _e( 'Practitioners Folders Settings', 'wicked-folders' ); ?></h1>
    <h2 class="nav-tab-wrapper wp-clearfix">
        <?php foreach ( $tabs as $tab ) : ?>
            <a class="nav-tab<?php if ( $active_tab == $tab['slug'] ) echo ' nav-tab-active'; ?>" href="options-general.php?page=wicked_folders_settings&tab=<?php echo esc_attr( $tab['slug'] ); ?>"><?php echo esc_html( $tab['label'] ); ?></a>
        <?php endforeach; ?>
    </h2>
    <div class="wicked-settings wicked-clearfix">
        <div class="wicked-left">
            <?php foreach ( $tabs as $tab ) : ?>
                <?php if ( $active_tab == $tab['slug'] ) : ?>
                    <?php call_user_func( $tab['callback'] ); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

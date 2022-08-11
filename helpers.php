<?php
/**
 * This file is part of the NestedDirectory plugin for WordPress
 */


/**
 * The capabilities which can gain control of Nested Directory
 */
function nested_directory_get_capabilities()
{
    return array( 'manage_options', 'manage_nested_directory' );
}


/**
 * Check if the user is allowed to administer the Nested Directory plugin
 */
function nested_directory_is_control_allowed()
{
    if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_nested_directory' ) )
    {
        return true;
    }

    return false;
}
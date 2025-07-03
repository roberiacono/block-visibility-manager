<?php
/**
 * Block Visibility Manager Uninstall Script.
 *
 * @package BlockVisibilityManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$option_name = 'block_visibility_manager_disabled_blocks';

delete_option( $option_name );

<?php
/**
 * Block Visibility Manager Uninstall Script.
 *
 * @package BlockVisibilityManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

delete_option( 'block_visibility_manager_disabled_blocks' );

<?php
/**
 * Plugin Name:       Block Visibility Manager
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-visibility-manager
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION', '1.0.0' );

/** Enqueue editor assets */
function bvm_enqueue_editor_assets() {
	wp_enqueue_script(
		'bvm-editor',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		array( 'wp-blocks', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
		filemtime( __DIR__ . '/build/index.js' )
	);

	$role_options = bvm_get_all_roles();

	$enabled = get_option( 'bvm_enabled_blocks', array() );
	wp_localize_script( 'bvm-editor', 'bvmEnabledBlocks', $enabled );

	wp_localize_script(
		'bvm-editor',
		'bvmRoleOptions',
		$role_options
	);
}
add_action( 'enqueue_block_editor_assets', 'bvm_enqueue_editor_assets' );

/**
 * Get all WP Roles.
 */
function bvm_get_all_roles() {
	$roles        = wp_roles()->roles;
	$role_options = array();

	foreach ( $roles as $role_slug => $role_details ) {
		$role_options[] = array(
			'label' => $role_details['name'],
			'value' => $role_slug,
		);
	}

	return $role_options;
}

/**
 * Processes and renders a block.
 *
 * @param string $block_content The content of the block to be processed.
 * @param array  $block An associative array containing block information.
 *
 * @return string The processed block content.
 */
function bvm_filter_render_block( $block_content, $block ) {
	if (
		empty( $block['attrs']['bvmEnableVisibility'] ) ||
		! apply_filters( 'bvm_should_render', true, $block['attrs'] )
	) {
		return $block_content;
	}

	ob_start();
	include __DIR__ . '/render.php';
	return ob_get_clean();
}
add_filter( 'render_block', 'bvm_filter_render_block', 10, 2 );

/**
 * Enqueue frontend styles for the block.
 */
function bvm_enqueue_frontend_css() {
	wp_enqueue_style( 'bvm-style', plugin_dir_url( __FILE__ ) . 'build/style-index.css', array(), BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION );
}
add_action( 'wp_enqueue_scripts', 'bvm_enqueue_frontend_css' );


/**
 * Include files if they exist.
 */
function bvm_include_settings_file() {
	$file_path_to_includes = array(
		plugin_dir_path( __FILE__ ) . 'includes/helpers.php',
		plugin_dir_path( __FILE__ ) . 'includes/settings.php',
	);

	foreach ( $file_path_to_includes as $file_path ) {
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
}
add_action( 'init', 'bvm_include_settings_file' );


register_activation_hook( __FILE__, 'bvm_set_default_disabled_blocks' );
/**
 * Set default enabled blocks on plugin activation.
 */
function bvm_set_default_disabled_blocks() {
	if ( get_option( 'bvm_disabled_blocks', null ) === null ) {
		update_option( 'bvm_disabled_blocks', bvm_get_default_disabled_blocks() );
	}
}

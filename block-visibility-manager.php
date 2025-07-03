<?php
/**
 * Plugin Name:       Block Visibility Manager
 * Description:       Control the visibility of Gutenberg blocks based on user role, device type, date, time, and more. Enhance content flexibility by dynamically showing or hiding blocks under specific conditions.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Roberto Iacono
 * Author URI:        https://robertoiacono.it
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-visibility-manager
 * Domain Path:     /languages
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION', '1.0.0' );

/** Enqueue editor assets */
function block_visibility_manager_enqueue_editor_assets() {
	wp_enqueue_script(
		'block-visibility-manager-editor',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		array( 'wp-blocks', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
		filemtime( __DIR__ . '/build/index.js' ),
		true
	);

	$role_options = block_visibility_manager_get_all_roles();

	$enabled = block_visibility_manager_get_enabled_blocks();

	wp_localize_script(
		'block-visibility-manager-editor',
		'bvmEnabledBlocks',
		$enabled
	);

	wp_localize_script(
		'block-visibility-manager-editor',
		'bvmRoleOptions',
		$role_options
	);

	wp_enqueue_style(
		'block-visibility-manager-editor-styles',
		plugins_url( 'build/index.css', __FILE__ ),
		array( 'wp-edit-blocks' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'block_visibility_manager_enqueue_editor_assets' );


/**
 * Processes and renders a block.
 *
 * @param string $block_content The content of the block to be processed.
 * @param array  $block An associative array containing block information.
 *
 * @return string The processed block content.
 */
function block_visibility_manager_filter_render_block( $block_content, $block ) {
	if (
		empty( $block['attrs']['bvmEnableVisibility'] ) ||
		! apply_filters( 'block_visibility_manager_should_render', true, $block['attrs'] )
	) {
		return $block_content;
	}

	$attributes = $block['attrs'] ?? array();

	if ( empty( $attributes['bvmEnableVisibility'] ) ) {
		return $block_content;
	}

	$visible = true;

	// Time.
	if ( ! empty( $attributes['bvmEnableTime'] ) && $attributes['bvmEnableTime'] ) {
		$now = current_time( 'H:i' );

		$from = $attributes['bvmTimeRange']['from'] ?? null;
		$to   = $attributes['bvmTimeRange']['to'] ?? null;

		if ( $from && $to ) {
			// Format times as strings "HH:MM".
			$time_from = sprintf( '%02d:%02d', $from['hours'], $from['minutes'] );
			$time_to   = sprintf( '%02d:%02d', $to['hours'], $to['minutes'] );

			if ( $now < $time_from || $now > $time_to ) {
				$visible = false;
			}
		}
	}

	// Date.
	if ( ! empty( $attributes['bvmEnableDate'] ) && $attributes['bvmEnableDate'] ) {
		$today = new DateTime( current_time( 'Y-m-d H:i:s' ) );

		$date_from_str = $attributes['bvmDateRange']['from'] ?? null;
		$date_to_str   = $attributes['bvmDateRange']['to'] ?? null;

		if ( $date_from_str && $date_to_str ) {
			$date_from = new DateTime( $date_from_str );
			$date_to   = new DateTime( $date_to_str );

			if ( $today < $date_from || $today > $date_to ) {
				$visible = false;
			}
		}
	}

	if ( ! $visible ) {
		return '';
	}

	// Device: just CSS (handled via class).
	$classes = '';
	if ( ! empty( $attributes['bvmHideOnMobile'] ) ) {
		$classes .= ' hide-on-mobile';
	}
	if ( ! empty( $attributes['bvmHideOnTablet'] ) ) {
		$classes .= ' hide-on-tablet';
	}
	if ( ! empty( $attributes['bvmHideOnDesktop'] ) ) {
		$classes .= ' hide-on-desktop';
	}

	// User Roles.
	if ( ! empty( $attributes['bvmUserRoles'] ) ) {
		if ( ! is_user_logged_in() ) {
			if ( in_array( 'guest', $attributes['bvmUserRoles'], true ) ) {
				return '';
			}
		}
		$user    = wp_get_current_user();
		$blocked = array_intersect( $attributes['bvmUserRoles'], $user->roles );
		if ( ! empty( $blocked ) ) {
			return;
		}
	}

	$block_content = new WP_HTML_Tag_Processor( $block_content );
	$block_content->next_tag(); /* first tag should always be ul or ol */
	$block_content->add_class( trim( $classes ) );
	$block_content->get_updated_html();

	return $block_content;
}
add_filter( 'render_block', 'block_visibility_manager_filter_render_block', 10, 2 );

/**
 * Enqueue frontend styles for the block.
 */
function block_visibility_manager_enqueue_frontend_css() {
	wp_enqueue_style( 'block-visibility-manager-style', plugin_dir_url( __FILE__ ) . 'build/style-index.css', array(), BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION );
}
add_action( 'wp_enqueue_scripts', 'block_visibility_manager_enqueue_frontend_css' );


add_action( 'admin_enqueue_scripts', 'block_visibility_manager_enqueue_admin_styles' );
/**
 * Enqueue admin styles for the Block Visibility Manager settings page.
 *
 * @param string $hook The current admin page hook suffix.
 */
function block_visibility_manager_enqueue_admin_styles( $hook ) {
	if ( 'settings_page_block-visibility-manager-settings' !== $hook ) {
		return;
	}
	wp_enqueue_style(
		'block-visibility-manager-admin-styles',
		plugin_dir_url( __FILE__ ) . 'build/index.css',
		array(),
		'1.0'
	);
}


/**
 * Include files if they exist.
 */
function block_visibility_manager_include_settings_file() {
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
add_action( 'init', 'block_visibility_manager_include_settings_file' );


register_activation_hook( __FILE__, 'block_visibility_manager_set_default_disabled_blocks' );
/**
 * Set default enabled blocks on plugin activation.
 */
function block_visibility_manager_set_default_disabled_blocks() {
	if ( get_option( 'block_visibility_manager_disabled_blocks', null ) === null ) {
		update_option( 'block_visibility_manager_disabled_blocks', block_visibility_manager_get_default_disabled_blocks() );
	}
}

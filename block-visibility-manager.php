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

// Load includes at plugin load time so helpers are available to the activation hook.
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';

/** Enqueue editor assets */
function block_visibility_manager_enqueue_editor_assets() {
	$index_js  = __DIR__ . '/build/index.js';
	$index_css = __DIR__ . '/build/index.css';

	wp_enqueue_script(
		'block-visibility-manager-editor',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-compose', 'wp-hooks', 'wp-data' ),
		file_exists( $index_js ) ? filemtime( $index_js ) : BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION,
		true
	);

	wp_localize_script(
		'block-visibility-manager-editor',
		'bvmEnabledBlocks',
		block_visibility_manager_get_enabled_blocks()
	);

	wp_localize_script(
		'block-visibility-manager-editor',
		'bvmRoleOptions',
		block_visibility_manager_get_all_roles()
	);

	wp_enqueue_style(
		'block-visibility-manager-editor-styles',
		plugins_url( 'build/index.css', __FILE__ ),
		array( 'wp-edit-blocks' ),
		file_exists( $index_css ) ? filemtime( $index_css ) : BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION
	);
}
add_action( 'enqueue_block_editor_assets', 'block_visibility_manager_enqueue_editor_assets' );


/**
 * Filters block rendering to apply conditional visibility rules.
 *
 * @param string $block_content The block's rendered HTML.
 * @param array  $block         Block data including attributes.
 * @return string The (possibly empty) rendered HTML.
 */
function block_visibility_manager_filter_render_block( $block_content, $block ) {
	if (
		empty( $block['attrs']['bvmEnableVisibility'] ) ||
		! apply_filters( 'block_visibility_manager_should_render', true, $block['attrs'] )
	) {
		return $block_content;
	}

	$attributes = $block['attrs'] ?? array();
	$visible    = true;

	// Time-based visibility (server time).
	if ( ! empty( $attributes['bvmEnableTime'] ) ) {
		$now  = current_time( 'H:i' );
		$from = $attributes['bvmTimeRange']['from'] ?? null;
		$to   = $attributes['bvmTimeRange']['to'] ?? null;

		if (
			is_string( $from ) && is_string( $to ) &&
			preg_match( '/^\d{2}:\d{2}$/', $from ) &&
			preg_match( '/^\d{2}:\d{2}$/', $to )
		) {
			if ( $now < $from || $now > $to ) {
				$visible = false;
			}
		}
	}

	// Date-based visibility (UTC).
	if ( ! empty( $attributes['bvmEnableDate'] ) ) {
		$today         = new DateTime( current_time( 'Y-m-d H:i:s' ) );
		$date_from_str = $attributes['bvmDateRange']['from'] ?? null;
		$date_to_str   = $attributes['bvmDateRange']['to'] ?? null;

		if ( $date_from_str && $date_to_str ) {
			try {
				$date_from = new DateTime( $date_from_str );
				$date_to   = new DateTime( $date_to_str );

				if ( $today < $date_from || $today > $date_to ) {
					$visible = false;
				}
			} catch ( Exception $e ) {
				$visible = false;
			}
		}
	}

	if ( ! $visible ) {
		return '';
	}

	// User role visibility.
	if ( ! empty( $attributes['bvmUserRoles'] ) ) {
		if ( ! is_user_logged_in() ) {
			if ( in_array( 'guest', $attributes['bvmUserRoles'], true ) ) {
				return '';
			}
		} else {
			$user    = wp_get_current_user();
			$blocked = array_intersect( $attributes['bvmUserRoles'], $user->roles );
			if ( ! empty( $blocked ) ) {
				return '';
			}
		}
	}

	// Device visibility via CSS classes.
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

	if ( '' === $classes ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( $processor->next_tag() ) {
		$processor->add_class( trim( $classes ) );
	}
	return $processor->get_updated_html();
}
add_filter( 'render_block', 'block_visibility_manager_filter_render_block', 10, 2 );

/**
 * Enqueue frontend styles.
 */
function block_visibility_manager_enqueue_frontend_css() {
	wp_enqueue_style(
		'block-visibility-manager-style',
		plugin_dir_url( __FILE__ ) . 'build/style-index.css',
		array(),
		BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'block_visibility_manager_enqueue_frontend_css' );


add_action( 'admin_enqueue_scripts', 'block_visibility_manager_enqueue_admin_styles' );
/**
 * Enqueue admin styles on the plugin's settings page.
 *
 * @param string $hook Current admin page hook suffix.
 */
function block_visibility_manager_enqueue_admin_styles( $hook ) {
	if ( 'settings_page_block-visibility-manager-settings' !== $hook ) {
		return;
	}
	$index_css = __DIR__ . '/build/index.css';
	wp_enqueue_style(
		'block-visibility-manager-admin-styles',
		plugin_dir_url( __FILE__ ) . 'build/index.css',
		array(),
		file_exists( $index_css ) ? filemtime( $index_css ) : BLOCK_VISIBILITY_MANAGER_PLUGIN_VERSION
	);
}


register_activation_hook( __FILE__, 'block_visibility_manager_set_default_disabled_blocks' );
/**
 * Set default enabled blocks on plugin activation.
 */
function block_visibility_manager_set_default_disabled_blocks() {
	if ( get_option( 'block_visibility_manager_disabled_blocks', null ) === null ) {
		update_option( 'block_visibility_manager_disabled_blocks', block_visibility_manager_get_default_disabled_blocks() );
	}
}

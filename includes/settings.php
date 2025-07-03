<?php
/**
 * Block Visibility Manager Settings Page.
 *
 * @package BlockVisibilityManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'block_visibility_manager_register_settings_page' );
/**
 * Registers the settings page in the WordPress admin menu.
 */
function block_visibility_manager_register_settings_page() {
	add_options_page(
		'Block Visibility Settings',
		'Block Visibility',
		'manage_options',
		'block-visibility-manager-settings',
		'block_visibility_manager_render_settings_page'
	);
}

/**
 * Retrieves all registered block types.
 *
 * @return array Array of block names and titles.
 */
function block_visibility_manager_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['block_visibility_manager_reset'] ) && check_admin_referer( 'block_visibility_manager_save_settings' ) ) {
		// Reset: Empty list means all blocks are enabled (default disabled mode).
		update_option( 'block_visibility_manager_disabled_blocks', block_visibility_manager_get_default_disabled_blocks() );
		echo '<div class="updated"><p>Settings reset to default (all enabled).</p></div>';
	}

	if ( isset( $_POST['block_visibility_manager_save'] ) && check_admin_referer( 'block_visibility_manager_save_settings' ) ) {
		$enabled_blocks  = isset( $_POST['block_visibility_manager_enabled_blocks'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['block_visibility_manager_enabled_blocks'] ) ) : array();
		$all_blocks      = block_visibility_manager_get_all_blocks_in_settings();
		$disabled_blocks = array_diff( $all_blocks, $enabled_blocks );

		update_option( 'block_visibility_manager_disabled_blocks', $disabled_blocks );
		echo '<div class="updated"><p>Settings saved.</p></div>';
	}

	echo '<style>
	.block-visibility-manager-card-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
		gap: 16px;
	}
	.block-visibility-manager-card {
		border: 1px solid #ccc;
		border-radius: 6px;
		padding: 12px;
		background: #fff;
		box-shadow: 0 1px 2px rgba(0,0,0,0.05);
	}
	.block-visibility-manager-card h4 {
		margin: 0 0 6px;
		font-size: 16px;
	}
	.block-visibility-manager-card label {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 14px;
	}
</style>';

	$grouped_blocks = block_visibility_manager_group_blocks_by_category();
	$category_map   = block_visibility_manager_get_block_category_map();
	$saved_blocks   = get_option( 'block_visibility_manager_disabled_blocks', null );

	if ( is_null( $saved_blocks ) ) {
		// First time, use defaults.
		$disabled_blocks = block_visibility_manager_get_default_disabled_blocks();
	} else {
		$disabled_blocks = $saved_blocks;
	}

	echo '<div class="wrap">';
	echo '<h1>Block Visibility Settings</h1>';
	echo '<form method="post">';
	wp_nonce_field( 'block_visibility_manager_save_settings' );

	foreach ( $category_map as $slug => $label ) {
		if ( empty( $grouped_blocks[ $slug ] ) ) {
			continue;
		}

		echo '<h2>' . esc_html( $label ) . '</h2>';
		echo '<div class="block-visibility-manager-card-grid">';

		foreach ( $grouped_blocks[ $slug ] as $block_name => $data ) {
			$is_enabled = ! in_array( $block_name, $disabled_blocks, true );
			echo '<div class="block-visibility-manager-card">';
			echo '<h4>' . esc_html( $data['title'] ) . '</h4>';
			echo '<label>';
			echo '<input type="checkbox" name="block_visibility_manager_enabled_blocks[]" value="' . esc_attr( $block_name ) . '" ' . checked( $is_enabled, true, false ) . ' />';
			echo ' Enable';
			echo '</label>';
			echo '</div>';
		}

		echo '</div>';
	}

	echo '<div style="display: flex; gap: 0.5rem;">';
	submit_button( 'Save Settings', 'primary', 'block_visibility_manager_save' );

	echo '<p class="submit"><input type="submit" name="block_visibility_manager_reset" id="block_visibility_manager_reset" class="button button-secondary" value="Reset to Default" onclick="return confirm(\'Are you sure you want to reset to default ? \');"></p>';
		echo '</div>';
		echo '</form>';
		echo '</div>';
}

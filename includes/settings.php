<?php
/**
 * Block Visibility Manager Settings Page.
 *
 * @package BlockVisibilityManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'bvm_register_settings_page' );
/**
 * Registers the settings page in the WordPress admin menu.
 */
function bvm_register_settings_page() {
	add_options_page(
		'Block Visibility Settings',
		'Block Visibility',
		'manage_options',
		'bvm-settings',
		'bvm_render_settings_page'
	);
}

/**
 * Retrieves all registered block types.
 *
 * @return array Array of block names and titles.
 */
function bvm_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['bvm_save'] ) && check_admin_referer( 'bvm_save_settings' ) ) {
		$enabled_blocks  = isset( $_POST['bvm_enabled_blocks'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bvm_enabled_blocks'] ) ) : array();
		$all_blocks      = bvm_get_all_blocks_in_settings();
		$disabled_blocks = array_diff( $all_blocks, $enabled_blocks );

		update_option( 'bvm_disabled_blocks', $disabled_blocks );
		echo '<div class="updated"><p>Settings saved.</p></div>';
	}

	echo '<style>
	.bvm-card-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
		gap: 16px;
	}
	.bvm-card {
		border: 1px solid #ccc;
		border-radius: 6px;
		padding: 12px;
		background: #fff;
		box-shadow: 0 1px 2px rgba(0,0,0,0.05);
	}
	.bvm-card h4 {
		margin: 0 0 6px;
		font-size: 16px;
	}
	.bvm-card label {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 14px;
	}
</style>';

	$grouped_blocks = bvm_group_blocks_by_category();
	$category_map   = bvm_get_block_category_map();
	$saved_blocks   = get_option( 'bvm_disabled_blocks', null );

	if ( is_null( $saved_blocks ) ) {
		// First time, use defaults.
		$disabled_blocks = bvm_get_default_disabled_blocks();
	} else {
		$disabled_blocks = $saved_blocks;
	}

	echo '<div class="wrap">';
	echo '<h1>Block Visibility Settings</h1>';
	echo '<form method="post">';
	wp_nonce_field( 'bvm_save_settings' );

	foreach ( $category_map as $slug => $label ) {
		if ( empty( $grouped_blocks[ $slug ] ) ) {
			continue;
		}

		echo '<h2>' . esc_html( $label ) . '</h2>';
		echo '<div class="bvm-card-grid">';

		foreach ( $grouped_blocks[ $slug ] as $block_name => $data ) {
			$is_enabled = ! in_array( $block_name, $disabled_blocks, true );
			echo '<div class="bvm-card">';
			echo '<h4>' . esc_html( $data['title'] ) . '</h4>';
			echo '<label>';
			echo '<input type="checkbox" name="bvm_enabled_blocks[]" value="' . esc_attr( $block_name ) . '" ' . checked( $is_enabled, true, false ) . ' />';
			echo ' Enable';
			echo '</label>';
			echo '</div>';
		}

		echo '</div>';
	}

	submit_button( 'Save Settings', 'primary', 'bvm_save' );
	echo '</form>';
	echo '</div>';
}

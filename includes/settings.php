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
		$enabled_blocks = isset( $_POST['bvm_enabled_blocks'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bvm_enabled_blocks'] ) ) : array();
		update_option( 'bvm_enabled_blocks', $enabled_blocks );
		echo '<div class="updated"><p>Settings saved.</p></div>';
	}

	$all_blocks = bvm_get_all_block_types();
	$enabled    = get_option( 'bvm_enabled_blocks', array() );

	echo '<div class="wrap">';
	echo '<h1>Block Visibility Settings</h1>';
	echo '<form method="post">';
	wp_nonce_field( 'bvm_save_settings' );

	echo '<table class="form-table"><tbody>';
	foreach ( $all_blocks as $block_name => $title ) {
		echo '<tr>';
		echo '<th scope="row">' . esc_html( $title ) . '</th>';
		echo '<td><label>';
		echo '<input type="checkbox" name="bvm_enabled_blocks[]" value="' . esc_attr( $block_name ) . '" ' . checked( in_array( $block_name, $enabled ), true, false ) . ' />';
		echo ' Enable visibility for this block';
		echo '</label></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	submit_button( 'Save Settings', 'primary', 'bvm_save' );
	echo '</form>';
	echo '</div>';
}

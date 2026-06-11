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
 * Renders the settings page and handles form submissions.
 */
function block_visibility_manager_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['block_visibility_manager_reset'] ) ) {
		check_admin_referer( 'bvm_reset_settings', 'bvm_reset_nonce' );
		update_option( 'block_visibility_manager_disabled_blocks', block_visibility_manager_get_default_disabled_blocks() );
		echo '<div class="updated"><p>Settings reset to default (all enabled).</p></div>';
	}

	if ( isset( $_POST['block_visibility_manager_save'] ) ) {
		check_admin_referer( 'bvm_save_settings', 'bvm_save_nonce' );
		$enabled_blocks  = isset( $_POST['block_visibility_manager_enabled_blocks'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['block_visibility_manager_enabled_blocks'] ) ) : array();
		$all_blocks      = block_visibility_manager_get_all_blocks_in_settings();
		$disabled_blocks = array_diff( $all_blocks, $enabled_blocks );

		update_option( 'block_visibility_manager_disabled_blocks', $disabled_blocks );
		echo '<div class="updated"><p>Settings saved.</p></div>';
	}

	$grouped_blocks = block_visibility_manager_group_blocks_by_category();
	$category_map   = block_visibility_manager_get_block_category_map();
	$saved_blocks   = get_option( 'block_visibility_manager_disabled_blocks', null );

	$disabled_blocks = is_null( $saved_blocks )
		? block_visibility_manager_get_default_disabled_blocks()
		: $saved_blocks;

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Block Visibility Settings', 'block-visibility-manager' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Control which blocks include the visibility option.', 'block-visibility-manager' ); ?></p>
		<form method="post">
			<?php
			wp_nonce_field( 'bvm_save_settings', 'bvm_save_nonce' );
			wp_nonce_field( 'bvm_reset_settings', 'bvm_reset_nonce' );
			?>

			<?php foreach ( $category_map as $slug => $label ) : ?>
				<?php
				if ( empty( $grouped_blocks[ $slug ] ) ) {
					continue;}
				?>
				<h2><?php echo esc_html( $label ); ?></h2>
				<div class="block-visibility-manager-card-grid">
					<?php foreach ( $grouped_blocks[ $slug ] as $block_name => $data ) : ?>
						<?php $is_enabled = ! in_array( $block_name, $disabled_blocks, true ); ?>
						<div class="block-visibility-manager-card">
							<h4><?php echo esc_html( $data['title'] ); ?></h4>
							<label>
								<input
									type="checkbox"
									name="block_visibility_manager_enabled_blocks[]"
									value="<?php echo esc_attr( $block_name ); ?>"
									<?php checked( $is_enabled ); ?>
								/>
								<?php esc_html_e( 'Enable', 'block-visibility-manager' ); ?>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<div style="display: flex; gap: 0.5rem;">
			<?php submit_button( esc_html__( 'Save Settings', 'block-visibility-manager' ), 'primary', 'block_visibility_manager_save' ); ?>
				<p class="submit">
					<input
						type="submit"
						name="block_visibility_manager_reset"
						class="button button-secondary"
						value="<?php esc_attr_e( 'Reset to Default', 'block-visibility-manager' ); ?>"
						onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset to default?', 'block-visibility-manager' ) ); ?>');"
					/>
				</p>
			</div>
		</form>
	</div>
	<?php
}

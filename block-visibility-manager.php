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
function bvm_enqueue_editor_assets() {
    wp_enqueue_script(
        'bvm-editor',
        plugin_dir_url(__FILE__) . 'build/index.js',
        [ 'wp-blocks', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ],
        filemtime(__DIR__ . '/build/index.js')
    );
}
add_action('enqueue_block_editor_assets', 'bvm_enqueue_editor_assets');

function bvm_filter_render_block($block_content, $block) {
    if (
        empty($block['attrs']['bvmEnableVisibility']) ||
        !apply_filters('bvm_should_render', true, $block['attrs'])
    ) {
        return $block_content;
    }

    ob_start();
    include __DIR__ . '/render.php';
    return ob_get_clean();
}
add_filter('render_block', 'bvm_filter_render_block', 10, 2);

function bvm_enqueue_frontend_css() {
    wp_enqueue_style('bvm-style', plugin_dir_url(__FILE__) . 'build/style-index.css');
}
add_action('wp_enqueue_scripts', 'bvm_enqueue_frontend_css');
<?php
/**
 * Block Visibility Manager Helpers.
 *
 * @package BlockVisibilityManager
 */

/**
 * Get all registered block types.
 */
function bvm_get_all_block_types() {

	$registry = WP_Block_Type_Registry::get_instance();
	$blocks   = $registry->get_all_registered();
	$list     = array();

	foreach ( $blocks as $block_name => $block ) {
		$list[ $block_name ] = $block->title ?? $block_name;
	}

	ksort( $list );

	return $list;
}

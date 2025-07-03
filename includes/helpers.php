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

/**
 * Get all registered block types grouped by namespace.
 */
function bvm_group_block_types_by_namespace() {
	$blocks  = WP_Block_Type_Registry::get_instance()->get_all_registered();
	$grouped = array();

	foreach ( $blocks as $name => $block ) {
		[ $namespace, $slug ] = explode( '/', $name );
		if ( ! isset( $grouped[ $namespace ] ) ) {
			$grouped[ $namespace ] = array();
		}
		$grouped[ $namespace ][ $name ] = $block->title ?? $name;
	}

	ksort( $grouped );
	return $grouped;
}

/**
 * Group blocks by category.
 */
function bvm_group_blocks_by_category() {
	$blocks     = WP_Block_Type_Registry::get_instance()->get_all_registered();
	$categories = bvm_get_block_category_map();
	$grouped    = array();

	foreach ( $blocks as $block_name => $block ) {
		// Skip if not shown in Inserter.
		if ( isset( $block->supports['inserter'] ) && false === $block->supports['inserter'] ) {
			continue;
		}

		$category = $block->category ?? 'custom';
		if ( ! isset( $grouped[ $category ] ) ) {
			$grouped[ $category ] = array();
		}

		$grouped[ $category ][ $block_name ] = array(
			'title' => $block->title ?? $block_name,
		);
	}

	return $grouped;
}

/**
 * Get block category map.
 */
function bvm_get_block_category_map() {
	return array(
		'text'    => 'Text',
		'media'   => 'Media',
		'design'  => 'Design',
		'widgets' => 'Widgets',
		'theme'   => 'Theme',
		'embed'   => 'Embed',
		'custom'  => 'Custom',
	);
}

/**
 * Get default enabled blocks.
 */
function bvm_get_default_enabled_blocks() {
	return array(
		'core/paragraph',
		'core/heading',
		'core/image',
		'core/group',
	);
}

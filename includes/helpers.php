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

	/*
	echo '<pre>';
	print_r( $blocks );
	echo '</pre>';
	*/
	foreach ( $blocks as $block_name => $block ) {
		// Skip if not shown in Inserter.
		if ( isset( $block->supports['inserter'] ) && false === $block->supports['inserter'] ) {
			continue;
		}

		// Exclude blocks that have a 'parent' property (child blocks).
		/*
		if ( ! empty( $block->parent ) && is_array( $block->parent ) && count( $block->parent ) > 0 ) {
			continue;
		} */

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
 * Get default disabled blocks.
 */
function bvm_get_default_disabled_blocks() {
	return array(
		'core/freeform',
		'core/navigation-submenu',
		'core/more',
		'core/nextpage',
		'core/separator',
		'core/spacer',
        'core/avatar',
        'core/comment-author-name',
        'core/comment-content',
        'core/comment-date',
        'core/comment-edit-link',
        'core/comment-reply-link',
        'core/comments',
        'core/comments-pagination',
        'core/comments-pagination-next',
        'core/comments-pagination-numbers',
        'core/comments-pagination-previous',
        'core/comments-title',
        'core/loginout',
        'core/navigation',
        'core/post-author',
        'core/post-author-biography',
        'core/post-author-name',
        'core/post-comments-form',
        'core/post-content',
        'core/post-date',
        'core/post-excerpt',
        'core/post-featured-image',
        'core/post-navigation-link',
        'core/post-template',
        'core/post-terms',
        'core/post-title',
        'core/query',
        'core/query-no-results',
        'core/query-pagination',
        'core/query-pagination-next',
        'core/query-pagination-numbers',
        'core/query-pagination-previous',
        'core/query-title',
        'core/query-total',
        'core/read-more',
        'core/site-logo',
        'core/site-tagline',
        'core/site-title',
        'core/template-part',
        'core/term-description',
	);
}

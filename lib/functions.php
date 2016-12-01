<?php

/**
 * @function wplb_get_layout_definitions
 * @since 1.0.0
 */
function wplb_get_layout_definitions()
{
	static $data = null;

	$args = array(
		'posts_per_page'   => 0,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'wplb-layout',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	   => '',
		'author_name'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true
	);

	$definitions = array();

	if ($data == null) {
		$data = get_posts($args);
	}

	foreach ($data as $definition) {

		$blocks = wpbb_get_blocks($definition->ID);
		$layout = array_shift($blocks);

		$definitions[] = array(
			'id' => $definition->ID,
			'name' => $definition->post_title,
			'layout' => $layout,
			'blocks' => $blocks,
		);
	}

	return $definitions;
}

/**
 * @function wplb_get_layouts
 * @since 1.0.0
 */
function wplb_get_layouts($stack_id)
{
	static $data = null;

	$args = array(
		'posts_per_page'   => 0,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'wplb-layout',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	   => '',
		'author_name'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true
	);

	$layouts = array();

	if ($data == null) {
		$data = get_posts($args);
	}

	$page_layouts = get_post_meta($stack_id, '_wplb_layouts', true);

	foreach ($data as $definition) {

		foreach ($page_layouts as $block_id => $definition_id) {

			if ($definition->ID != $definition_id) {
				continue;
			}

			$blocks = wpbb_get_blocks($definition->ID);
			$layout = array_shift($blocks);

			$layouts[$block_id] = array(
				'id' => $definition->ID,
				'name' => $definition->post_title,
				'blocks' => $blocks,
				'layout' => $layout
			);
		}
	}

	return $layouts;
}

/**
 * @function wplb_get_layout
 * @since 1.0.0
 */
function wplb_get_layout($stack_id, $block_id)
{
	static $layouts = null;

	if ($layouts == null) {
		$layouts = wplb_get_layouts($stack_id);
	}

	return isset($layouts[$block_id]) ? $layouts[$block_id] : null;
}
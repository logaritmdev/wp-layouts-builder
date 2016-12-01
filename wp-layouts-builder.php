<?php
/*
Plugin Name: WP Layouts Builder
Plugin URI: http://logaritm.ca
Description: Creates layouts that contains blocks.
Version: 1.0.0
Author: Jean-Philippe Dery (jean-philippe.dery@logaritm.ca)
Author URI: http://logaritm.ca
License: MIT
Copyright: Jean-Philippe Dery
Mention: JBLP (jblp.ca)
*/

define('WP_LAYOUT_BUILDER_VERSION', '1.0.0');
define('WP_LAYOUT_BUILDER_PLUGIN_URL', plugins_url('/', __FILE__));

require_once __DIR__ . '/lib/functions.php';

//------------------------------------------------------------------------------
// Post Types
//------------------------------------------------------------------------------

$labels = array(
	'name'               => _x('Layouts', 'post type general name', 'your-plugin-textdomain' ),
	'singular_name'      => _x('Layout', 'post type singular name', 'your-plugin-textdomain' ),
	'menu_name'          => _x('Layouts', 'admin menu', 'your-plugin-textdomain' ),
	'name_admin_bar'     => _x('Layout', 'add new on admin bar', 'your-plugin-textdomain' ),
	'add_new'            => _x('Add new', 'Layout', 'your-plugin-textdomain' ),
	'add_new_item'       => __('Add new layout', 'your-plugin-textdomain' ),
	'new_item'           => __('New layout', 'your-plugin-textdomain' ),
	'edit_item'          => __('Edit layout', 'your-plugin-textdomain' ),
	'view_item'          => __('View layout', 'your-plugin-textdomain' ),
	'all_items'          => __('All layouts', 'your-plugin-textdomain' ),
	'search_items'       => __('Search layouts', 'your-plugin-textdomain' ),
	'parent_item_colon'  => __('Parent layouts:', 'your-plugin-textdomain' ),
	'not_found'          => __('No layouts found.', 'your-plugin-textdomain' ),
	'not_found_in_trash' => __('No layouts found in Trash.', 'your-plugin-textdomain' )
);

register_post_type('wplb-layout', array(
	'labels'             => $labels,
	'description'        => '',
	'public'             => false,
	'publicly_queryable' => false,
	'show_ui'            => true,
	'show_in_menu'       => false,
	'query_var'          => false,
	'rewrite'            => false,
	'capability_type'    => 'post',
	'has_archive'        => false,
	'hierarchical'       => false,
	'menu_position'      => null,
	'supports'           => array('title')
));

/**
 * @action init
 * @since 1.0.0
 */
add_action('init', function() {

	if (!function_exists('wpbb_get_block_types')) {
		return;
	}

	$layouts = array();

	foreach (wpbb_get_block_types() as $block_type) {
		if ($block_type['category'] === 'Layout') {
			$layouts[$block_type['buid']] = $block_type['name'];
		}
	}

	if (function_exists('acf_add_local_field_group')) acf_add_local_field_group(array(
		'key' => 'group_57dab63eb6a82',
		'title' => 'Layout',
		'fields' => array(
			array(
				'key' => 'field_57dab64ad6e4a',
				'label' => 'Layout',
				'name' => 'layout',
				'type' => 'select',
				'instructions' => 'This is an instruction',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => $layouts,
				'default_value' => array(
				),
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'return_format' => 'value',
				'placeholder' => '',
			)
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'wplb-layout',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

});

/**
 * @action admin_menu
 * @since 1.0.0
 */
add_action('admin_menu', function() {
	global $submenu;
	$submenu['themes.php'][] = array('Layouts', 'manage_options', 'edit.php?post_type=wplb-layout');
});

/**
 * @action admin_enqueue_scripts
 * @since 1.0.0
 */
add_action('admin_enqueue_scripts', function() {
	if (get_post_type() == 'wplb-layout') {
		wp_enqueue_script('wplb_layout_editor_js', WP_LAYOUT_BUILDER_PLUGIN_URL . 'assets/js/admin-layout-editor.js', array('wpbb_admin_metabox_js'), WP_LAYOUT_BUILDER_VERSION);
		wp_enqueue_style('wplb_layout_editor_css', WP_LAYOUT_BUILDER_PLUGIN_URL . 'assets/css/admin-layout-editor.css', array('wpbb_admin_metabox_css'), WP_LAYOUT_BUILDER_VERSION);
	}
});

/**
 * @filter wpbb/content_types
 * @since 1.0.0
 */
add_filter('wpbb/content_types', function($context) {
	$context[] = 'wplb-layout';
	return $context;
});

/**
 * @filter wpbb/metabox_title
 * @since 1.0.0
 */
add_filter('wpbb/metabox_title', function($title, $post_type) {

	if ($post_type == 'wplb-layout') {
		return 'Layout';
	}

	return $title;

}, 10, 2);

/**
 * @filter wpbb/metabox_priority
 * @since 1.0.0
 */
add_filter('wpbb/metabox_priority', function($priority, $post_type) {

	if ($post_type == 'wplb-layout') {
		return 'default';
	}

	return $priority;

}, 10, 2);

/**
 * @filter wpbb/preview_header
 * @since 1.0.0
 */
add_filter('wpbb/preview_header', function($header, $block) {

	if (get_post_type() === 'page') {

		if ($block->infos['category'] === 'Layout') {

			$definitions = wplb_get_layout_definitions();

			$layouts = array_filter(array_map(function($definition) use ($block) {

				if ($definition['layout']['buid'] != $block->infos['buid']) {
					return null;
				}

				$selected = wplb_get_layout(
					$block->get_stack_id(),
					$block->get_block_id()
				);

				if ($selected['id'] == $definition['id']) {
					$selected = true;
				} else {
					$selected = false;
				}

				return array(
					'id' => $definition['id'],
					'name' => $definition['name'],
					'selected' => $selected
				);

			}, $definitions), function($layout) {
				return $layout;
			});

			ob_start();

			?>

			<label>Inherits:</label>
			<select name="_wplb_layouts[<?php echo $block->get_block_id() ?>]">
				<option>Do not inherit an existing layout</option>
				<?php foreach ($layouts as $layout) : ?>
					<option <?php echo $layout['selected'] ? 'selected="selected"' : '' ?> value="<?php echo $layout['id'] ?>">
						<?php echo $layout['name'] ?>
					</option>
				<?php endforeach ?>
			</select>

			<?php

			$header = $header . ob_get_contents();

			ob_end_clean();
		}
	}

	return $header;

}, 10, 2);

/**
 * @filter wpbb/preview_footer
 * @since 1.0.0
 */
add_filter('wpbb/preview_footer', function($footer, $block) {

	return $footer;

}, 10, 2);

/**
 * @filter wpbb/sub_blocks
 * @since 1.0.0
 */
add_filter('wpbb/sub_blocks', function($blocks, $block) {

	if (!function_exists('wpbb_get_content_types')) {
		return $blocks;
	}

	foreach (wpbb_get_content_types() as $post_type) {

		if (get_post_type() == $post_type) {

			if ($block->infos['category'] === 'Layout') {

				$layout = wplb_get_layout(
					$block->get_stack_id(),
					$block->get_block_id()
				);

				if (isset($layout['blocks'])) {

					foreach ($layout['blocks'] as $child) {
						$child['super_id'] = $block->get_block_id();
						$child['disable'] = true;
						$blocks[] = $child;
					}

					$ordered_blocks = array();
					$ordered_offset = 0;

					foreach ($blocks as $index => $block) {

						$position = isset($block['position']) ? $block['position'] : null;

						if ($position !== null) {

							if (isset($ordered_blocks[$position])) {

								$ordered_blocks = array_merge(
									array_slice($ordered_blocks, 0, $position), array($block),
									array_slice($ordered_blocks, $position)
								);

								continue;
							}

							$ordered_blocks[$position] = $block;
							continue;
						}

						while (isset($ordered_blocks[$ordered_offset])) {
							$ordered_offset++;
						}

						$ordered_blocks[$ordered_offset] = $block;
					}

					ksort($ordered_blocks);

					$blocks = $ordered_blocks;
				}
			}
		}
	}

	return $blocks;

}, 10, 2);

/**
 * @filter wpbb/render
 * @since 1.0.0
 */
add_filter('wpbb/render', function($render, $block) {

	if (get_post_type() === 'page') {

		if ($block->infos['category'] === 'Layout') {

			$layout = wplb_get_layout(
				$block->get_stack_id(),
				$block->get_block_id()
			);
			// What's supposed to be happening here
			return $render;
		}
	}

	return $render;

}, 10, 2);

/**
 * @action wpbb/save_block
 * @since 1.0.0
 */
add_action('wpbb/save_block', function($stack_id, $blocks) {

	if (get_post_type() === 'page') {

		$save = isset(
			$_POST['_wpbb_blocks']
		) ? $_POST['_wpbb_blocks'] : array();

		foreach ($save as $position => $block_id) {
			foreach ($blocks as &$block) {
				if ($block['block_id'] == $block_id) {
					$block['position'] = $position;
				}
			}
		}

		update_post_meta($stack_id, '_wpbb_blocks', $blocks);
	}

	$layouts = isset(
		$_POST['_wplb_layouts']
	) ? $_POST['_wplb_layouts'] : array();

	update_post_meta($stack_id, '_wplb_layouts', $layouts);

}, 10, 2);
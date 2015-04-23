<?php
/**
 *
 * Created by themify
 *
 * @since 1.0.0
 */

/**
 * Setup general options
 * @return mixed|void
 */
function themify_base_settings_config() {
	$pre = 'setting-';
	return apply_filters( 'themify_base_settings_config', array(
		$pre . 'footer_widgets' => array(
		   'name' => __( 'Footer Widgets', 'themify' ),
		   'type' => 'layout',
		   'args' => array(
			   'std'     => 'footerwidget-3col',
				'options' => array(
					array(
					   'value' => 'footerwidget-4col',
					   'img'   => 'themify/img/sidebars/4col.png',
					   'title' => __( 'Widgets 4 Columns', 'themify' )
					),
					array(
					   'value'    => 'footerwidget-3col',
					   'img'      => 'themify/img/sidebars/3col.png',
					   'title'    => __( 'Widgets 3 Columns', 'themify' ),
					   'selected' => true
					),
					array(
					   'value' => 'footerwidget-2col',
					   'img'   => 'themify/img/sidebars/2col.png',
					   'title' => __( 'Widgets 2 Columns', 'themify' )
					),
					array(
					   'value' => 'footerwidget-1col',
					   'img'   => 'themify/img/sidebars/1col.png',
					   'title' => __( 'Widgets 1 Column', 'themify' )
					),
					array(
					   'value' => 'none',
					   'img'   => 'themify/img/sidebars/none.png',
					   'title' => __( 'No Widgets', 'themify' )
					)
				),
			   'desc'    => __( 'Select the number of sidebars in footer.', 'themify' ),
		   ),
		),
		$pre . 'default_layout' => array(
		   'name' => __( 'Index Layout', 'themify' ),
		   'type' => 'layout',
		   'args' => array(
			   'std'     => 'sidebar1',
			   'options' => array(
				   array(
					   'value' => 'sidebar1',
					   'img' => 'themify/img/sidebars/sidebar1.png',
					   'title' => __( 'Sidebar Right', 'themify' ),
					   'selected' => true,
				   ),
				   array(
					   'value' => 'sidebar1 sidebar-left',
					   'img'   => 'themify/img/sidebars/sidebar1-left.png',
					   'title' => __( 'Sidebar Left', 'themify' )
				   ),
				   array(
					   'value' => 'sidebar-none',
					   'img'   => 'themify/img/sidebars/sidebar-none.png',
					   'title' => __( 'No Sidebar', 'themify' )
				   )
			   ),
			   'desc'    => __( 'Select the sidebar layout for index and archive views.', 'themify' ),
		   ),
		),
		$pre . 'default_archive_content' => array(
		   'name' => __( 'Archive Entries Content', 'themify' ),
		   'type' => 'select',
		   'args' => array(
			   'std'     => 'full',
			   'options' => array(
				   array(
					   'value' => 'full',
					   'title' => __( 'Full', 'themify' ),
					   'selected' => true,
				   ),
				   array(
					   'value' => 'excerpt',
					   'title' => __( 'Excerpt', 'themify' ),
				   ),
				   array(
					   'value' => 'none',
					   'title' => __( 'None', 'themify' ),
				   ),
			   ),
			   'desc'    => __( 'Show full content, an excerpt or none in archive view entries.', 'themify' ),
		   ),
		),
		$pre . 'default_page_layout' => array(
		   'name' => __( 'Page Layout', 'themify' ),
		   'type' => 'layout',
		   'args' => array(
			   'std'     => 'sidebar1',
			   'options' => array(
				   array(
					   'value' => 'sidebar1',
					   'img' => 'themify/img/sidebars/sidebar1.png',
					   'selected' => true,
					   'title' => __( 'Sidebar Right', 'themify' ),
				   ),
				   array(
					   'value' => 'sidebar1 sidebar-left',
					   'img'   => 'themify/img/sidebars/sidebar1-left.png',
					   'title' => __( 'Sidebar Left', 'themify' )
				   ),
				   array(
					   'value' => 'sidebar-none',
					   'img'   => 'themify/img/sidebars/sidebar-none.png',
					   'title' => __( 'No Sidebar', 'themify' )
				   )
			   ),
			   'desc'    => __( 'Select the sidebar layout for single page views.', 'themify' ),
		   ),
		),
		$pre . 'default_page_post_layout' => array(
			'name' => __( 'Post Layout', 'themify' ),
			'type' => 'layout',
			'args' => array(
				'std'     => 'sidebar1',
				'options' => array(
				   array(
					   'value' => 'sidebar1',
					   'img' => 'themify/img/sidebars/sidebar1.png',
					   'title' => __( 'Sidebar Right', 'themify' ),
					   'selected' => true,
				   ),
				   array(
					   'value' => 'sidebar1 sidebar-left',
					   'img'   => 'themify/img/sidebars/sidebar1-left.png',
					   'title' => __( 'Sidebar Left', 'themify' )
				   ),
				   array(
					   'value' => 'sidebar-none',
					   'img'   => 'themify/img/sidebars/sidebar-none.png',
					   'title' => __( 'No Sidebar', 'themify' )
				   )
				),
				'desc'    => __( 'Select the sidebar layout for single post views.', 'themify' ),
			),
		),
	));
}

/**
 * Visual options module function.
 *
 * @param array $args Arguments for module.
 */
function themify_base_setting_field_layout( $args ) {
	global $themify_settings;
	$data = $themify_settings;
	$field_id = $args['field_id'];
	$wrap_class = isset( $args['class'] ) ? $args['class'] : '';

	if ( isset( $data[$field_id] ) ) {
		$val = $data[$field_id];
	} elseif ( isset( $args['std'] ) ) {
		$val = $args['std'];
	} else {
		$val = '';
	}

	$out = '<div class="layout-options-wrap ' . $wrap_class . '">';

	foreach ( $args['options'] as $option ) {

		$class = $val == $option['value'] ? 'selected' : '';

		$out .= '<a href="#" class="preview-icon ' . $class . '" title="' . $option['title'] . '"><img src="' . THEME_URI . '/' . $option['img'] . '" alt="' . $option['value'] . '"  /></a>';
	}

	$out .= '</div>';

	$out .= '<input type="hidden" name="themify_settings[' . $field_id . ']" class="val" value="' . $val . '" />';

	if ( isset( $args['desc'] ) ) {
		$out .= '<p><span class="description">' . $args['desc'] . '</span></p>';
	}
	echo $out;
}

/**
 * Select dropdown module function.
 *
 * @param array $args Arguments for module.
 */
function themify_base_setting_field_select( $args ) {
	global $themify_settings;
	$data = $themify_settings;
	$field_id = $args['field_id'];
	$wrap_class = isset( $args['class'] ) ? $args['class'] : '';

	if ( isset( $data[$field_id] ) ) {
		$val = $data[$field_id];
	} elseif ( isset( $args['std'] ) ) {
		$val = $args['std'];
	} else {
		$val = '';
	}

	$out = '<div class="select-options-wrap ' . $wrap_class . '">';

	$out .= '<select name="themify_settings[' . $field_id . ']">';
	foreach ( $args['options'] as $option ) {
		$out .= '<option value="' . $option['value'] . '" ' . selected( $val, $option['value'], false ) . '>' . $option['title'] . '</option>';
	}
	$out .= '</select>';

	$out .= '</div>';

	if ( isset( $args['desc'] ) ) {
		$out .= '<p><span class="description">' . $args['desc'] . '</span></p>';
	}
	echo $out;
}
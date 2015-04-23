<?php 
/**
 * annina functions and dynamic template
 *
 * @package annina
 */
 
/**
 * Custom Excerpt Length
 */
function annina_custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'annina_custom_excerpt_length', 999 );

/**
 * Replace Excerpt More
 */
function annina_new_excerpt_more( $more ) {
	return '...';
}
add_filter('excerpt_more', 'annina_new_excerpt_more');

 /**
 * Delete font size style from tag cloud widget
 */
function annina_fix_tag_cloud($tag_string){
   return preg_replace("/style='font-size:.+pt;'/", '', $tag_string);
}
add_filter('wp_generate_tag_cloud', 'annina_fix_tag_cloud',10,3);

 /**
 * Register All Colors and Section
 */
function annina_color_primary_register( $wp_customize ) {
	$colors = array();
	
	$colors[] = array(
	'slug'=>'text_color_first', 
	'default' => '#222222',
	'label' => __('Box Text Color', 'annina')
	);
	
	$colors[] = array(
	'slug'=>'header_background_fourth', 
	'default' => '#222222',
	'label' => __('Header Background', 'annina')
	);
	
	$colors[] = array(
	'slug'=>'box_color_second', 
	'default' => '#ffffff',
	'label' => __('Box Background Color', 'annina')
	);
	
	$colors[] = array(
	'slug'=>'special_color_third', 
	'default' => '#dd4c39',
	'label' => __('Special Color', 'annina')
	);
	
	foreach( $colors as $annina_theme_options ) {
	// SETTINGS
		$wp_customize->add_setting( 'annina_theme_options[' . $annina_theme_options['slug'] . ']', array(
			'default' => $annina_theme_options['default'],
			'type' => 'option', 
			'sanitize_callback' => 'sanitize_hex_color',
			'capability' => 'edit_theme_options'
		)
	);
	// CONTROLS
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			$annina_theme_options['slug'], 
			array('label' => $annina_theme_options['label'], 
			'section' => 'colors',
			'settings' =>'annina_theme_options[' . $annina_theme_options['slug'] . ']',
			)
		)
	);
	
	}
}
add_action( 'customize_register', 'annina_color_primary_register' );

/**
 * Add Custom CSS to Header 
 */
function annina_custom_css_styles() { 	
	global $annina_theme_options;
	$se_options = get_option( 'annina_theme_options', $annina_theme_options );
	if( isset( $se_options[ 'text_color_first' ] ) ) {
		$text_color_first = $se_options['text_color_first'];
	}
	if( isset( $se_options[ 'box_color_second' ] ) ) {
		$box_color_second = $se_options['box_color_second'];
	}
	if( isset( $se_options[ 'special_color_third' ] ) ) {
		$special_color_third = $se_options['special_color_third'];
	}
	if( isset( $se_options[ 'header_background_fourth' ] ) ) {
		$header_background_fourth = $se_options['header_background_fourth'];
	}
?>

<style type="text/css">
	<?php if (!empty($text_color_first) && $text_color_first != '#222222' ) : ?>
	body,
	button,
	input,
	select,
	textarea,
	a:hover,
	a:focus,
	a:active,
	.entry-title a	{
		color: <?php echo esc_attr($text_color_first); ?>;
	}
	<?php endif; ?>
	
	<?php if (!empty($header_background_fourth) && $header_background_fourth != '#222222' ) : ?>
	.site-header {
		background: <?php echo esc_attr($header_background_fourth); ?>;
	}
	<?php endif; ?>
	
	<?php if (!empty($box_color_second) && $box_color_second != '#ffffff' ) : ?>
	<?php list($r, $g, $b) = sscanf($box_color_second, '#%02x%02x%02x'); ?>
	#search-full {
		background: rgba(<?php echo esc_attr($r).', '.esc_attr($g).', '.esc_attr($b); ?>, 0.9);
	}
	button,
	input[type="button"],
	input[type="reset"],
	input[type="submit"],
	.site-header a, 
	.site-header a:hover, 
	.site-header a:focus, 
	.site-header, 
	.site-footer a, 
	.site-footer a:hover,
	.comment-navigation .nav-previous a, 
	.comment-navigation .nav-previous a:hover,
	.comment-navigation .nav-next a, 
	.comment-navigation .nav-next a:hover,
	.post-navigation .meta-nav, 
	.paging-navigation .meta-nav,
	#wp-calendar > caption,
	.content-annina-title,
	.tagcloud a {
		color: <?php echo esc_attr($box_color_second); ?>;
	}
	.post-navigation .meta-nav:hover, 
	.paging-navigation .meta-nav:hover,
	.content-annina {
		background: <?php echo esc_attr($box_color_second); ?>;
	}
	<?php endif; ?>
	
	<?php if (!empty($special_color_third) && $special_color_third != '#dd4c39' ) : ?>
	button,
	input[type="button"],
	input[type="reset"],
	input[type="submit"],
	.comment-navigation .nav-previous,
	.comment-navigation .nav-next,
	.post-navigation .meta-nav, 
	.paging-navigation .meta-nav,
	#wp-calendar > caption,
	.content-annina-title,
	.tagcloud a {
		background: <?php echo esc_attr($special_color_third); ?>;
	}
	blockquote::before,
	button:hover:not(.menu-toggle),
	input[type="button"]:hover,
	input[type="reset"]:hover,
	input[type="submit"]:hover,
	a,
	.main-navigation ul li:hover > a, 
	.main-navigation li a:focus, 
	.main-navigation li.current-menu-item > a, 
	.main-navigation li.current-menu-parent > a, 
	.main-navigation li.current-page-ancestor > a,
	.main-navigation .current_page_item > a, 
	.main-navigation .current_page_parent > a, 
	.main-navigation ul > li:hover .indicator, 
	.main-navigation li.current-menu-parent .indicator, 
	.main-navigation li.current-menu-item .indicator,
	.post-navigation .meta-nav:hover,
	.paging-navigation .meta-nav:hover,
	.tagcloud a:hover,
	.entry-meta, 
	.read-more, 
	.edit-link, 
	.tags-links,
	.sticky:before {
		color: <?php echo esc_attr($special_color_third); ?>;
	}
	button:hover:not(.menu-toggle),
	input[type="button"]:hover,
	input[type="reset"]:hover,
	input[type="submit"]:hover,
	.post-navigation .meta-nav:hover,
	.paging-navigation .meta-nav:hover,
	#wp-calendar tbody td#today,
	.tagcloud a:hover {
		border: 1px solid <?php echo esc_attr($special_color_third); ?>;
	}
	blockquote {
		border-left: 4px solid <?php echo esc_attr($special_color_third); ?>;
		border-right: 2px solid <?php echo esc_attr($special_color_third); ?>;
	}
	.main-navigation ul li:hover > a, 
	.main-navigation li a:focus, 
	.main-navigation li.current-menu-item > a, 
	.main-navigation li.current-menu-parent > a, 
	.main-navigation li.current-page-ancestor > a,
	.main-navigation .current_page_item > a, 
	.main-navigation .current_page_parent > a, 
	.main-navigation ul > li:hover .indicator, 
	.main-navigation li.current-menu-parent .indicator, 
	.main-navigation li.current-menu-item .indicator {
		border-left: 2px solid <?php echo esc_attr($special_color_third); ?>;
	}
	.widget-title h3 {
		border-bottom: 2px solid <?php echo esc_attr($special_color_third); ?>;
	}
	<?php endif; ?>
	
</style>
    <?php
}
add_action('wp_head', 'annina_custom_css_styles');

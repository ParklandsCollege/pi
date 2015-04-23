<?php
/**
 * 
 * Created by themify
 * @since 1.0.0
 */

//////////////////////////////////////////////////////////////////////////
// Settings API
//////////////////////////////////////////////////////////////////////////

/**
 * Create admin menu links
 */
function themify_base_admin_nav() {
	$theme = wp_get_theme();
	$page_name = sprintf( __( '%s Settings', 'themify' ), $theme->display( 'Name' ) );
	add_theme_page( $page_name, $page_name, 'manage_options', 'themify', 'themify_base_page' );
}

/**
 * Render settings page
 */
function themify_base_page() {
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to update this site.', 'themify' ) );
	}

	$theme = wp_get_theme();
	$theme_name = is_child_theme() ? $theme->parent()->Name : $theme->display( 'Name' );

	?>
	<h2><?php printf( __( '%s Settings', 'themify' ), $theme_name ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'themify_settings' ); ?>
		<?php do_settings_sections( 'themify' ); ?>
		<?php submit_button(); ?>
	</form>

	<?php
}

/**
 * Render settings fields
 */
function themify_base_settings_admin_init() {

	register_setting( 'themify_settings', 'themify_settings' );

	add_settings_section( 'themify_main', __( 'Main Settings', 'themify' ), 'themify_base_settings_main', 'themify' );

	$fields = themify_base_settings_config();

	foreach( $fields as $id => $field ) {
		$args = isset( $field['args'] ) ? $field['args'] : array();
		$args['field_id'] = $id;
		add_settings_field( $id, $field['name'], 'themify_base_setting_field_'.$field['type'], 'themify', 'themify_main', $args );
	}
}

/**
 * Section description.
 */
function themify_base_settings_main() {
	echo __( 'Layout and other general options', 'themify' );
}

/**
 * Get setting.
 *
 * @param string $key Setting key to query.
 * @param string $default Default value if setting doesn't exist.
 * @return mixed|bool
 */
function themify_base_get( $key = '', $default = '' ) {
	global $themify_settings;
	if ( isset( $themify_settings[$key] ) ) {
		return $themify_settings[$key];
	} elseif ( '' != $default ) {
		return $default;
	}
	return false;
}

/**
 * Returns the entire Themify data object.
 *
 * @uses $themify_settings Global variable that stores all the settings.
 * @return mixed
 */
function themify_base_get_data() {
	global $themify_settings;
	return $themify_settings;
}

/**
 * Load assets needed
 */
function themify_base_admin_enqueue_assets() {
	// Color Picker CSS
	wp_enqueue_style( 'themify-colorpicker', THEMIFY_BASE_URI . '/css/jquery.minicolors.css', array(), THEMIFY_VERSION );

	// Admin CSS
	wp_enqueue_style( 'themify-admin-styles', THEMIFY_BASE_URI . '/css/themify-ui.css' );

	// Color Picker JS
	wp_enqueue_script( 'themify-colorpicker-js', THEMIFY_BASE_URI . '/js/jquery.minicolors.js', array('jquery'), THEMIFY_VERSION );

	// Admin JS
	wp_enqueue_script( 'themify-admin-scripts', THEMIFY_BASE_URI . '/js/scripts.js', array( 'jquery' ), THEMIFY_VERSION );
}

//////////////////////////////////////////////////////////////////////////
// Utility Functions
//////////////////////////////////////////////////////////////////////////

/**
 * Add Themify Settings link to admin bar
 * @since 1.0.0
 */
function themify_base_admin_bar() {
	global $wp_admin_bar;
	if ( !is_super_admin() || !is_admin_bar_showing() )
		return;
	$wp_admin_bar->add_menu( array(
		'id' => 'themify-settings',
		'parent' => 'appearance',
		'title' => __( 'Themify Settings', 'themify' ),
		'href' => admin_url( 'admin.php?page=themify' )
	));
}

if ( ! function_exists( 'themify_base_site_title' ) ) {
	/**
	 * Returns markup for site name
	 * @param string $location
	 * @return mixed|void
	 */
	function themify_base_site_title( $location = 'site-logo' ) {
		$html = '<h1 id="' . $location . '" class="' . $location . '">';

		global $themify_customizer;
		$html .= $themify_customizer->site_logo( $location );

		$html .= '</h1>';
		return apply_filters( 'themify_' . $location . '_logo_html', $html, $location );
	}
}

if ( ! function_exists( 'themify_base_get_category_description' ) ) {
	/**
	 * Returns taxonomy term description.
	 * @return string
	 */
	function themify_base_get_category_description() {
		$description = term_description();
		return ! empty( $description ) ? '<div class="category-description">' . $description . '</div>' : '';
	}
}

/**
 * Echoes page navigation
 *
 * @param string $before Markup to show before pagination links.
 * @param string $after Markup to show after pagination links.
 * @param bool   $query WordPress query object to use.
 * @uses themify_base_get_pagenav
 * @since 1.0.0
 */
function themify_base_pagenav( $before = '', $after = '', $query = false ) {
	echo themify_base_get_pagenav( $before, $after, $query );
}

if ( ! function_exists( 'themify_base_get_pagenav' ) ) {
	/**
	 * Returns page navigation.
	 *
	 * @param string $before Markup to show before pagination links.
	 * @param string $after Markup to show after pagination links.
	 * @param bool   $query WordPress query object to use.
	 * @return string
	 */
	function themify_base_get_pagenav( $before = '', $after = '', $query = false ) {
		global $wp_query;

		if ( false == $query ) {
			$query = $wp_query;
		}
		$paged = intval( get_query_var( 'paged' ) );
		$max_page = $query->max_num_pages;

		if ( empty( $paged ) || $paged == 0 ) {
			$paged = 1;
		}
		$pages_to_show = apply_filters( 'themify_filter_pages_to_show', 5 );
		$pages_to_show_minus_1 = $pages_to_show - 1;
		$half_page_start = floor( $pages_to_show_minus_1 / 2 );
		$half_page_end = ceil( $pages_to_show_minus_1 / 2 );
		$start_page = $paged - $half_page_start;
		if ( $start_page <= 0 ) {
			$start_page = 1;
		}
		$end_page = $paged + $half_page_end;
		if ( ( $end_page - $start_page ) != $pages_to_show_minus_1 ) {
			$end_page = $start_page + $pages_to_show_minus_1;
		}
		if ( $end_page > $max_page ) {
			$start_page = $max_page - $pages_to_show_minus_1;
			$end_page = $max_page;
		}
		if ( $start_page <= 0 ) {
			$start_page = 1;
		}
		$out = '';
		if ( $max_page > 1 ) {
			$out .= $before . '<div class="pagenav clearfix">';
			if ( $start_page >= 2 && $pages_to_show < $max_page ) {
				$first_page_text = "&laquo;";
				$out .= '<a href="' . get_pagenum_link() . '" title="' . $first_page_text . '" class="number">' . $first_page_text . '</a>';
			}
			if ( $pages_to_show < $max_page ) {
				$out .= get_previous_posts_link( '&lt;' );
			}
			for ( $i = $start_page; $i <= $end_page; $i ++ ) {
				if ( $i == $paged ) {
					$out .= ' <span class="number current">' . $i . '</span> ';
				} else {
					$out .= ' <a href="' . get_pagenum_link( $i ) . '" class="number">' . $i . '</a> ';
				}
			}
			if ( $pages_to_show < $max_page ) {
				$out .= get_next_posts_link( '&gt;' );
			}
			if ( $end_page < $max_page ) {
				$last_page_text = "&raquo;";
				$out .= '<a href="' . get_pagenum_link( $max_page ) . '" title="' . $last_page_text . '" class="number">' . $last_page_text . '</a>';
			}
			$out .= '</div>' . $after;
		}
		return $out;
	}
}

/**
 * Outputs footer text
 * @param string $block The block of text this is.
 * @param string $date_fmt Date format for year shown.
 * @param bool $echo Whether to echo or return the markup.
 * @return string $html The markup and text.
 */
function themify_base_the_footer_text( $block = 'one', $date_fmt = 'Y', $echo = true ) {

	if ( 'one' == $block ) {
		$text = '&copy; <a href="' . home_url() . '">' . get_bloginfo( 'name' ) . '</a> ' . date( $date_fmt );
	} elseif ( 'two' == $block ) {
		$text = sprintf( __( 'Powered by <a href="%s">WordPress</a> &bull; <a href="%s">Themify WordPress Themes</a>', 'themify' ), 'http://wordpress.org', 'http://themify.me' );
	} else {
		$text = '';
	}

	$html = '<div class="' . $block . '">' . apply_filters( 'themify_base_the_footer_text_' . $block, $text ) . '</div>';
	$html = apply_filters( 'themify_base_the_footer_text', $html, $block );

	if ( $echo ) {
		echo $html;
	}
	return $html;
}

/**
 * Returns a list of web safe fonts
 * @param bool $only_names Whether to return only the array keys or the values as well
 * @return mixed|void
 * @since 1.0.0
 */
function themify_base_get_web_safe_fonts($only_names = false) {
	$web_safe_font_names = array(
		'Arial, Helvetica, sans-serif',
		'Verdana, Geneva, sans-serif',
		'Georgia, \'Times New Roman\', Times, serif',
		'\'Times New Roman\', Times, serif',
		'Tahoma, Geneva, sans-serif',
		'\'Trebuchet MS\', Arial, Helvetica, sans-serif',
		'Palatino, \'Palatino Linotype\', \'Book Antiqua\', serif',
		'\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif'
	);

	if( ! $only_names ) {
		$web_safe_fonts = array();
		foreach( $web_safe_font_names as $font ) {
			$web_safe_fonts[str_replace( '\'', '"', $font )] = $font;
		}
	} else {
		$web_safe_fonts = $web_safe_font_names;
	}

	return apply_filters( 'themify_base_get_web_safe_fonts', $web_safe_fonts );
}

if ( ! function_exists( 'themify_is_touch' ) ) {
	/**
	 * Returns true if it's a phone or tablet
	 * @param string $check What to check, all, phone or tablet.
	 * @return bool
	 */
	function themify_is_touch( $check = 'all' ) {
		global $themify_mobile_detect;
		switch ( $check ) {
			case 'phone':
				return $themify_mobile_detect->isMobile() && ! $themify_mobile_detect->isTablet();
				break;
			case 'tablet':
				return $themify_mobile_detect->isTablet();
				break;
		}
		return $themify_mobile_detect->isMobile();
	}
}

/**
 * Check if the site is using an HTTPS scheme and returns the proper url
 * @param string $url The requested to set its scheme.
 * @return string
 */
function themify_base_https_esc( $url = '' ) {
	if ( is_ssl() ) {
		$url = str_replace( 'http://', 'https://', $url );
	}
	return $url;
}

/**
 * Registers footer sidebars.
 * @param array $columns Sets of sidebars that can be created.
 * @param array $widget_attr General markup for widgets.
 * @param string $widgets_key Theme settings key to use.
 * @param string $default_set Set of widgets to create.
 */
function themify_base_register_grouped_widgets( $columns = array(), $widget_attr = array(), $widgets_key = 'setting-footer_widgets', $default_set = 'footerwidget-3col' ) {

	if ( empty( $columns ) ) {
		$columns = array(
			'footerwidget-4col' => 4,
			'footerwidget-3col' => 3,
			'footerwidget-2col' => 2,
			'footerwidget-1col' => 1,
			'none'              => 0
		);
	}
	$option = themify_base_get( $widgets_key, $default_set );

	if ( empty( $widget_attr ) ) {
		$widget_attr = array(
			'sidebar_name'  => __( 'Footer Widget', 'themify' ),
			'sidebar_id'    => 'footer-widget',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widgettitle">',
			'after_title'   => '</h4>',
		);
	}

	for ( $x = 1; $x <= $columns[$option]; $x ++ ) {
		register_sidebar( array(
			'name'          => $widget_attr['sidebar_name'] . ' ' . $x,
			'id'            => $widget_attr['sidebar_id'] . '-' . $x,
			'before_widget' => $widget_attr['before_widget'],
			'after_widget'  => $widget_attr['after_widget'],
			'before_title'  => $widget_attr['before_title'],
			'after_title'   => $widget_attr['after_title'],
		));
	}
}

if ( ! function_exists( 'themify_base_lightbox_vars_init' ) ) {
	/**
	 * Post Gallery lightbox/fullscreen and single lightbox definition
	 *
	 * @return array Lightbox/Fullscreen galleries initialization parameters
	 */
	function themify_base_lightbox_vars_init() {
		$lightbox_content_images = themify_base_get( 'setting-lightbox_content_images' );
		$gallery_lightbox = themify_base_get( 'setting-gallery_lightbox' );
		$lightboxSelector = '.lightbox';
		$file_extensions = array( 'jpg', 'gif', 'png', 'JPG', 'GIF', 'PNG', 'jpeg', 'JPEG' );
		$content_images = '';
		$gallery_selector = '';
		foreach ( $file_extensions as $ext ) {
			$content_images .= '.post-content a[href$=' . $ext . '],.page-content a[href$=' . $ext . '],';
			$gallery_selector .= '.gallery-icon > a[href$=' . $ext . '],';
		}
		$content_images = substr( $content_images, 0, - 1 );
		$gallery_selector = substr( $gallery_selector, 0, - 1 );

		// Include Magnific style and script
		wp_enqueue_style( 'magnific', THEMIFY_BASE_URI . '/css/lightbox.css' );
		wp_enqueue_script( 'magnific', THEMIFY_BASE_URI . '/js/lightbox.js', array( 'jquery' ), false, true );

		// Lightbox default settings
		$overlay_args = array(
			'lightboxSelector'              => $lightboxSelector,
			'lightboxOn'                    => true,
			'lightboxContentImages'         => '' == $lightbox_content_images ? false : true,
			'lightboxContentImagesSelector' => $content_images,
			'theme'                         => apply_filters( 'themify_overlay_gallery_theme', 'pp_default' ),
			'social_tools'                  => false,
			'allow_resize'                  => true,
			'show_title'                    => false,
			'overlay_gallery'               => false,
			'screenWidthNoLightbox'         => 600,
			'deeplinking'                   => false,
			'contentImagesAreas'            => '.post, .type-page, .type-highlight, .type-slider'
		);

		// If user selected lightbox or is a new install/reset
		if ( 'lightbox' == $gallery_lightbox || 'prettyphoto' == $gallery_lightbox || null == $gallery_lightbox ) {
			$overlay_args['gallerySelector'] = $gallery_selector;
			$overlay_args['lightboxGalleryOn'] = true;

			// else if user selected fullscreen gallery
		} elseif ( 'photoswipe' == $gallery_lightbox ) {
			// Include fullscreen gallery style and script
			wp_enqueue_style( 'photoswipe', THEMIFY_BASE_URI . '/css/photoswipe.css' );
			wp_enqueue_script( 'photoswipe', THEMIFY_BASE_URI . '/js/photoswipe.js', array( 'jquery' ), false, true );

			// Parameter to handle fullscreen gallery
			$overlay_args = array_merge( $overlay_args, array(
				'fullscreenSelector' => $gallery_selector,
				'fullscreenOn'       => true
			));
		}

		return apply_filters( 'themify_gallery_plugins_args', $overlay_args );
	}
}

/**
 * Writes the page title according to the content viewed.
 *
 * @since 1.0.3
 *
 * @param $title
 * @param $sep
 * @return string
 */
function themify_base_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() ) {
		return $title;
	}

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$tagline = get_bloginfo( 'description', 'display' );
	if ( $tagline && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $tagline";
	}

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 ) {
		$title .= " $sep " . sprintf( __( 'Page %s', 'themify' ), max( $paged, $page ) );
	}

	return $title;
}

/**
 * Add different CSS classes to body tag.
 * Outputs skin name and layout.
 *
 * @since 1.0.3
 *
 * @param array
 * @return array
 */
function themify_base_body_classes( $classes ) {

	// Add skin name
	if ( $skin = themify_base_get( 'skin' ) ) {
		$classes[] = 'skin-' . $skin;
	} else {
		$classes[] = 'skin-default';
	}

	// Browser classes
	global $is_gecko, $is_opera, $is_iphone, $is_IE, $is_winIE, $is_macIE;
	$is_android = stripos( $_SERVER['HTTP_USER_AGENT'], 'android' ) ? true : false;
	$is_webkit = stripos( $_SERVER['HTTP_USER_AGENT'], 'webkit' ) ? true : false;
	$is_ie10 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 10' ) ? true : false;
	$is_ie9 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 9' ) ? true : false;
	$is_ie8 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 8' ) ? true : false;
	$is_ie7 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 7' ) ? true : false;

	$is_not_ie = true;

	$browsers = array(
		'gecko'   => $is_gecko,
		'opera'   => $is_opera,
		'iphone'  => $is_iphone,
		'android' => $is_android,
		'webkit'  => $is_webkit,
		'ie'      => $is_IE,
		'iewin'   => $is_winIE,
		'iemac'   => $is_macIE,
		'ie10'    => $is_ie10,
		'ie9'     => $is_ie9,
		'ie8'     => $is_ie8,
		'ie7'     => $is_ie7
	);

	foreach ( $browsers as $browser => $state ) {
		if ( $state ) {
			$classes[] = $browser;
			if ( stripos( $browser, 'ie' ) !== false ) {
				$is_not_ie = false;
			}
		}
	}
	if ( $is_not_ie ) {
		$classes[] = 'not-ie';
	}

	$layout = themify_base_get_sidebar_layout();

	// If still empty, set default
	if ( apply_filters( 'themify_default_layout_condition', '' == $layout ) ) {
		$layout = apply_filters( 'themify_default_layout', 'sidebar1' );
	}
	$classes[] = $layout;

	return apply_filters( 'themify_base_body_classes', $classes );
}

/**
 * Return sidebar layout.
 * @return bool|mixed
 */
function themify_base_get_sidebar_layout() {
	if ( is_page() ) {
		// It's a page
		$layout = themify_base_get( 'setting-default_page_layout', 'sidebar1' );
	} elseif ( is_single() ) {
		// It's a post
		$layout = themify_base_get( 'setting-default_page_post_layout', 'sidebar1' );
	} else {
		// Add default layout and post layout
		$layout = themify_base_get( 'setting-default_layout', 'sidebar1' );
	}

	return apply_filters( 'themify_base_sidebar_layout', $layout );
}

/**
 * Add JavaScript files if IE version is lower than 9
 */
function themify_base_ie_enhancements() {
	echo '
	<!-- media-queries.js -->
	<!--[if lt IE 9]>
		<script src="' . THEME_URI . '/js/respond.js"></script>
	<![endif]-->

	<!-- html5.js -->
	<!--[if lt IE 9]>
		<script src="' . themify_base_https_esc( 'http://html5shim.googlecode.com/svn/trunk/html5.js' ) . '"></script>
	<![endif]-->
	';
}

/**
 * Add viewport tag for responsive layouts
 */
function themify_base_viewport_tag() {
	echo "\n" . '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">' . "\n";
}

/**
 * Make IE behave like a standards-compliant browser
 */
function themify_base_ie_standards_compliant() {
	echo '
	<!--[if lt IE 9]>
	<script src="' . themify_base_https_esc( 'http://s3.amazonaws.com/nwapi/nwmatcher/nwmatcher-1.2.5-min.js' ) . '"></script>
	<script type="text/javascript" src="' . themify_base_https_esc( 'http://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js' ) . '"></script>
	<![endif]-->
	';
}

/**
 * Convert array key name with square bracket to valid array
 * @param array $inputArr
 * @return array
 */
function themify_base_convert_brackets_string_to_arrays( $inputArr ) {
	$result = array();

	foreach ($inputArr as $key => $val) {
		$keyParts = preg_split('/[\[\]]+/', $key, -1, PREG_SPLIT_NO_EMPTY);

		$ref = &$result;

		while ($keyParts) {
				$part = array_shift($keyParts);

			if (!isset($ref[$part])) {
				$ref[$part] = array();
			}

			$ref = &$ref[$part];
		}

		$ref = $val;
	}
	return $result;
}

if ( ! function_exists('themify_base_get_google_web_fonts_list') ) {
	/**
	 * Returns a list of Google Web Fonts
	 * @return array
	 * @since 1.5.6
	 */
	function themify_base_get_google_web_fonts_list() {
		$google_fonts_list = array(
			array('value' => '', 'name' => ''),
			array(
				'value' => '',
				'name' => '--- '.__('Google Fonts', 'themify').' ---'
			)
		);
		foreach( wp_list_pluck( themify_base_get_google_font_lists(), 'family' ) as $font ) {
			$google_fonts_list[] = array(
				'value' => $font,
				'name' => $font
			);
		}
		return apply_filters('themify_base_get_google_web_fonts_list', $google_fonts_list);
	}
}

if ( ! function_exists('themify_base_get_web_safe_font_list') ) {
	/**
	 * Returns a list of web safe fonts
	 * @param bool $only_names Whether to return only the array keys or the values as well
	 * @return mixed|void
	 * @since 1.0.0
	 */
	function themify_base_get_web_safe_font_list($only_names = false) {
		$web_safe_font_names = array(
			'Arial, Helvetica, sans-serif',
			'Verdana, Geneva, sans-serif',
			'Georgia, \'Times New Roman\', Times, serif',
			'\'Times New Roman\', Times, serif',
			'Tahoma, Geneva, sans-serif',
			'\'Trebuchet MS\', Arial, Helvetica, sans-serif',
			'Palatino, \'Palatino Linotype\', \'Book Antiqua\', serif',
			'\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif'
		);

		if( ! $only_names ) {
			$web_safe_fonts = array(
				array('value' => 'default', 'name' => '', 'selected' => true),
				array('value' => '', 'name' => '--- '.__('Web Safe Fonts', 'themify').' ---')
			);
			foreach( $web_safe_font_names as $font ) {
				$web_safe_fonts[] = array(
					'value' => $font,
					'name' => str_replace( '\'', '"', $font )
				);
			}
		} else {
			$web_safe_fonts = $web_safe_font_names;
		}

		return apply_filters( 'themify_base_get_web_safe_font_list', $web_safe_fonts );
	}
}

/**
 * Get google font lists
 * @return array
 */
function themify_base_get_google_font_lists() {
	if( !defined('THEMIFY_GOOGLE_FONTS') ) define('THEMIFY_GOOGLE_FONTS', true);
	if( !THEMIFY_GOOGLE_FONTS ) return array();

	$fonts = themify_base_grab_remote_google_fonts();
	return $fonts;
}

/**
 * Grab google fonts lists from api
 * @return array
 */
function themify_base_grab_remote_google_fonts() {
	$user_subsets = themify_base_get( 'setting-webfonts_subsets', array('latin') );
	$subsets = apply_filters( 'themify_google_fonts_subsets', array_unique( array_merge( array( 'latin' ), $user_subsets ) ) );

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
	WP_Filesystem();
	global $wp_filesystem;
	$fonts_file = THEMIFY_BASE_DIR . '/js/google-fonts.json';
	if ( $wp_filesystem->exists( $fonts_file ) ) {
		$response = $wp_filesystem->get_contents( $fonts_file );
	} else {
		$response = false;
	}
	$fonts = array();
	if( $response !== false ) {
		$results = json_decode( $response );
		foreach ( $results->items as $font ) {
			$subsets_match = true;

			// Check that all specified subsets are available in this font
			foreach ( $subsets as $subset ) {
				if ( ! in_array( $subset, $font->subsets ) ) {
					$subsets_match = false;
				}
			}

			// Ok, this font supports all subsets requested by user, add it to the list
			if ( $subsets_match ) {
				$fonts[] = array(
					'family' => $font->family,
					'variant' => implode(',', $font->variants),
					'subsets' => implode(',', $font->subsets)
				);
			}
		}
	}
	return $fonts;
}

/**
 * Check if given value is google fonts or web safe fonts
 * @param string $value
 * @return boolean
 */
function themify_base_is_google_fonts( $value ) {
	global $themify_gfonts;
	$found = false;
	if ( sizeof( $themify_gfonts ) > 0 ) {
		foreach ( $themify_gfonts as $font ) {
			if ( $found ) break;
			if ( $font['family'] == $value ) $found = true;
		}
	}
	return $found;
}

/**
 * Get selected custom css google fonts
 * @return array
 */
function themify_base_get_custom_css_gfonts() {
	$data = themify_base_get_data();
	$fonts = array();
	if ( is_array( $data ) ) {
		$new_arr = array();
		foreach ( $data as $name => $value ) {
			$array = explode( '-', $name );
			$path = '';
			foreach( $array as $part ) {
				$path .= "[$part]";
			}
			$new_arr[ $path ] = $value;
		}
		$config = themify_base_convert_brackets_string_to_arrays( $new_arr );
		if ( isset( $config['styling'] ) && is_array( $config['styling'] ) ) {
			foreach ( $config['styling'] as $ks => $styling ) {
				foreach ( $styling as $element => $val ) {
					foreach ( $val as $attribute => $v ) {
						switch ( $attribute ) {
							case 'font_family':
								if ( ! empty( $v['value']['value'] ) && themify_base_is_google_fonts( $v['value']['value'] ) )
									array_push( $fonts, $v['value']['value'] );
								break;
						}
					}
				}
			}
		}
	}
	return $fonts;
}

/**
 * Load google fonts library
 */
function themify_base_enqueue_gfonts() {
	$fonts = themify_base_get_custom_css_gfonts();
	$families = array();
	$user_subsets = themify_base_get( 'setting-webfonts_subsets', array('latin') );
	$subsets = apply_filters( 'themify_google_fonts_subsets', array_unique( array_merge( array( 'latin' ), $user_subsets ) ) );
	$query = null;
	$fonts = array_unique( $fonts );
	foreach ( $fonts as $font ) {
		$words = explode( '-', $font );
		$variant = themify_base_get_gfont_variant( $font );
		foreach ( $words as $key => $word ) {
			$words[$key] = ucwords( $word );
		}
		array_push( $families, implode( '+', $words ) . ':' . $variant );
	}
	if ( ! empty( $families ) ) {
		$query .= '?family=' . implode( '|', $families );
		$query .= '&subset=' . implode( ',', $subsets );

		// check to see if site is uses https
		$http = ( is_ssl() ) ? 'https' : 'http';
		$url = $http.'://fonts.googleapis.com/css';
		$url .= $query;

		wp_enqueue_style( 'themify-google-fonts', $url );
	}
}
add_action( 'wp_enqueue_scripts', 'themify_base_enqueue_gfonts' );

if ( ! function_exists( 'themify_base_get_gfont_variant' ) ) {
	/**
	 * Get font default variant
	 * @param $family
	 * @return string
	 */
	function themify_base_get_gfont_variant( $family ) {
		global $themify_gfonts;
		$variant = 400;
		foreach ($themify_gfonts as $v) {
			if ( $v['family'] == $family ) {
				$variant = $v['variant'];
				break;
			}
		}
		return $variant;
	}
}

/**
 * Change the sidebar layout for the Full width page template
 *
 * @param $layout
 *
 * @return string
 */
function themify_base_full_width_template_layout( $layout ) {
	if( is_page_template( 'page-templates/full-width.php' ) ) {
		$layout = 'sidebar-none';
	}
	return $layout;
}
add_filter( 'themify_base_sidebar_layout', 'themify_base_full_width_template_layout' );
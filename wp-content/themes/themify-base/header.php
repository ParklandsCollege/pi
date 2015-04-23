<?php
/**
 * Template for site header
 * @package themify
 * @since 1.0.0
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<?php
/**
 * Document title is generated in functions.php
 * Stylesheets and Javascript files are enqueued in functions.php
 */
?>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php themify_base_body_start(); // hook ?>
<div id="pagewrap">

	<div id="headerwrap">
    
		<?php themify_base_header_before(); // hook ?>
		<header id="header" class="pagewidth">
        <?php themify_base_header_start(); // hook ?>
			<?php echo themify_base_site_title( 'site-logo' ); ?>

			<?php if ( $site_desc = get_bloginfo( 'description' ) ) : ?>
				<?php global $themify_customizer; ?>
				<div id="site-description" class="site-description"><?php echo class_exists( 'Themify_Customizer' ) ? $themify_customizer->site_description( $site_desc ) : $site_desc; ?></div>
			<?php endif; ?>

			<nav>
				<div id="menu-icon" class="mobile-button"><i class="icon-menu"></i></div>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'main-nav',
					'fallback_cb'    => 'themify_base_default_main_nav',
					'container'      => '',
					'menu_id'        => 'main-nav',
					'menu_class'     => 'main-nav'
				));
				?>
				<!-- /#main-nav --> 
			</nav>

		<?php themify_base_header_end(); // hook ?>
		</header>
		<!-- /#header -->
        <?php themify_base_header_after(); // hook ?>
				
	</div>
	<!-- /#headerwrap -->
	
	<div id="body" class="clearfix">
    <?php themify_base_layout_before(); //hook ?>
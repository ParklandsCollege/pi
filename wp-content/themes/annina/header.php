<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package annina
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5shiv.min.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
<?php 
	global $annina_theme_options;
	$se_options = get_option( 'annina_theme_options', $annina_theme_options );
?>
<?php if ( ! $se_options['hidesearch'] ) : ?>
<!-- Start: Search Form -->
					<div id="search-full">
						<div class="search-container">
							<form role="search" method="get" id="search-form" action="<?php echo home_url( '/' ); ?>">
								<label>
									<span class="screen-reader-text"><?php _e( 'Search for:', 'annina' ); ?></span>
									<input type="search" name="s" id="search-field" placeholder="<?php _e('Type here and hit enter...', 'annina'); ?>">
								</label>
							</form>
							<span><a id="close-search"><i class="fa fa-close spaceRight"></i><?php _e('Close', 'annina'); ?></a></span>
						</div>
					</div>
<!-- End: Search Form -->
<?php endif; ?>

	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'annina' ); ?></a>

	<header id="masthead" class="site-header" role="banner">
		<div class="site-branding annCenter">
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<p class="site-description"><?php bloginfo( 'description' ); ?></p>
		</div>

		<nav id="site-navigation" class="main-navigation" role="navigation">
			<button class="menu-toggle"><?php _e( 'Main Menu', 'annina' ); ?><i class="fa fa-align-justify"></i></button>
			<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
		</nav><!-- #site-navigation -->

			<div class="socialLine annCenter">
				<?php if ( $se_options['facebookurl'] != '' ) : ?>
					<a href="<?php echo esc_url($se_options['facebookurl']); ?>" title="Facebook" target="_blank"><i class="fa spaceRightDouble fa-facebook"></i></a>
				<?php endif; ?>
				
				<?php if ( $se_options['twitterurl'] != '' ) : ?>
					<a href="<?php echo esc_url($se_options['twitterurl']); ?>" title="Twitter" target="_blank"><i class="fa spaceRightDouble fa-twitter"></i></a>
				<?php endif; ?>
				
				<?php if ( $se_options['googleplusurl'] != '' ) : ?>
					<a href="<?php echo esc_url($se_options['googleplusurl']); ?>" title="Google Plus" target="_blank"><i class="fa spaceRightDouble fa-google-plus"></i></a>
				<?php endif; ?>
				
				<?php if ( $se_options['linkedinurl'] != '' ) : ?>
					<a href="<?php echo esc_url($se_options['linkedinurl']); ?>" title="Linkedin" target="_blank"><i class="fa spaceRightDouble fa-linkedin"></i></a>
				<?php endif; ?>
				
				<?php if ( $se_options['instagramurl'] != '' ) : ?>
					<a href="<?php echo esc_url($se_options['instagramurl']); ?>" title="Instagram" target="_blank"><i class="fa spaceRightDouble fa-instagram"></i></a>
				<?php endif; ?>
				
				<?php if ( $se_options['youtubeurl'] != '' ) : ?>
					<a href="<?php echo esc_url($se_options['youtubeurl']); ?>" title="YouTube" target="_blank"><i class="fa spaceRightDouble fa-youtube"></i></a>
				<?php endif; ?>
				
				<?php if ( $se_options['pinteresturl'] != '' ) : ?>
					<a href="<?php echo esc_url($se_options['pinteresturl']); ?>" title="Pinterest" target="_blank"><i class="fa spaceRightDouble fa-pinterest"></i></a>
				<?php endif; ?>
				
				<?php if ( $se_options['emailurl'] != '' ) : ?>
					<a href="mailto:<?php echo sanitize_email($se_options['emailurl']); ?>" title="Email"><i class="fa spaceRightDouble fa-envelope"></i></a>
				<?php endif; ?>
				
				<?php if ( ! $se_options['hidesearch'] ) : ?>
					<div id="open-search" class="top-search"><i class="fa spaceRightDouble fa-search"></i></div>
				<?php endif; ?>
			</div>
	</header><!-- #masthead -->

	<div id="content" class="site-content">

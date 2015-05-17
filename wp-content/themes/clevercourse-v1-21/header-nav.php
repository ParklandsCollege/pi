<?php
	global $theme_option;

	echo '<div class="gdlr-navigation-wrapper">';

	// navigation
	if( has_nav_menu('main_menu') ){
		if( class_exists('gdlr_menu_walker') ){
			echo '<nav class="gdlr-navigation" id="gdlr-main-navigation" role="navigation">';
			wp_nav_menu( array(
				'theme_location'=>'main_menu',
				'container'=> '',
				'menu_class'=> 'sf-menu gdlr-main-menu',
				'walker'=> new gdlr_menu_walker()
			) );
		}else{
			echo '<nav class="gdlr-navigation" role="navigation">';
			wp_nav_menu( array('theme_location'=>'main_menu') );
		}


		echo '<div class="gdlr-nav-search-form-button" id="gdlr-nav-search-form-button"><i class="icon-search"></i></div>';
		echo '</nav>'; // gdlr-navigation
	}
	echo '<div class="clear"></div>';
	echo '</div>'; // gdlr-navigation-wrapper


	echo '<div class="gdlr-navigation-wrapper">';
	function register_my_menu() {
	  register_nav_menu('header-menu',__( 'Sub Menu' ));
	}
	add_action( 'init', 'register_my_menu' );

	echo '<div class="clear"></div>';
	echo '</div>';
?>

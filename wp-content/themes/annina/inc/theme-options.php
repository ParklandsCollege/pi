<?php

/**
 * Default Options
 */
$annina_theme_options = array( 
	'hidesearch' => '0',
	'firstbig' => '0',
	'facebookurl' => '#', 
	'twitterurl' => '#', 
	'googleplusurl' => '#', 
	'linkedinurl' => '#', 
	'instagramurl' => '#', 
	'youtubeurl' => '#', 
	'pinteresturl' => '#', 
	'emailurl' => '#'
);

if ( current_user_can('manage_options') ) {
	function annina_toolbar_link_to_mypage( $wp_admin_bar ) {
		$args = array(
			'id'    => 'annina_theme_options',
			'title' => __('Annina Theme Options', 'annina' ),
			'href'  => admin_url('themes.php?page=theme_options')
		);
		$wp_admin_bar->add_node( $args );
	}
	add_action( 'admin_bar_menu', 'annina_toolbar_link_to_mypage', 999 );
}

if ( is_admin() ) : // Load only if we are viewing an admin page

add_action( 'admin_init', 'annina_options_init' );
add_action( 'admin_menu', 'annina_options_add_page' );

/**
 * Init plugin options to white list our options
 */
function annina_options_init(){
	register_setting( 'annina_options_add_page', 'annina_theme_options', 'annina_options_validate' );
}

/**
 * Load up the menu page
 */
function annina_options_add_page() {
	add_theme_page( __( 'Annina Theme Options', 'annina' ), __( 'Annina Theme Options', 'annina' ), 'edit_theme_options', 'theme_options', 'annina_options_do_page' );
}

/**
 * Create the options page
 */
function annina_options_do_page() {
	global $annina_theme_options;

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;
	?>
	<div class="wrap">
		<?php echo "<h2>" . wp_get_theme() . __( ' Free Theme Options', 'annina' ) . "</h2>"; ?>
			
		<div class="updated" style="background:#E9F7DF;clear: both;display: table;width: 100%;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;border-left: 4px solid #1fa67a;">
			<h3><div class="dashicons dashicons-megaphone"></div> <?php _e( 'Need more features and options? Upgrade to PRO!', 'annina' ); ?></h3>
			<p><?php _e( 'Get', 'annina' ); ?> <b><?php _e( 'Annina PRO', 'annina' ); ?></b> <?php _e( 'WordPress Theme for only', 'annina' ); ?> <b>24,90&euro;</b></p>
			<div class="anninaLeft" style="float:left; width: 30%; text-align: center;">
				<a style="display: inline-block;padding: 20px;background: #1fa67a;border-radius: 5px;color: #ffffff;font-size: 125%;-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);margin: 40px 0 20px;" href="http://crestaproject.com/demo/annina-pro/" target="_blank"><div class="dashicons dashicons-visibility"></div> <?php _e( 'Demo (Annina PRO)', 'annina' ); ?></a>
				<br />
				<a style="display: inline-block;padding: 20px;background: #1fa67a;border-radius: 5px;color: #ffffff;font-size: 125%;-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);" href=" http://crestaproject.com/downloads/annina/" target="_blank"><div class="dashicons dashicons-heart"></div> <?php _e( 'Get The Pro Version', 'annina' ); ?></a></a>
			</div>
			<div class="anninaRight" style="float:right; width: 70%;">
			<ul>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Advanced Theme Options', 'annina' ); ?></b> <?php _e( '(Manage Loading Page, Additional Custom Code, Font switcher and much more...)', 'annina' ); ?></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Logo and Favicon Upload', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Unlimited Colors and Skin', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Post views counter', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Infinite Scroll', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Breadcrumb', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( '7 Shortcodes', 'annina' ); ?></b> <?php _e( '(Toggle, Tabs, Boxes, Columns, Highlights, Buttons and Drop Cap)', 'annina' ); ?></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( '11 Exclusive Widgets', 'annina' ); ?></b> <?php _e( '(Latest Tweet, Instagram, Social Buttons, Recent Posts with Thumbnail and Most Commented Posts...)', 'annina' ); ?></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Related Posts Box', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Information About Author Box', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'Advertising system', 'annina' ); ?></b></li>
				<li><div class="dashicons dashicons-yes" style="color: #1fa67a;"></div><b><?php _e( 'And much more...', 'annina' ); ?></b></li>
			<ul>
			</div>
		</div>	
		
		<p><?php _e( 'These options will let you setup the social icons at the bottom of the theme. You can enter the URLs of your profiles to have the icons show up.', 'annina' ); ?></p>
		<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'Options saved', 'annina' ); ?></strong></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
		<?php $se_options = get_option( 'annina_theme_options', $annina_theme_options ); ?>
		
		<?php settings_fields( 'annina_options_add_page' ); ?>
			
			<table class="form-table">
			
				<?php
				/**
				 * Search Button
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Last post box big?', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[firstbig]" name="annina_theme_options[firstbig]" type="checkbox" value="1" <?php checked( '1', $se_options['firstbig'] ); ?> />
						<label class="description" for="annina_theme_options[firstbig]"><?php _e( 'Enable it if you want the last post box big', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * Search Button
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Hide Search Button?', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[hidesearch]" name="annina_theme_options[hidesearch]" type="checkbox" value="1" <?php checked( '1', $se_options['hidesearch'] ); ?> />
						<label class="description" for="annina_theme_options[hidesearch]"><?php _e( 'Hide the Search button?', 'annina' ); ?></label>
					</td>
				</tr>

				<?php
				/**
				 * Facebook Icon
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your Facebook URL', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[facebookurl]" class="regular-text" type="text" name="annina_theme_options[facebookurl]" value="<?php if( isset( $se_options[ 'facebookurl' ] ) ) echo esc_url( $se_options[ 'facebookurl' ] ); ?>" />
						<label class="description" for="annina_theme_options[facebookurl]"><?php _e( 'Leave blank to hide Facebook Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * Twitter URL
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your Twitter URL', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[twitterurl]" class="regular-text" type="text" name="annina_theme_options[twitterurl]" value="<?php if( isset( $se_options[ 'twitterurl' ] ) ) echo esc_url( $se_options[ 'twitterurl' ] ); ?>" />
						<label class="description" for="annina_theme_options[twitterurl]"><?php _e( 'Leave blank to hide Twitter Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * Google +
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your Google + URL', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[googleplusurl]" class="regular-text" type="text" name="annina_theme_options[googleplusurl]" value="<?php if( isset( $se_options[ 'googleplusurl' ] ) ) echo esc_url( $se_options[ 'googleplusurl' ] ); ?>" />
						<label class="description" for="annina_theme_options[googleplusurl]"><?php _e( 'Leave blank to hide Google + Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * Linkedin
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your Linkedin URL', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[linkedinurl]" class="regular-text" type="text" name="annina_theme_options[linkedinurl]" value="<?php if( isset( $se_options[ 'linkedinurl' ] ) ) echo esc_url( $se_options[ 'linkedinurl' ] ); ?>" />
						<label class="description" for="annina_theme_options[linkedinurl]"><?php _e( 'Leave blank to hide Linkedin Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * Instagram
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your Instagram URL', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[instagramurl]" class="regular-text" type="text" name="annina_theme_options[instagramurl]" value="<?php if( isset( $se_options[ 'instagramurl' ] ) ) echo esc_url( $se_options[ 'instagramurl' ] ); ?>" />
						<label class="description" for="annina_theme_options[instagramurl]"><?php _e( 'Leave blank to hide Instagram Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * YouTube
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your YouTube URL', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[youtubeurl]" class="regular-text" type="text" name="annina_theme_options[youtubeurl]" value="<?php if( isset( $se_options[ 'youtubeurl' ] ) ) echo esc_url( $se_options[ 'youtubeurl' ] ); ?>" />
						<label class="description" for="annina_theme_options[youtubeurl]"><?php _e( 'Leave blank to hide YouTube Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * Pinterest
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your Pinterest URL', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[pinteresturl]" class="regular-text" type="text" name="annina_theme_options[pinteresturl]" value="<?php if( isset( $se_options[ 'pinteresturl' ] ) ) echo esc_url( $se_options[ 'pinteresturl' ] ); ?>" />
						<label class="description" for="annina_theme_options[pinteresturl]"><?php _e( 'Leave blank to hide Pinterest Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
				<?php
				/**
				 * Email
				 */
				?>
				<tr valign="top"><th scope="row"><?php _e( 'Enter your Email', 'annina' ); ?></th>
					<td>
						<input id="annina_theme_options[emailurl]" class="regular-text" type="text" name="annina_theme_options[emailurl]" value="<?php if( isset( $se_options[ 'emailurl' ] ) ) echo sanitize_email( $se_options[ 'emailurl' ] ); ?>" />
						<label class="description" for="annina_theme_options[emailurl]"><?php _e( 'Leave blank to hide Email Icon', 'annina' ); ?></label>
					</td>
				</tr>
				
			</table>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'annina' ); ?>" />
			</p>
			
		</form>
	</div>
	<?php
}

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function annina_options_validate( $input ) {
	global $annina_theme_options;
	
	$se_options = get_option( 'annina_theme_options', $annina_theme_options );

	// Our checkbox value is either 0 or 1
	if ( ! isset( $input['firstbig'] ) )
		$input['firstbig'] = null;
		$input['firstbig'] = ( $input['firstbig'] == 1 ? 1 : 0 );
		
	if ( ! isset( $input['hidesearch'] ) )
		$input['hidesearch'] = null;
		$input['hidesearch'] = ( $input['hidesearch'] == 1 ? 1 : 0 );

	// Encode URLs
	if( isset( $se_options[ 'twitterurl' ] ) )
		$input['twitterurl'] = esc_url_raw( $input['twitterurl'] );
	if( isset( $se_options[ 'facebookurl' ] ) )
		$input['facebookurl'] = esc_url_raw( $input['facebookurl'] );
	if( isset( $se_options[ 'googleplusurl' ] ) )
		$input['googleplusurl'] = esc_url_raw( $input['googleplusurl'] );
	if( isset( $se_options[ 'linkedinurl' ] ) )
		$input['linkedinurl'] = esc_url_raw( $input['linkedinurl'] );
	if( isset( $se_options[ 'instagramurl' ] ) )
		$input['instagramurl'] = esc_url_raw( $input['instagramurl'] );
	if( isset( $se_options[ 'youtubeurl' ] ) )
		$input['youtubeurl'] = esc_url_raw( $input['youtubeurl'] );
	if( isset( $se_options[ 'pinteresturl' ] ) )
		$input['pinteresturl'] = esc_url_raw( $input['pinteresturl'] );
	if( isset( $se_options[ 'emailurl' ] ) )
		$input['emailurl'] = sanitize_email( $input['emailurl'] );

	return $input;
}

endif;  // EndIf is_admin()
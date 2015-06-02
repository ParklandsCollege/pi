<?php

require_once( plugin_dir_path(__FILE__).'/core_google_apps_login.php' );

class commercial_google_apps_login extends core_google_apps_login {
		
	public function ga_activation_hook($network_wide) {
		parent::ga_activation_hook($network_wide);
		
		// Inherit settings from basic version?
		// Save settings if we changed them
		$new_option = get_site_option($this->get_options_name());
		
		if (!$new_option){
			$old_option = get_site_option('galogin');
			if ($old_option && is_array($old_option)) {
				$new_option = Array();
				$default_options = $this->get_default_options();
				foreach ($default_options as $k => $v) {
					$new_option[$k] = isset($old_option[$k]) ? $old_option[$k] : $v;
				}
				$this->save_option_galogin($new_option);
			}
		}
	}
	
	protected function checkRegularWPLogin($user, $username, $password, $options) {
		if (!empty($username) || !empty($password)) {
			// Should we allow this user login to continue?
			// Don't enforce if Google Login not configured
			if ($options['ga_disablewplogin'] && $options['ga_clientid'] != '' && $options['ga_clientsecret'] != '') {
				// Halt if user is on our domain
				$tryuser = get_user_by('login', $username);
				if ($tryuser && isset($tryuser->user_email)) {
					$email = $tryuser->user_email;
			
					$domain_list = $this->split_domainslist($options['ga_domainname']);
			
					$parts = explode("@", $email);
					if (count($parts) == 2) { // Pretty likely since got it from WP
						if (in_array(strtolower($parts[1]), $domain_list)) {
							$user = new WP_Error('ga_login_error', 
									sprintf(__('User with email address %s must use Login with Google', 'google-apps-login'),
											 $email) );
							// We do not want password check any more
						}
					}
				}
			}
		}
		return $user;
	}
	
	protected function checkRegularWPError($user, $username, $password) {
		if (!empty($username) || !empty($password)) {
			// Need to redirect back to wp-login.php?error=
			// to ensure regular WP auth will not override our error
			// with a successful username/password login
			$errarray = Array('error' => urlencode($user->get_error_message()));
			wp_redirect( add_query_arg( $errarray, site_url('wp-login.php')) );
			exit;
		}	
	}
	
	protected function createUserOrError($userinfo, $options) {
		$user = null;
		$google_email = $userinfo->email;
		
		if (!$options['ga_autocreate']) {
			return new WP_Error('ga_login_error', 
								 sprintf( __('User %s does not exist', 'google-apps-login'),
								 		$google_email));
		}
		
		$domain_list = $this->split_domainslist($options['ga_domainname']);
		
		$parts = explode("@", $google_email);
		if (count($parts) != 2) {
			// Pretty unlikely since got it from Google
			$user = new WP_Error('ga_login_error', __('Invalid email address', 'google-apps-login'));
		} else {
			if (in_array(strtolower($parts[1]), $domain_list) || $options['ga_domainname'] == 'ANY_DOMAIN') {
				// Yes, create user
				$user = $this->createUser($userinfo, $parts, $options);
			}
			else {
				$user = new WP_Error('ga_login_error', 
						sprintf( __('Email address needs to be in %s.', 'google-apps-login'), 
								implode(', ', $domain_list) )
						.' ('
						.sprintf( __( '%s not authorized -  <a href="%s">Sign out of Google</a> to switch accounts', 'google-apps-login' ),
								$google_email, $this->get_google_logout_url()). ')'
										);
			}
		}
		return $user;
	}
	
	protected function createUser($userinfo, $parts, $options) {
		if (!function_exists('wp_insert_user')) {
			include 'wp-includes/registration.php';
		}

		/* $userinfo example: 
			"name": "Dan Lester",
			"given_name": "Dan",
			"family_name": "Lester",
			"link": "https://plus.google.com/115886881859296909934"
		*/
		
		$wpuserdata = Array(
			'user_login' => apply_filters('gal_sanitize_username', $userinfo->email, $userinfo), // May need to de-dupe
			// 'user_nicename' - WP defaults to sanitize_title(user_login)
			'user_pass' => wp_generate_password(12, false),
			'user_email' => $userinfo->email, // Should be unique since didn't match existing user
			'display_name' => empty($userinfo->name) ? false : $userinfo->name,
			// 'nickname' - WP defaults to username
			'first_name' => empty($userinfo->givenName) ? false : $userinfo->givenName,
			'last_name' => empty($userinfo->familyName) ? false : $userinfo->familyName,
			'user_url' => empty($userinfo->link) ? false : $userinfo->link,
			'role' => $options['ga_defaultrole']
		);
		
		$user_id = wp_insert_user($wpuserdata);
		
		if (is_wp_error($user_id)) {
			return $user_id;
		}
		
		return get_user_by('id', $user_id);		
	}
	
	protected function should_hidewplogin($options) {
		return $options['ga_hidewplogin'] && !(isset($_GET['gahidewplogin']) && $_GET['gahidewplogin'] != 'true');
	}
	
	protected function get_login_button_text() {
		$options = $this->get_option_galogin();
		if ($options['ga_loginbuttontext'] == '') {
			return parent::get_login_button_text();
		}
		return $options['ga_loginbuttontext'];
	}
	
	protected function loggedout_form() {
		return;
		?>
		<p><b><?php printf( __( 'Redirecting to <a href="%s">Login via Google</a>...' , 'google-apps-login'), $authUrl ); ?></b></p>
		<script type="text/javascript">
		window.location = "http://danlester.com/";
		</script>
		<?php
	}
	
	// ADMIN AND OPTIONS
	// *****************
	
	protected function draw_more_tabs() {
		?>
		<a href="#license" id="license-tab" class="nav-tab">License</a>
		<?php
	}	
	
	protected function ga_domainsection_text() {
		echo '<div id="domain-section" class="galtab">';
		echo '<p>';
		_e( 'By default, any existing account can authenticate either via Google (if a Gmail/Google Apps account), or by WordPress username/password.', 'google-apps-login');
		echo ' ';
		_e( 'To allow special behaviour on your Google Apps domain (auto-create users who don\'t yet exist, or disable regular WordPress username/password access for your users), fill in the following section.', 'google-apps-login');
		echo '</p> <p>';
		sprintf( __( 'Please read the <a href="%s" target="gainstr">instructions here</a> first.' , 'google-apps-login'), $this->calculate_instructions_url('d').'#domaincontrol' );
		echo '</p>';
		
		
		$options = $this->get_option_galogin();
		echo '<label for="input_ga_domainname" class="textinput">'.__('My Google Apps domain', 'google-apps-login').'</label>';
		echo "<input id='input_ga_domainname' name='".$this->get_options_name()."[ga_domainname]' size='40' type='text' value='".esc_attr($options['ga_domainname'])."' class='textinput' />";
		echo '<br class="clear">';
		
		echo "<input id='input_ga_autocreate' name='".$this->get_options_name()."[ga_autocreate]' type='checkbox' ".($options['ga_autocreate'] ? 'checked' : '')." class='checkbox' />";
		echo '<label for="input_ga_autocreate" class="checkbox plain">'.__('Auto-create new users on my domain', 'google-apps-login').'</label>';
		echo '<br class="clear">';

		if ($this->want_premium_default_role()) {
			echo '<label for="ga_defaultrole" class="textinput">'.__('Default role for new users', 'google-apps-login').'</label>';
			echo "<select name='".$this->get_options_name()."[ga_defaultrole]' id='ga_defaultrole' class='select'>";
			wp_dropdown_roles( $options['ga_defaultrole'] );
			echo "</select>";
			echo '<br class="clear">';
		}
		
		echo "<input id='input_ga_disablewplogin' name='".$this->get_options_name()."[ga_disablewplogin]' type='checkbox' ".($options['ga_disablewplogin'] ? 'checked' : '')." class='checkbox' />";
		echo '<label for="input_ga_disablewplogin" class="checkbox plain">'.__('Disable WordPress username/password login for my domain', 'google-apps-login').'</label>';

		echo '<br class="clear">';
		
		echo "<input id='input_ga_hidewplogin' name='".$this->get_options_name()."[ga_hidewplogin]' type='checkbox' ".($options['ga_hidewplogin'] ? 'checked' : '')." class='checkbox' />";
		echo '<label for="input_ga_hidewplogin" class="checkbox plain">'.__('Completely hide WordPress username and password boxes', 'google-apps-login').'</label>';
		
		echo '<br class="clear">';
		
		echo '<p class="desc">';
		_e( 'Tick the last two with caution - leave unchecked until you are confident Google Login is working for your own admin account' , 'google-apps-login');
		echo '</p>';
		
		echo '<br class="clear">';
		
		$this->groupsection_text();
		
		echo '</div>';
	}
	
	protected function want_premium_default_role() {
		return true;
	}
	
	protected function groupsection_text() {
	}
	
	protected function ga_advancedsection_extra() {
		$options = $this->get_option_galogin();
		echo '<br class="clear" />';
		echo "<input id='input_ga_googlelogout' name='".$this->get_options_name()."[ga_googlelogout]' type='checkbox' ".($options['ga_googlelogout'] ? 'checked' : '')." class='checkbox' />";
		echo '<label for="input_ga_googlelogout" class="checkbox plain">';
		_e( 'Automatically logout of Google when logging out of WordPress' , 'google-apps-login' );
		echo '</label>';
		echo '<br class="clear" />';
		echo '<label for="input_ga_loginbuttontext" class="textinput">'.__('Login button text (optional)', 'google-apps-login').'</label>';
		echo '<input id="input_ga_loginbuttontext" name="'.$this->get_options_name().'[ga_loginbuttontext]" size="40" type="text" value="'
				.esc_attr($options['ga_loginbuttontext']).'" class="textinput" '
		.'placeholder="'.esc_attr__( 'Login with Google' , 'google-apps-login').'" />';
	}
	
	protected function ga_moresection_text() {
		$options = $this->get_option_galogin();

		echo '<div id="license-section" class="galtab">';
		echo '<p>';
		_e( 'You should have received a license key when you purchased this professional version of Google Apps Login.', 'google-apps-login');
		echo '</p> <p> ';
		_e( 'Please enter it below to enable automatic updates, or <a href="mailto:contact@wp-glogin.com">email us</a> if you do not have one.', 'google-apps-login');
		echo '</p>';
		
		
		echo '<label for="input_ga_license_key" class="textinput big">'.__('License Key', 'google-apps-login').'</label>';
		echo "<input id='input_ga_license_key' name='".$this->get_options_name()."[ga_license_key]' size='40' type='text' value='".esc_attr($options['ga_license_key'])."' class='textinput' />";
		
		echo '<br class="clear" />';
		
		// Display latest license status
		
		$license_status = get_site_option($this->get_eddsl_optname(), true);
		
		if (is_array($license_status) && isset($license_status['license_id']) && $license_status['license_id'] != '') {
			echo '<br class="clear" />';
			echo '<table>';
			echo '<tr><td>Current License: </td><td>'.htmlentities(isset($license_status['license_id']) ? $license_status['license_id'] : '').'</td></tr>';
		
			if (isset($license_status['status']) && $license_status['status'] != '') {
				echo '<tr><td>Status: </td><td>'.htmlentities(strtoupper($license_status['status'])).'</td></tr>';
			}

			if (isset($license_status['last_check_time']) && $license_status['last_check_time'] != '') {
				echo '<tr><td>Last Checked: </td><td>'.htmlentities(date("j M Y H:i:s",$license_status['last_check_time'])).'</td></tr>';
			}

			/* if (isset($license_status['first_check_time']) && $license_status['first_check_time'] != '') {
				echo '<p>Result First Seen: '.htmlentities(date("M j Y H:i:s",$license_status['first_check_time'])).'</p>';
			} */
				
			if (isset($license_status['expires_time'])) { // && $license_status['expires_time'] < time() + 24*60*60*30) {
				echo '<tr><td>License Expires: </td><td>'.htmlentities(date("j M Y H:i:s",$license_status['expires_time'])).'</td></tr>';
			}
			
			/* if (isset($license_status['result_cleared'])) {
				echo '<p>Result cleared: '.($license_status['result_cleared'] ? 'yes' : 'no').'</p>';
			}*/
			
			echo '</table>';
			
			if (isset($license_status['expires_time']) && $license_status['expires_time'] < time() + 24*60*60*60) {
				echo '<p>';
				if (isset($license_status['renewal_link']) && $license_status['renewal_link']) {
					echo 'To renew your license, please <a href="'.esc_attr($license_status['renewal_link'])
							.'" target="_blank">click here</a>.';
				}
				echo ' You will receive a 50% discount if you renew before your license expires.</p>';
			}
			
			echo '<br class="clear" />';
		}
		
		echo '</div>';
	}
	
	public function ga_options_validate($input) {
		$newinput = parent::ga_options_validate($input);
		
		$newinput['ga_domainname'] = trim($input['ga_domainname']);
		if (!preg_match('/^(([0-9a-z-]+\.)*[0-9a-z-]+\.[a-z]{2,7}([ ,]*))*$/', $newinput['ga_domainname'])
					 && $newinput['ga_domainname'] != 'ANY_DOMAIN') {
			add_settings_error(
			'ga_domainname',
			'invalid_domains',
			self::get_error_string('ga_domainname|invalid_domains'),
			'error'
					);
		}
		$newinput['ga_autocreate'] = isset($input['ga_autocreate']) ? (boolean)$input['ga_autocreate'] : false;
		$newinput['ga_disablewplogin'] = isset($input['ga_disablewplogin']) ? (boolean)$input['ga_disablewplogin'] : false;
		$newinput['ga_hidewplogin'] = isset($input['ga_hidewplogin']) ? (boolean)$input['ga_hidewplogin'] : false;
		
		$newinput['ga_defaultrole'] = isset($input['ga_defaultrole']) 
					&& ($input['ga_defaultrole']=='' || !is_null(get_role($input['ga_defaultrole']))) 
			? $input['ga_defaultrole'] 
			: get_option('default_role');
		
		$newinput['ga_googlelogout'] = isset($input['ga_googlelogout']) ? (boolean)$input['ga_googlelogout'] : false;
		$newinput['ga_loginbuttontext'] = trim(stripslashes($input['ga_loginbuttontext']));
		
		// License Key
		$newinput['ga_license_key'] = trim($input['ga_license_key']);
		if ($newinput['ga_license_key'] != '') {
			if(!preg_match('/^.{32}.*$/i', $newinput['ga_license_key'])) {
				add_settings_error(
				'ga_license_key',
				'tooshort_texterror',
				self::get_error_string('ga_license_key|tooshort_texterror'),
				'error'
						);
			}
			else {
				// There is a valid-looking license key present
				
				$checked_license_status = get_site_option($this->get_eddsl_optname(), true);

				// Only bother trying to activate if we have a new license key OR the same license key but it was invalid on last check. 
				$existing_valid_license = '';
				if (is_array($checked_license_status) && isset($checked_license_status['license_id']) && $checked_license_status['license_id'] != ''
												&& isset($checked_license_status['status']) && $checked_license_status['status'] == 'valid') {
					$existing_valid_license = $checked_license_status['license_id'];
				}

				if ($existing_valid_license != $newinput['ga_license_key']) {
					
					$license_status = $this->edd_license_activate($newinput['ga_license_key']);
					if (isset($license_status['status']) && $license_status['status'] != 'valid') {
						add_settings_error(
						'ga_license_key',
						$license_status['status'],
						self::get_error_string('ga_license_key|'.$license_status['status']),
						'error'
								);
					}
				}
			}
		}
		
		return $newinput;
	}
	
	protected function get_error_string($fielderror) {
		$premium_local_error_strings = Array(
				'ga_domainname|invalid_domains' => __('Domain name should be a space-separated list of valid domains, in lowercase letters (or blank)', 'google-apps-login'),
				'ga_license_key|tooshort_texterror' => __('License key is too short', 'google-apps-login'),
				//	'valid', 'invalid', 'missing', 'item_name_mismatch', 'expired', 'site_inactive', 'inactive', 'disabled', 'empty'
				'ga_license_key|invalid' => __('License key failed to activate', 'google-apps-login'),
				'ga_license_key|missing' => __('License key does not exist in our system at all', 'google-apps-login'),
				'ga_license_key|item_name_mismatch' => __('License key entered is for the wrong product', 'google-apps-login'),
				'ga_license_key|expired' => __('License key has expired', 'google-apps-login'),
				'ga_license_key|site_inactive' => __('License key is not permitted for this website', 'google-apps-login'),
				'ga_license_key|inactive' => __('License key is not active for this website', 'google-apps-login'),
				'ga_license_key|disabled' => __('License key has been disabled', 'google-apps-login'),
				'ga_license_key|empty' => __('License key was not provided', 'google-apps-login')
		);
		if (isset($premium_local_error_strings[$fielderror])) {
			return $premium_local_error_strings[$fielderror];
		}
		return parent::get_error_string($fielderror);
	}

	protected function get_options_menuname() {
		return 'galogin_list_premium';
	}
	
	protected function get_options_pagename() {
		return 'galogin_premium';
	}
	
	protected function get_options_name() {
		return 'galogin_premium';
	}
	
	protected function get_default_options() {
		return array_merge( parent::get_default_options(), 
			Array('ga_domainname' => '',
				  'ga_autocreate' => false,
				  'ga_disablewplogin' => false,
				  'ga_hidewplogin' => false,
				  'ga_defaultrole' => get_option('default_role'),
				  'ga_license_key' => '',
				  'ga_loginbuttontext' => '',
				  'ga_googlelogout' => false) );
	}
	
	protected function get_wpglogincom_baseurl() {
		return 'http://wp-glogin.com/google-apps-login-premium/premium-setup/';
	}
	
	protected function add_actions() {
		parent::add_actions();
		$options = $this->get_option_galogin();
		
		add_action('login_message', Array($this, 'ga_login_message'));
		
		if ($options['ga_googlelogout']) {
			add_action('wp_logout', Array($this, 'ga_logout'));
		}
	}
	
	// Also logout of Google
	public function ga_login_message($message) {
		$options = $this->get_option_galogin();

		if ($options['ga_googlelogout'] && isset($_GET['loggedout']) && $_GET['loggedout'] == 'true'
			 && !isset($_GET['galoggedout'])) {

			$continueurl = $this->get_google_logout_url();

			$cancelurl = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : '';
			
			ob_start();
			?>
			<p class="galogin-logout">Also logging out of Google in <span>5</span> sec... (<a href="#">Cancel</a>)</p>
			
			<script type="text/javascript">
				jQuery(document).ready(function(){
					var logoutbox = jQuery('p.galogin-logout');

					jQuery('#login p.message').filter(':last').after(logoutbox);
					
					logoutbox.find('a').click(function() { 
						logoutbox.remove();
						var cancelurl = "<?php echo esc_js( $cancelurl ); ?>";
						if (cancelurl != '') {
							window.location = cancelurl;
						}
					});

					var timerfn = function() {
						var timerspan = jQuery('p.galogin-logout span');
						if (timerspan.length > 0) {
							var curtime = timerspan.html();
							--curtime;
							timerspan.html(curtime);
							if (curtime <= 0) {
								window.location = "<?php echo esc_js( $continueurl ); ?>";
							}
							else {
								setTimeout(timerfn, 1000);
							}
						}
					};
					
					setTimeout(timerfn, 1000);
				});
			</script>
			
			<?php
			$message .= ob_get_clean();
		}
		return $message;
	}
	
	protected function get_google_logout_url() {
		$redirect_url = !empty($_GET['redirect_to']) ? $_GET['redirect_to']
		: $this->get_login_url().'?loggedout=true&galoggedout=true';
			
		$continueurl = 'https://www.google.com/accounts/Logout?continue='
				.urlencode('https://appengine.google.com/_ah/logout?continue='
						.urlencode( $redirect_url )
				);
		
		return $continueurl;
	}
	
	public function ga_logout() {
		$options = $this->get_option_galogin();
		if (!empty( $_REQUEST['redirect_to'] ) && $options['ga_googlelogout']) {
			// Normal logout would send straight to redirect_to, but we need to do our Google auto-logout first
			wp_safe_redirect( 'wp-login.php?loggedout=true&redirect_to='.urlencode($_REQUEST['redirect_to']) );
			exit();
		}
	}
	
	// EDD auto-updates
	
	public function ga_admin_init() {
		$edd_updater = $this->edd_plugin_updater();
		$edd_updater->setup_hooks();
		parent::ga_admin_init();
	}
	
	protected function edd_plugin_updater($license_key=null) {
		if (is_null($license_key)) {
			$options = $this->get_option_galogin();
			$license_key = $options['ga_license_key'];
		}
	
		if( !class_exists( 'EDD_SL_Plugin_Updater4' ) ) {
			// load our custom updater
			include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
		}
			
		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater4( WPGLOGIN_GA_STORE_URL, $this->my_plugin_basename(),
				array(
						'version' 	=> $this->PLUGIN_VERSION,
						'license' 	=> $license_key,
						'item_name' => WPGLOGIN_GA_ITEM_NAME,
						'author' 	=> 'Dan Lester'
				),
				$this->get_eddsl_optname(),
				$this->get_settings_url()."#license"
		);

		return $edd_updater;
	}
	
	protected function get_eddsl_optname() {
		return null;
	}
	
	protected function edd_license_activate($license_key) {
		$edd_updater = $this->edd_plugin_updater($license_key);
		return $edd_updater->edd_license_activate();
	}

	// AUX FUNCTIONS
	
	private function split_domainslist($listtext) {
		$outdomains = Array();
		$indomains = preg_split('/[, ]+/', $listtext);
		foreach ($indomains as $domain) {
			$domain = trim($domain);
			if (preg_match('/^([0-9a-z-]+\.)+[a-z]{2,7}$/', $domain)) {
				$outdomains[] = $domain;
			}
		}
		return $outdomains;
	}
	
}


?>

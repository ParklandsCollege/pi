<?php

/**
 * Plugin Name: Google Apps Login Enterprise
 * Plugin URI: http://wp-glogin.com/
 * Description: Simple secure login and user management for Wordpress through your Google Apps domain (uses secure OAuth2, and MFA if enabled)
 * Version: 2.8.7
 * Author: Dan Lester
 * Author URI: http://wp-glogin.com/
 * License: Enterprise Paid per WordPress site and Google Apps domain
 * Network: true
 * Text Domain: google-apps-login
 * Domain Path: /lang
 * 
 * Do not copy, modify, or redistribute without authorization from author Lesterland Ltd (contact@wp-glogin.com)
 * 
 * You need to have purchased a license to install this software on one website, to be used in 
 * conjunction with a Google Apps domain containing the number of users you specified when you
 * purchased this software.
 * 
 * You are not authorized to use, modify, or distribute this software beyond the single site license that you
 * have purchased.
 * 
 * You must not remove or alter any copyright notices on any and all copies of this software.
 * 
 * This software is NOT licensed under one of the public "open source" licenses you may be used to on the web.
 * 
 * For full license details, and to understand your rights, please refer to the agreement you made when you purchased it 
 * from our website at https://wp-glogin.com/
 * 
 * THIS SOFTWARE IS SUPPLIED "AS-IS" AND THE LIABILITY OF THE AUTHOR IS STRICTLY LIMITED TO THE PURCHASE PRICE YOU PAID 
 * FOR YOUR LICENSE.
 * 
 * Please report violations to contact@wp-glogin.com
 * 
 * Copyright Lesterland Ltd, registered company in the UK number 08553880
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGLOGIN_GA_STORE_URL', 'http://wp-glogin.com' );
define( 'WPGLOGIN_GA_ITEM_NAME', 'Google Apps Login for WordPress Enterprise' );

require_once( plugin_dir_path(__FILE__).'/core/commercial_google_apps_login.php' );

class enterprise_google_apps_login extends commercial_google_apps_login {
	
	protected $PLUGIN_VERSION = '2.8.7';
	
	// Singleton
	private static $instance = null;
	
	public static function get_instance() {
		if (null == self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function ga_activation_hook($network_wide) {
		parent::ga_activation_hook($network_wide);
		
		// Populate ga_grouproles with current admins
		$options = $this->get_option_galogin();
		if (!isset($options['ga_grouproles'])) {
			$adminrole = 'administrator';
			$admins = get_users(Array('role' => $adminrole));
			if (is_array($admins) && count($admins) > 0) {
				$grouproles = Array();
				foreach ($admins as $usr) {
					$grouproles[$usr->user_email] = $adminrole;
				}
				
				$options['ga_grouproles'] = $grouproles;
				$this->save_option_galogin($options);
			}
		}
	}
	
	// Register for GAL service account
	
	public function gad_gather_serviceacct_reqs($reqs_array) {
		$reqs_array[] = array('Google Apps Login Enterprise version', 
							array('https://www.googleapis.com/auth/admin.directory.group.readonly'
								=> 'Access Google Groups information on your domain'));
		return $reqs_array;
	}
	
	protected function add_actions() {
		parent::add_actions();
		add_filter('gal_gather_serviceacct_reqs',  array($this, 'gad_gather_serviceacct_reqs'));
	}
	
	
	// Enterprise functions
	
	protected function check_groups($client, $userinfo, $user, $userdidnotexist) {
		$options = $this->get_option_galogin();
		if (!property_exists($userinfo, 'id') || !isset($options['ga_defaultrole'])) {
			return;
		}
		
		// There is a setting to check either every login or only when a new user is auto-created
		if (!$options['ga_groupsonlogin'] && !$userdidnotexist) {
			return;
		}
		
		$saoptions = $this->get_sa_option();
		
		$want_role = $options['ga_defaultrole'];
		
		$in_groups = Array(strtolower($userinfo->email) => true); // Can use emails instead of group
		
		if (isset($options['ga_grouproles']) && is_array($options['ga_grouproles']) && count($options['ga_grouproles']) > 0) {
			$grouproles = $options['ga_grouproles'];
						
			if (!isset($saoptions['ga_serviceemail']) || $saoptions['ga_serviceemail'] == '' 
					|| !isset($saoptions['ga_sakey']) || $saoptions['ga_sakey'] == '' 
							|| $options['ga_domainadmin'] == '') {
				$this->logTransError(LOG_WARNING, $userinfo->email, 'Skipping Google Groups check because Service Account options are not yet set');
			}
			else {
				
				try {
				
					$cred = $this->get_Auth_AssertionCredentials(
							array('https://www.googleapis.com/auth/admin.directory.group.readonly'));
					
					$serviceclient = $this->get_Google_Client();
					
					$serviceclient->setAssertionCredentials($cred);

					// Include paths were set when client was created
					if (!class_exists('GoogleGAL_Service_Directory')) {
						require_once( 'Google/Service/Directory.php' );
					}
					
					$groupservice = new GoogleGAL_Service_Directory($serviceclient);
					
					$nextToken = '';
					
					do {
					
						$groupsresult = $groupservice->groups->listGroups(Array('userKey' => $userinfo->id, 
																				'pageToken' => $nextToken));
						
						$groupsdata = $groupsresult->getGroups();
							
						foreach ($groupsdata as $g) {
							$group_email = strtolower($g->email);
							$in_groups[$group_email] = true;
						}
						
						$nextToken = $groupsresult->getNextPageToken();
					
					} while ($nextToken);
				
				} catch (GoogleGAL_Service_Exception $ge) {
					error_log("Google Service Error fetching Groups: ".$ge->getMessage());
					
					$errors = $ge->getErrors();
					$doneerr = false;
					if (is_array($errors) && count($errors) > 0) {
						if (isset($errors[0]['reason'])) {
							switch ($errors[0]['reason']) {
								case 'insufficientPermissions':
									$this->logTransError(LOG_WARNING, $userinfo->email, 'User had insufficient permission to fetch Google Group memberships', false);
									$doneerr = true;
									break;
						
								case 'accessNotConfigured':
									$this->logTransError(LOG_ERR, $userinfo->email, 'You need to enable Admin SDK for your project in Google Cloud Console', false);
									$doneerr = true;
									break;
							}
						}
					}
					
					if (!$doneerr) {
						$this->logTransError(LOG_WARNING, $userinfo->email, 'Service Error fetching Google Groups: '.$ge->getMessage(), false);
					}
					
				} catch (GoogleGAL_Auth_Exception $ge) {
					$error = $ge->getMessage();
					
					if (preg_match('/access_denied.+Requested scopes not allowed.+admin\.directory\.group\.readonly/s', $error)) {
						// Need to go in GA Admin: Security -> Advanced Settings -> Manage API client access and add 
						// https://www.googleapis.com/auth/admin.directory.group.readonly scope next to Service Account client ID
						$this->logTransError(LOG_ERR, $userinfo->email, 'Auth error fetching Groups - have you enabled domain-wide delegation');
					}
					elseif (preg_match('/refreshing the OAuth2 token.*invalid_grant/s', $error)) {
						$this->logTransError(LOG_ERR, $userinfo->email, 'Auth error invalid grant - does your JSON file match a valid Service Account key in a Google Cloud Console project');
					}
					else {
						$this->logTransError(LOG_WARNING, $userinfo->email, 'Auth Error fetching Google Groups: '.$ge->getMessage());
					}
				}
				catch (Exception $e) {
					$this->logTransError(LOG_ERR, $userinfo->email, 'General Error fetching Google Groups: '.$e->getMessage());
				}
			}
			
			
			foreach ($grouproles as $g => $r) {
				if (isset($in_groups[$g])) {
					$want_role = $r;
					break;
				}
			}
		}
		
		// Adjust roles if needed
		$this->apply_user_roles($want_role, $user, $in_groups);
	}
	
	// Consider applying roles for user on all blogs
	protected function apply_user_roles($want_role, $user, $in_groups) {
		$options = $this->get_option_galogin();
		
		if (is_multisite()) {
			// Super Admin is a special case: _gal_superadmin
			if ($want_role == '_gal_superadmin') {
				if (!is_super_admin($user->ID)) {
					
					if (!function_exists('grant_super_admin')) {
						require_once ABSPATH . 'wp-admin/includes/ms.php';
					}
					
					$rv = grant_super_admin($user->ID);
					$this->logTransError(LOG_INFO, $user->user_email, 'Promoting user to Network Super Admin: '.($rv ? 'success' : 'failed'));
				}

				// For sub-site purposes, set role to normal sub-site admin
				$want_role = 'admin';
			}
			elseif ($options['ga_demotesupers']) {
				// If they are an existing super admin, we should demote them
				if (is_super_admin($user->ID)) {
					
					if (!function_exists('revoke_super_admin')) {
						require_once ABSPATH . 'wp-admin/includes/ms.php';
					}
					
					$rv = revoke_super_admin($user->ID);
					$this->logTransError(LOG_INFO, $user->user_email, 'Demoted from Network Super Admin: '.($rv ? 'success' : 'failed'));
				}
			}
			
			// Examine membership and roles of sub-sites
			$blogs = $this->get_all_blogids();
			
			foreach ($blogs as $blogid) {
				$is_user_member = is_user_member_of_blog($user->ID, $blogid);
				if ($is_user_member || $options['ga_addsubsites']) {
					$this->set_user_role($want_role, $user, $blogid, $is_user_member, $in_groups);
				}
			}
				
		}
		else {
			$this->set_user_role($want_role, $user, get_current_blog_id(), true, $in_groups);
		}
	}
	
	// Actually set role for user on one specific blog
	// Filter gal_user_new_role can change the outcome
	// Will do nothing if user role is already as desired
	protected function set_user_role($want_role, $user, $blogid, $is_user_member, $in_groups) {
		$old_want_role = $want_role;
		$want_role = apply_filters('gal_user_new_role', $want_role, $user, $blogid, $is_user_member, $in_groups);
		
		if (is_multisite()) {
			if ($want_role == '') {
				if ($is_user_member) {
					remove_user_from_blog($user->ID, $blogid);
					$this->logTransError(LOG_INFO, $user->user_email, 'Removed user from sub-site ID '.$blogid);
				}
			}
			else {
				// But what was the old role?
				switch_to_blog($blogid);
				$blog_user = get_userdata( $user->ID );
				restore_current_blog();
				
				$prev_role = '';
				if ($is_user_member && $blog_user) {
					if (is_array($blog_user->roles) && count($blog_user->roles) == 1) {
						$rolelist = array_values($blog_user->roles);
						$prev_role = $rolelist[0];
					}
				}
				
				if ($prev_role != $want_role) {
					add_user_to_blog($blogid, $user->ID, $want_role);
					$this->logTransError(LOG_INFO, $user->user_email, 'Setting role to '.($want_role == '' ? '<no access>' : $want_role).' from role '
												.($prev_role == '' ? '<undefined>' : $prev_role).' on sub-site ID '.$blogid);
				}
			}
		}
		else {
			$prev_role = '';
			if (is_array($user->roles) && count($user->roles) == 1) {
				$rolelist = array_values($user->roles);
				$prev_role = $rolelist[0];
			}
			if ($prev_role != $want_role) {
				// Can this be blank to mean no privileges?
				$user->set_role($want_role);
				
				$this->logTransError(LOG_INFO, $user->user_email, 'Changed role to '.($want_role == '' ? '<no access>' : $want_role)
												.' from '.($prev_role == '' ? '<undefined>' : $prev_role));
			}
		}
	}
	
	private function get_all_blogids() {
		global $wpdb;
		$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d AND archived = '0' AND spam = '0' AND deleted = '0'", $wpdb->siteid ) );
		return is_array($blogids) ? $blogids : Array();
	}
	
	// Just log user creation
	protected function createUser($userinfo, $parts, $options) {
		$user = parent::createUser($userinfo, $parts, $options);
		if (!is_wp_error($user)) {
			$this->logTransError(LOG_INFO, $userinfo->email, 'Auto-created new user with role '.$options['ga_defaultrole']);
		}
		return $user;
	}
	
	protected function groupsection_text() {
		$options = $this->get_option_galogin();
		
		$grouproles = isset($options['ga_grouproles']) && is_array($options['ga_grouproles']) ? $options['ga_grouproles'] : Array();
		$grouproles[''] = '';
		
		echo '<h3>Enterprise Group Roles</h3>';
		
		$i = 1;
		foreach ($grouproles as $g => $r) {
		
		?>
		
		<p>If user is in Google Group/User with email 
		<input id='input_ga_group<?php echo $i; ?>' name='<?php echo $this->get_options_name(); ?>[ga_group<?php echo $i; ?>]' size='35' type='text' value='<?php echo esc_attr($g); ?>' />
		 then change role to 
		
		<select name='<?php echo $this->get_options_name(); ?>[ga_role<?php echo $i; ?>]' id='ga_role<?php echo $i; ?>'>
		<?php
		$this->output_roles_dropdown($r);
		?>
		</select>
				
		</p>
		
		<?php
			++$i;
		}
		
		//echo '<br class="clear">';
		echo '<label for="ga_defaultrole_ent" class="textinput">'.__('Default role', 'google-apps-login').'</label>';
		echo "<select name='".$this->get_options_name()."[ga_defaultrole]' id='ga_defaultrole_ent' class='select'>";
		$this->output_roles_dropdown($options['ga_defaultrole']);
		echo "</select>";
			
		echo '<br class="clear">';
		
		echo "<input id='input_ga_groupsonlogin' name='".$this->get_options_name()."[ga_groupsonlogin]' type='checkbox' ".($options['ga_groupsonlogin'] ? 'checked' : '')." class='checkbox' />";
		echo '<label for="input_ga_groupsonlogin" class="checkbox plain">'.__('Check and reset roles on every login', 'google-apps-login').'</label>';

		if (is_multisite()) {
			echo '<br class="clear">';
			
			echo "<input id='input_ga_addsubsites' name='".$this->get_options_name()."[ga_addsubsites]' type='checkbox' ".($options['ga_addsubsites'] ? 'checked' : '')." class='checkbox' />";
			echo '<label for="input_ga_addsubsites" class="checkbox plain">'.__('Add users to sub-sites if they are not yet members', 'google-apps-login').'</label>';

			echo '<br class="clear">';
				
			echo "<input id='input_ga_demotesupers' name='".$this->get_options_name()."[ga_demotesupers]' type='checkbox' ".($options['ga_demotesupers'] ? 'checked' : '')." class='checkbox' />";
			echo '<label for="input_ga_demotesupers" class="checkbox plain">'.__('Demote existing Super Admins who do not have a Super Admin mapping above', 'google-apps-login').'</label>';
		}
		
		echo '<br class="clear">';

		?>
		
		<p>Where a user matches multiple groups, the first match will be used. You can specify a user's own email address 
		instead of a Group email in order to target an individual user.</p>
		
		<p>Don't forget to enable the Admin SDK and also ensure your account will remain an Administrator.</p>
		
		<br class="clear">
		
		<p>In order for all users to have permissions to access their Group information from Google, you will need to create 
		a Service Account. Please see our 
		<a href="https://wp-glogin.com/google-apps-login-premium/enterprise-setup/?utm_source=EntServiceAccount&utm_medium=freemium&utm_campaign=EnterpriseLogin" target="_blank">extended instructions here</a> 
		or email contact@wp-glogin.com to arrange a walkthrough of this process.</p>
		
		<?php		
	}
	
	private function output_roles_dropdown($default_role) {
		if (is_multisite()) {
			echo "<option ".($default_role=='_gal_superadmin' ? "selected='selected'" : "")." value='_gal_superadmin'>-- Super Admin --</option>";
		}
		wp_dropdown_roles( $default_role );
		echo "<option ".($default_role=='' ? "selected='selected'" : "")." value=''>-- No Access --</option>";
	}
	
	protected function want_premium_default_role() {
		return false;
	}
	
	public function ga_options_validate($input) {
		$newinput = parent::ga_options_validate($input);
		
		$newgrouproles = Array();
		if (isset($input['ga_grouproles']) && is_array($input['ga_grouproles'])) {
			// If being called just to sanitize the options
			
			foreach ($input['ga_grouproles'] as $g => $r) {
				$g = sanitize_email($g);
				if ($g != '') {
					$newgrouproles[$g] = (string)$r;
				}
			}
		}
		else {
			// If being called on form submission
			
			$i = 1;
			while (isset($input['ga_group'.$i]) && isset($input['ga_role'.$i])) {
				$g = strtolower(trim($input['ga_group'.$i]));
				if ($g != '') {
					$g = sanitize_email($g);
					if ($g == '') {
						add_settings_error(
						'ga_grouproles',
						'email_error',
						self::get_error_string('ga_grouproles|email_error'),
						'error'
						);
					}
					$newgrouproles[$g] = $input['ga_role'.$i];
				}
				++$i;
			}
		}
		
		$newinput['ga_grouproles'] = $newgrouproles;
		
		$newinput['ga_groupsonlogin'] = isset($input['ga_groupsonlogin']) ? (boolean)$input['ga_groupsonlogin'] : false;
		
		$newinput['ga_addsubsites'] = isset($input['ga_addsubsites']) ? (boolean)$input['ga_addsubsites'] : false;
		$newinput['ga_demotesupers'] = isset($input['ga_demotesupers']) ? (boolean)$input['ga_demotesupers'] : false;
		
		return $newinput;
	}
	
	protected function get_error_string($fielderror) {
		$premium_local_error_strings = Array(
				'ga_grouproles|email_error' => __('Group names must be valid email addresses', 'google-apps-login')
		);
		if (isset($premium_local_error_strings[$fielderror])) {
			return $premium_local_error_strings[$fielderror];
		}
		return parent::get_error_string($fielderror);
	}
	
	protected function get_default_options() {
		return array_merge( parent::get_default_options(),
				Array('ga_groupsonlogin' => false,
					  'ga_addsubsites' => false,
					  'ga_demotesupers' => false)
				 );
	}
	
	// Error Logging
	
	protected function logTransError($level, $useremail, $msg, $echo_to_errlog=true) {
		$trans = $this->getTransErrors();
		if ($trans === false || !is_array($trans)) {
			$trans = Array();
		}
		array_unshift($trans, Array('level' => $level, 'time' => time(), 'user' => $useremail, 'msg' => $msg));
		$trans = array_slice($trans, 0, 100);
		set_site_transient('gal_enterprise_logs', $trans, 0);
		
		if ($echo_to_errlog) {
			error_log("For user ".$useremail.": ".$msg);
		}
	}
	
	protected function getTransErrors() {
		return get_site_transient('gal_enterprise_logs');
	}
	
	protected function draw_more_tabs() {
		parent::draw_more_tabs();
		?>
		<a href="#logs" id="logs-tab" class="nav-tab">Logs</a>
		<?php
	}
	
	protected function ga_moresection_text() {
		parent::ga_moresection_text();
		
		$errors = $this->getTransErrors();
	
		echo '<div id="logs-section" class="galtab">';
		
		if ($errors === false || !is_array($errors)) {
			echo '<p>';
			_e( 'There are no logs to view.', 'google-apps-login');
			echo '</p>';
		}
		else {
			echo '<table>';
			
			foreach ($errors as $err) {
				echo '<tr class="gal_log_level'.$err['level'].'"> ';
				echo '<td>'.htmlentities(date("Y-m-d H:i:s",$err['time'])).'</td> ';
				echo '<td>'.htmlentities($err['user']).'</td> ';
				echo '<td>'.htmlentities($err['msg']).'</td> ';
				echo '</tr>';
			}
		
			echo '</table>';
		
		}
	
		echo '</div>';
	}
	
	// AUX FUNCTIONS
	
	protected function get_eddsl_optname() {
		return 'eddsl_gal_enterprise_ls';
	}
	
	public function my_plugin_basename() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			$basename = basename(dirname(__FILE__)).'/'.basename(__FILE__);
		}
		return $basename;
	}
	
	protected function my_plugin_url() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			return plugins_url().'/'.basename(dirname(__FILE__)).'/';
		}
		// Normal case (non symlink)
		return plugin_dir_url( __FILE__ );
	}
	
}

// Global accessor function to singleton
function GoogleAppsLogin() {
	return enterprise_google_apps_login::get_instance();
}

// Initialise at least once
GoogleAppsLogin();

?>

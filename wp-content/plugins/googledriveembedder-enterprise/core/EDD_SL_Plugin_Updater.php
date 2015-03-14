<?php

// uncomment this line for testing
// set_site_transient( 'update_plugins', null );

/* To test locally, change line 454 of
 * wp-admin/includes/file.php
 * from wp_safe_remote_get to wp_remote_get
 * (within function download_url)
 */

/**
 * Allows plugins to use their own update API.
 *
 * @author Pippin Williamson and Dan Lester
 * @version 3
 */
class EDD_SL_Plugin_Updater3 {
	private $api_url  = '';
	private $api_data = array();
	private $name     = '';
	private $slug     = '';
	
	private $license_status_optname = '';
	private $license_settings_url = '';
	
	private $license_warning_delay = 7200; // Two hours

	/**
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 * @param string $_api_url The URL pointing to the custom API endpoint.
	 * @param string $_plugin_file Path to the plugin file.
	 * @param array $_api_data Optional data to send with API calls.
	 * @return void
	 */
	function __construct( $_api_url, $_plugin_file, $_api_data = null, 
							$license_status_optname = null, $license_settings_url = null ) {
		$this->api_url  = trailingslashit( $_api_url );
		$this->api_data = urlencode_deep( $_api_data );
		$this->name     = plugin_basename( $_plugin_file );
		$this->slug     = basename( $_plugin_file, '.php');
		$this->version  = $_api_data['version'];
		
		if (is_null($license_status_optname)) {
			$license_status_optname = 'eddsl_'.$this->slug;
		}
		$this->license_status_optname = $license_status_optname;
		
		if (!is_null($license_settings_url)) {
			$this->license_settings_url = $license_settings_url;
		}
	}

	/**
	 * Set up Wordpress filters to hook into WP's update process.
	 *
	 * @uses add_filter()
	 *
	 * @return void
	 */
	public function setup_hooks() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins_filter' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
		add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 );
		
		// Set notices
		
		if (!current_user_can( is_multisite() ? 'manage_network_options' : 'manage_options' )) {
			return;
		}
		
		// Setup hooks to display message if license is not valid
		$license_status = $this->get_license_status_option();

		if (isset($license_status['status']) && isset($license_status['result_cleared'])
				 && !$license_status['result_cleared']) {
			
			// Do they want it cleared?
			// $nothanks_url = add_query_arg( $this->license_status_optname.'_eddsl_action', 'no_thanks' );
			$queryname = $this->license_status_optname.'_eddsl_action';
			if (isset($_REQUEST[$queryname]) && $_REQUEST[$queryname]=='no_thanks') {
				$license_status = $this->get_license_status_option();
				$license_status['result_cleared'] = true;
				update_site_option($this->license_status_optname, $license_status);
				$this->license_status_option = $license_status;
			}
			else {
				if (is_multisite()) {
					add_action('network_admin_notices', Array($this, 'eddsl_license_notice'));
				}
				add_action('admin_notices', Array($this, 'eddsl_license_notice'));
			}
		}
	}
	
	protected $version_info = null;
	protected $auto_license_checked = false;

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update api just when Wordpress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native Wordpress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @uses api_request()
	 *
	 * @param array $_transient_data Update array build by Wordpress.
	 * @return array Modified update array with custom plugin data.
	 */	
	function pre_set_site_transient_update_plugins_filter( $_transient_data ) {

		if( ! is_object( $_transient_data ) ) {
			$_transient_data = new stdClass;
		}
	
		if ( empty( $_transient_data->response ) || empty( $_transient_data->response[ $this->name ] ) ) {
	
			if (is_null($this->version_info)) { 
				$this->version_info = $this->api_request( 'get_version', array( 'slug' => $this->slug ) );
			}
	
			if ( false !== $this->version_info && is_object( $this->version_info ) && isset( $this->version_info->new_version ) ) {
	
				if( version_compare( $this->version, $this->version_info->new_version, '<' ) ) {
	
					$_transient_data->response[ $this->name ] = $this->version_info;
	
				}
	
				$_transient_data->last_checked = time();
				$_transient_data->checked[ $this->name ] = $this->version;
	
			}
	
		}
	
		// Do actual license check now
		if (!$this->auto_license_checked) {
			$api_check = $this->api_request( 'check_license', array( 'slug' => $this->slug ) );
			$license_status = $this->update_license_status_option($api_check);
			$this->auto_license_checked = true;
		}
	
		return $_transient_data;
	}
	
	
	protected function update_license_status_option($api_response) {
		$license_status = array('license_id' => $this->api_data['license']);
		
		// Problem such as missing license key?
		$license_status['status'] = 'invalid';
		if (is_null($api_response)) {
			$license_status['status'] = 'empty';
		}
		elseif (isset($api_response->license_check)) { // Probably called get_version
			$license_status['status'] = $api_response->license_check;
		}
		elseif (isset($api_response->success)) { // Call was activate_license
			$license_status['status'] = isset($api_response->error) ? $api_response->error : 
											($api_response->success ? 'valid' : 'invalid');
		}
		else if (isset($api_response->license)) { // check_license
			$license_status['status'] = $api_response->license;
		}
		
		if (!in_array($license_status['status'], array('valid', 'invalid', 'missing', 'item_name_mismatch', 'expired', 'site_inactive', 'inactive', 'disabled', 'empty'))) {
			$license_status['status'] = 'invalid';
		}
		
		$license_status['expires'] = null;
		$license_status['expires_time'] = null;
		$license_status['expires_day'] = null;
		$license_status['renewal_link'] = null;
		if (isset($api_response->expires)) {
			$expires_time = strtotime($api_response->expires);
			
			if ($expires_time) {
				$license_status['expires'] = $api_response->expires;
				$license_status['expires_time'] = $expires_time;
				$license_status['expires_day'] = date("j M Y", $expires_time);
			}
		}
		if (isset($api_response->renewal_link)) {
			$license_status['renewal_link'] = $api_response->renewal_link;
		}
		
		// Compare to existing option if any
		$license_status['last_check_time'] = $license_status['first_check_time'] = time();
		$license_status['result_cleared'] = false;
		$old_license_status = get_site_option($this->license_status_optname, true);
		if (is_array($old_license_status) && isset($old_license_status['license_id'])
					&& isset($old_license_status['status'])) {
			if ($old_license_status['license_id'] == $license_status['license_id']
				&& $old_license_status['status'] == $license_status['status']
				&& (isset($old_license_status['expires']) ? $old_license_status['expires'] : 0) == $license_status['expires']) {
				if (isset($old_license_status['first_check_time'])) {
					$license_status['first_check_time'] = $old_license_status['first_check_time'];
				}
				if (isset($old_license_status['result_cleared'])) {
					$license_status['result_cleared'] = $old_license_status['result_cleared'];
				}
			}
		}
		
		update_site_option($this->license_status_optname, $license_status);
		$this->license_status_option = $license_status;
		
		return $license_status;
	}
	
	protected $license_status_option = null;
	protected function get_license_status_option() {
		if (!is_null($this->license_status_option)) {
			return $this->license_status_option;
		}
		$this->license_status_option = get_site_option($this->license_status_optname, Array());
		return $this->license_status_option;
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @uses api_request()
	 *
	 * @param mixed $_data
	 * @param string $_action
	 * @param object $_args
	 * @return object $_data
	 */
	function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		if ( ( $_action != 'plugin_information' ) || !isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) return $_data;

		$to_send = array( 'slug' => $this->slug );

		$api_response = $this->api_request( 'get_version', $to_send ); // plugin_information
		if ( false !== $api_response ) $_data = $api_response;

		return $_data;
	}


	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array $args
	 * @param string $url
	 * @return object $array
	 */
	function http_request_args( $args, $url ) {
		// If it is an https request and we are performing a package download, disable ssl verification
		if( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
			$args['sslverify'] = false;
		}
		return $args;
	}

	/**
	 * Calls the API and, if successful, returns the object delivered by the API.
	 *
	 * @uses get_bloginfo()
	 * @uses wp_remote_post()
	 * @uses is_wp_error()
	 *
	 * @param string $_action The requested action.
	 * @param array $_data Parameters for the API action.
	 * @return false||object
	 */
	private function api_request( $_action, $_data ) {

		global $wp_version;

		$data = array_merge( $this->api_data, $_data );

		if( $data['slug'] != $this->slug ) {
			return;
		}

		if( empty( $data['license'] ) )
			return;

		$api_params = array(
			'edd_action' 	=> $_action, // 'get_version'
			'license' 		=> $data['license'],
			'item_name' 	=> $data['item_name'], // Used to be key 'name'
			'slug' 			=> $this->slug,
			'author'		=> $data['author'],
			'url'           => home_url()
		);
		
		$request = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( ! is_wp_error( $request ) ):
			$request = json_decode( wp_remote_retrieve_body( $request ) );
			if( $request && isset( $request->sections ) )
				$request->sections = maybe_unserialize( $request->sections );
			return $request;
		else:
			return false;
		endif;
	}
	
	public function edd_license_activate() {
		// data to send in our API request
		$api_params = array(
				'edd_action'=> 'activate_license',
				'license' 	=> $this->api_data['license'],
				'item_name' => $this->api_data['item_name']
		);
		
		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, $this->api_url ),
				array( 'timeout' => 15, 'sslverify' => false ) );
		
		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;
		
		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		return $this->update_license_status_option($license_data);
	}
	
	public function eddsl_license_notice() {
		$license_status = $this->get_license_status_option();
		$msg = '';
		
		$yes_link = $this->license_settings_url ? $this->license_settings_url : '';
		$yes_text = 'Enter License';
		$yes_target = '';
		
		if (isset($license_status['status']) && $license_status['status'] != 'valid'
			&& (!isset($license_status['result_cleared']) || !$license_status['result_cleared'])) {
			// Wait a couple of days before warning about the issue - give them time to finish setup first
			if (!isset($license_status['first_check_time']) || $license_status['first_check_time'] < time() - $this->license_warning_delay) {

				// 'valid', 'invalid', 'missing', 'item_name_mismatch', 'expired', 'site_inactive', 'inactive', 'disabled', 'empty'
				switch ($license_status['status']) {
					case 'missing':
						$msg = 'Your license key is not found in our system at all.';
						break;
					case 'item_name_mismatch':
						$msg = 'The license key you entered is for a different product.';
						break;
					case 'expired':
						$msg = 'Your license key has expired.';
						if (isset($license_status['renewal_link']) && $license_status['renewal_link']) {
							$yes_link = $license_status['renewal_link'];
							$yes_text = 'Renew License';
							$yes_target = ' target="_blank" ';
						}
						break;
					case 'site_inactive':
						$msg = 'Your license key is not active for this website.';
						break;
					case 'inactive':
						$msg = 'Your license key is not active.';
						break;
					case 'disabled':
						$msg = 'Your license key has been disabled.';
						break;
					case 'empty':
						$msg = 'You have not entered your license key.';
						break;
					case 'invalid':
					default:
						$msg = 'Your license key is invalid.';
						break;
					
				}
			}
		}
		else if (isset($license_status['status']) && $license_status['status'] == 'valid'
			&& (!isset($license_status['result_cleared']) || !$license_status['result_cleared'])) {
			// License valid, but will it expire soon?
			if (isset($license_status['expires_time']) && $license_status['expires_time'] < time() + 24*60*60*30) {
				$msg = 'License will expire '.(isset($license_status['expires_day']) ? 'on '.$license_status['expires_day'] : 'soon');
				$msg .= '. Save 50% by renewing in advance!';
				if (isset($license_status['renewal_link']) && $license_status['renewal_link']) {
					$yes_link = $license_status['renewal_link'];
					$yes_text = 'Renew License';
					$yes_target = ' target="_blank" ';
				}
			}
		}
		
		if ($msg != '') {
			$nothanks_url = add_query_arg( $this->license_status_optname.'_eddsl_action', 'no_thanks' );
			echo '<div class="error"><p>';
			if (isset($this->api_data['item_name'])) {
				echo htmlentities('Alert for '.urldecode($this->api_data['item_name']).': ');
			}
			echo htmlentities($msg);
			if ($yes_link) {
				echo ' &nbsp; <a href="'.esc_attr($yes_link).'" class="button-secondary" '.$yes_target
						.'>'.htmlentities($yes_text).'</a>';
			}
			echo '&nbsp;<a href="' . esc_url( $nothanks_url ) . '" class="button-secondary">Ignore</a>';
			echo '</p></div>';
		}
	}
	
}
<?php

/*
Font Fetcher.
Gets google font list per API
https://developers.google.com/fonts/docs/developer_api

Call:
https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=AIzaSyAx7cyr9G4t5NajODtUfRfrJ-M8DULKC5o

If unsuccessful [max-quote, error] uses manually provided list.
*/


if( !function_exists( 'sleek_get_google_fonts' ) ){
	function sleek_get_google_fonts() {

		/*------------------------------------------------------------*/
		/*	Get Google Fonts by API call and store in global var
		/*------------------------------------------------------------*/

		static $sleek_google_fonts;

		if( $sleek_google_fonts ) {
			return $sleek_google_fonts;
		}

		// cached file location
		$sleek_cached_file = THEME_ADMIN . '/google_fonts/google_fonts.txt';
		// Total time the file will be cached in seconds, set to a 28days
		$cachetime = 86400 * 28;

		if(
			file_exists($sleek_cached_file)
			&& filesize($sleek_cached_file) != 0
			&& $cachetime > time()-filemtime($sleek_cached_file)
		){

			// $sleek_google_fonts = (array)wp_remote_get( esc_url_raw( THEME_ADMIN_URI.'/google_fonts/google_fonts.txt' ) );
			// $sleek_google_fonts = json_decode( $sleek_google_fonts['body'] );
			$sleek_google_fonts = file_get_contents( THEME_ADMIN . '/google_fonts/google_fonts.txt' );
			$sleek_google_fonts = json_decode( $sleek_google_fonts );

		}else{

			$sleek_google_fonts_response = wp_remote_get( esc_url_raw( 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=AIzaSyAx7cyr9G4t5NajODtUfRfrJ-M8DULKC5o' ) );

			if( !is_wp_error( $sleek_google_fonts_response ) && $sleek_google_fonts_response['response']['code'] == 200 ){

				// save cache
				$fp = fopen($sleek_cached_file, 'w');
				fwrite($fp, $sleek_google_fonts_response['body']);
				fclose($fp);

				$sleek_google_fonts = json_decode($sleek_google_fonts_response['body']);

			}else{
				$sleek_google_fonts = file_get_contents( THEME_ADMIN . '/google_fonts/google_fonts.txt' );
				$sleek_google_fonts = json_decode( $sleek_google_fonts );
			}

		}

		$sleek_google_fonts = isset($sleek_google_fonts) ? $sleek_google_fonts->items : '';
		return $sleek_google_fonts;
	}
}

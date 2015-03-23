<?php

/**
 * Class WPGlobus_Utils
 */
class WPGlobus_Utils {

	/**
	 * Localize URL by inserting language prefix
	 *
	 * @param string          $url      URL to localize
	 * @param string          $language Language code
	 * @param WPGlobus_Config $config Alternative configuration (i.e. Unit Test mock object)
	 *
	 * @return string
	 */
	public static function localize_url( $url = '', $language = '', WPGlobus_Config $config = null ) {

		/**
		 * Use the global configuration is alternative not passed
		 */
		if ( is_null( $config ) ) {
			$config = WPGlobus::Config();
		}

		/**
		 * In Admin-Settings-General:
		 * WordPress Address (URL) is site_url()
		 * Site Address (URL) is home_url
		 * We need home_url, and we cannot use the @home_url function,
		 * because it will filter back here causing endless loop.
		 * @todo Multisite?
		 */
		$home_url = get_option( 'home' );
		
		/**
		 * Adduction $home_url and $url to equal with/without www
		 */
		if ( false === strpos($url, 'www.') ) {
			$home_url = str_replace('www.', '', $home_url);	
		} else {
			if ( false === strpos($home_url, 'www.') ) {
				$url = str_replace('www.', '', $url);	
			}	
		}	

		/**
		 * Use the current language if not passed
		 */
		$language = empty( $language ) ? $config->language : $language;

		/**
		 * This says "Do not use language code in the default URL"
		 * So, no /en/page/, just /page/
		 */
		if ( $language === $config->default_language && $config->hide_default_language ) {
			$language = '';
		}

		/**
		 * Language prefix looks like '/ru/'
		 */
		$language_url_prefix = trailingslashit( '/' . $language );

		/**
		 * Regex to replace current language prefix with the requested one.
		 * @example !http://www\.example\.com/?(en|ru|pt)?/?!
         * @example !http://example\.com/?(en|ru|pt)?/?!
		 */
		$re = '!' .
		      str_replace( '.', '\.', $home_url ) .
		      '/?(' . join( '|', $config->enabled_languages ) . ')?/?' . '!';

		/**
		 * Replace the existing (or empty) language prefix with the requested one
		 */
		$localized_url = preg_replace( $re, $home_url . $language_url_prefix, $url );

		return $localized_url;
	}

	/**
	 * @deprecated 1.0.7.2
	 * Get converted url
	 *
	 * @param string $url
	 * @param string $language
	 *
	 * @return string
	 */
	public static function get_convert_url( $url = '', $language = '' ) {

		global $WPGlobus_Config;

		if ( empty( $url ) ) {
			return $url;
		}

		//		$converted_url = '';

		$language = empty( $language ) ? $WPGlobus_Config->language : $language;

		$parsed_url = self::parse_url( $url );

		if ( ! $parsed_url ) {
			return $url;
		}

		if ( empty( $parsed_url['host'] ) ) {
			return $url;
		}

		if ( false === strpos( get_option( 'home' ), $parsed_url['host'] ) ) {
			/**
			 * Don't convert external url
			 */
			return $url;
		}

		//		switch ( $WPGlobus_Config->get_url_mode() ) :
		//			case WPGlobus_Config::GLOBUS_URL_PATH:
		// pre url

		if ( $language == $WPGlobus_Config->default_language && $WPGlobus_Config->hide_default_language ) {
			$language = '';
		} else {
			$language = '/' . $language;
		}

		$fragment = empty( $parsed_url['fragment'] ) ? '' : '#' . $parsed_url['fragment'];

		$home = WPGlobus::Config()->url_info['home'];
		if ( '/' == $home ) {
			$converted_url =
				$parsed_url['scheme'] . '://' . $parsed_url['host'] . $language . $parsed_url['path'] . $fragment;

		} else {
			/**
			 * Case when WordPress Address (URL) and Site Address (URL) ==  http://example.com/blog or
			 * WordPress Address (URL) == http://example.com/blog and Site Address (URL) ==  http://example.com
			 */
			$path          = $home . $language . '/' . str_replace( $home, '', $parsed_url['path'] . '/' );
			$path          = str_replace( array( '///', '//' ), '/', $path );
			$converted_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $path . $fragment;
		}	
		
		//				break;
		//			case WPGlobus_Config::GLOBUS_URL_DOMAIN:
		//				// pre domain
		//
		//				break;
		//			case WPGlobus_Config::GLOBUS_URL_QUERY:
		//				// query (question mark)
		//
		//				break;
		//		endswitch;

		return $converted_url;

	}

	/**
	 * Returns cleaned string and language information
	 * Improved version, also understands $url without scheme:
	 * //example.com, example.com/, and so on
	 *
	 * @param string $current_language
	 *
	 * @return string
	 */
	public static function get_url( $current_language = '' ) {
		global $WPGlobus_Config;

		$current_language = ( '' == $current_language ) ? $WPGlobus_Config->language : $current_language;
		//		$url              = '';

		//		if ( $WPGlobus_Config->get_url_mode() == $WPGlobus_Config::GLOBUS_URL_PATH ) {

		$language = '/' . $current_language;
		if ( $current_language == $WPGlobus_Config->default_language && $WPGlobus_Config->hide_default_language ) {
			$language = '';
		}

		$url = self::get_scheme() . '://' . $_SERVER["HTTP_HOST"] . $language . $WPGlobus_Config->url_info['url'];

		//		}

		//		elseif ( $WPGlobus_Config->get_url_mode() == $WPGlobus_Config::GLOBUS_URL_QUERY ) {
		//
		//			if ( $current_language == $WPGlobus_Config->default_language && $WPGlobus_Config->hide_default_language ) {
		//
		//				$url = '';
		//
		//			} else {
		//
		//				$arr = self::extract_url( $WPGlobus_Config->url_info['url'] );
		//
		//				if ( false === strpos( $arr['url'], '?' ) ) {
		//					$url = '?';
		//				} else {
		//					$url = '&';
		//				}
		//				$url .= 'lang=' . $current_language;
		//
		//			}
		//
		//			$url =
		//				self::get_scheme() . '://' . $_SERVER["HTTP_HOST"] . $WPGlobus_Config->url_info['url'] . $url;
		//		}

		return $url;
	}

	/**
	 * Get Request Scheme
	 * @return string
	 */
	public static function get_scheme() {
		if ( is_ssl() ) {
			return 'https';
		}

		return 'http';
	}

	/**
	 * Return true if language is in array of opened languages, otherwise false
	 *
	 * @param string $language
	 *
	 * @return bool
	 */
	public static function is_open( $language ) {
		return in_array( $language, WPGlobus::Config()->open_languages );
	}

	/**
	 * Return true if language is in array of enabled languages, otherwise false
	 *
	 * @param string $language
	 *
	 * @return bool
	 */
	public static function is_enabled( $language ) {
		global $WPGlobus_Config;

		return in_array( $language, $WPGlobus_Config->enabled_languages );
	}

	/**
	 * @param string $s
	 * @param string $n
	 *
	 * @return bool
	 */
	public static function starts_with( $s, $n ) {
		if ( strlen( $n ) > strlen( $s ) ) {
			return false;
		}
		if ( $n == substr( $s, 0, strlen( $n ) ) ) {
			return true;
		}

		return false;
	}


	/**
	 * @param string $url
	 *
	 * @return false
	 * @return array
	 * @todo Why not use native PHP method?
	 * @see  parse_url()
	 */
	public static function parse_url( $url ) {

		if ( empty( $url ) ) {
			return false;
		}

		$scheme   = '(?:(\w+)://)';
		$userpass = '(?:(\w+)\:(\w+)@)';
		$host     = '([^/:]+)';
		$port     = '(?:\:(\d*))';
		$path     = '(/[^#?]*)';
		$query    = '(?:\?([^#]+))';
		$fragment = '(?:#(.+$))';

		$r =
			'!' . $scheme . '?' . $userpass . '?' . $host . '?' . $port . '?' . $path . '?' . $query . '?' . $fragment . '?!i';

		preg_match( $r, $url, $out );

		$result = array(
			"scheme"   => ( empty( $out[1] ) ? '' : $out[1] ),
			"host"     => ( empty( $out[4] ) ? '' : $out[4] ) . ( empty( $out[5] ) ? '' : ':' . $out[5] ),
			"user"     => ( empty( $out[2] ) ? '' : $out[2] ),
			"pass"     => ( empty( $out[3] ) ? '' : $out[3] ),
			"path"     => ( empty( $out[6] ) ? '' : $out[6] ),
			"query"    => ( empty( $out[7] ) ? '' : $out[7] ),
			"fragment" => ( empty( $out[8] ) ? '' : $out[8] )
		);

		// Host can be in path in case of url with incorrect scheme. Try to find it in path
		if ( empty( $result['host'] ) ) {
			$www    = '(www\.)';
			$domain = '((?:\w+\.)+\w+)';

			$r2 = '!' . $www . '?' . $domain . $path . '?!i';

			if ( preg_match( $r2, $url, $out2 ) ) {
				$result['host'] = $out2[1] . $out2[2];
				/**
				 * @todo check /wp-admin/edit.php?post_type=product with WPGlobus WC
				 * PHP Notice:  Undefined offset: 3 in class-wpglobus-utils.php
				 */
				$result['path'] = isset( $out2[3] ) ? $out2[3] : '';
			}
		}

		return $result;
	}

	/**
	 * @param string $url
	 * @param string $host
	 * @param string $referer
	 *
	 * @return array
	 */
	public static function extract_url( $url, $host = '', $referer = '' ) {

		$referer_save = $referer;

		$home_url = get_option( 'home' );

		$home         = self::parse_url( $home_url );
		$home['path'] = trailingslashit( $home['path'] );
		$referer      = self::parse_url( $referer );

		$result                     = array();
		$result['language']         = WPGlobus::Config()->default_language;
		$result['url']              = $url;
		$result['original_url']     = $url;
		$result['host']             = $host;
		$result['redirect']         = false;
		$result['internal_referer'] = false;
		$result['home']             = $home['path'];
		$result['schema']           = is_ssl() ? 'https://' : 'http://';

		//		switch ( WPGlobus::Config()->get_url_mode() ) {
		//			case WPGlobus_Config::GLOBUS_URL_PATH:
		// pre url
		$url = substr( $url, strlen( $home['path'] ) );
		if ( $url ) {
			// might have language information
			if ( preg_match( "#^([a-z]{2})(/.*)?$#i", $url, $match ) ) {
				if ( self::is_enabled( $match[1] ) ) {
					// found language information
					$result['language'] = $match[1];
					$result['url']      = $home['path'] . substr( $url, 3 );
				}
			}
		}
		//				break;
		//			case WPGlobus_Config::GLOBUS_URL_DOMAIN:
		//				// pre domain
		//				if ( $host ) {
		//					if ( preg_match( "#^([a-z]{2}).#i", $host, $match ) ) {
		//						if ( self::is_enabled( $match[1] ) ) {
		//							// found language information
		//							$result['language'] = $match[1];
		//							$result['host']     = substr( $host, 3 );
		//						}
		//					}
		//				}
		//				break;
		//		}

		// check if referer is internal
		if ( $referer['host'] == $result['host'] && self::starts_with( $referer['path'], $home['path'] ) ) {
			// user coming from internal link
			$result['internal_referer'] = true;
		}

		if ( isset( $_GET['lang'] ) && self::is_enabled( $_GET['lang'] ) ) {
			// language override given
			$result['language'] = $_GET['lang'];
			$result['url']      = preg_replace( "#(&|\?)lang=" . $result['language'] . "&?#i", "$1", $result['url'] );
			$result['url']      = preg_replace( "#[\?\&]+$#i", "", $result['url'] );

		} elseif ( $home['host'] == $result['host'] && $home['path'] == $result['url'] ) {

			if ( empty( $referer['host'] ) || ! WPGlobus::Config()->hide_default_language ) {

				$result['redirect'] = true;

			} else {
				// check if activating language detection is possible
				if ( preg_match( "#^([a-z]{2}).#i", $referer['host'], $match ) ) {
					if ( self::is_enabled( $match[1] ) ) {
						// found language information
						$referer['host'] = substr( $referer['host'], 3 );
					}
				}
				if ( ! $result['internal_referer'] ) {
					// user coming from external link
					$result['redirect'] = true;
				}
			}
		}

		/**
		 * If DOING_AJAX, we cannot retrieve the language information from the URL,
		 * because it's always `admin-ajax`. Therefore, we'll rely on the HTTP_REFERER.
		 * @since 1.0.9
		 */
		if ( ! empty( $referer_save ) && WPGlobus_WP::is_doing_ajax() ) {

			/**
			 * Regex to find the language prefix.
			 * @example !http://www\.example\.com/(en|ru|pt)/!
			 */
			$re = '!' .
			      str_replace( '.', '\.', $home_url ) .
			      '/(' . join( '|', WPGlobus::Config()->enabled_languages ) . ')/' . '!';

			if ( preg_match( $re, $referer_save, $match ) ) {
				// Found language information
				$result['language'] = $match[1];
			}

		}


		return $result;
	}

	/**
	 * Check if was called by a specific function (could be any levels deep).
	 * Note: does not check if the function is in a class method.
	 *
	 * @param string $function_name
	 *
	 * @return bool
	 */
	public static function is_function_in_backtrace( $function_name ) {
		$function_in_backtrace = false;

		foreach ( debug_backtrace() as $_ ) {
			if ( ! empty( $_['function'] ) && $_['function'] === $function_name ) {
				$function_in_backtrace = true;
				break;
			}
		}

		return $function_in_backtrace;
	}

} // class

# --- EOF
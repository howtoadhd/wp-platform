<?php
/**
 * WordPress Coding Standard.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform;

/**
 * Class PlatformFactory
 *
 * @since   0.1.0
 *
 * @package HowToADHD\WPPlatform
 */
class Util {

	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param  string       $haystack The string to search in.
	 * @param  string|array $needles  The string or array of strings to search for.
	 *
	 * @return bool
	 */
	public static function starts_with( $haystack, $needles ) {
		foreach ( (array) $needles as $needle ) {
			if ( '' !== $needle && strpos( $haystack, $needle ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param  string       $haystack The string to search in.
	 * @param  string|array $needles  The string or array of strings to search for.
	 *
	 * @return bool
	 */
	public static function ends_with( $haystack, $needles ) {
		foreach ( (array) $needles as $needle ) {
			if ( substr( $haystack, - strlen( $needle ) ) === (string) $needle ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed $value Value to return.
	 *
	 * @return mixed
	 */
	public static function value( $value ) {
		return ( $value instanceof \Closure ) ? $value() : $value;
	}

	/**
	 * Gets the value of an environment variable. Supports boolean, empty and null.
	 *
	 * @param  string $key     Environment variable name.
	 * @param  mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public static function env( $key, $default = null ) {
		$value = getenv( $key );

		if ( false === $value ) {
			return self::value( $default );
		}

		switch ( strtolower( $value ) ) {
			case 'true':
			case '(true)':
				return true;

			case 'false':
			case '(false)':
				return false;

			case 'empty':
			case '(empty)':
				return '';

			case 'null':
			case '(null)':
				return null;
		}

		if ( self::starts_with( $value, '"' ) && self::ends_with( $value, '"' ) ) {
			return substr( $value, 1, - 1 );
		}

		return $value;
	}

}

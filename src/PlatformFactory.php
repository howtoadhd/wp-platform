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
final class PlatformFactory {

	/**
	 * Create and return an instance of the plugin.
	 *
	 * This always returns a shared instance.
	 *
	 * @return Platform Plugin instance.
	 */
	public static function create() {
		static $plugin = null;

		if ( null === $plugin ) {
			$plugin = new Platform();
		}

		return $plugin;
	}
}

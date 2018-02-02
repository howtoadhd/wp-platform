<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform;

/**
 * Interface Registerable
 *
 * An object that can be `register()`ed.
 *
 * @since   0.1.0
 *
 * @package HowToADHD\WPPlatform
 */
interface Registerable {

	/**
	 * Register the current Registerable.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register();
}

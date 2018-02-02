<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform\Service;

use HowToADHD\WPPlatform\Platform;
use HowToADHD\WPPlatform\Registerable;

/**
 * Abstract Class BaseService.
 *
 * A generic service.
 *
 * @since   0.1.0
 *
 * @package HowToADHD\WPPlatform
 */
abstract class Service implements Registerable {

	/**
	 * Path to the platform modules directory.
	 *
	 * @var string
	 */
	protected $modules_path;

	/**
	 * The platform instance
	 *
	 * @var Platform
	 */
	protected $platform;

	/**
	 * Instantiate a Service object.
	 *
	 * @param Platform $platform The platform instance.
	 */
	public function __construct( Platform $platform ) {
		$this->platform     = $platform;
		$this->modules_path = $platform->modules_path;
	}

	/**
	 * Check if the service is enabled.
	 *
	 * @return bool
	 */
	abstract public function is_enabled();
}

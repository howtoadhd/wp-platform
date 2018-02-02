<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform\Service;

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
	 * Instantiate a Service object.
	 *
	 * @param String $modules_path Path to the platform modules directory.
	 */
	public function __construct( string $modules_path ) {
		$this->modules_path = $modules_path;
	}
}

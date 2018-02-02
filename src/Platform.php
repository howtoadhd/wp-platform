<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform;

use HowToADHD\WPPlatform\Service\ObjectCache;
use HowToADHD\WPPlatform\Service\PageCache;
use HowToADHD\WPPlatform\Service\Service;
use HowToADHD\WPPlatform\Service\Database;

/**
 * Class Platform
 *
 * Main plugin controller class that hooks the plugin's functionality into the
 * WordPress request lifecycle.
 *
 * @since   0.1.0
 *
 * @package HowToADHD\WPPlatform
 */
final class Platform implements Registerable {

	/**
	 * Path to the platform root directory.
	 *
	 * @var string
	 */
	private $platform_path;

	/**
	 * Path to the platform modules directory.
	 *
	 * @var string
	 */
	private $modules_path;

	/**
	 * Instantiate a Platform object.
	 */
	public function __construct() {
		$this->platform_path = dirname( __DIR__ );
		$this->modules_path  = $this->platform_path . '/modules';
	}

	/**
	 * Register the platform with the WordPress system.
	 *
	 * @throws Exception\InvalidService If a service is not valid.
	 */
	public function register() {
		$GLOBALS['wp_filter']['enable_wp_debug_mode_checks'][10]['wp_platform'] = [ // WPCS: override ok.
			'function'      => [ $this, 'register_services' ],
			'accepted_args' => 1,
		];

		// This must be defined so we can toggle the cache via env var.
		define( 'WP_CACHE', true );
	}

	/**
	 * Register the individual services of this platform.
	 *
	 * @throws Exception\InvalidService If a service is not valid.
	 */
	public function register_services() {
		$services = $this->get_services();
		$services = array_map( [ $this, 'instantiate_service' ], $services );
		array_walk(
			$services, function ( Service $service ) {
				$service->register();
			}
		);
	}

	/**
	 * Instantiate a single service.
	 *
	 * @param string $class Service class to instantiate.
	 *
	 * @return Service
	 * @throws Exception\InvalidService If the service is not valid.
	 */
	private function instantiate_service( $class ) {
		if ( ! class_exists( $class ) ) {
			throw Exception\InvalidService::from_service( $class );
		}

		$service = new $class( $this->modules_path );

		if ( ! $service instanceof Service ) {
			throw Exception\InvalidService::from_service( $service );
		}

		return $service;
	}

	/**
	 * Get the list of services to register.
	 *
	 * @return array<string> Array of fully qualified class names.
	 */
	private function get_services() {
		return [
			Database::class,
			ObjectCache::class,
			PageCache::class,
		];
	}

}

<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform\Service;

use HowToADHD\WPPlatform\Util;

/**
 * Class ObjectCache
 *
 * @since 0.1.0
 *
 * @package HowToADHD\WPPlatform\Service
 */
class ObjectCache extends Service {

	/**
	 * Check if the service is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return Util::env( 'ENABLE_OBJECT_CACHE', true );
	}

	/**
	 * Register Memcached object cache if ENABLE_OBJECT_CACHE env var is defined.
	 */
	public function register() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->init();
	}

	/**
	 * Enable the object cache.
	 */
	public function init() {
		$GLOBALS['memcached_servers'] = $this->get_memcached_servers();

		if ( empty( $GLOBALS['memcached_servers'] ) ) {
			die( 'You have enabled the object cache but have not specified any servers.' );
		}

		wp_using_ext_object_cache( true );

		require $this->modules_path . '/object-cache/object-cache.php';

		// cache must be initialised once it's included, else we'll get a fatal.
		wp_cache_init();
	}


	/**
	 * Get array of memcached servers from MEMCACHED_SERVERS env var.
	 *
	 * @return array Memcached IP/Port mappings.
	 */
	public function get_memcached_servers() {
		$env     = Util::env( 'MEMCACHED_SERVERS' );
		$servers = [];

		foreach ( explode( ',', $env ) as $server ) {
			if ( empty( $server ) ) {
				continue;
			}

			$servers[] = explode( ':', $server, 2 );
		}

		return $servers;
	}
}

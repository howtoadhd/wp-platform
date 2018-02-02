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
 * Class PageCache
 *
 * @since 0.1.0
 *
 * @package HowToADHD\WPPlatform\Service
 */
class PageCache extends Service {

	/**
	 * Check if the service is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$object_cache = $this->platform->get_service( 'ObjectCache' );

		if ( ! $object_cache->is_enabled() ) {
			return false;
		}

		return Util::env( 'ENABLE_OBJECT_CACHE', true );
	}

	/**
	 * Register Batcache.
	 */
	public function register() {
		add_filter( 'enable_loading_advanced_cache_dropin', [ $this, 'maybe_init' ], 10, 1 );
	}

	/**
	 * Enable the page cache if ENABLE_OBJECT_CACHE and ENABLE_PAGE_CACHE env var is defined.
	 *
	 * @param bool $should_load Whether to enable loading.
	 *
	 * @return false Dont include wp-content/object-cache.php.
	 */
	public function maybe_init( $should_load ) {
		if ( ! $this->is_enabled() || ! $should_load ) {
			return false;
		}

		require $this->modules_path . '/batcache/advanced-cache.php';

		// Re-initialize any hooks added manually by advanced-cache.php.
		$GLOBALS['wp_filter'] = \WP_Hook::build_preinitialized_hooks( $GLOBALS['wp_filter'] ); // WPCS: override ok.

		return false;
	}
}

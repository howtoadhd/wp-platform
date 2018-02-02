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
	 * Register Batcache.
	 */
	public function register() {
		add_filter( 'enable_loading_advanced_cache_dropin', [ $this, 'maybe_enable_page_cache' ], 10, 1 );
	}

	/**
	 * Enable the page cache if ENABLE_OBJECT_CACHE and ENABLE_PAGE_CACHE env var is defined.
	 *
	 * @param bool $should_load Whether to enable loading.
	 *
	 * @return false Dont include wp-content/object-cache.php.
	 */
	public function maybe_enable_page_cache( $should_load ) {
		if ( ! Util::env( 'ENABLE_OBJECT_CACHE', true )
			|| ! Util::env( 'ENABLE_PAGE_CACHE', true )
			|| ! $should_load
		) {
			return false;
		}

		require $this->modules_path . '/batcache/advanced-cache.php';

		// Re-initialize any hooks added manually by advanced-cache.php.
		$GLOBALS['wp_filter'] = \WP_Hook::build_preinitialized_hooks( $GLOBALS['wp_filter'] ); // WPCS: override ok.

		return false;
	}
}

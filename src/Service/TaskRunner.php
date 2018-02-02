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
 * Class TaskRunner
 *
 * @since 0.1.0
 *
 * @package HowToADHD\WPPlatform\Service
 */
class TaskRunner extends Service {

	/**
	 * Get the name of this service.
	 *
	 * @return string Service name.
	 */
	public function get_service_name() {
		return __( 'Task Runner', 'wp-platform' );
	}

	/**
	 * Get the description of this service.
	 *
	 * @return string Service description.
	 */
	public function get_service_description() {
		return __( 'A better wp-cron. Horizontally scalable, works perfectly with multisite.', 'wp-platform' );
	}

	/**
	 * Check if the service is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return Util::env( 'ENABLE_TASK_RUNNER', true );
	}

	/**
	 * Register the current Registerable.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		define( 'DISABLE_WP_CRON', true );

		add_action( 'muplugins_loaded', [ $this, 'load' ] );
	}

	/**
	 * Load Cavalcade.
	 */
	public function load() {
		require $this->modules_path . '/cavalcade/plugin.php';
	}
}

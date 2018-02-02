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
 * Class SESMail
 *
 * @since 0.1.0
 *
 * @package HowToADHD\WPPlatform\Service
 */
class SESMail extends Service {

	/**
	 * Get the name of this service.
	 *
	 * @return string Service name.
	 */
	public function get_service_name() {
		return __( 'SES Mail', 'wp-platform' );
	}

	/**
	 * Get the description of this service.
	 *
	 * @return string Service description.
	 */
	public function get_service_description() {
		return __( 'Replacement for wp_mail that uses AWS SES.', 'wp-platform' );
	}

	/**
	 * Check if the service is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return Util::env( 'ENABLE_SES_MAIL', true );
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

		add_action( 'muplugins_loaded', [ $this, 'load' ] );
	}

	/**
	 * Load SES WP Mail.
	 */
	public function load() {
		$this->configure();

		add_filter( 'aws_ses_wp_mail_ses_client_params', [ $this, 'configure_ses_params' ] );

		require $this->modules_path . '/ses-wp-mail/aws-ses-wp-mail.php';
	}

	/**
	 * Set constants required by SES WP Mail
	 */
	private function configure() {
		$key = Util::env( 'SES_MAIL_KEY', 'mock' );
		if ( $key ) {
			define( 'AWS_SES_WP_MAIL_KEY', $key );
		}

		$secret = Util::env( 'SES_MAIL_SECRET', 'mock' );
		if ( $secret ) {
			define( 'AWS_SES_WP_MAIL_SECRET', $secret );
		}

		$region = Util::env( 'SES_MAIL_REGION', 'mock' );
		if ( $region ) {
			define( 'AWS_SES_WP_MAIL_REGION', $region );
		}
	}

	/**
	 * Configure the SES client.
	 *
	 * @param array $params SES client params.
	 *
	 * @return array SES client params.
	 */
	public function configure_ses_params( $params ) {
		$endpoint = Util::env( 'SES_MAIL_ENDPOINT', false );
		if ( $endpoint ) {
			$params['endpoint'] = $endpoint;
		}

		$params['http']['verify'] = ! (bool) Util::env( 'SES_MAIL_SKIP_TLS', false );

		return $params;
	}
}

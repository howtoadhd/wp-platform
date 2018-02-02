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
 * Class S3Uploads
 *
 * @since 0.1.0
 *
 * @package HowToADHD\WPPlatform\Service
 */
class S3Uploads extends Service {

	/**
	 * Get the name of this service.
	 *
	 * @return string Service name.
	 */
	public function get_service_name() {
		return __( 'S3 Uploads', 'wp-platform' );
	}

	/**
	 * Get the description of this service.
	 *
	 * @return string Service description.
	 */
	public function get_service_description() {
		return __( 'Store uploads in S3.', 'wp-platform' );
	}

	/**
	 * Check if the service is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return Util::env( 'ENABLE_S3_UPLOADS', true );
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
	 * Load S3 Uploads.
	 */
	public function load() {
		$this->configure();

		add_filter( 's3_uploads_s3_client_params', [ $this, 'configure_s3_params' ] );

		require $this->modules_path . '/s3-uploads/s3-uploads.php';
	}

	/**
	 * Set constants required by S3 Uploads
	 */
	private function configure() {
		$bucket = Util::env( 'S3_UPLOADS_BUCKET', false );
		if ( $bucket ) {
			define( 'S3_UPLOADS_BUCKET', $bucket );
		}

		$key = Util::env( 'S3_UPLOADS_KEY', false );
		if ( $key ) {
			define( 'S3_UPLOADS_KEY', $key );
		}

		$secret = Util::env( 'S3_UPLOADS_SECRET', false );
		if ( $secret ) {
			define( 'S3_UPLOADS_SECRET', $secret );
		}

		$region = Util::env( 'S3_UPLOADS_REGION', false );
		if ( $region ) {
			define( 'S3_UPLOADS_REGION', $region );
		}

		$bucket_url = Util::env( 'S3_UPLOADS_BUCKET_URL', false );
		if ( $bucket_url ) {
			define( 'S3_UPLOADS_BUCKET_URL', $bucket_url );
		}
	}

	/**
	 * Configure the S3 client.
	 *
	 * @param array $params S3 client params.
	 *
	 * @return array S3 client params.
	 */
	public function configure_s3_params( $params ) {

		$endpoint = Util::env( 'S3_UPLOADS_ENDPOINT', false );
		if ( $endpoint ) {
			$params['endpoint'] = $endpoint;
		}

		$params['path_style']     = (bool) Util::env( 'S3_UPLOADS_ENDPOINT_PATH_STYLE', false );
		$params['http']['verify'] = ! (bool) Util::env( 'S3_UPLOADS_SKIP_TLS', false );

		return $params;
	}
}

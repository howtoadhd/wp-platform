<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform\Exception;

/**
 * Class InvalidService.
 *
 * @since   0.1.0
 *
 * @package HowToADHD\WPPlatform\Exception
 */
class InvalidService extends \InvalidArgumentException implements WPPlatformException {

	/**
	 * Create a new instance of the exception for a service class name that is
	 * not recognized.
	 *
	 * @since 0.1.0
	 *
	 * @param string $service Class name of the service that was not recognized.
	 *
	 * @return static
	 */
	public static function from_service( $service ) {
		$message = sprintf(
			'The service "%s" is not recognized and cannot be registered.',
			is_object( $service ) ? get_class( $service ) : (string) $service
		);

		return new static( $message );
	}
}

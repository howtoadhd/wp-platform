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
 * Class DB
 *
 * @since 0.1.0
 *
 * @package HowToADHD\WPPlatform\Service
 */
class Database extends Service {

	/**
	 * Register LudicrousDB.
	 */
	public function register() {
		add_action( 'wp_platform_database_init', [ $this, 'init' ] );
	}

	/**
	 * Initialise LudicrousDB.
	 */
	public function init() {
		global $wpdb;

		// Bail if database object is already set.
		if ( isset( $wpdb ) ) {
			return;
		}

		// Required files.
		require $this->modules_path . '/ludicrousdb/ludicrousdb/includes/functions.php';
		require $this->modules_path . '/ludicrousdb/ludicrousdb/includes/class-ludicrousdb.php';

		// Set default constants.
		ldb_default_constants();

		// Create database object.
		$wpdb = new \LudicrousDB(); // WPCS: override ok.

		$this->configure( $wpdb );
	}

	/**
	 * Configure LudicrousDB.
	 *
	 * @param \LudicrousDB $wpdb The initialised DB object.
	 */
	public function configure( \LudicrousDB $wpdb ) {
		$wpdb->save_queries             = false;
		$wpdb->persistent               = false;
		$wpdb->max_connections          = 10;
		$wpdb->check_tcp_responsiveness = true;

		$master = $this->get_db_server( false );
		$slave  = $this->get_db_server( true );

		if ( empty( $slave ) ) {
			$slave          = $master;
			$slave['write'] = 0;
		}

		$wpdb->add_database( $master );
		$wpdb->add_database( $slave );
	}

	/**
	 * Get database server from env var.
	 *
	 * If $slave is false get master server.
	 * If $slave is true get slave server
	 *
	 * @param bool $slave Default false.
	 *
	 * @return array Database connection details.
	 */
	public function get_db_server( bool $slave = false ) {
		$env_var = ( $slave ) ? 'DB_SLAVE' : 'DB_MASTER';
		$env_val = Util::env( $env_var, false );

		if ( false === $env_val ) {
			return [];
		}

		if ( strpos( $env_val, '//' ) === false ) {
			$env_val = 'mysql://' . $env_val;
		}

		if ( ! Util::starts_with( $env_val, 'mysql://' ) ) {
			$env_val = "//${env_val}";
		}

		$db = parse_url( $env_val ); // phpcs:disable WordPress.WP.AlternativeFunctions

		return [
			'name'     => trim( $db['path'], '/' ),
			'user'     => $db['user'],
			'password' => $db['pass'],
			'host'     => $db['host'],
			'write'    => ( $slave ) ? 0 : 1,
			'read'     => 1,
			'dataset'  => 'global',
			'timeout'  => 0.2,
		];
	}
}

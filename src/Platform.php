<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform;

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
final class Platform {
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
	 *
	 * @param bool $skip_preflight Whether or not to run the pre-flight checks.
	 */
	public function __construct( bool $skip_preflight = false ) {
		if ( ! $skip_preflight ) {
			$this->preflight();
		}

		// Hacky workaround so we can filter advanced cache loading.
		define( 'WP_CACHE', true );

		$this->platform_path = dirname( __DIR__ );
		$this->modules_path  = $this->platform_path . '/modules';

		$GLOBALS['wp_filter']['enable_wp_debug_mode_checks'][10]['wp_platform'] = [ // WPCS: override ok.
			'function'      => [ $this, 'bootstrap' ],
			'accepted_args' => 1,
		];
	}

	/**
	 * Start the engine.
	 */
	public function bootstrap() {
		global $wp_version;

		if ( version_compare( '4.6', $wp_version, '>' ) ) {
			die( 'WP Platform is only supported on WordPress 4.6+.' );
		}

		$this->maybe_enable_object_cache();

		add_filter( 'enable_loading_advanced_cache_dropin', [ $this, 'maybe_enable_page_cache' ], 10, 1 );
	}

	/**
	 * Check the environment is configures correctly.
	 */
	public function preflight() {
		if ( defined( 'WP_CACHE' ) ) {
			die( 'WP Platform requires you to remove define( \'WP_CACHE\', &lt;value&gt; ); from wp-config.php.' );
		}

		if ( defined( 'DISABLE_WP_CRON' ) ) {
			die( 'WP Platform requires you to remove define( \'DISABLE_WP_CRON\', &lt;value&gt;); from wp-config.php.' );
		}
	}

	/**
	 * Enable the object cache if ENABLE_OBJECT_CACHE env var is defined.
	 */
	public function maybe_enable_object_cache() {
		if ( ! Util::env( 'ENABLE_OBJECT_CACHE', true ) ) {
			return;
		}

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

	/**
	 * Enable the replacement DB class.
	 */
	public function enable_db() {
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

		$db_class = apply_filters( 'wp_platform_db_class', 'LudicrousDB' );

		// Create database object.
		$wpdb = new $db_class(); // WPCS: override ok.

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

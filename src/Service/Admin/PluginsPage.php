<?php
/**
 * WP Platform.
 *
 * @package HowToADHD\WPPlatform
 * @link    https://github.com/howtoadhd/wp-platform
 * @license https://opensource.org/licenses/GPL-2.0 GPLv2+
 */

namespace HowToADHD\WPPlatform\Service\Admin;

use HowToADHD\WPPlatform\Service\Service;

/**
 * Class PluginsPage
 *
 * @since 0.1.0
 *
 * @package HowToADHD\WPPlatform\Service\Admin
 */
class PluginsPage extends Service {

	/**
	 * Get the name of this service.
	 *
	 * @return string Service name.
	 */
	public function get_service_name() {
		return __( 'Admin Plugin Page', 'wp-platform' );
	}

	/**
	 * Get the description of this service.
	 *
	 * @return string Service description.
	 */
	public function get_service_description() {
		return __( 'This page, such meta.', 'wp-platform' );
	}

	/**
	 * Check if the service is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return is_admin();
	}

	/**
	 * Register the current Registerable.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'manage_plugins_columns', [ $this, 'alter_columns' ] );
		add_filter( 'views_plugins', [ $this, 'add_platform_link' ] );
		add_action( 'pre_current_active_plugins', [ $this, 'add_plugins_to_table' ] );
		add_action( 'network_admin_plugin_action_links', [ $this, 'get_platform_actions' ], 10, 4 );
		add_action( 'plugin_action_links', [ $this, 'get_platform_actions' ], 10, 4 );
	}

	/**
	 * Alter list table columns for the platform page.
	 *
	 * @param array $columns Map of column ID => description.
	 *
	 * @return array Altered columns.
	 */
	public function alter_columns( $columns ) {
		global $status;
		if ( ! isset( $_REQUEST['plugin_status'] ) || 'platform' !== $_REQUEST['plugin_status'] ) { // WPCS: input var ok. WPCS: CSRF ok.
			return $columns;
		}
		// Remove the checkbox.
		unset( $columns['cb'] );

		return $columns;
	}

	/**
	 * Add platform link to the views.
	 *
	 * @param array $views Views for the list table.
	 *
	 * @return array Views with platform added.
	 */
	public function add_platform_link( $views ) {
		global $status;
		$views['platform'] = sprintf(
			"<a href='%s' %s> %s</a>",
			add_query_arg( 'plugin_status', 'platform', 'plugins.php' ),
			( 'platform' === $status ) ? ' class="current"' : '',
			__( 'Platform', 'wp-platform' )
		);

		return $views;
	}

	/**
	 * Add plugin data to the plugin list table.
	 */
	public function add_plugins_to_table() {
		global $plugins, $wp_list_table;

		$plugins['platform'] = []; // WPCS: override ok.

		// Add our own mu-plugins to the page.
		foreach ( $this->platform->get_services() as $name => $service ) {
			$plugins['platform'][ $name ] = [ // WPCS: override ok.
				'Name'        => $service->get_service_name(),
				'Description' => $service->get_service_description(),
				'TextDomain'  => '',
				'Author'      => '',
				'Version'     => '',
				'PluginURI'   => '',
				'AuthorURI'   => '',
			];
		}

		// Recount totals.
		$GLOBALS['totals']['platform'] = count( $plugins['platform'] ); // WPCS: override ok.

		// Only apply the rest if we're actually looking at the page.
		if ( ! isset( $_REQUEST['plugin_status'] ) || 'platform' !== $_REQUEST['plugin_status'] ) { // WPCS: input var ok. WPCS: CSRF ok.
			return;
		}

		// Reset the global.
		$GLOBALS['status'] = 'platform'; // WPCS: override ok.

		// Reset the list table's data.
		$wp_list_table->items = $plugins['platform'];
		foreach ( $wp_list_table->items as $plugin_file => $plugin_data ) {
			$wp_list_table->items[ $plugin_file ] = _get_plugin_data_markup_translate(
				$plugin_file, $plugin_data,
				false, true
			);
		}

		$total_this_page = $GLOBALS['totals']['platform'];

		if ( $GLOBALS['orderby'] ) {
			uasort( $wp_list_table->items, [ $wp_list_table, '_order_callback' ] );
		}

		// Force showing all plugins.
		// See https://core.trac.wordpress.org/ticket/27110.
		$plugins_per_page = $total_this_page;

		$wp_list_table->set_pagination_args(
			[
				'total_items' => $total_this_page,
				'per_page'    => $plugins_per_page,
			]
		);
	}

	/**
	 * Get platform plugin actions.
	 *
	 * @param array  $actions Existing actions for the row.
	 * @param string $plugin_file Filename for the plugin.
	 * @param array  $plugin_data Headers from the plugin file.
	 * @param string $context Current subpage of the plugin page.
	 *
	 * @return array Altered actions.
	 */
	public function get_platform_actions( $actions, $plugin_file, $plugin_data, $context ) {
		if ( 'platform' !== $context ) {
			return $actions;
		}

		$actions = [];

		if ( $this->platform->get_service( $plugin_file )->is_enabled() ) {
			$actions[] = '<span style="color:#46b450">' . __( 'Active', 'wp-platform' ) . '</span>';
		} else {
			$actions[] = '<span style="color:#dc3232">' . __( 'Inactive', 'wp-platform' ) . '</span>';

		}

		return $actions;
	}
}

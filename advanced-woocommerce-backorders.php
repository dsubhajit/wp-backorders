<?php
/**
 * Plugin Name:       Advanced WooCommerce Backorders & Lead Times
 * Plugin URI:        https://wordpress.org/plugins/advanced-woocommerce-backorders/
 * Description:       Advanced management of WooCommerce backorders, custom lead times, multi-tier rule prioritization, and clear customer messaging across catalog, cart, checkout, emails, and order history.
 * Version:           1.0.0
 * Author:            Subhajit Dey
 * Author URI:        https://github.com/dsubhajit/
 * Text Domain:       advanced-woocommerce-backorders
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * WC requires at least: 5.0
 * WC tested up to:   9.3
 *
 * @package           AWBL
 */

defined( 'ABSPATH' ) || exit;

// Define Plugin Constants
define( 'AWBL_VERSION', '1.0.0' );
define( 'AWBL_PLUGIN_FILE', __FILE__ );
define( 'AWBL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AWBL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AWBL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare WooCommerce HPOS (High-Performance Order Storage) compatibility.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Check if WooCommerce is installed and active before running the plugin.
 */
function awbl_check_woocommerce_dependency() {
	$is_active = false;

	// Standard check
	if ( class_exists( 'WooCommerce' ) ) {
		$is_active = true;
	} else {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
		if ( is_multisite() ) {
			$active_sitewide = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
			$active_plugins  = array_merge( $active_plugins, $active_sitewide );
		}
		if ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
			$is_active = true;
		}
	}

	return $is_active;
}

/**
 * Display admin warning notice if WooCommerce is inactive.
 */
function awbl_missing_woocommerce_notice() {
	if ( current_user_can( 'activate_plugins' ) ) {
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Advanced WooCommerce Backorders & Lead Times', 'advanced-woocommerce-backorders' ); ?></strong>:
				<?php esc_html_e( 'This plugin requires WooCommerce to be installed and active.', 'advanced-woocommerce-backorders' ); ?>
			</p>
		</div>
		<?php
	}
}

/**
 * Plugin activation routine.
 */
function awbl_activate_plugin() {
	require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-settings.php';

	// Populate defaults if not yet present without overwriting existing WC product data
	$defaults = AWBL_Settings::get_defaults();
	foreach ( $defaults as $key => $default_val ) {
		if ( false === get_option( $key ) ) {
			add_option( $key, $default_val );
		}
	}
}
register_activation_hook( __FILE__, 'awbl_activate_plugin' );

/**
 * Plugin deactivation routine.
 */
function awbl_deactivate_plugin() {
	// Do not delete any options or product inventory data on plain deactivation
}
register_deactivation_hook( __FILE__, 'awbl_deactivate_plugin' );

/**
 * Bootstrap the plugin after all plugins are loaded.
 */
function awbl_init_plugin() {
	if ( ! awbl_check_woocommerce_dependency() ) {
		add_action( 'admin_notices', 'awbl_missing_woocommerce_notice' );
		return;
	}

	// Load core plugin orchestrator
	require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-plugin.php';
	AWBL_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'awbl_init_plugin' );

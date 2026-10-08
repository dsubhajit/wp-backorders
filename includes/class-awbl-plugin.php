<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Main Core Orchestrator
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Plugin Class
 */
class AWBL_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var AWBL_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get main class instance.
	 *
	 * @return AWBL_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include all required module classes.
	 */
	private function includes() {
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-logger.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-settings.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-category-rules.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-backorders.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-lead-time.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-product.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-cart.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-checkout.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-emails.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-ajax.php';
		require_once AWBL_PLUGIN_DIR . 'includes/class-awbl-admin.php';
	}

	/**
	 * Initialize hooks across WordPress and WooCommerce.
	 */
	private function init_hooks() {
		// Internationalization
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Initialize subsystems
		AWBL_Backorders::init();
		AWBL_Product::init();
		AWBL_Cart::init();
		AWBL_Checkout::init();
		AWBL_Emails::init();
		AWBL_Ajax::init();
		AWBL_Admin::init();

		// Enqueue public frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		add_action( 'wp_head', array( $this, 'output_frontend_custom_styles' ), 100 );
	}

	/**
	 * Load plugin textdomain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'advanced-woocommerce-backorders',
			false,
			dirname( plugin_basename( AWBL_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Enqueue frontend CSS and JavaScript.
	 */
	public function enqueue_public_assets() {
		if ( ! AWBL_Settings::is_plugin_enabled() || is_admin() || is_feed() ) {
			return;
		}

		// Frontend CSS
		wp_enqueue_style(
			'awbl-public-css',
			AWBL_PLUGIN_URL . 'public/css/awbl-public.css',
			array(),
			AWBL_VERSION . '.' . time() // Avoid browser cache stale during active tweaking
		);

		// Frontend JS (for Variable products live switching)
		wp_enqueue_script(
			'awbl-public-js',
			AWBL_PLUGIN_URL . 'public/js/awbl-public.js',
			array( 'jquery' ),
			AWBL_VERSION,
			true
		);
	}

	/**
	 * Output dynamic custom colors and custom CSS in wp_head.
	 */
	public function output_frontend_custom_styles() {
		if ( ! AWBL_Settings::is_plugin_enabled() || is_admin() ) {
			return;
		}

		$bg_color     = AWBL_Settings::get( 'awbl_badge_bg_color', '#fff8e5' );
		$text_color   = AWBL_Settings::get( 'awbl_badge_text_color', '#8a6d3b' );
		$border_color = AWBL_Settings::get( 'awbl_badge_border_color', '#faebcc' );
		$lead_color   = AWBL_Settings::get( 'awbl_lead_time_color', '#2d3748' );
		$custom_css   = AWBL_Settings::get( 'awbl_custom_css', '' );

		echo '<style id="awbl-dynamic-styles">' . "\n";
		echo ':root {' . "\n";
		echo '  --awbl-badge-bg: ' . esc_attr( $bg_color ) . ';' . "\n";
		echo '  --awbl-badge-color: ' . esc_attr( $text_color ) . ';' . "\n";
		echo '  --awbl-badge-border: ' . esc_attr( $border_color ) . ';' . "\n";
		echo '  --awbl-lead-time-color: ' . esc_attr( $lead_color ) . ';' . "\n";
		echo '}' . "\n";
		echo 'p.stock.available-on-backorder, .woocommerce p.stock.available-on-backorder, .available-on-backorder:not(.awbl-clean-child) {' . "\n";
		echo '  display: inline-flex !important;' . "\n";
		echo '  align-items: center !important;' . "\n";
		echo '  gap: 8px !important;' . "\n";
		echo '  padding: 10px 16px !important;' . "\n";
		echo '  background-color: ' . esc_attr( $bg_color ) . ' !important;' . "\n";
		echo '  color: ' . esc_attr( $text_color ) . ' !important;' . "\n";
		echo '  border: 1px solid ' . esc_attr( $border_color ) . ' !important;' . "\n";
		echo '  border-left: 4px solid #f59e0b !important;' . "\n";
		echo '  border-radius: 6px !important;' . "\n";
		echo '  font-weight: 600 !important;' . "\n";
		echo '  font-size: 14px !important;' . "\n";
		echo '  margin: 12px 0 16px 0 !important;' . "\n";
		echo '  box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;' . "\n";
		echo '}' . "\n";

		if ( ! empty( $custom_css ) ) {
			echo wp_strip_all_tags( $custom_css ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</style>' . "\n";
	}
}

<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Admin Controller
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Admin Class
 */
class AWBL_Admin {

	/**
	 * Hook suffix for plugin admin page.
	 *
	 * @var string
	 */
	private static $hook_suffix = '';

	/**
	 * Initialize admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ), 60 );
		add_action( 'admin_init', array( __CLASS__, 'handle_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register submenu under WooCommerce: WooCommerce → Backorders & Lead Times.
	 */
	public static function register_admin_menu() {
		self::$hook_suffix = add_submenu_page(
			'woocommerce',
			__( 'Backorders & Lead Times', 'advanced-woocommerce-backorders' ),
			__( 'Backorders & Lead Times', 'advanced-woocommerce-backorders' ),
			'manage_woocommerce',
			'awbl-settings',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin scripts & styles only on relevant admin screens.
	 *
	 * @param string $hook Page hook suffix.
	 */
	public static function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();

		$is_awbl_page    = ( $hook === self::$hook_suffix || ( isset( $_GET['page'] ) && 'awbl-settings' === $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_product_page = ( $screen && 'product' === $screen->post_type );

		if ( ! $is_awbl_page && ! $is_product_page ) {
			return;
		}

		// CSS
		wp_enqueue_style(
			'awbl-admin-css',
			AWBL_PLUGIN_URL . 'admin/css/awbl-admin.css',
			array(),
			AWBL_VERSION
		);

		// JS
		wp_enqueue_script(
			'awbl-admin-js',
			AWBL_PLUGIN_URL . 'admin/js/awbl-admin.js',
			array( 'jquery', 'wp-util' ),
			AWBL_VERSION,
			true
		);

		// Localize script with AJAX URL, Nonce, and i18n
		wp_localize_script(
			'awbl-admin-js',
			'AWBL_Admin_Params',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( AWBL_Ajax::NONCE_ACTION ),
				'i18n'       => array(
					'confirm_reset'  => __( 'Are you sure you want to reset all plugin settings to defaults? This cannot be undone.', 'advanced-woocommerce-backorders' ),
					'confirm_delete' => __( 'Are you sure you want to delete this category rule?', 'advanced-woocommerce-backorders' ),
					'confirm_clear'  => __( 'Are you sure you want to clear the debug log?', 'advanced-woocommerce-backorders' ),
					'rule_saved'     => __( 'Rule saved successfully.', 'advanced-woocommerce-backorders' ),
					'error'          => __( 'An error occurred. Please try again.', 'advanced-woocommerce-backorders' ),
				),
			)
		);
	}

	/**
	 * Handle manual POST save for settings tabs with nonce verification.
	 */
	public static function handle_save_settings() {
		if ( ! isset( $_POST['awbl_save_settings_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['awbl_save_settings_nonce'] ) ), 'awbl_save_settings_action' ) ) {
			wp_die( esc_html__( 'Security check failed. Please refresh and try again.', 'advanced-woocommerce-backorders' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Unauthorized user.', 'advanced-woocommerce-backorders' ) );
		}

		$active_tab = isset( $_POST['awbl_current_tab'] ) ? sanitize_key( wp_unslash( $_POST['awbl_current_tab'] ) ) : 'general';

		// All configurable options mapped to their types
		$checkbox_keys = array(
			'awbl_enable_plugin',
			'awbl_enable_management',
			'awbl_global_override',
			'awbl_category_overrides_global',
			'awbl_treat_zero_stock_as_backorder',
			'awbl_hide_add_to_cart_if_no_backorder',
			'awbl_display_lead_time_single',
			'awbl_display_lead_time_cart',
			'awbl_display_lead_time_checkout',
			'awbl_display_lead_time_archive',
			'awbl_display_lead_time_order',
			'awbl_display_lead_time_email',
			'awbl_lead_time_for_instock',
			'awbl_lead_time_for_outofstock',
			'awbl_lead_time_for_backorder',
			'awbl_enable_stock_status_lead_times',
			'awbl_show_lead_time_icon',
			'awbl_email_customer_include',
			'awbl_email_admin_include',
			'awbl_enable_debug_log',
			'awbl_delete_data_on_uninstall',
		);

		// Process checkboxes (if present in tab form, checked = yes, missing = no)
		foreach ( $checkbox_keys as $key ) {
			if ( isset( $_POST[ 'field_present_' . $key ] ) ) {
				$val = isset( $_POST[ $key ] ) ? 'yes' : 'no';
				update_option( $key, $val );
			}
		}

		// Text and select options
		$text_keys = array(
			'awbl_global_backorder_behaviour',
			'awbl_global_default_lead_time',
			'awbl_lead_time_instock',
			'awbl_lead_time_lowstock',
			'awbl_lead_time_outofstock',
			'awbl_lead_time_backorder',
			'awbl_single_product_placement',
			'awbl_single_product_hook_priority',
			'awbl_archive_placement',
			'awbl_archive_hook_priority',
			'awbl_cart_placement',
			'awbl_badge_style',
			'awbl_badge_bg_color',
			'awbl_badge_text_color',
			'awbl_badge_border_color',
			'awbl_lead_time_color',
			'awbl_msg_product',
			'awbl_msg_cart',
			'awbl_msg_checkout',
			'awbl_msg_checkout_item',
			'awbl_msg_order',
			'awbl_msg_email',
			'awbl_lead_time_prefix',
		);

		foreach ( $text_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}

		// Custom CSS
		if ( isset( $_POST['awbl_custom_css'] ) ) {
			update_option( 'awbl_custom_css', wp_strip_all_tags( wp_unslash( $_POST['awbl_custom_css'] ) ) );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'awbl-settings',
					'tab'     => $active_tab,
					'updated' => 'true',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render the main plugin admin interface.
	 */
	public static function render_admin_page() {
		$tabs = array(
			'general'       => __( '1. General', 'advanced-woocommerce-backorders' ),
			'backorders'    => __( '2. Backorders', 'advanced-woocommerce-backorders' ),
			'lead_times'    => __( '3. Lead Times', 'advanced-woocommerce-backorders' ),
			'display'       => __( '4. Display', 'advanced-woocommerce-backorders' ),
			'product_rules' => __( '5. Product Rules', 'advanced-woocommerce-backorders' ),
			'cat_rules'     => __( '6. Category Rules', 'advanced-woocommerce-backorders' ),
			'messages'      => __( '7. Messages', 'advanced-woocommerce-backorders' ),
			'styling'       => __( '8. Styling', 'advanced-woocommerce-backorders' ),
			'tools'         => __( '9. Tools', 'advanced-woocommerce-backorders' ),
			'status'        => __( '10. System Status', 'advanced-woocommerce-backorders' ),
		);

		$current_tab = isset( $_GET['tab'] ) && array_key_exists( sanitize_key( wp_unslash( $_GET['tab'] ) ), $tabs ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';

		include AWBL_PLUGIN_DIR . 'admin/views/admin-main.php';
	}

	/**
	 * Compute dashboard summary counters.
	 *
	 * @return array
	 */
	public static function get_dashboard_counts() {
		global $wpdb;

		// 1. Total Products on Backorder
		// Stock status 'onbackorder' OR products with 0 stock that allow backorders
		$backorder_count = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID) 
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_status ON (p.ID = pm_status.post_id AND pm_status.meta_key = '_stock_status' AND pm_status.meta_value = 'onbackorder')
			 WHERE p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'"
		);

		// 2. Total Products Allowing Backorders
		$allowed_backorders = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID) 
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_bo ON (p.ID = pm_bo.post_id AND pm_bo.meta_key = '_backorders' AND pm_bo.meta_value IN ('yes', 'notify'))
			 WHERE p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'"
		);

		// 3. Products with Lead Times Configured
		$with_lead_time = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID) 
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_lt ON (p.ID = pm_lt.post_id AND pm_lt.meta_key = '_awbl_lead_time' AND pm_lt.meta_value != '')
			 WHERE p.post_type IN ('product', 'product_variation') AND p.post_status = 'publish'"
		);

		// 4. Categories with Backorder Rules
		$rules = AWBL_Category_Rules::get_all( false );
		$cat_rules_count = count( $rules );

		return array(
			'on_backorder'       => $backorder_count,
			'allowed_backorders' => $allowed_backorders,
			'with_lead_time'     => $with_lead_time,
			'category_rules'     => $cat_rules_count,
		);
	}
}

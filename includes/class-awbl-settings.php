<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Settings Class
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Settings Class
 */
class AWBL_Settings {

	/**
	 * Settings group name.
	 *
	 * @var string
	 */
	const OPTION_GROUP = 'awbl_settings_group';

	/**
	 * All registered plugin options with default values.
	 *
	 * @var array
	 */
	private static $defaults = array(
		// Tab 1: General
		'awbl_enable_plugin'                  => 'yes',
		'awbl_enable_management'              => 'no',
		'awbl_global_backorder_behaviour'     => 'wc', // 'wc', 'no', 'notify', 'yes'
		'awbl_global_override'                => 'no',

		// Tab 2: Backorders & Priority
		'awbl_category_overrides_global'      => 'yes',
		'awbl_treat_zero_stock_as_backorder'  => 'yes',
		'awbl_hide_add_to_cart_if_no_backorder' => 'no',

		// Tab 3: Lead Times
		'awbl_display_lead_time_single'       => 'yes',
		'awbl_display_lead_time_cart'         => 'yes',
		'awbl_display_lead_time_checkout'     => 'yes',
		'awbl_display_lead_time_archive'      => 'yes',
		'awbl_display_lead_time_order'        => 'yes',
		'awbl_display_lead_time_email'        => 'yes',

		'awbl_lead_time_for_instock'          => 'no',
		'awbl_lead_time_for_outofstock'       => 'yes',
		'awbl_lead_time_for_backorder'        => 'yes',

		'awbl_enable_stock_status_lead_times' => 'no',
		'awbl_lead_time_instock'              => 'Ships within 1-2 business days',
		'awbl_lead_time_lowstock'             => 'Ships within 2-3 business days',
		'awbl_lead_time_outofstock'           => 'Ships within 2-3 weeks',
		'awbl_lead_time_backorder'            => 'Available on backorder — ships within 2-3 weeks',

		'awbl_global_default_lead_time'       => '2-3 weeks',

		// Tab 4: Display & Placements
		'awbl_single_product_placement'       => 'stock_notice', // stock_notice, after_price, before_price, before_add_to_cart, after_add_to_cart, after_short_description
		'awbl_single_product_hook_priority'   => 12,
		'awbl_archive_placement'              => 'after_title', // after_title, after_item
		'awbl_archive_hook_priority'          => 10,
		'awbl_cart_placement'                 => 'after_title', // after_title, below_quantity
		'awbl_badge_style'                    => 'notice', // plain, notice, badge, custom
		'awbl_show_lead_time_icon'            => 'yes',

		// Tab 5 & 6 are Product Rules & Category Rules (Category Rules stored in awbl_category_rules)
		'awbl_category_rules'                 => array(),

		// Tab 7: Messages & Placeholders
		'awbl_msg_product'                    => 'Available on backorder',
		'awbl_msg_cart'                       => 'Available on backorder',
		'awbl_msg_checkout'                   => 'Your order contains an item available on backorder: {product_name}',
		'awbl_msg_checkout_item'              => 'Available on backorder',
		'awbl_msg_order'                      => 'Item was ordered on backorder',
		'awbl_msg_email'                      => 'Available on backorder — estimated dispatch in {lead_time}',
		'awbl_lead_time_prefix'               => 'Estimated dispatch: ',

		// Tab 8: Styling & Custom CSS
		'awbl_badge_bg_color'                 => '#fff8e5',
		'awbl_badge_text_color'               => '#8a6d3b',
		'awbl_badge_border_color'             => '#faebcc',
		'awbl_lead_time_color'                => '#2d3748',
		'awbl_custom_css'                     => '',

		// Tab 9 & 10: Tools & System
		'awbl_enable_debug_log'               => 'no',
		'awbl_delete_data_on_uninstall'       => 'no',
		'awbl_email_customer_include'         => 'yes',
		'awbl_email_admin_include'            => 'yes',
	);

	/**
	 * Get option value with fallback.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value if not set.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$fallback = ( null !== $default ) ? $default : ( isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : '' );
		return get_option( $key, $fallback );
	}

	/**
	 * Set option value.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Value to set.
	 * @return bool
	 */
	public static function set( $key, $value ) {
		return update_option( $key, $value );
	}

	/**
	 * Get all defaults.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return self::$defaults;
	}

	/**
	 * Register settings with WordPress.
	 */
	public static function register_settings() {
		foreach ( self::$defaults as $key => $default_val ) {
			register_setting(
				self::OPTION_GROUP,
				$key,
				array(
					'sanitize_callback' => array( __CLASS__, 'sanitize_field' ),
					'default'           => $default_val,
				)
			);
		}
	}

	/**
	 * Sanitization callback router.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return mixed
	 */
	public static function sanitize_field( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( __CLASS__, 'sanitize_field' ), $value );
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Check if plugin is globally enabled.
	 *
	 * @return bool
	 */
	public static function is_plugin_enabled() {
		return 'yes' === self::get( 'awbl_enable_plugin', 'yes' );
	}

	/**
	 * Check if backorder management feature is enabled.
	 *
	 * @return bool
	 */
	public static function is_management_enabled() {
		return self::is_plugin_enabled() && ( 'yes' === self::get( 'awbl_enable_management', 'no' ) );
	}

	/**
	 * Check if global override is enabled.
	 *
	 * @return bool
	 */
	public static function is_global_override() {
		return self::is_management_enabled() && ( 'yes' === self::get( 'awbl_global_override', 'no' ) );
	}

	/**
	 * Export all settings as JSON array.
	 *
	 * @return array
	 */
	public static function export_settings() {
		$exported = array(
			'version'   => AWBL_VERSION,
			'timestamp' => time(),
			'settings'  => array(),
		);

		foreach ( array_keys( self::$defaults ) as $key ) {
			$exported['settings'][ $key ] = get_option( $key, self::$defaults[ $key ] );
		}

		return $exported;
	}

	/**
	 * Import settings from array.
	 *
	 * @param array $imported Imported settings data.
	 * @return bool|WP_Error
	 */
	public static function import_settings( $imported ) {
		if ( ! is_array( $imported ) || empty( $imported['settings'] ) || ! is_array( $imported['settings'] ) ) {
			return new WP_Error( 'invalid_data', __( 'Invalid or corrupted settings format.', 'advanced-woocommerce-backorders' ) );
		}

		foreach ( $imported['settings'] as $key => $val ) {
			if ( array_key_exists( $key, self::$defaults ) ) {
				if ( 'awbl_custom_css' === $key ) {
					update_option( $key, wp_strip_all_tags( $val ) );
				} elseif ( 'awbl_category_rules' === $key && is_array( $val ) ) {
					update_option( $key, self::sanitize_category_rules( $val ) );
				} elseif ( is_array( $val ) ) {
					update_option( $key, array_map( 'sanitize_text_field', $val ) );
				} else {
					update_option( $key, sanitize_text_field( $val ) );
				}
			}
		}

		return true;
	}

	/**
	 * Sanitize category rules data structure.
	 *
	 * @param array $rules Category rules.
	 * @return array
	 */
	public static function sanitize_category_rules( $rules ) {
		if ( ! is_array( $rules ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $rules as $id => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$sanitized[ sanitize_key( $id ) ] = array(
				'id'          => sanitize_key( isset( $rule['id'] ) ? $rule['id'] : $id ),
				'category_id' => absint( isset( $rule['category_id'] ) ? $rule['category_id'] : 0 ),
				'backorders'  => sanitize_text_field( isset( $rule['backorders'] ) ? $rule['backorders'] : 'wc' ),
				'lead_time'   => sanitize_text_field( isset( $rule['lead_time'] ) ? $rule['lead_time'] : '' ),
				'message'     => sanitize_text_field( isset( $rule['message'] ) ? $rule['message'] : '' ),
				'status'      => ( isset( $rule['status'] ) && 'inactive' === $rule['status'] ) ? 'inactive' : 'active',
				'priority'    => isset( $rule['priority'] ) ? intval( $rule['priority'] ) : 10,
			);
		}
		return $sanitized;
	}

	/**
	 * Reset all plugin settings to defaults.
	 */
	public static function reset_defaults() {
		foreach ( self::$defaults as $key => $default_val ) {
			update_option( $key, $default_val );
		}
	}
}

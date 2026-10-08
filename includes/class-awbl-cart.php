<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Cart Integration
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Cart Class
 */
class AWBL_Cart {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		if ( ! AWBL_Settings::is_plugin_enabled() ) {
			return;
		}

		$placement = AWBL_Settings::get( 'awbl_cart_placement', 'after_title' );
		if ( 'below_quantity' === $placement ) {
			add_filter( 'woocommerce_cart_item_quantity', array( __CLASS__, 'render_cart_item_notice_below_qty' ), 10, 3 );
		} else {
			add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'render_cart_item_notice_after_title' ), 10, 3 );
		}

		// Also attach lead time info to cart item data
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'display_cart_item_custom_data' ), 10, 2 );
	}

	/**
	 * Append notice after cart item title.
	 *
	 * @param string $item_name Cart item name HTML.
	 * @param array  $cart_item Cart item array.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public static function render_cart_item_notice_after_title( $item_name, $cart_item, $cart_item_key ) {
		if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
			return $item_name;
		}

		$product = $cart_item['data'];
		$qty     = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;

		if ( self::is_cart_item_on_backorder( $cart_item ) ) {
			$html = self::build_cart_badge_html( $product, $qty );
			return $item_name . $html;
		}

		return $item_name;
	}

	/**
	 * Append notice below cart item quantity.
	 *
	 * @param string $quantity_html Quantity HTML.
	 * @param string $cart_item_key Cart item key.
	 * @param array  $cart_item     Cart item array.
	 * @return string
	 */
	public static function render_cart_item_notice_below_qty( $quantity_html, $cart_item_key, $cart_item ) {
		if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
			return $quantity_html;
		}

		$product = $cart_item['data'];
		$qty     = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;

		if ( self::is_cart_item_on_backorder( $cart_item ) ) {
			$html = self::build_cart_badge_html( $product, $qty );
			return $quantity_html . '<div class="awbl-cart-qty-notice">' . $html . '</div>';
		}

		return $quantity_html;
	}

	/**
	 * Check if a cart item is currently on backorder (either fully or partially).
	 *
	 * @param array $cart_item Cart item array.
	 * @return bool
	 */
	public static function is_cart_item_on_backorder( $cart_item ) {
		$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		$qty = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;

		// If WooCommerce natively flags backorders allowed
		if ( $product->backorders_allowed() ) {
			if ( $product->is_on_backorder( $qty ) ) {
				return true;
			}
			if ( $product->managing_stock() ) {
				$stock = $product->get_stock_quantity();
				if ( null !== $stock && $stock < $qty ) {
					return true;
				}
			}
			if ( 'onbackorder' === $product->get_stock_status() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build badge HTML for cart item.
	 *
	 * @param WC_Product $product Product instance.
	 * @param int        $qty     Quantity.
	 * @return string
	 */
	private static function build_cart_badge_html( $product, $qty = 1 ) {
		$msg       = AWBL_Lead_Time::get_backorder_message( $product, 'cart', $qty );
		$lead_time = AWBL_Lead_Time::get_lead_time( $product );
		$prefix    = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
		$show_lead = AWBL_Lead_Time::is_enabled_for_context( 'cart' ) && AWBL_Lead_Time::should_display_for_stock_status( $product );

		$output  = '<div class="awbl-cart-item-backorder">';
		$output .= '<span class="awbl-cart-pill available-on-backorder">' . esc_html( $msg ) . '</span>';

		if ( $show_lead && ! empty( $lead_time ) ) {
			$output .= '<div class="awbl-cart-lead-time"><span class="awbl-icon">🚚</span> ' . esc_html( $prefix . $lead_time ) . '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Add custom lead time metadata to cart item.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param int   $product_id     Product ID.
	 * @param int   $variation_id   Variation ID.
	 * @return array
	 */
	public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		$check_id = ! empty( $variation_id ) ? $variation_id : $product_id;
		$product  = wc_get_product( $check_id );

		if ( $product ) {
			$lead_time = AWBL_Lead_Time::get_lead_time( $product );
			if ( ! empty( $lead_time ) ) {
				$cart_item_data['awbl_lead_time'] = $lead_time;
			}
		}

		return $cart_item_data;
	}

	/**
	 * Display custom cart item data in cart summary list.
	 *
	 * @param array $item_data Existing item data.
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public static function display_cart_item_custom_data( $item_data, $cart_item ) {
		// Only display as standard WC meta if cart lead time is enabled and item is on backorder
		if ( ! empty( $cart_item['awbl_lead_time'] ) && self::is_cart_item_on_backorder( $cart_item ) && AWBL_Lead_Time::is_enabled_for_context( 'cart' ) ) {
			// Avoid duplicate display if already printed after title
			$placement = AWBL_Settings::get( 'awbl_cart_placement', 'after_title' );
			if ( 'below_quantity' === $placement ) {
				$item_data[] = array(
					'key'   => __( 'Lead Time', 'advanced-woocommerce-backorders' ),
					'value' => esc_html( $cart_item['awbl_lead_time'] ),
				);
			}
		}
		return $item_data;
	}
}

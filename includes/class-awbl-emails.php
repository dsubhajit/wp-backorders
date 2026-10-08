<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Emails & Order Details
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Emails Class
 */
class AWBL_Emails {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		if ( ! AWBL_Settings::is_plugin_enabled() ) {
			return;
		}

		// Emails: Display backorder & lead time in order items
		add_action( 'woocommerce_order_item_meta_end', array( __CLASS__, 'render_email_item_meta' ), 10, 4 );

		// Customer Order Details (Thank You page & My Account View Order)
		if ( 'yes' === AWBL_Settings::get( 'awbl_display_lead_time_order', 'yes' ) ) {
			add_action( 'woocommerce_order_item_meta_end', array( __CLASS__, 'render_order_details_item_meta' ), 10, 3 );
		}

		// Admin Order Edit Screen: show in item meta
		add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'render_admin_order_item_meta' ), 10, 3 );
	}

	/**
	 * Render backorder & lead time info in WooCommerce transactional emails.
	 *
	 * @param int                   $item_id Order item ID.
	 * @param WC_Order_Item_Product $item    Order item object.
	 * @param WC_Order              $order   Order object.
	 * @param bool                  $plain_text Plain text or HTML email.
	 */
	public static function render_email_item_meta( $item_id, $item, $order, $plain_text = false ) {
		// Only run during email sending
		if ( ! did_action( 'woocommerce_email_header' ) ) {
			return;
		}

		if ( 'yes' !== AWBL_Settings::get( 'awbl_display_lead_time_email', 'yes' ) ) {
			return;
		}

		// Check recipient preference
		global $current_filter;
		$is_customer_enabled = 'yes' === AWBL_Settings::get( 'awbl_email_customer_include', 'yes' );
		$is_admin_enabled    = 'yes' === AWBL_Settings::get( 'awbl_email_admin_include', 'yes' );

		// Retrieve stored order item meta or fallback to product
		$is_backorder = $item->get_meta( '_awbl_is_backorder' );
		$lead_time    = $item->get_meta( '_awbl_lead_time' );

		// If historical meta not saved, check product directly
		if ( empty( $is_backorder ) ) {
			$product = $item->get_product();
			if ( $product ) {
				$is_backorder = AWBL_Backorders::is_product_on_backorder( $product ) ? 'yes' : 'no';
				if ( empty( $lead_time ) ) {
					$lead_time = AWBL_Lead_Time::get_lead_time( $product );
				}
			}
		}

		if ( 'yes' !== $is_backorder && empty( $lead_time ) ) {
			return;
		}

		$prefix  = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
		$msg     = AWBL_Settings::get( 'awbl_msg_email', 'Available on backorder — estimated dispatch in {lead_time}' );
		$msg     = str_replace( '{lead_time}', $lead_time, $msg );

		if ( $plain_text ) {
			echo "\n";
			if ( 'yes' === $is_backorder ) {
				echo esc_html__( 'Status: Available on backorder', 'advanced-woocommerce-backorders' ) . "\n";
			}
			if ( ! empty( $lead_time ) ) {
				echo esc_html( $prefix . $lead_time ) . "\n";
			}
		} else {
			echo '<div style="margin: 6px 0; padding: 6px 10px; background-color: #fff8e5; border-left: 3px solid #ffba00; font-size: 13px; color: #555;">';
			if ( 'yes' === $is_backorder ) {
				echo '<strong style="color:#d97706;">' . esc_html__( 'Available on backorder', 'advanced-woocommerce-backorders' ) . '</strong><br>';
			}
			if ( ! empty( $lead_time ) ) {
				echo '<span style="color:#4b5563;">' . esc_html( $prefix . $lead_time ) . '</span>';
			}
			echo '</div>';
		}
	}

	/**
	 * Render backorder & lead time info on customer Thank You & View Order pages.
	 *
	 * @param int                   $item_id Order item ID.
	 * @param WC_Order_Item_Product $item    Order item object.
	 * @param WC_Order              $order   Order object.
	 */
	public static function render_order_details_item_meta( $item_id, $item, $order ) {
		// Avoid running inside emails (handled above) or in WP admin
		if ( did_action( 'woocommerce_email_header' ) || is_admin() ) {
			return;
		}

		$is_backorder = $item->get_meta( '_awbl_is_backorder' );
		$lead_time    = $item->get_meta( '_awbl_lead_time' );

		if ( empty( $is_backorder ) ) {
			$product = $item->get_product();
			if ( $product ) {
				$is_backorder = AWBL_Backorders::is_product_on_backorder( $product ) ? 'yes' : 'no';
				if ( empty( $lead_time ) ) {
					$lead_time = AWBL_Lead_Time::get_lead_time( $product );
				}
			}
		}

		if ( 'yes' !== $is_backorder && empty( $lead_time ) ) {
			return;
		}

		$prefix = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
		$msg    = AWBL_Settings::get( 'awbl_msg_order', 'Item was ordered on backorder' );

		echo '<div class="awbl-order-item-badge" style="margin-top:6px; font-size:0.9em;">';
		if ( 'yes' === $is_backorder ) {
			echo '<span class="awbl-mini-badge available-on-backorder" style="display:inline-block; padding:2px 6px; background:#fff8e5; color:#b45309; border-radius:3px; margin-right:6px;">⏱ ' . esc_html( $msg ) . '</span>';
		}
		if ( ! empty( $lead_time ) ) {
			echo '<span class="awbl-order-lead-time" style="color:#4b5563;">' . esc_html( $prefix . $lead_time ) . '</span>';
		}
		echo '</div>';
	}

	/**
	 * Render backorder & lead time in WooCommerce Admin Order Edit screen.
	 *
	 * @param int                   $item_id Order item ID.
	 * @param WC_Order_Item_Product $item    Order item object.
	 * @param WC_Product|null       $product Product object.
	 */
	public static function render_admin_order_item_meta( $item_id, $item, $product ) {
		if ( ! is_admin() ) {
			return;
		}

		$is_backorder = $item->get_meta( '_awbl_is_backorder' );
		$lead_time    = $item->get_meta( '_awbl_lead_time' );

		if ( empty( $is_backorder ) && $product ) {
			$is_backorder = AWBL_Backorders::is_product_on_backorder( $product ) ? 'yes' : 'no';
			if ( empty( $lead_time ) ) {
				$lead_time = AWBL_Lead_Time::get_lead_time( $product );
			}
		}

		if ( 'yes' !== $is_backorder && empty( $lead_time ) ) {
			return;
		}

		echo '<div class="awbl-admin-order-item-notice" style="margin-top:4px; font-size:11px; padding:3px 6px; background:#f0f6fc; border-left:2px solid #2271b1; display:inline-block;">';
		if ( 'yes' === $is_backorder ) {
			echo '<strong>' . esc_html__( '[AWBL Backordered]', 'advanced-woocommerce-backorders' ) . '</strong> ';
		}
		if ( ! empty( $lead_time ) ) {
			echo '<span>' . esc_html( sprintf( __( 'Lead Time: %s', 'advanced-woocommerce-backorders' ), $lead_time ) ) . '</span>';
		}
		echo '</div>';
	}
}

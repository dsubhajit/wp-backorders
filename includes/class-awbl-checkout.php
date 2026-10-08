<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Checkout Integration
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Checkout Class
 */
class AWBL_Checkout {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		if ( ! AWBL_Settings::is_plugin_enabled() ) {
			return;
		}

		// Prominent banner before checkout form
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'render_checkout_banner' ), 5 );

		// Checkout review order table item notice
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( __CLASS__, 'render_checkout_item_notice' ), 10, 3 );

		// Save snapshot of backorder status & lead time into order line item meta
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'save_order_line_item_meta' ), 10, 4 );
	}

	/**
	 * Render checkout banner if order contains any backordered product.
	 */
	public static function render_checkout_banner() {
		if ( ! AWBL_Lead_Time::is_enabled_for_context( 'checkout' ) ) {
			return;
		}

		if ( ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}

		$backorder_items = array();

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( AWBL_Cart::is_cart_item_on_backorder( $cart_item ) ) {
				$product   = $cart_item['data'];
				$lead_time = AWBL_Lead_Time::get_lead_time( $product );
				$backorder_items[] = array(
					'name'      => $product->get_name(),
					'lead_time' => $lead_time,
				);
			}
		}

		if ( empty( $backorder_items ) ) {
			return;
		}

		$prefix = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
		?>
		<div class="woocommerce-info awbl-checkout-banner" role="alert" style="margin-bottom:20px;">
			<div class="awbl-banner-heading">
				<strong><?php esc_html_e( 'Notice: Your order contains items available on backorder.', 'advanced-woocommerce-backorders' ); ?></strong>
			</div>
			<ul class="awbl-banner-item-list" style="margin:8px 0 0 18px; list-style-type:disc;">
				<?php foreach ( $backorder_items as $item ) : ?>
					<li>
						<strong><?php echo esc_html( $item['name'] ); ?></strong>
						<span class="awbl-item-pill available-on-backorder"><?php esc_html_e( 'Backorder', 'advanced-woocommerce-backorders' ); ?></span>
						<?php if ( ! empty( $item['lead_time'] ) ) : ?>
							— <span class="awbl-lead-time"><?php echo esc_html( $prefix . $item['lead_time'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Append lead time badge to item quantity in checkout review table.
	 *
	 * @param string $quantity_html Quantity HTML.
	 * @param array  $cart_item     Cart item data.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public static function render_checkout_item_notice( $quantity_html, $cart_item, $cart_item_key ) {
		if ( ! AWBL_Lead_Time::is_enabled_for_context( 'checkout' ) ) {
			return $quantity_html;
		}

		if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
			return $quantity_html;
		}

		$product = $cart_item['data'];
		$qty     = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1;

		if ( AWBL_Cart::is_cart_item_on_backorder( $cart_item ) ) {
			$lead_time = AWBL_Lead_Time::get_lead_time( $product );
			$prefix    = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
			$msg       = AWBL_Lead_Time::get_backorder_message( $product, 'checkout', $qty );

			$notice_html  = '<div class="awbl-checkout-item-badge">';
			$notice_html .= '<span class="awbl-mini-badge available-on-backorder">' . esc_html( $msg ) . '</span>';
			if ( ! empty( $lead_time ) ) {
				$notice_html .= '<small class="awbl-checkout-lead-time" style="display:block; color:#666;">' . esc_html( $prefix . $lead_time ) . '</small>';
			}
			$notice_html .= '</div>';

			return $quantity_html . $notice_html;
		}

		return $quantity_html;
	}

	/**
	 * Freeze backorder status and lead time onto order line item meta during checkout.
	 *
	 * @param WC_Order_Item_Product $item          Order line item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values        Cart item values.
	 * @param WC_Order              $order         Order object.
	 */
	public static function save_order_line_item_meta( $item, $cart_item_key, $values, $order ) {
		$product = isset( $values['data'] ) ? $values['data'] : null;
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$qty = isset( $values['quantity'] ) ? absint( $values['quantity'] ) : 1;
		$is_backorder = AWBL_Cart::is_cart_item_on_backorder( $values );

		if ( $is_backorder ) {
			$item->add_meta_data( '_awbl_is_backorder', 'yes', true );
			$item->add_meta_data( '_awbl_backorder_status', __( 'Backordered', 'advanced-woocommerce-backorders' ), true );
		} else {
			$item->add_meta_data( '_awbl_is_backorder', 'no', true );
		}

		$lead_time = AWBL_Lead_Time::get_lead_time( $product );
		if ( ! empty( $lead_time ) ) {
			$item->add_meta_data( '_awbl_lead_time', $lead_time, true );
		}
	}
}

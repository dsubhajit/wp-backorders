<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Lead Time Engine
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Lead_Time Class
 */
class AWBL_Lead_Time {

	/**
	 * Get resolved lead time string for a product.
	 *
	 * Priority order:
	 * 1. Variation-specific lead time (if variation product)
	 * 2. Product-specific lead time
	 * 3. Category-specific lead time
	 * 4. Stock-status-based lead time (if stock-status lead times enabled)
	 * 5. Global default lead time
	 *
	 * @param WC_Product|int $product Product or Variation object or ID.
	 * @return string Lead time text.
	 */
	public static function get_lead_time( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		$product_id = $product->get_id();
		$lead_time  = '';
		$source     = 'none';

		// 1. Variation override (if variation product)
		if ( $product->is_type( 'variation' ) ) {
			$var_lead_time = get_post_meta( $product_id, '_awbl_variation_lead_time', true );
			if ( ! empty( $var_lead_time ) ) {
				$lead_time = $var_lead_time;
				$source    = 'variation';
			}
		}

		// 2. Product-specific lead time
		if ( empty( $lead_time ) ) {
			$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id;
			$enable_custom = get_post_meta( $parent_id, '_awbl_enable_custom', true );

			if ( 'yes' === $enable_custom ) {
				// Check structured vs raw text
				$lead_time_type = get_post_meta( $parent_id, '_awbl_lead_time_type', true );
				if ( 'structured' === $lead_time_type ) {
					$min  = get_post_meta( $parent_id, '_awbl_lead_time_min', true );
					$max  = get_post_meta( $parent_id, '_awbl_lead_time_max', true );
					$unit = get_post_meta( $parent_id, '_awbl_lead_time_unit', true );
					if ( '' !== $min && '' !== $max && ! empty( $unit ) ) {
						/* translators: 1: Min lead time, 2: Max lead time, 3: Unit (e.g. weeks) */
						$lead_time = sprintf( '%s-%s %s', $min, $max, self::format_unit( $unit, $max ) );
						$source    = 'product';
					} elseif ( '' !== $min && ! empty( $unit ) ) {
						/* translators: 1: Min lead time, 2: Unit */
						$lead_time = sprintf( '%s %s', $min, self::format_unit( $unit, $min ) );
						$source    = 'product';
					}
				} else {
					$custom_lead = get_post_meta( $parent_id, '_awbl_lead_time', true );
					if ( ! empty( $custom_lead ) ) {
						$lead_time = $custom_lead;
						$source    = 'product';
					}
				}
			}
		}

		// 3. Category-specific lead time
		if ( empty( $lead_time ) ) {
			$check_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id;
			$category_rule = AWBL_Category_Rules::get_rule_for_product( $check_id );
			if ( $category_rule && ! empty( $category_rule['lead_time'] ) ) {
				$lead_time = $category_rule['lead_time'];
				$source    = 'category';
			}
		}

		// 4. Stock-status-based lead time (if enabled)
		if ( empty( $lead_time ) && 'yes' === AWBL_Settings::get( 'awbl_enable_stock_status_lead_times', 'no' ) ) {
			$stock_status = self::get_stock_status_category( $product );
			switch ( $stock_status ) {
				case 'instock':
					$lead_time = AWBL_Settings::get( 'awbl_lead_time_instock', '' );
					$source    = 'status_instock';
					break;
				case 'lowstock':
					$lead_time = AWBL_Settings::get( 'awbl_lead_time_lowstock', '' );
					$source    = 'status_lowstock';
					break;
				case 'outofstock':
					$lead_time = AWBL_Settings::get( 'awbl_lead_time_outofstock', '' );
					$source    = 'status_outofstock';
					break;
				case 'backorder':
					$lead_time = AWBL_Settings::get( 'awbl_lead_time_backorder', '' );
					$source    = 'status_backorder';
					break;
			}
		}

		// 5. Global default lead time fallback
		if ( empty( $lead_time ) ) {
			$lead_time = AWBL_Settings::get( 'awbl_global_default_lead_time', '' );
			$source    = 'global_default';
		}

		/**
		 * Filter resolved lead time string.
		 *
		 * @param string     $lead_time Resolved lead time.
		 * @param WC_Product $product   Product object.
		 * @param string     $source    Resolution source.
		 */
		return apply_filters( 'awbl_lead_time', trim( $lead_time ), $product, $source );
	}

	/**
	 * Determine stock status classification for lead times.
	 *
	 * @param WC_Product $product WooCommerce product.
	 * @return string 'backorder'|'lowstock'|'outofstock'|'instock'
	 */
	public static function get_stock_status_category( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return 'instock';
		}

		if ( AWBL_Backorders::is_product_on_backorder( $product ) ) {
			return 'backorder';
		}

		$stock_status = $product->get_stock_status();
		if ( 'outofstock' === $stock_status ) {
			return 'outofstock';
		}

		if ( $product->managing_stock() ) {
			$stock_qty = $product->get_stock_quantity();
			$low_stock_amount = $product->get_low_stock_amount();
			if ( '' === $low_stock_amount || null === $low_stock_amount ) {
				$low_stock_amount = get_option( 'woocommerce_notify_low_stock_amount', 2 );
			}
			$low_stock_amount = (int) $low_stock_amount;

			if ( null !== $stock_qty && $stock_qty <= $low_stock_amount && $stock_qty > 0 ) {
				return 'lowstock';
			}
		}

		return 'instock';
	}

	/**
	 * Helper: Format unit string pluralization.
	 *
	 * @param string $unit Unit key (days, weeks, months).
	 * @param int    $count Count.
	 * @return string
	 */
	private static function format_unit( $unit, $count = 1 ) {
		$count = (int) $count;
		switch ( strtolower( $unit ) ) {
			case 'days':
			case 'day':
				return _n( 'day', 'days', $count, 'advanced-woocommerce-backorders' );
			case 'weeks':
			case 'week':
				return _n( 'week', 'weeks', $count, 'advanced-woocommerce-backorders' );
			case 'months':
			case 'month':
				return _n( 'month', 'months', $count, 'advanced-woocommerce-backorders' );
			default:
				return $unit;
		}
	}

	/**
	 * Check if lead time should be displayed for the product based on its stock status.
	 *
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	public static function should_display_for_stock_status( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		$is_on_backorder = AWBL_Backorders::is_product_on_backorder( $product );
		if ( $is_on_backorder ) {
			return ( 'yes' === AWBL_Settings::get( 'awbl_lead_time_for_backorder', 'yes' ) );
		}

		$stock_status = $product->get_stock_status();
		if ( 'outofstock' === $stock_status ) {
			return ( 'yes' === AWBL_Settings::get( 'awbl_lead_time_for_outofstock', 'yes' ) );
		}

		// Product is in stock
		return ( 'yes' === AWBL_Settings::get( 'awbl_lead_time_for_instock', 'no' ) );
	}

	/**
	 * Check if lead time display is enabled for a given location context.
	 *
	 * @param string $context Context: 'single', 'cart', 'checkout', 'archive', 'order', 'email'.
	 * @return bool
	 */
	public static function is_enabled_for_context( $context ) {
		$option_key = 'awbl_display_lead_time_' . $context;
		$enabled    = ( 'yes' === AWBL_Settings::get( $option_key, 'yes' ) );
		return apply_filters( 'awbl_display_lead_time', $enabled, $context );
	}

	/**
	 * Get resolved backorder message with placeholders replaced.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $context Context: 'product', 'cart', 'checkout', 'order', 'email'.
	 * @param int        $qty     Quantity ordered or in cart.
	 * @return string
	 */
	public static function get_backorder_message( $product, $context = 'product', $qty = 1 ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		$product_id = $product->get_id();
		$parent_id  = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id;

		$message = '';

		// 1. Product-specific message if enabled
		$allow_product_msg = get_post_meta( $parent_id, '_awbl_allow_custom_message', true );
		$product_custom_msg = get_post_meta( $parent_id, '_awbl_custom_message', true );
		if ( 'yes' === $allow_product_msg && ! empty( $product_custom_msg ) ) {
			$message = $product_custom_msg;
		}

		// 2. Category message if available
		if ( empty( $message ) ) {
			$category_rule = AWBL_Category_Rules::get_rule_for_product( $parent_id );
			if ( $category_rule && ! empty( $category_rule['message'] ) ) {
				$message = $category_rule['message'];
			}
		}

		// 3. Contextual setting message
		if ( empty( $message ) ) {
			switch ( $context ) {
				case 'cart':
					$message = AWBL_Settings::get( 'awbl_msg_cart', 'Available on backorder' );
					break;
				case 'checkout':
					$message = AWBL_Settings::get( 'awbl_msg_checkout_item', 'Available on backorder' );
					break;
				case 'order':
					$message = AWBL_Settings::get( 'awbl_msg_order', 'Item was ordered on backorder' );
					break;
				case 'email':
					$message = AWBL_Settings::get( 'awbl_msg_email', 'Available on backorder — estimated dispatch in {lead_time}' );
					break;
				case 'product':
				default:
					$message = AWBL_Settings::get( 'awbl_msg_product', 'Available on backorder' );
					break;
			}
		}

		// Replace placeholders
		$lead_time  = self::get_lead_time( $product );
		$sku        = $product->get_sku() ? $product->get_sku() : __( 'N/A', 'advanced-woocommerce-backorders' );
		$stock_qty  = ( null !== $product->get_stock_quantity() ) ? (string) $product->get_stock_quantity() : '0';

		$replacements = array(
			'{product_name}'   => $product->get_name(),
			'{product_sku}'    => $sku,
			'{lead_time}'      => $lead_time,
			'{stock_quantity}' => $stock_qty,
			'{quantity}'       => (string) $qty,
		);

		$message = str_replace( array_keys( $replacements ), array_values( $replacements ), $message );

		return apply_filters( 'awbl_backorder_message', $message, $product, $context, $replacements );
	}

	/**
	 * Build complete HTML notice / badge for backorder and lead time.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $context Context: 'product', 'cart', 'checkout', 'archive', 'order', 'email'.
	 * @param array      $args    Optional display arguments.
	 * @return string HTML output.
	 */
	public static function render_html( $product, $context = 'product', $args = array() ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		$is_backorder = AWBL_Backorders::is_product_on_backorder( $product );
		$show_lead    = self::is_enabled_for_context( $context ) && self::should_display_for_stock_status( $product );

		// If not on backorder AND lead time not to be shown, output nothing
		if ( ! $is_backorder && ! $show_lead ) {
			return '';
		}

		// Backorders not allowed on product and product out of stock
		if ( ! AWBL_Backorders::is_backorders_allowed( $product ) && ! $show_lead ) {
			return '';
		}

		$style_class = AWBL_Settings::get( 'awbl_badge_style', 'notice' );
		$lead_time   = self::get_lead_time( $product );
		$prefix      = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
		$show_icon   = 'yes' === AWBL_Settings::get( 'awbl_show_lead_time_icon', 'yes' );

		$output = '<div class="awbl-wrapper awbl-context-' . esc_attr( $context ) . ' awbl-style-' . esc_attr( $style_class ) . '" data-product-id="' . esc_attr( $product->get_id() ) . '">';

		// Backorder notification line
		if ( $is_backorder ) {
			$msg = self::get_backorder_message( $product, $context );
			if ( ! empty( $msg ) ) {
				$output .= '<div class="awbl-badge available-on-backorder">';
				if ( $show_icon ) {
					$output .= '<span class="awbl-icon awbl-icon-backorder" aria-hidden="true">⏱</span> ';
				}
				$output .= '<span class="awbl-msg-text">' . esc_html( $msg ) . '</span>';
				$output .= '</div>';
			}
		}

		// Lead time line
		if ( $show_lead && ! empty( $lead_time ) ) {
			$output .= '<div class="awbl-lead-time">';
			if ( $show_icon && ! $is_backorder ) {
				$output .= '<span class="awbl-icon awbl-icon-lead-time" aria-hidden="true">🚚</span> ';
			}
			$output .= '<span class="awbl-lead-prefix">' . esc_html( $prefix ) . '</span>';
			$output .= '<span class="awbl-lead-val">' . esc_html( $lead_time ) . '</span>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return apply_filters( 'awbl_lead_time_html', $output, $product, $context, $lead_time );
	}
}

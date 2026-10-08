<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Backorder Logic Engine
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Backorders Class
 */
class AWBL_Backorders {

	/**
	 * Resolution source cache to avoid repetitive calculations within same request.
	 *
	 * @var array
	 */
	private static $resolution_cache = array();

	/**
	 * Hook into WooCommerce backorder filters if management is enabled.
	 */
	public static function init() {
		// Only hook into WooCommerce backorder logic if backorder management is turned on
		if ( AWBL_Settings::is_management_enabled() ) {
			add_filter( 'woocommerce_product_backorders_allowed', array( __CLASS__, 'filter_backorders_allowed' ), 20, 3 );
			add_filter( 'woocommerce_product_get_backorders', array( __CLASS__, 'filter_get_backorders' ), 20, 2 );
			add_filter( 'woocommerce_product_variation_get_backorders', array( __CLASS__, 'filter_get_backorders' ), 20, 2 );
		}
	}

	/**
	 * Determine effective backorder status for a product following the 4-tier priority:
	 * 1. Product-specific rule
	 * 2. Category-specific rule
	 * 3. Global rule (if Global Override enabled)
	 * 4. Native WooCommerce setting
	 *
	 * Returns array:
	 * [
	 *   'backorders' => 'no'|'notify'|'yes',
	 *   'source'     => 'product'|'category'|'global'|'native',
	 *   'rule_data'  => array|null
	 * ]
	 *
	 * @param WC_Product|int $product Product object or ID.
	 * @return array
	 */
	public static function resolve_backorder_rule( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product instanceof WC_Product ) {
			return array(
				'backorders' => 'no',
				'source'     => 'native',
				'rule_data'  => null,
			);
		}

		$product_id = $product->get_id();
		if ( isset( self::$resolution_cache[ $product_id ] ) ) {
			return self::$resolution_cache[ $product_id ];
		}

		// 4. Fallback: Native WooCommerce setting
		// We get raw native setting directly from meta to avoid recursive filter calling
		$native_backorders = get_post_meta( $product_id, '_backorders', true );
		if ( empty( $native_backorders ) ) {
			$native_backorders = 'no';
		}

		// If management is disabled, immediately return native
		if ( ! AWBL_Settings::is_management_enabled() ) {
			$result = array(
				'backorders' => $native_backorders,
				'source'     => 'native',
				'rule_data'  => null,
			);
			self::$resolution_cache[ $product_id ] = $result;
			return $result;
		}

		// 1. Check Product-level rule
		$enable_product_custom = get_post_meta( $product_id, '_awbl_enable_custom', true );
		$product_backorder_opt = get_post_meta( $product_id, '_awbl_backorder_status', true );

		if ( 'yes' === $enable_product_custom && ! empty( $product_backorder_opt ) && 'wc' !== $product_backorder_opt ) {
			$result = array(
				'backorders' => $product_backorder_opt, // 'no', 'notify', 'yes'
				'source'     => 'product',
				'rule_data'  => array( 'product_id' => $product_id ),
			);
			self::$resolution_cache[ $product_id ] = $result;
			AWBL_Logger::log_resolution( $product_id, 'product', '', $product->get_stock_status(), 'no' !== $product_backorder_opt );
			return apply_filters( 'awbl_product_rule', $result, $product );
		}

		// 2. Check Category-level rule
		$cat_override_global = 'yes' === AWBL_Settings::get( 'awbl_category_overrides_global', 'yes' );
		$category_rule = AWBL_Category_Rules::get_rule_for_product( $product_id );

		if ( $category_rule && ! empty( $category_rule['backorders'] ) && 'wc' !== $category_rule['backorders'] && $cat_override_global ) {
			$result = array(
				'backorders' => $category_rule['backorders'],
				'source'     => 'category',
				'rule_data'  => $category_rule,
			);
			self::$resolution_cache[ $product_id ] = $result;
			AWBL_Logger::log_resolution( $product_id, 'category', isset( $category_rule['lead_time'] ) ? $category_rule['lead_time'] : '', $product->get_stock_status(), 'no' !== $category_rule['backorders'] );
			return $result;
		}

		// 3. Check Global rule (active only if Global Override is enabled)
		if ( AWBL_Settings::is_global_override() ) {
			$global_behaviour = AWBL_Settings::get( 'awbl_global_backorder_behaviour', 'wc' );
			if ( 'wc' !== $global_behaviour ) {
				$result = array(
					'backorders' => $global_behaviour,
					'source'     => 'global',
					'rule_data'  => array( 'behaviour' => $global_behaviour ),
				);
				self::$resolution_cache[ $product_id ] = $result;
				AWBL_Logger::log_resolution( $product_id, 'global', '', $product->get_stock_status(), 'no' !== $global_behaviour );
				return $result;
			}
		}

		// If category rule exists but category_overrides_global was OFF and global wasn't overriding
		if ( $category_rule && ! empty( $category_rule['backorders'] ) && 'wc' !== $category_rule['backorders'] ) {
			$result = array(
				'backorders' => $category_rule['backorders'],
				'source'     => 'category',
				'rule_data'  => $category_rule,
			);
			self::$resolution_cache[ $product_id ] = $result;
			return $result;
		}

		// 4. Return Native WooCommerce setting
		$result = array(
			'backorders' => $native_backorders,
			'source'     => 'native',
			'rule_data'  => null,
		);
		self::$resolution_cache[ $product_id ] = $result;
		return $result;
	}

	/**
	 * Filter WooCommerce product backorders allowed.
	 *
	 * @param bool       $allowed Whether backorders are allowed.
	 * @param int        $product_id Product ID.
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	public static function filter_backorders_allowed( $allowed, $product_id, $product ) {
		$rule = self::resolve_backorder_rule( $product );
		$is_allowed = ( 'no' !== $rule['backorders'] );
		return apply_filters( 'awbl_backorder_allowed', $is_allowed, $product, $rule );
	}

	/**
	 * Filter WooCommerce get_backorders() method value ('no', 'notify', 'yes').
	 *
	 * @param string     $value Value: 'no', 'notify', or 'yes'.
	 * @param WC_Product $product Product instance.
	 * @return string
	 */
	public static function filter_get_backorders( $value, $product ) {
		$rule = self::resolve_backorder_rule( $product );
		return $rule['backorders'];
	}

	/**
	 * Check if a product is actually on backorder in WooCommerce context.
	 * Accurately detects:
	 * - $product->is_on_backorder()
	 * - Stock quantity <= 0 when backorders allowed and manage stock is on
	 * - Or stock status is 'onbackorder'
	 *
	 * @param WC_Product $product WooCommerce product.
	 * @param int        $qty     Optional requested quantity.
	 * @return bool
	 */
	public static function is_product_on_backorder( $product, $qty = 1 ) {
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		// Native WooCommerce method
		if ( $product->is_on_backorder( $qty ) ) {
			return true;
		}

		// Check stock status directly
		if ( 'onbackorder' === $product->get_stock_status() ) {
			return true;
		}

		// Check if managing stock, stock is 0 (or less than requested qty), and backorders are allowed
		$backorders_allowed = self::is_backorders_allowed( $product );
		if ( $backorders_allowed && $product->managing_stock() ) {
			$stock = $product->get_stock_quantity();
			if ( null !== $stock && $stock < $qty ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Helper: Check if backorders are allowed for a product.
	 *
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	public static function is_backorders_allowed( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		$rule = self::resolve_backorder_rule( $product );
		return ( 'no' !== $rule['backorders'] );
	}
}

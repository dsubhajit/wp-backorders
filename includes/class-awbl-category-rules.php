<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Category Rules Manager
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Category_Rules Class
 */
class AWBL_Category_Rules {

	/**
	 * In-memory cache for category rules.
	 *
	 * @var array|null
	 */
	private static $rules_cache = null;

	/**
	 * Get all category rules sorted by priority (lowest number = highest priority).
	 *
	 * @param bool $active_only Whether to return only active rules.
	 * @return array
	 */
	public static function get_all( $active_only = false ) {
		if ( null === self::$rules_cache ) {
			$raw_rules = get_option( 'awbl_category_rules', array() );
			if ( ! is_array( $raw_rules ) ) {
				$raw_rules = array();
			}
			self::$rules_cache = $raw_rules;
		}

		$rules = self::$rules_cache;

		if ( $active_only ) {
			$rules = array_filter(
				$rules,
				function( $rule ) {
					return isset( $rule['status'] ) && 'active' === $rule['status'];
				}
			);
		}

		// Sort by priority ascending
		uasort(
			$rules,
			function( $a, $b ) {
				$pa = isset( $a['priority'] ) ? intval( $a['priority'] ) : 10;
				$pb = isset( $b['priority'] ) ? intval( $b['priority'] ) : 10;
				if ( $pa === $pb ) {
					return 0;
				}
				return ( $pa < $pb ) ? -1 : 1;
			}
		);

		return $rules;
	}

	/**
	 * Get a single rule by rule ID.
	 *
	 * @param string $rule_id Rule identifier.
	 * @return array|null
	 */
	public static function get( $rule_id ) {
		$all = self::get_all();
		return isset( $all[ $rule_id ] ) ? $all[ $rule_id ] : null;
	}

	/**
	 * Save (add or update) a category rule.
	 *
	 * @param array $data Rule data.
	 * @return string|WP_Error Rule ID or WP_Error.
	 */
	public static function save( $data ) {
		$category_id = isset( $data['category_id'] ) ? absint( $data['category_id'] ) : 0;
		if ( ! $category_id ) {
			return new WP_Error( 'missing_category', __( 'Product category is required.', 'advanced-woocommerce-backorders' ) );
		}

		$all = self::get_all();

		// Determine rule ID (edit existing or create new)
		$rule_id = ! empty( $data['id'] ) ? sanitize_key( $data['id'] ) : 'cat_rule_' . time() . '_' . wp_rand( 100, 999 );

		$all[ $rule_id ] = array(
			'id'          => $rule_id,
			'category_id' => $category_id,
			'backorders'  => isset( $data['backorders'] ) ? sanitize_text_field( $data['backorders'] ) : 'wc', // 'wc', 'no', 'notify', 'yes'
			'lead_time'   => isset( $data['lead_time'] ) ? sanitize_text_field( $data['lead_time'] ) : '',
			'message'     => isset( $data['message'] ) ? sanitize_text_field( $data['message'] ) : '',
			'status'      => ( isset( $data['status'] ) && 'inactive' === $data['status'] ) ? 'inactive' : 'active',
			'priority'    => isset( $data['priority'] ) ? intval( $data['priority'] ) : 10,
		);

		update_option( 'awbl_category_rules', $all );
		self::$rules_cache = null;

		return $rule_id;
	}

	/**
	 * Delete a category rule.
	 *
	 * @param string $rule_id Rule identifier.
	 * @return bool
	 */
	public static function delete( $rule_id ) {
		$all = self::get_all();
		if ( isset( $all[ $rule_id ] ) ) {
			unset( $all[ $rule_id ] );
			update_option( 'awbl_category_rules', $all );
			self::$rules_cache = null;
			return true;
		}
		return false;
	}

	/**
	 * Toggle status (active/inactive) for a rule.
	 *
	 * @param string $rule_id Rule ID.
	 * @return string|false New status or false.
	 */
	public static function toggle_status( $rule_id ) {
		$rule = self::get( $rule_id );
		if ( ! $rule ) {
			return false;
		}

		$new_status = ( 'active' === $rule['status'] ) ? 'inactive' : 'active';
		$rule['status'] = $new_status;
		self::save( $rule );
		return $new_status;
	}

	/**
	 * Find the best matching category rule for a specific product.
	 *
	 * @param int $product_id Product ID.
	 * @return array|null Matching category rule data or null.
	 */
	public static function get_rule_for_product( $product_id ) {
		if ( ! $product_id ) {
			return null;
		}

		// Get all categories for this product (including parent categories)
		$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			// If variation, check parent product categories
			$parent_id = wp_get_post_parent_id( $product_id );
			if ( $parent_id ) {
				$terms = wp_get_post_terms( $parent_id, 'product_cat', array( 'fields' => 'ids' ) );
			}
		}

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		// Expand to include ancestors
		$all_cat_ids = $terms;
		foreach ( $terms as $cat_id ) {
			$ancestors = get_ancestors( $cat_id, 'product_cat' );
			if ( ! empty( $ancestors ) ) {
				$all_cat_ids = array_merge( $all_cat_ids, $ancestors );
			}
		}
		$all_cat_ids = array_unique( $all_cat_ids );

		// Get all active rules sorted by priority
		$rules = self::get_all( true );
		foreach ( $rules as $rule ) {
			if ( in_array( (int) $rule['category_id'], $all_cat_ids, true ) ) {
				return apply_filters( 'awbl_category_rule', $rule, $product_id );
			}
		}

		return null;
	}
}

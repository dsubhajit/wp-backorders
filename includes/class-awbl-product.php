<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Product Integration
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Product Class
 */
class AWBL_Product {

	/**
	 * Track rendered products in loop to avoid duplicate messages.
	 *
	 * @var array
	 */
	private static $rendered_archive_items = array();

	/**
	 * Track rendered badges on single product to avoid duplicate output.
	 *
	 * @var array
	 */
	private static $rendered_product_badges = array();

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// Admin Product Data Panels
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'render_product_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_meta' ) );

		// Variable Product Variations
		add_action( 'woocommerce_variation_options_inventory', array( __CLASS__, 'render_variation_fields' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation_fields' ), 10, 2 );

		// Product Admin Columns
		add_filter( 'manage_edit-product_columns', array( __CLASS__, 'add_product_columns' ), 20 );
		add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'render_product_column' ), 10, 2 );
		add_filter( 'manage_edit-product_sortable_columns', array( __CLASS__, 'sortable_product_columns' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'handle_product_columns_sort' ) );

		// Product Admin Filters
		add_action( 'restrict_manage_posts', array( __CLASS__, 'render_admin_filters' ) );
		add_filter( 'parse_query', array( __CLASS__, 'handle_admin_filters' ) );

		// Bulk Actions
		add_filter( 'bulk_actions-edit-product', array( __CLASS__, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-product', array( __CLASS__, 'handle_bulk_actions' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'bulk_admin_notices' ) );

		// Frontend hooks (only if plugin enabled)
		if ( AWBL_Settings::is_plugin_enabled() ) {
			self::init_frontend_hooks();
		}
	}

	/**
	 * Hook frontend rendering hooks according to settings.
	 */
	private static function init_frontend_hooks() {
		// Single Product Page placement
		$placement = AWBL_Settings::get( 'awbl_single_product_placement', 'stock_notice' );
		$priority  = absint( AWBL_Settings::get( 'awbl_single_product_hook_priority', 12 ) );

		if ( 'stock_notice' !== $placement ) {
			switch ( $placement ) {
				case 'before_price':
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_single_product_badge' ), 9 );
					break;
				case 'before_add_to_cart':
					add_action( 'woocommerce_before_add_to_cart_form', array( __CLASS__, 'render_single_product_badge' ), $priority );
					break;
				case 'after_add_to_cart':
					add_action( 'woocommerce_after_add_to_cart_form', array( __CLASS__, 'render_single_product_badge' ), $priority );
					break;
				case 'after_short_description':
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_single_product_badge' ), 21 );
					break;
				case 'after_price':
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_single_product_badge' ), $priority );
					break;
			}
		}

		// Guarantee availability text displays "Available on backorder" when product is on backorder
		add_filter( 'woocommerce_get_availability', array( __CLASS__, 'filter_availability_data' ), 99, 2 );

		// Filter WooCommerce stock html to render full rich AWBL markup at the native stock position
		add_filter( 'woocommerce_get_stock_html', array( __CLASS__, 'filter_stock_html' ), 20, 2 );

		// Pass variation data to frontend for instant JS switching
		add_filter( 'woocommerce_available_variation', array( __CLASS__, 'add_variation_data' ), 10, 3 );

		// Shop Archive / Category loops
		if ( 'yes' === AWBL_Settings::get( 'awbl_display_lead_time_archive', 'no' ) ) {
			$arch_placement = AWBL_Settings::get( 'awbl_archive_placement', 'after_title' );
			$arch_priority  = absint( AWBL_Settings::get( 'awbl_archive_hook_priority', 10 ) );

			if ( 'after_item' === $arch_placement ) {
				add_action( 'woocommerce_after_shop_loop_item', array( __CLASS__, 'render_archive_badge' ), $arch_priority );
			} else {
				add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'render_archive_badge' ), $arch_priority );
			}
		}
	}

	/**
	 * Register "Backorders & Lead Time" tab in WooCommerce Product Data metabox.
	 *
	 * @param array $tabs Product tabs.
	 * @return array
	 */
	public static function add_product_data_tab( $tabs ) {
		$tabs['awbl_tab'] = array(
			'label'    => __( 'Backorders & Lead Time', 'advanced-woocommerce-backorders' ),
			'target'   => 'awbl_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_external' ),
			'priority' => 55,
		);
		return $tabs;
	}

	/**
	 * Render Product Data panel content.
	 */
	public static function render_product_data_panel() {
		global $post, $thepostid, $product_object;

		$product_id = ! empty( $thepostid ) ? $thepostid : $post->ID;

		$enable_custom   = get_post_meta( $product_id, '_awbl_enable_custom', true );
		$status          = get_post_meta( $product_id, '_awbl_backorder_status', true );
		$lead_time_type  = get_post_meta( $product_id, '_awbl_lead_time_type', true );
		$lead_time_text  = get_post_meta( $product_id, '_awbl_lead_time', true );
		$lead_time_min   = get_post_meta( $product_id, '_awbl_lead_time_min', true );
		$lead_time_max   = get_post_meta( $product_id, '_awbl_lead_time_max', true );
		$lead_time_unit  = get_post_meta( $product_id, '_awbl_lead_time_unit', true );
		$allow_custom_msg = get_post_meta( $product_id, '_awbl_allow_custom_message', true );
		$custom_message  = get_post_meta( $product_id, '_awbl_custom_message', true );

		if ( empty( $lead_time_type ) ) {
			$lead_time_type = 'text';
		}
		if ( empty( $status ) ) {
			$status = 'wc';
		}
		?>
		<div id="awbl_product_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<p class="form-field">
					<strong><?php esc_html_e( 'Advanced WooCommerce Backorders & Lead Times', 'advanced-woocommerce-backorders' ); ?></strong>
					<span class="description" style="display:block; margin-top:4px;">
						<?php esc_html_e( 'Configure product-level overrides. Product-specific settings have highest priority (#1) over Category and Global rules.', 'advanced-woocommerce-backorders' ); ?>
					</span>
				</p>

				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => '_awbl_enable_custom',
						'label'       => __( 'Enable Custom Settings', 'advanced-woocommerce-backorders' ),
						'description' => __( 'Check to override category and global backorder / lead time rules for this product.', 'advanced-woocommerce-backorders' ),
						'value'       => $enable_custom,
					)
				);

				woocommerce_wp_select(
					array(
						'id'          => '_awbl_backorder_status',
						'label'       => __( 'Backorder Status', 'advanced-woocommerce-backorders' ),
						'description' => __( 'Choose whether this product allows backorders.', 'advanced-woocommerce-backorders' ),
						'desc_tip'    => true,
						'value'       => $status,
						'options'     => array(
							'wc'     => __( 'Use WooCommerce Product Setting', 'advanced-woocommerce-backorders' ),
							'no'     => __( 'Do not allow backorders', 'advanced-woocommerce-backorders' ),
							'yes'    => __( 'Allow backorders', 'advanced-woocommerce-backorders' ),
							'notify' => __( 'Allow backorders and notify customer', 'advanced-woocommerce-backorders' ),
						),
					)
				);
				?>
			</div>

			<div class="options_group">
				<p class="form-field">
					<strong><?php esc_html_e( 'Lead Time Configuration', 'advanced-woocommerce-backorders' ); ?></strong>
				</p>

				<?php
				woocommerce_wp_radio(
					array(
						'id'          => '_awbl_lead_time_type',
						'label'       => __( 'Lead Time Format', 'advanced-woocommerce-backorders' ),
						'value'       => $lead_time_type,
						'options'     => array(
							'text'       => __( 'Custom Free Text (e.g. "2-3 weeks", "Ships in 48h")', 'advanced-woocommerce-backorders' ),
							'structured' => __( 'Structured Range (Min, Max, Unit)', 'advanced-woocommerce-backorders' ),
						),
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'          => '_awbl_lead_time',
						'label'       => __( 'Lead Time Text', 'advanced-woocommerce-backorders' ),
						'placeholder' => __( 'e.g. 2-3 weeks', 'advanced-woocommerce-backorders' ),
						'description' => __( 'Displayed lead time text for this product.', 'advanced-woocommerce-backorders' ),
						'value'       => $lead_time_text,
						'wrapper_class' => 'awbl-field-text-mode',
					)
				);
				?>

				<div class="awbl-field-structured-mode" style="padding-left:12px; margin-bottom:12px;">
					<p class="form-field" style="display:flex; align-items:center; gap:8px;">
						<label style="width:150px;"><?php esc_html_e( 'Structured Range', 'advanced-woocommerce-backorders' ); ?></label>
						<input type="number" name="_awbl_lead_time_min" id="_awbl_lead_time_min" value="<?php echo esc_attr( $lead_time_min ); ?>" placeholder="<?php esc_attr_e( 'Min (e.g. 2)', 'advanced-woocommerce-backorders' ); ?>" style="width:90px;" min="0" step="1" />
						<span>-</span>
						<input type="number" name="_awbl_lead_time_max" id="_awbl_lead_time_max" value="<?php echo esc_attr( $lead_time_max ); ?>" placeholder="<?php esc_attr_e( 'Max (e.g. 3)', 'advanced-woocommerce-backorders' ); ?>" style="width:90px;" min="0" step="1" />
						<select name="_awbl_lead_time_unit" id="_awbl_lead_time_unit" style="width:120px;">
							<option value="days" <?php selected( $lead_time_unit, 'days' ); ?>><?php esc_html_e( 'Days', 'advanced-woocommerce-backorders' ); ?></option>
							<option value="weeks" <?php selected( $lead_time_unit, 'weeks' ); ?>><?php esc_html_e( 'Weeks', 'advanced-woocommerce-backorders' ); ?></option>
							<option value="months" <?php selected( $lead_time_unit, 'months' ); ?>><?php esc_html_e( 'Months', 'advanced-woocommerce-backorders' ); ?></option>
						</select>
					</p>
				</div>
			</div>

			<div class="options_group">
				<p class="form-field">
					<strong><?php esc_html_e( 'Custom Messages', 'advanced-woocommerce-backorders' ); ?></strong>
				</p>

				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => '_awbl_allow_custom_message',
						'label'       => __( 'Product-Specific Message', 'advanced-woocommerce-backorders' ),
						'description' => __( 'Override default global backorder message for this product.', 'advanced-woocommerce-backorders' ),
						'value'       => $allow_custom_msg,
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'          => '_awbl_custom_message',
						'label'       => __( 'Backorder Message', 'advanced-woocommerce-backorders' ),
						'placeholder' => __( 'Available on backorder', 'advanced-woocommerce-backorders' ),
						'description' => __( 'Custom message displayed when product is on backorder. Placeholders: {product_name}, {product_sku}, {lead_time}', 'advanced-woocommerce-backorders' ),
						'value'       => $custom_message,
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save product metabox fields.
	 *
	 * @param int $post_id Product ID.
	 */
	public static function save_product_meta( $post_id ) {
		// Nonce check performed by WooCommerce parent form
		$enable_custom    = isset( $_POST['_awbl_enable_custom'] ) ? 'yes' : 'no';
		$status           = isset( $_POST['_awbl_backorder_status'] ) ? sanitize_text_field( wp_unslash( $_POST['_awbl_backorder_status'] ) ) : 'wc';
		$lead_time_type   = isset( $_POST['_awbl_lead_time_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_awbl_lead_time_type'] ) ) : 'text';
		$lead_time        = isset( $_POST['_awbl_lead_time'] ) ? sanitize_text_field( wp_unslash( $_POST['_awbl_lead_time'] ) ) : '';
		$lead_time_min    = isset( $_POST['_awbl_lead_time_min'] ) ? sanitize_text_field( wp_unslash( $_POST['_awbl_lead_time_min'] ) ) : '';
		$lead_time_max    = isset( $_POST['_awbl_lead_time_max'] ) ? sanitize_text_field( wp_unslash( $_POST['_awbl_lead_time_max'] ) ) : '';
		$lead_time_unit   = isset( $_POST['_awbl_lead_time_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['_awbl_lead_time_unit'] ) ) : 'weeks';
		$allow_custom_msg = isset( $_POST['_awbl_allow_custom_message'] ) ? 'yes' : 'no';
		$custom_message   = isset( $_POST['_awbl_custom_message'] ) ? sanitize_text_field( wp_unslash( $_POST['_awbl_custom_message'] ) ) : '';

		update_post_meta( $post_id, '_awbl_enable_custom', $enable_custom );
		update_post_meta( $post_id, '_awbl_backorder_status', $status );
		update_post_meta( $post_id, '_awbl_lead_time_type', $lead_time_type );
		update_post_meta( $post_id, '_awbl_lead_time', $lead_time );
		update_post_meta( $post_id, '_awbl_lead_time_min', $lead_time_min );
		update_post_meta( $post_id, '_awbl_lead_time_max', $lead_time_max );
		update_post_meta( $post_id, '_awbl_lead_time_unit', $lead_time_unit );
		update_post_meta( $post_id, '_awbl_allow_custom_message', $allow_custom_msg );
		update_post_meta( $post_id, '_awbl_custom_message', $custom_message );
	}

	/**
	 * Render variation custom fields in variation edit box.
	 *
	 * @param int     $loop           Loop index.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Variation post object.
	 */
	public static function render_variation_fields( $loop, $variation_data, $variation ) {
		$var_lead_time = get_post_meta( $variation->ID, '_awbl_variation_lead_time', true );
		?>
		<div class="awbl-variation-lead-time form-row form-row-full" style="border-top:1px dashed #ddd; padding-top:8px; margin-top:8px;">
			<label>
				<?php esc_html_e( 'Variation Lead Time Override (AWBL)', 'advanced-woocommerce-backorders' ); ?>
				<?php echo wc_help_tip( __( 'Leave empty to inherit parent product or category lead time.', 'advanced-woocommerce-backorders' ) ); ?>
			</label>
			<input type="text" name="_awbl_variation_lead_time[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $var_lead_time ); ?>" placeholder="<?php esc_attr_e( 'e.g. 4-5 weeks (overrides parent)', 'advanced-woocommerce-backorders' ); ?>" class="short" />
		</div>
		<?php
	}

	/**
	 * Save variation fields.
	 *
	 * @param int $variation_id Variation post ID.
	 * @param int $i            Loop index.
	 */
	public static function save_variation_fields( $variation_id, $i ) {
		if ( isset( $_POST['_awbl_variation_lead_time'][ $i ] ) ) {
			$val = sanitize_text_field( wp_unslash( $_POST['_awbl_variation_lead_time'][ $i ] ) );
			update_post_meta( $variation_id, '_awbl_variation_lead_time', $val );
		}
	}

	/**
	 * Add variation lead time to frontend available_variation JSON.
	 *
	 * @param array                $data      Available variation data.
	 * @param WC_Product_Variable $product   Variable product.
	 * @param WC_Product_Variation $variation Variation product.
	 * @return array
	 */
	public static function add_variation_data( $data, $product, $variation ) {
		$lead_time    = AWBL_Lead_Time::get_lead_time( $variation );
		$is_backorder = AWBL_Backorders::is_product_on_backorder( $variation );
		$show_lead    = AWBL_Lead_Time::is_enabled_for_context( 'single' ) && AWBL_Lead_Time::should_display_for_stock_status( $variation );
		$msg          = AWBL_Lead_Time::get_backorder_message( $variation, 'product' );

		$data['awbl_lead_time']       = $lead_time;
		$data['awbl_is_backorder']    = $is_backorder;
		$data['awbl_show_lead_time']  = $show_lead;
		$data['awbl_backorder_msg']   = $msg;
		$data['awbl_badge_html']      = AWBL_Lead_Time::render_html( $variation, 'product' );

		return $data;
	}

	/**
	 * Render single product page badge.
	 */
	public static function render_single_product_badge() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$pid = $product->get_id();
		if ( ! empty( self::$rendered_product_badges[ $pid ] ) ) {
			return;
		}
		self::$rendered_product_badges[ $pid ] = true;

		echo AWBL_Lead_Time::render_html( $product, 'product' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Replace or enhance WooCommerce stock HTML with AWBL rendered markup.
	 *
	 * @param string     $html    Original stock HTML.
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	public static function filter_stock_html( $html, $product ) {
		if ( ! $product instanceof WC_Product ) {
			return $html;
		}

		$pid       = $product->get_id();
		$placement = AWBL_Settings::get( 'awbl_single_product_placement', 'stock_notice' );

		// If placement is NOT stock_notice and already rendered elsewhere, suppress duplicate native stock
		if ( 'stock_notice' !== $placement && ! empty( self::$rendered_product_badges[ $pid ] ) ) {
			return '';
		}

		if ( AWBL_Backorders::is_product_on_backorder( $product ) || ( AWBL_Lead_Time::is_enabled_for_context( 'single' ) && AWBL_Lead_Time::should_display_for_stock_status( $product ) ) ) {
			self::$rendered_product_badges[ $pid ] = true;
			return AWBL_Lead_Time::render_html( $product, 'product' );
		}

		return $html;
	}

	/**
	 * ISSUE #39 FIX:
	 * Guarantee availability text displays "Available on backorder" when product is on backorder.
	 *
	 * @param array      $availability Availability data array [ 'availability' => string, 'class' => string ].
	 * @param WC_Product $product      Product object.
	 * @return array
	 */
	public static function filter_availability_data( $availability, $product ) {
		if ( ! $product instanceof WC_Product ) {
			return $availability;
		}

		if ( AWBL_Backorders::is_product_on_backorder( $product ) ) {
			$custom_msg = AWBL_Lead_Time::get_backorder_message( $product, 'product' );
			$lead_time  = AWBL_Lead_Time::get_lead_time( $product );
			$show_lead  = AWBL_Lead_Time::is_enabled_for_context( 'single' ) && AWBL_Lead_Time::should_display_for_stock_status( $product );

			$text = ! empty( $custom_msg ) ? $custom_msg : __( 'Available on backorder', 'advanced-woocommerce-backorders' );
			if ( $show_lead && ! empty( $lead_time ) ) {
				$prefix = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
				$text  .= ' — ' . $prefix . $lead_time;
			}

			$availability['availability'] = $text;
			$availability['class']        = 'available-on-backorder';
		}

		return $availability;
	}

	/**
	 * Render archive / shop loop badge.
	 */
	public static function render_archive_badge() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$pid = $product->get_id();
		if ( isset( self::$rendered_archive_items[ $pid ] ) ) {
			return; // Prevent duplicate notices on same product card
		}
		self::$rendered_archive_items[ $pid ] = true;

		echo AWBL_Lead_Time::render_html( $product, 'archive' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add custom columns to WooCommerce Products table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function add_product_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			if ( 'is_in_stock' === $key ) {
				$new_columns['awbl_backorders'] = __( 'Backorders', 'advanced-woocommerce-backorders' );
				$new_columns['awbl_lead_time']  = __( 'Lead Time', 'advanced-woocommerce-backorders' );
			}
		}

		if ( ! isset( $new_columns['awbl_backorders'] ) ) {
			$new_columns['awbl_backorders'] = __( 'Backorders', 'advanced-woocommerce-backorders' );
			$new_columns['awbl_lead_time']  = __( 'Lead Time', 'advanced-woocommerce-backorders' );
		}

		return $new_columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public static function render_product_column( $column, $post_id ) {
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		if ( 'awbl_backorders' === $column ) {
			$rule = AWBL_Backorders::resolve_backorder_rule( $product );
			$status = $rule['backorders'];
			$source = $rule['source'];

			$badge_class = 'no' === $status ? 'awbl-pill-no' : ( 'notify' === $status ? 'awbl-pill-notify' : 'awbl-pill-yes' );
			$status_label = ( 'no' === $status ) ? __( 'Not Allowed', 'advanced-woocommerce-backorders' ) : ( ( 'notify' === $status ) ? __( 'Notify Customer', 'advanced-woocommerce-backorders' ) : __( 'Allowed', 'advanced-woocommerce-backorders' ) );

			echo '<span class="awbl-status-pill ' . esc_attr( $badge_class ) . '">' . esc_html( $status_label ) . '</span>';
			echo '<small style="display:block; color:#777; margin-top:3px;">' . esc_html( sprintf( __( 'Source: %s', 'advanced-woocommerce-backorders' ), ucfirst( $source ) ) ) . '</small>';
		} elseif ( 'awbl_lead_time' === $column ) {
			$lead_time = AWBL_Lead_Time::get_lead_time( $product );
			if ( ! empty( $lead_time ) ) {
				echo '<strong>' . esc_html( $lead_time ) . '</strong>';
			} else {
				echo '<span style="color:#aaa;">—</span>';
			}
		}
	}

	/**
	 * Make columns sortable.
	 *
	 * @param array $sortable Sortable columns.
	 * @return array
	 */
	public static function sortable_product_columns( $sortable ) {
		$sortable['awbl_lead_time']  = 'awbl_lead_time';
		$sortable['awbl_backorders'] = 'awbl_backorders';
		return $sortable;
	}

	/**
	 * Handle orderby query for product columns.
	 *
	 * @param WP_Query $query Query object.
	 */
	public static function handle_product_columns_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'product' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( 'awbl_lead_time' === $orderby ) {
			$query->set( 'meta_key', '_awbl_lead_time' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'awbl_backorders' === $orderby ) {
			$query->set( 'meta_key', '_backorders' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Render custom filter dropdowns in Products screen.
	 */
	public static function render_admin_filters() {
		global $typenow;
		if ( 'product' !== $typenow ) {
			return;
		}

		$selected_backorder = isset( $_GET['awbl_filter_backorder'] ) ? sanitize_text_field( wp_unslash( $_GET['awbl_filter_backorder'] ) ) : '';
		$selected_lead_time = isset( $_GET['awbl_filter_lead_time'] ) ? sanitize_text_field( wp_unslash( $_GET['awbl_filter_lead_time'] ) ) : '';
		?>
		<select name="awbl_filter_backorder" id="awbl_filter_backorder">
			<option value=""><?php esc_html_e( 'Filter by Backorders', 'advanced-woocommerce-backorders' ); ?></option>
			<option value="yes" <?php selected( $selected_backorder, 'yes' ); ?>><?php esc_html_e( 'Backorders: Allowed', 'advanced-woocommerce-backorders' ); ?></option>
			<option value="notify" <?php selected( $selected_backorder, 'notify' ); ?>><?php esc_html_e( 'Backorders: Allow + Notify', 'advanced-woocommerce-backorders' ); ?></option>
			<option value="no" <?php selected( $selected_backorder, 'no' ); ?>><?php esc_html_e( 'Backorders: Not Allowed', 'advanced-woocommerce-backorders' ); ?></option>
		</select>

		<select name="awbl_filter_lead_time" id="awbl_filter_lead_time">
			<option value=""><?php esc_html_e( 'Filter by Lead Time', 'advanced-woocommerce-backorders' ); ?></option>
			<option value="configured" <?php selected( $selected_lead_time, 'configured' ); ?>><?php esc_html_e( 'Lead Time: Configured', 'advanced-woocommerce-backorders' ); ?></option>
			<option value="empty" <?php selected( $selected_lead_time, 'empty' ); ?>><?php esc_html_e( 'Lead Time: Not Configured', 'advanced-woocommerce-backorders' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Parse query for custom filters.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function handle_admin_filters( $query ) {
		global $pagenow;
		if ( ! is_admin() || 'edit.php' !== $pagenow || ! isset( $query->query['post_type'] ) || 'product' !== $query->query['post_type'] ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		if ( ! empty( $_GET['awbl_filter_backorder'] ) ) {
			$bo_val = sanitize_text_field( wp_unslash( $_GET['awbl_filter_backorder'] ) );
			$meta_query[] = array(
				'key'     => '_backorders',
				'value'   => $bo_val,
				'compare' => '=',
			);
		}

		if ( ! empty( $_GET['awbl_filter_lead_time'] ) ) {
			$lt_val = sanitize_text_field( wp_unslash( $_GET['awbl_filter_lead_time'] ) );
			if ( 'configured' === $lt_val ) {
				$meta_query[] = array(
					'key'     => '_awbl_lead_time',
					'value'   => '',
					'compare' => '!=',
				);
			} elseif ( 'empty' === $lt_val ) {
				$meta_query[] = array(
					'relation' => 'OR',
					array(
						'key'     => '_awbl_lead_time',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_awbl_lead_time',
						'value'   => '',
						'compare' => '=',
					),
				);
			}
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Register bulk actions on Products table.
	 *
	 * @param array $bulk_actions Actions.
	 * @return array
	 */
	public static function register_bulk_actions( $bulk_actions ) {
		$bulk_actions['awbl_bulk_enable_bo']        = __( 'AWBL: Allow Backorders', 'advanced-woocommerce-backorders' );
		$bulk_actions['awbl_bulk_enable_bo_notify'] = __( 'AWBL: Allow Backorders (Notify)', 'advanced-woocommerce-backorders' );
		$bulk_actions['awbl_bulk_disable_bo']       = __( 'AWBL: Do Not Allow Backorders', 'advanced-woocommerce-backorders' );
		$bulk_actions['awbl_bulk_clear_lead_time']  = __( 'AWBL: Clear Custom Lead Time', 'advanced-woocommerce-backorders' );
		return $bulk_actions;
	}

	/**
	 * Handle bulk actions execution.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $doaction    Action name.
	 * @param array  $post_ids    Array of selected post IDs.
	 * @return string
	 */
	public static function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $redirect_to;
		}

		$valid_actions = array( 'awbl_bulk_enable_bo', 'awbl_bulk_enable_bo_notify', 'awbl_bulk_disable_bo', 'awbl_bulk_clear_lead_time' );
		if ( ! in_array( $doaction, $valid_actions, true ) ) {
			return $redirect_to;
		}

		$count = 0;
		foreach ( $post_ids as $pid ) {
			$product = wc_get_product( $pid );
			if ( ! $product ) {
				continue;
			}

			switch ( $doaction ) {
				case 'awbl_bulk_enable_bo':
					$product->set_backorders( 'yes' );
					update_post_meta( $pid, '_awbl_enable_custom', 'yes' );
					update_post_meta( $pid, '_awbl_backorder_status', 'yes' );
					$product->save();
					$count++;
					break;
				case 'awbl_bulk_enable_bo_notify':
					$product->set_backorders( 'notify' );
					update_post_meta( $pid, '_awbl_enable_custom', 'yes' );
					update_post_meta( $pid, '_awbl_backorder_status', 'notify' );
					$product->save();
					$count++;
					break;
				case 'awbl_bulk_disable_bo':
					$product->set_backorders( 'no' );
					update_post_meta( $pid, '_awbl_enable_custom', 'yes' );
					update_post_meta( $pid, '_awbl_backorder_status', 'no' );
					$product->save();
					$count++;
					break;
				case 'awbl_bulk_clear_lead_time':
					delete_post_meta( $pid, '_awbl_lead_time' );
					delete_post_meta( $pid, '_awbl_lead_time_min' );
					delete_post_meta( $pid, '_awbl_lead_time_max' );
					$count++;
					break;
			}
		}

		return add_query_arg(
			array(
				'awbl_bulk_updated' => $count,
				'awbl_action'       => $doaction,
			),
			$redirect_to
		);
	}

	/**
	 * Display admin notice after bulk action.
	 */
	public static function bulk_admin_notices() {
		if ( ! empty( $_GET['awbl_bulk_updated'] ) ) {
			$count = absint( $_GET['awbl_bulk_updated'] );
			/* translators: %d: number of products updated */
			$msg = sprintf( _n( '%d product updated successfully by AWBL.', '%d products updated successfully by AWBL.', $count, 'advanced-woocommerce-backorders' ), $count );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
		}
	}
}

<?php
/**
 * Tab 5: Product Rules Overview
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Query recent products with pagination
$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page = 20;

$args = array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'title',
	'order'          => 'ASC',
);

// Apply filters if provided
$filter_bo = isset( $_GET['filter_bo'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_bo'] ) ) : '';
$filter_lt = isset( $_GET['filter_lt'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_lt'] ) ) : '';
$filter_stock = isset( $_GET['filter_stock'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_stock'] ) ) : '';

$meta_query = array();

if ( ! empty( $filter_bo ) ) {
	$meta_query[] = array(
		'key'     => '_backorders',
		'value'   => $filter_bo,
		'compare' => '=',
	);
}

if ( ! empty( $filter_stock ) ) {
	$meta_query[] = array(
		'key'     => '_stock_status',
		'value'   => $filter_stock,
		'compare' => '=',
	);
}

if ( 'configured' === $filter_lt ) {
	$meta_query[] = array(
		'key'     => '_awbl_lead_time',
		'value'   => '',
		'compare' => '!=',
	);
} elseif ( 'empty' === $filter_lt ) {
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

if ( ! empty( $meta_query ) ) {
	$args['meta_query'] = $meta_query;
}

$query = new WP_Query( $args );
?>

<div class="awbl-card">
	<div class="awbl-card-header-flex">
		<div>
			<h2><?php esc_html_e( 'Product-Specific Rules Overview', 'advanced-woocommerce-backorders' ); ?></h2>
			<p class="awbl-card-desc"><?php esc_html_e( 'Inspect resolved backorder statuses, active lead times, and configure bulk rules for your catalog.', 'advanced-woocommerce-backorders' ); ?></p>
		</div>
		<div>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>" class="button button-secondary">
				<span class="dashicons dashicons-external" style="vertical-align:text-bottom;"></span>
				<?php esc_html_e( 'Manage All in WooCommerce Products List', 'advanced-woocommerce-backorders' ); ?>
			</a>
		</div>
	</div>

	<!-- Filter Bar -->
	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-filter-bar">
		<input type="hidden" name="page" value="awbl-settings" />
		<input type="hidden" name="tab" value="product_rules" />

		<div class="awbl-filter-group">
			<label for="filter_bo"><?php esc_html_e( 'Backorders:', 'advanced-woocommerce-backorders' ); ?></label>
			<select name="filter_bo" id="filter_bo">
				<option value=""><?php esc_html_e( 'All Backorder States', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="yes" <?php selected( $filter_bo, 'yes' ); ?>><?php esc_html_e( 'Allowed', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="notify" <?php selected( $filter_bo, 'notify' ); ?>><?php esc_html_e( 'Allow + Notify', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="no" <?php selected( $filter_bo, 'no' ); ?>><?php esc_html_e( 'Not Allowed', 'advanced-woocommerce-backorders' ); ?></option>
			</select>
		</div>

		<div class="awbl-filter-group">
			<label for="filter_stock"><?php esc_html_e( 'Stock Status:', 'advanced-woocommerce-backorders' ); ?></label>
			<select name="filter_stock" id="filter_stock">
				<option value=""><?php esc_html_e( 'All Stock States', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="instock" <?php selected( $filter_stock, 'instock' ); ?>><?php esc_html_e( 'In Stock', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="outofstock" <?php selected( $filter_stock, 'outofstock' ); ?>><?php esc_html_e( 'Out of Stock', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="onbackorder" <?php selected( $filter_stock, 'onbackorder' ); ?>><?php esc_html_e( 'On Backorder', 'advanced-woocommerce-backorders' ); ?></option>
			</select>
		</div>

		<div class="awbl-filter-group">
			<label for="filter_lt"><?php esc_html_e( 'Lead Time:', 'advanced-woocommerce-backorders' ); ?></label>
			<select name="filter_lt" id="filter_lt">
				<option value=""><?php esc_html_e( 'All', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="configured" <?php selected( $filter_lt, 'configured' ); ?>><?php esc_html_e( 'Configured', 'advanced-woocommerce-backorders' ); ?></option>
				<option value="empty" <?php selected( $filter_lt, 'empty' ); ?>><?php esc_html_e( 'Not Configured', 'advanced-woocommerce-backorders' ); ?></option>
			</select>
		</div>

		<button type="submit" class="button"><?php esc_html_e( 'Apply Filter', 'advanced-woocommerce-backorders' ); ?></button>
		<?php if ( ! empty( $filter_bo ) || ! empty( $filter_stock ) || ! empty( $filter_lt ) ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=awbl-settings&tab=product_rules' ) ); ?>" class="button button-link"><?php esc_html_e( 'Reset Filter', 'advanced-woocommerce-backorders' ); ?></a>
		<?php endif; ?>
	</form>

	<!-- Product List Table -->
	<table class="wp-list-table widefat fixed striped table-view-list awbl-table">
		<thead>
			<tr>
				<th style="width:30px;"><input type="checkbox" id="awbl-select-all-products" /></th>
				<th style="width:60px;"><?php esc_html_e( 'Image', 'advanced-woocommerce-backorders' ); ?></th>
				<th><?php esc_html_e( 'Product', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:110px;"><?php esc_html_e( 'Stock', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:160px;"><?php esc_html_e( 'Backorder Rule', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:180px;"><?php esc_html_e( 'Effective Lead Time', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:90px; text-align:right;"><?php esc_html_e( 'Actions', 'advanced-woocommerce-backorders' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( $query->have_posts() ) : ?>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$product = wc_get_product( get_the_ID() );
					if ( ! $product ) {
						continue;
					}
					$pid       = $product->get_id();
					$rule      = AWBL_Backorders::resolve_backorder_rule( $product );
					$lead_time = AWBL_Lead_Time::get_lead_time( $product );
					$thumb     = $product->get_image( array( 40, 40 ) );
					$stock_qty = $product->get_stock_quantity();
					$stock_str = ( null !== $stock_qty ) ? $stock_qty : ( $product->is_in_stock() ? __( 'In stock', 'advanced-woocommerce-backorders' ) : __( 'Out of stock', 'advanced-woocommerce-backorders' ) );
					?>
					<tr>
						<td><input type="checkbox" class="awbl-product-cb" value="<?php echo esc_attr( $pid ); ?>" /></td>
						<td><?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td>
							<strong>
								<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>">
									<?php echo esc_html( $product->get_name() ); ?>
								</a>
							</strong>
							<span style="display:block; color:#777; font-size:11px;">SKU: <?php echo esc_html( $product->get_sku() ? $product->get_sku() : '—' ); ?> | Type: <?php echo esc_html( $product->get_type() ); ?></span>
						</td>
						<td>
							<?php if ( $product->is_on_backorder() ) : ?>
								<span class="awbl-badge-text-orange">⏱ <?php esc_html_e( 'On Backorder', 'advanced-woocommerce-backorders' ); ?></span>
							<?php elseif ( $product->is_in_stock() ) : ?>
								<span class="awbl-badge-text-green"><?php echo esc_html( $stock_str ); ?></span>
							<?php else : ?>
								<span class="awbl-badge-text-red"><?php esc_html_e( 'Out of stock', 'advanced-woocommerce-backorders' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php
							$b_class = 'no' === $rule['backorders'] ? 'awbl-pill-no' : ( 'notify' === $rule['backorders'] ? 'awbl-pill-notify' : 'awbl-pill-yes' );
							$b_label = 'no' === $rule['backorders'] ? __( 'Not Allowed', 'advanced-woocommerce-backorders' ) : ( 'notify' === $rule['backorders'] ? __( 'Notify', 'advanced-woocommerce-backorders' ) : __( 'Allowed', 'advanced-woocommerce-backorders' ) );
							?>
							<span class="awbl-status-pill <?php echo esc_attr( $b_class ); ?>"><?php echo esc_html( $b_label ); ?></span>
							<span style="display:block; color:#888; font-size:11px; margin-top:2px;"><?php echo esc_html( sprintf( __( 'Priority: %s', 'advanced-woocommerce-backorders' ), ucfirst( $rule['source'] ) ) ); ?></span>
						</td>
						<td>
							<?php if ( ! empty( $lead_time ) ) : ?>
								<strong><?php echo esc_html( $lead_time ); ?></strong>
							<?php else : ?>
								<span style="color:#aaa;">—</span>
							<?php endif; ?>
						</td>
						<td style="text-align:right;">
							<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>" class="button button-small" title="<?php esc_attr_e( 'Edit Product', 'advanced-woocommerce-backorders' ); ?>">
								<?php esc_html_e( 'Edit', 'advanced-woocommerce-backorders' ); ?>
							</a>
						</td>
					</tr>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No products found matching the criteria.', 'advanced-woocommerce-backorders' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Bulk Lead Time Toolbar -->
	<div class="awbl-bulk-toolbar" style="margin-top:15px; padding:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; display:flex; gap:12px; align-items:center;">
		<span style="font-weight:600;"><?php esc_html_e( 'Bulk Action on Selected Products:', 'advanced-woocommerce-backorders' ); ?></span>
		<input type="text" id="awbl_bulk_lead_time_input" placeholder="<?php esc_attr_e( 'e.g. 2-3 weeks', 'advanced-woocommerce-backorders' ); ?>" style="width:160px;" />
		<button type="button" class="button button-secondary" id="awbl-btn-bulk-set-lt"><?php esc_html_e( 'Set Lead Time', 'advanced-woocommerce-backorders' ); ?></button>
		<button type="button" class="button button-link-delete" id="awbl-btn-bulk-clear-lt"><?php esc_html_e( 'Clear Lead Time', 'advanced-woocommerce-backorders' ); ?></button>
		<span id="awbl-bulk-status-msg" style="margin-left:auto; font-weight:500;"></span>
	</div>
</div>

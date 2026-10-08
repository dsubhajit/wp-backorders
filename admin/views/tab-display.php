<?php
/**
 * Tab 4: Display & Placements View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$single_placement = AWBL_Settings::get( 'awbl_single_product_placement', 'after_price' );
$single_priority  = absint( AWBL_Settings::get( 'awbl_single_product_hook_priority', 12 ) );
$arch_placement   = AWBL_Settings::get( 'awbl_archive_placement', 'after_title' );
$arch_priority    = absint( AWBL_Settings::get( 'awbl_archive_hook_priority', 10 ) );
$cart_placement   = AWBL_Settings::get( 'awbl_cart_placement', 'after_title' );
$badge_style      = AWBL_Settings::get( 'awbl_badge_style', 'notice' );
$show_icon        = AWBL_Settings::get( 'awbl_show_lead_time_icon', 'yes' );
$lead_prefix      = AWBL_Settings::get( 'awbl_lead_time_prefix', 'Estimated dispatch: ' );
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-settings-form">
	<?php wp_nonce_field( 'awbl_save_settings_action', 'awbl_save_settings_nonce' ); ?>
	<input type="hidden" name="awbl_current_tab" value="display" />

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Display Positions & Hook Placements', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Fine-tune where backorder notices and lead times render on your WooCommerce store pages.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<!-- Single Product Placement -->
			<tr>
				<th scope="row">
					<label for="awbl_single_product_placement"><?php esc_html_e( 'Single Product Page Placement', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<select name="awbl_single_product_placement" id="awbl_single_product_placement">
						<option value="stock_notice" <?php selected( $single_placement, 'stock_notice' ); ?>><?php esc_html_e( 'Above Add to Cart (Native WooCommerce stock position)', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="after_price" <?php selected( $single_placement, 'after_price' ); ?>><?php esc_html_e( 'After price', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="before_price" <?php selected( $single_placement, 'before_price' ); ?>><?php esc_html_e( 'Before price', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="before_add_to_cart" <?php selected( $single_placement, 'before_add_to_cart' ); ?>><?php esc_html_e( 'Before add to cart form', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="after_add_to_cart" <?php selected( $single_placement, 'after_add_to_cart' ); ?>><?php esc_html_e( 'After add to cart form', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="after_short_description" <?php selected( $single_placement, 'after_short_description' ); ?>><?php esc_html_e( 'After short description', 'advanced-woocommerce-backorders' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Choose hook location inside the single product summary.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Custom Hook Priority -->
			<tr>
				<th scope="row">
					<label for="awbl_single_product_hook_priority"><?php esc_html_e( 'Single Product Hook Priority', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="number" name="awbl_single_product_hook_priority" id="awbl_single_product_hook_priority" value="<?php echo esc_attr( $single_priority ); ?>" min="1" max="99" style="width:90px;" />
					<p class="description"><?php esc_html_e( 'Custom priority value for hook execution (default: 12). Useful for matching custom theme layouts.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Archive Placement -->
			<tr>
				<th scope="row">
					<label for="awbl_archive_placement"><?php esc_html_e( 'Shop / Category Archive Placement', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<select name="awbl_archive_placement" id="awbl_archive_placement">
						<option value="after_title" <?php selected( $arch_placement, 'after_title' ); ?>><?php esc_html_e( 'After product title', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="after_item" <?php selected( $arch_placement, 'after_item' ); ?>><?php esc_html_e( 'After product item / card', 'advanced-woocommerce-backorders' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Location inside product loops (shop, category archives, search results).', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Cart Placement -->
			<tr>
				<th scope="row">
					<label for="awbl_cart_placement"><?php esc_html_e( 'Cart Page Location', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<select name="awbl_cart_placement" id="awbl_cart_placement">
						<option value="after_title" <?php selected( $cart_placement, 'after_title' ); ?>><?php esc_html_e( 'After product title', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="below_quantity" <?php selected( $cart_placement, 'below_quantity' ); ?>><?php esc_html_e( 'Below item quantity', 'advanced-woocommerce-backorders' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Choose placement near the product name or near quantity in cart table.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Badge Appearance & Formatting', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Style format and prefix controls for frontend rendered elements.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<!-- Message Style -->
			<tr>
				<th scope="row">
					<label for="awbl_badge_style"><?php esc_html_e( 'Message Style', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<select name="awbl_badge_style" id="awbl_badge_style">
						<option value="plain" <?php selected( $badge_style, 'plain' ); ?>><?php esc_html_e( 'Plain Text', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="notice" <?php selected( $badge_style, 'notice' ); ?>><?php esc_html_e( 'Notice Box (Soft background & border)', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="badge" <?php selected( $badge_style, 'badge' ); ?>><?php esc_html_e( 'Pill Badge', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="custom" <?php selected( $badge_style, 'custom' ); ?>><?php esc_html_e( 'Custom (Target .awbl-style-custom in CSS)', 'advanced-woocommerce-backorders' ); ?></option>
					</select>
				</td>
			</tr>

			<!-- Show Lead Time Icon -->
			<tr>
				<th scope="row">
					<label for="awbl_show_lead_time_icon"><?php esc_html_e( 'Display Icons', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_show_lead_time_icon" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_show_lead_time_icon" id="awbl_show_lead_time_icon" value="yes" <?php checked( $show_icon, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description"><?php esc_html_e( 'Display icons (⏱ for backorders, 🚚 for lead times) beside the messages.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Lead Time Prefix -->
			<tr>
				<th scope="row">
					<label for="awbl_lead_time_prefix"><?php esc_html_e( 'Lead Time Prefix', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_lead_time_prefix" id="awbl_lead_time_prefix" value="<?php echo esc_attr( $lead_prefix ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Prefix prepended before lead times (e.g. "Estimated dispatch: ").', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>
		</table>

		<div class="awbl-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Save Display Settings', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>
</form>

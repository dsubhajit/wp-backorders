<?php
/**
 * Tab 3: Lead Times View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$disp_single    = AWBL_Settings::get( 'awbl_display_lead_time_single', 'yes' );
$disp_cart      = AWBL_Settings::get( 'awbl_display_lead_time_cart', 'yes' );
$disp_checkout  = AWBL_Settings::get( 'awbl_display_lead_time_checkout', 'yes' );
$disp_archive   = AWBL_Settings::get( 'awbl_display_lead_time_archive', 'no' );
$disp_order     = AWBL_Settings::get( 'awbl_display_lead_time_order', 'yes' );
$disp_email     = AWBL_Settings::get( 'awbl_display_lead_time_email', 'yes' );

$for_instock    = AWBL_Settings::get( 'awbl_lead_time_for_instock', 'no' );
$for_outofstock = AWBL_Settings::get( 'awbl_lead_time_for_outofstock', 'yes' );
$for_backorder  = AWBL_Settings::get( 'awbl_lead_time_for_backorder', 'yes' );

$enable_stock_status_lt = AWBL_Settings::get( 'awbl_enable_stock_status_lead_times', 'no' );
$lt_instock             = AWBL_Settings::get( 'awbl_lead_time_instock', 'Ships within 1-2 business days' );
$lt_lowstock            = AWBL_Settings::get( 'awbl_lead_time_lowstock', 'Ships within 2-3 business days' );
$lt_outofstock          = AWBL_Settings::get( 'awbl_lead_time_outofstock', 'Ships within 2-3 weeks' );
$lt_backorder           = AWBL_Settings::get( 'awbl_lead_time_backorder', 'Available on backorder — ships within 2-3 weeks' );
$global_default_lt      = AWBL_Settings::get( 'awbl_global_default_lead_time', '2-3 weeks' );
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-settings-form">
	<?php wp_nonce_field( 'awbl_save_settings_action', 'awbl_save_settings_nonce' ); ?>
	<input type="hidden" name="awbl_current_tab" value="lead_times" />

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Lead Time Display Locations', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Choose which areas of the customer journey should display lead time and dispatch information.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Display Locations', 'advanced-woocommerce-backorders' ); ?></th>
				<td>
					<fieldset>
						<input type="hidden" name="field_present_awbl_display_lead_time_single" value="1" />
						<label>
							<input type="checkbox" name="awbl_display_lead_time_single" value="yes" <?php checked( $disp_single, 'yes' ); ?> />
							<?php esc_html_e( 'Single product page', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_display_lead_time_cart" value="1" />
						<label>
							<input type="checkbox" name="awbl_display_lead_time_cart" value="yes" <?php checked( $disp_cart, 'yes' ); ?> />
							<?php esc_html_e( 'Cart page', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_display_lead_time_checkout" value="1" />
						<label>
							<input type="checkbox" name="awbl_display_lead_time_checkout" value="yes" <?php checked( $disp_checkout, 'yes' ); ?> />
							<?php esc_html_e( 'Checkout page', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_display_lead_time_archive" value="1" />
						<label>
							<input type="checkbox" name="awbl_display_lead_time_archive" value="yes" <?php checked( $disp_archive, 'yes' ); ?> />
							<?php esc_html_e( 'Shop / category pages (catalog archives)', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_display_lead_time_order" value="1" />
						<label>
							<input type="checkbox" name="awbl_display_lead_time_order" value="yes" <?php checked( $disp_order, 'yes' ); ?> />
							<?php esc_html_e( 'Order details (Thank You & My Account)', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_display_lead_time_email" value="1" />
						<label>
							<input type="checkbox" name="awbl_display_lead_time_email" value="yes" <?php checked( $disp_email, 'yes' ); ?> />
							<?php esc_html_e( 'Customer emails', 'advanced-woocommerce-backorders' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
	</div>

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Stock Status Display Rules', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Control when lead times are rendered depending on the product’s inventory status.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Display Lead Times For', 'advanced-woocommerce-backorders' ); ?></th>
				<td>
					<fieldset>
						<input type="hidden" name="field_present_awbl_lead_time_for_instock" value="1" />
						<label>
							<input type="checkbox" name="awbl_lead_time_for_instock" value="yes" <?php checked( $for_instock, 'yes' ); ?> />
							<?php esc_html_e( 'In-stock products', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_lead_time_for_outofstock" value="1" />
						<label>
							<input type="checkbox" name="awbl_lead_time_for_outofstock" value="yes" <?php checked( $for_outofstock, 'yes' ); ?> />
							<?php esc_html_e( 'Out-of-stock products', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_lead_time_for_backorder" value="1" />
						<label>
							<input type="checkbox" name="awbl_lead_time_for_backorder" value="yes" <?php checked( $for_backorder, 'yes' ); ?> />
							<?php esc_html_e( 'Products on backorder', 'advanced-woocommerce-backorders' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>

			<!-- Global Default Lead Time -->
			<tr>
				<th scope="row">
					<label for="awbl_global_default_lead_time"><?php esc_html_e( 'Global Default Lead Time', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_global_default_lead_time" id="awbl_global_default_lead_time" value="<?php echo esc_attr( $global_default_lt ); ?>" class="regular-text" placeholder="2-3 weeks" />
					<p class="description"><?php esc_html_e( 'Default lead time used when no product, variation, or category rule is specified.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Dynamic Stock-Status Lead Times', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Optionally display a different lead time depending on whether the product is in stock, low stock, out of stock, or on backorder.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<tr>
				<th scope="row">
					<label for="awbl_enable_stock_status_lead_times"><?php esc_html_e( 'Different Lead Times by Stock Status', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_enable_stock_status_lead_times" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_enable_stock_status_lead_times" id="awbl_enable_stock_status_lead_times" value="yes" <?php checked( $enable_stock_status_lt, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description"><?php esc_html_e( 'Enable stock-status-specific messages when product or category lead time is not specifically customized.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="awbl_lead_time_instock"><?php esc_html_e( 'In Stock Lead Time', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_lead_time_instock" id="awbl_lead_time_instock" value="<?php echo esc_attr( $lt_instock ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Example: Ships within 1-2 business days', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="awbl_lead_time_lowstock"><?php esc_html_e( 'Low Stock Lead Time', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_lead_time_lowstock" id="awbl_lead_time_lowstock" value="<?php echo esc_attr( $lt_lowstock ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Example: Ships within 2-3 business days', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="awbl_lead_time_outofstock"><?php esc_html_e( 'Out of Stock Lead Time', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_lead_time_outofstock" id="awbl_lead_time_outofstock" value="<?php echo esc_attr( $lt_outofstock ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Example: Ships within 2-3 weeks', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="awbl_lead_time_backorder"><?php esc_html_e( 'Backorder Lead Time', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_lead_time_backorder" id="awbl_lead_time_backorder" value="<?php echo esc_attr( $lt_backorder ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Example: Available on backorder — ships within 2-3 weeks', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>
		</table>

		<div class="awbl-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Save Lead Time Settings', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>
</form>

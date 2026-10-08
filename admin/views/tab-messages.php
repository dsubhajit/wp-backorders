<?php
/**
 * Tab 7: Messages & Placeholders View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$msg_product       = AWBL_Settings::get( 'awbl_msg_product', 'Available on backorder' );
$msg_cart          = AWBL_Settings::get( 'awbl_msg_cart', 'Available on backorder' );
$msg_checkout      = AWBL_Settings::get( 'awbl_msg_checkout', 'Your order contains an item available on backorder: {product_name}' );
$msg_checkout_item = AWBL_Settings::get( 'awbl_msg_checkout_item', 'Available on backorder' );
$msg_order         = AWBL_Settings::get( 'awbl_msg_order', 'Item was ordered on backorder' );
$msg_email         = AWBL_Settings::get( 'awbl_msg_email', 'Available on backorder — estimated dispatch in {lead_time}' );
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-settings-form">
	<?php wp_nonce_field( 'awbl_save_settings_action', 'awbl_save_settings_nonce' ); ?>
	<input type="hidden" name="awbl_current_tab" value="messages" />

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Backorder Messages & Dynamic Placeholders', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Configure dedicated customer messages for every stage of the purchasing and notification journey.', 'advanced-woocommerce-backorders' ); ?></p>

		<div class="awbl-placeholders-reference" style="margin-bottom:20px; padding:12px 16px; background:#f0f6fc; border-left:4px solid #2271b1; border-radius:4px;">
			<h4 style="margin:0 0 6px 0;"><?php esc_html_e( 'Available Dynamic Placeholders:', 'advanced-woocommerce-backorders' ); ?></h4>
			<ul style="margin:0; padding-left:18px; list-style-type:circle;">
				<li><code>{product_name}</code> — <?php esc_html_e( 'Product or variation title', 'advanced-woocommerce-backorders' ); ?></li>
				<li><code>{product_sku}</code> — <?php esc_html_e( 'Product SKU code', 'advanced-woocommerce-backorders' ); ?></li>
				<li><code>{lead_time}</code> — <?php esc_html_e( 'Calculated lead time string (e.g. 2-3 weeks)', 'advanced-woocommerce-backorders' ); ?></li>
				<li><code>{stock_quantity}</code> — <?php esc_html_e( 'Current inventory stock quantity', 'advanced-woocommerce-backorders' ); ?></li>
				<li><code>{quantity}</code> — <?php esc_html_e( 'Quantity added to cart or ordered', 'advanced-woocommerce-backorders' ); ?></li>
			</ul>
		</div>

		<table class="form-table awbl-form-table">
			<!-- Product Page Message -->
			<tr>
				<th scope="row">
					<label for="awbl_msg_product"><?php esc_html_e( 'Single Product Page Message', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_msg_product" id="awbl_msg_product" value="<?php echo esc_attr( $msg_product ); ?>" class="large-text" />
					<p class="description"><?php esc_html_e( 'Default: "Available on backorder"', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Cart Page Message -->
			<tr>
				<th scope="row">
					<label for="awbl_msg_cart"><?php esc_html_e( 'Cart Item Notice', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_msg_cart" id="awbl_msg_cart" value="<?php echo esc_attr( $msg_cart ); ?>" class="large-text" />
					<p class="description"><?php esc_html_e( 'Displayed near the product name or quantity in the cart table.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Checkout Banner Message -->
			<tr>
				<th scope="row">
					<label for="awbl_msg_checkout"><?php esc_html_e( 'Checkout Banner Notice', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_msg_checkout" id="awbl_msg_checkout" value="<?php echo esc_attr( $msg_checkout ); ?>" class="large-text" />
					<p class="description"><?php esc_html_e( 'Notice banner displayed above checkout form when backordered items are present.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Checkout Review Table Item -->
			<tr>
				<th scope="row">
					<label for="awbl_msg_checkout_item"><?php esc_html_e( 'Checkout Review Item Notice', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_msg_checkout_item" id="awbl_msg_checkout_item" value="<?php echo esc_attr( $msg_checkout_item ); ?>" class="large-text" />
					<p class="description"><?php esc_html_e( 'Notice displayed in the checkout review table alongside the backordered item.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Order Details Message -->
			<tr>
				<th scope="row">
					<label for="awbl_msg_order"><?php esc_html_e( 'Order Details Message', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_msg_order" id="awbl_msg_order" value="<?php echo esc_attr( $msg_order ); ?>" class="large-text" />
					<p class="description"><?php esc_html_e( 'Displayed on the Order Received (Thank You) page and customer My Account -> View Order screen.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Emails Message -->
			<tr>
				<th scope="row">
					<label for="awbl_msg_email"><?php esc_html_e( 'Email Notice Message', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="text" name="awbl_msg_email" id="awbl_msg_email" value="<?php echo esc_attr( $msg_email ); ?>" class="large-text" />
					<p class="description"><?php esc_html_e( 'Notice displayed in WooCommerce transactional emails sent to customers and admins.', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>
		</table>

		<div class="awbl-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Save Message Templates', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>
</form>

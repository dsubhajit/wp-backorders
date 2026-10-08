<?php
/**
 * Tab 2: Backorders & Priority Hierarchy View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$cat_overrides_global = AWBL_Settings::get( 'awbl_category_overrides_global', 'yes' );
$treat_zero_stock     = AWBL_Settings::get( 'awbl_treat_zero_stock_as_backorder', 'yes' );
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-settings-form">
	<?php wp_nonce_field( 'awbl_save_settings_action', 'awbl_save_settings_nonce' ); ?>
	<input type="hidden" name="awbl_current_tab" value="backorders" />

	<!-- Rule Priority Hierarchy Card -->
	<div class="awbl-card">
		<h2><?php esc_html_e( 'Backorder Rule Priority Resolution', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'The plugin resolves backorders and lead times following a strict hierarchical priority waterfall.', 'advanced-woocommerce-backorders' ); ?></p>

		<div class="awbl-priority-waterfall">
			<div class="awbl-priority-step awbl-priority-1">
				<div class="awbl-priority-badge">#1</div>
				<div class="awbl-priority-body">
					<h4><?php esc_html_e( 'Product-Specific Rule (Highest Priority)', 'advanced-woocommerce-backorders' ); ?></h4>
					<p><?php esc_html_e( 'Configured directly in the product edit screen under "Backorders & Lead Time" tab or variation panel. Overrides all category and global rules.', 'advanced-woocommerce-backorders' ); ?></p>
				</div>
			</div>

			<div class="awbl-priority-arrow">↓</div>

			<div class="awbl-priority-step awbl-priority-2">
				<div class="awbl-priority-badge">#2</div>
				<div class="awbl-priority-body">
					<h4><?php esc_html_e( 'Category-Specific Rule', 'advanced-woocommerce-backorders' ); ?></h4>
					<p><?php esc_html_e( 'Rules configured in the "Category Rules" tab. Applies to all products assigned to that category unless a product has a custom #1 rule.', 'advanced-woocommerce-backorders' ); ?></p>
				</div>
			</div>

			<div class="awbl-priority-arrow">↓</div>

			<div class="awbl-priority-step awbl-priority-3">
				<div class="awbl-priority-badge">#3</div>
				<div class="awbl-priority-body">
					<h4><?php esc_html_e( 'Global Rule (Active when Global Override is ON)', 'advanced-woocommerce-backorders' ); ?></h4>
					<p><?php esc_html_e( 'Defined in General Settings. Enforces store-wide behavior if Global Override is active.', 'advanced-woocommerce-backorders' ); ?></p>
				</div>
			</div>

			<div class="awbl-priority-arrow">↓</div>

			<div class="awbl-priority-step awbl-priority-4">
				<div class="awbl-priority-badge">#4</div>
				<div class="awbl-priority-body">
					<h4><?php esc_html_e( 'Native WooCommerce Setting (Fallback)', 'advanced-woocommerce-backorders' ); ?></h4>
					<p><?php esc_html_e( 'The standard WooCommerce Inventory setting on the product (Do not allow / Allow / Allow, but notify).', 'advanced-woocommerce-backorders' ); ?></p>
				</div>
			</div>
		</div>

		<hr style="margin:25px 0 20px 0; border:0; border-top:1px solid #e2e8f0;">

		<table class="form-table awbl-form-table">
			<!-- Category Rules Override Global -->
			<tr>
				<th scope="row">
					<label for="awbl_category_overrides_global"><?php esc_html_e( 'Category Rules Override Global', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_category_overrides_global" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_category_overrides_global" id="awbl_category_overrides_global" value="yes" <?php checked( $cat_overrides_global, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description">
						<?php esc_html_e( 'When ON (recommended): Category-level rules take precedence over global settings. When OFF: Global override settings take precedence over category rules.', 'advanced-woocommerce-backorders' ); ?>
					</p>
				</td>
			</tr>

			<!-- Treat Zero Stock as Backorder -->
			<tr>
				<th scope="row">
					<label for="awbl_treat_zero_stock_as_backorder"><?php esc_html_e( 'Auto-detect Zero Stock with Backorders', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_treat_zero_stock_as_backorder" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_treat_zero_stock_as_backorder" id="awbl_treat_zero_stock_as_backorder" value="yes" <?php checked( $treat_zero_stock, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description">
						<?php esc_html_e( 'Explicitly activates frontend backorder notifications and lead times whenever stock is 0 and backorders are allowed, even if the active theme overrides WooCommerce default availability notices.', 'advanced-woocommerce-backorders' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<div class="awbl-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Save Backorder Settings', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>
</form>

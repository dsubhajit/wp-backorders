<?php
/**
 * Tab 9: Tools, Import/Export & Maintenance View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$delete_uninstall = AWBL_Settings::get( 'awbl_delete_data_on_uninstall', 'no' );
$enable_debug_log = AWBL_Settings::get( 'awbl_enable_debug_log', 'no' );
$email_customer   = AWBL_Settings::get( 'awbl_email_customer_include', 'yes' );
$email_admin      = AWBL_Settings::get( 'awbl_email_admin_include', 'yes' );
?>

<div class="awbl-card">
	<h2><?php esc_html_e( 'Export / Import Plugin Settings', 'advanced-woocommerce-backorders' ); ?></h2>
	<p class="awbl-card-desc"><?php esc_html_e( 'Backup or transfer your backorder settings and category rules across development, staging, and production stores.', 'advanced-woocommerce-backorders' ); ?></p>

	<div style="display:flex; gap:30px; margin-top:15px; flex-wrap:wrap;">
		<!-- Export Section -->
		<div style="flex:1; min-width:300px; padding:15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Export Settings', 'advanced-woocommerce-backorders' ); ?></h3>
			<p><?php esc_html_e( 'Download all configured plugin options and category rules as a JSON file.', 'advanced-woocommerce-backorders' ); ?></p>
			<button type="button" class="button button-primary button-large" id="awbl-btn-export-settings">
				<span class="dashicons dashicons-download" style="vertical-align:text-bottom;"></span>
				<?php esc_html_e( 'Export Settings JSON', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>

		<!-- Import Section -->
		<div style="flex:1; min-width:300px; padding:15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Import Settings', 'advanced-woocommerce-backorders' ); ?></h3>
			<p><?php esc_html_e( 'Upload a valid AWBL JSON settings file to restore your configuration.', 'advanced-woocommerce-backorders' ); ?></p>
			<input type="file" id="awbl-import-file-input" accept=".json" style="margin-bottom:10px; display:block;" />
			<button type="button" class="button button-secondary" id="awbl-btn-import-settings">
				<span class="dashicons dashicons-upload" style="vertical-align:text-bottom;"></span>
				<?php esc_html_e( 'Import Settings', 'advanced-woocommerce-backorders' ); ?>
			</button>
			<span id="awbl-import-feedback" style="margin-left:10px; font-weight:500;"></span>
		</div>
	</div>
</div>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-settings-form">
	<?php wp_nonce_field( 'awbl_save_settings_action', 'awbl_save_settings_nonce' ); ?>
	<input type="hidden" name="awbl_current_tab" value="tools" />

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Email & Maintenance Preferences', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Configure email notifications, debug logging, and plugin lifecycle policies.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<!-- Email Preferences -->
			<tr>
				<th scope="row"><?php esc_html_e( 'Email Inclusions', 'advanced-woocommerce-backorders' ); ?></th>
				<td>
					<fieldset>
						<input type="hidden" name="field_present_awbl_email_customer_include" value="1" />
						<label>
							<input type="checkbox" name="awbl_email_customer_include" value="yes" <?php checked( $email_customer, 'yes' ); ?> />
							<?php esc_html_e( 'Include backorder and lead time details in Customer emails', 'advanced-woocommerce-backorders' ); ?>
						</label><br>

						<input type="hidden" name="field_present_awbl_email_admin_include" value="1" />
						<label>
							<input type="checkbox" name="awbl_email_admin_include" value="yes" <?php checked( $email_admin, 'yes' ); ?> />
							<?php esc_html_e( 'Include backorder and lead time details in Admin (New Order) emails', 'advanced-woocommerce-backorders' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>

			<!-- Debug Logging -->
			<tr>
				<th scope="row">
					<label for="awbl_enable_debug_log"><?php esc_html_e( 'Debug Logging', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_enable_debug_log" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_enable_debug_log" id="awbl_enable_debug_log" value="yes" <?php checked( $enable_debug_log, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description">
						<?php esc_html_e( 'When enabled, records backorder rule resolution events, product IDs, rule sources, and stock statuses to WooCommerce logs (wc_get_logger). Customer data is never logged.', 'advanced-woocommerce-backorders' ); ?>
					</p>
				</td>
			</tr>

			<!-- Data Clean on Uninstall -->
			<tr>
				<th scope="row">
					<label for="awbl_delete_data_on_uninstall"><?php esc_html_e( 'Delete Plugin Data on Uninstall', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_delete_data_on_uninstall" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_delete_data_on_uninstall" id="awbl_delete_data_on_uninstall" value="yes" <?php checked( $delete_uninstall, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description" style="color:#b91c1c;">
						<?php esc_html_e( 'WARNING: If checked, completely deletes AWBL options and category rules when the plugin is deleted via Plugins screen. Native WooCommerce products and orders are NEVER touched or deleted.', 'advanced-woocommerce-backorders' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<div class="awbl-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Save Tool Settings', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>
</form>

<!-- Reset Settings Section -->
<div class="awbl-card" style="border-left:4px solid #dc2626;">
	<h2 style="color:#b91c1c;"><?php esc_html_e( 'Reset Plugin Settings', 'advanced-woocommerce-backorders' ); ?></h2>
	<p class="awbl-card-desc">
		<?php esc_html_e( 'Restores all plugin settings, lead times, and messages to their initial factory defaults. This will NOT modify or delete any WooCommerce products, stock levels, or customer orders.', 'advanced-woocommerce-backorders' ); ?>
	</p>
	<button type="button" class="button button-link-delete button-large" id="awbl-btn-reset-settings">
		<?php esc_html_e( 'Reset All Settings to Default', 'advanced-woocommerce-backorders' ); ?>
	</button>
</div>

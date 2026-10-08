<?php
/**
 * Tab 1: General Settings View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$enable_plugin      = AWBL_Settings::get( 'awbl_enable_plugin', 'yes' );
$enable_management  = AWBL_Settings::get( 'awbl_enable_management', 'no' );
$global_behaviour   = AWBL_Settings::get( 'awbl_global_backorder_behaviour', 'wc' );
$global_override    = AWBL_Settings::get( 'awbl_global_override', 'no' );
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-settings-form">
	<?php wp_nonce_field( 'awbl_save_settings_action', 'awbl_save_settings_nonce' ); ?>
	<input type="hidden" name="awbl_current_tab" value="general" />

	<div class="awbl-card">
		<h2><?php esc_html_e( 'General Plugin Settings', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Master toggles controlling the backorder engine behavior across your entire WooCommerce catalog.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<!-- Enable Plugin -->
			<tr>
				<th scope="row">
					<label for="awbl_enable_plugin"><?php esc_html_e( 'Enable Plugin', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_enable_plugin" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_enable_plugin" id="awbl_enable_plugin" value="yes" <?php checked( $enable_plugin, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description"><?php esc_html_e( 'Enable or disable all plugin frontend features (badges, lead times, notices).', 'advanced-woocommerce-backorders' ); ?></p>
				</td>
			</tr>

			<!-- Enable Backorder Management -->
			<tr>
				<th scope="row">
					<label for="awbl_enable_management"><?php esc_html_e( 'Enable Backorder Management', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_enable_management" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_enable_management" id="awbl_enable_management" value="yes" <?php checked( $enable_management, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<p class="description">
						<?php esc_html_e( 'When OFF: Native WooCommerce inventory rules govern purchasing. When ON: The plugin rules engine manages backorder availability based on your defined priority rules.', 'advanced-woocommerce-backorders' ); ?>
					</p>
				</td>
			</tr>

			<!-- Global Backorder Behaviour -->
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Global Backorder Behaviour', 'advanced-woocommerce-backorders' ); ?>
				</th>
				<td>
					<fieldset class="awbl-radio-group">
						<label>
							<input type="radio" name="awbl_global_backorder_behaviour" value="wc" <?php checked( $global_behaviour, 'wc' ); ?> />
							<span><?php esc_html_e( 'Use WooCommerce product settings', 'advanced-woocommerce-backorders' ); ?></span>
						</label><br>
						<label>
							<input type="radio" name="awbl_global_backorder_behaviour" value="no" <?php checked( $global_behaviour, 'no' ); ?> />
							<span><?php esc_html_e( 'Do not allow backorders', 'advanced-woocommerce-backorders' ); ?></span>
						</label><br>
						<label>
							<input type="radio" name="awbl_global_backorder_behaviour" value="yes" <?php checked( $global_behaviour, 'yes' ); ?> />
							<span><?php esc_html_e( 'Allow backorders', 'advanced-woocommerce-backorders' ); ?></span>
						</label><br>
						<label>
							<input type="radio" name="awbl_global_backorder_behaviour" value="notify" <?php checked( $global_behaviour, 'notify' ); ?> />
							<span><?php esc_html_e( 'Allow backorders and notify customer', 'advanced-woocommerce-backorders' ); ?></span>
						</label>
					</fieldset>
					<p class="description" style="margin-top:6px;">
						<?php esc_html_e( 'Defines the global baseline behavior. Only actively applied if "Global Override" below is turned ON.', 'advanced-woocommerce-backorders' ); ?>
					</p>
				</td>
			</tr>

			<!-- Global Override -->
			<tr>
				<th scope="row">
					<label for="awbl_global_override"><?php esc_html_e( 'Global Override', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="hidden" name="field_present_awbl_global_override" value="1" />
					<label class="awbl-switch">
						<input type="checkbox" name="awbl_global_override" id="awbl_global_override" value="yes" <?php checked( $global_override, 'yes' ); ?> />
						<span class="awbl-slider round"></span>
					</label>
					<div class="awbl-info-box" style="margin-top:10px;">
						<p><strong><?php esc_html_e( 'When OFF:', 'advanced-woocommerce-backorders' ); ?></strong> <?php esc_html_e( 'Respect each product’s native WooCommerce backorder setting unless a product-specific rule or category rule is explicitly configured.', 'advanced-woocommerce-backorders' ); ?></p>
						<p><strong><?php esc_html_e( 'When ON:', 'advanced-woocommerce-backorders' ); ?></strong> <?php esc_html_e( 'Apply the selected global backorder rule to all products across the store, unless a product or category has a higher-priority custom rule.', 'advanced-woocommerce-backorders' ); ?></p>
					</div>
				</td>
			</tr>
		</table>

		<div class="awbl-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Save General Settings', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>
</form>

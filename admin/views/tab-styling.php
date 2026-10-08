<?php
/**
 * Tab 8: Styling & Custom CSS View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$bg_color     = AWBL_Settings::get( 'awbl_badge_bg_color', '#fff8e5' );
$text_color   = AWBL_Settings::get( 'awbl_badge_text_color', '#8a6d3b' );
$border_color = AWBL_Settings::get( 'awbl_badge_border_color', '#faebcc' );
$lead_color   = AWBL_Settings::get( 'awbl_lead_time_color', '#2d3748' );
$custom_css   = AWBL_Settings::get( 'awbl_custom_css', '' );
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="awbl-settings-form">
	<?php wp_nonce_field( 'awbl_save_settings_action', 'awbl_save_settings_nonce' ); ?>
	<input type="hidden" name="awbl_current_tab" value="styling" />

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Colors & Visual Appearance', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Adjust badge colors to blend seamlessly with your active WooCommerce theme palette.', 'advanced-woocommerce-backorders' ); ?></p>

		<!-- Visual Live Preview -->
		<div class="awbl-preview-box" style="margin-bottom:25px; padding:18px; border:1px solid #e2e8f0; border-radius:6px; background:#fafafa;">
			<h4 style="margin:0 0 10px 0; color:#4a5568;"><?php esc_html_e( 'Live Frontend Preview:', 'advanced-woocommerce-backorders' ); ?></h4>
			<div id="awbl-live-preview-badge" class="awbl-badge available-on-backorder" style="display:inline-block; padding:8px 14px; border-radius:4px; font-weight:600; font-size:14px; background:<?php echo esc_attr( $bg_color ); ?>; color:<?php echo esc_attr( $text_color ); ?>; border:1px solid <?php echo esc_attr( $border_color ); ?>;">
				<span class="awbl-icon">⏱</span> <span id="awbl-preview-text"><?php esc_html_e( 'Available on backorder', 'advanced-woocommerce-backorders' ); ?></span>
			</div>
			<div id="awbl-live-preview-lead" class="awbl-lead-time" style="margin-top:8px; font-size:13px; font-weight:500; color:<?php echo esc_attr( $lead_color ); ?>;">
				<span class="awbl-icon">🚚</span> <span><?php esc_html_e( 'Estimated dispatch: 2-3 weeks', 'advanced-woocommerce-backorders' ); ?></span>
			</div>
		</div>

		<table class="form-table awbl-form-table">
			<!-- Badge Background Color -->
			<tr>
				<th scope="row">
					<label for="awbl_badge_bg_color"><?php esc_html_e( 'Badge Background Color', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="color" name="awbl_badge_bg_color" id="awbl_badge_bg_color" value="<?php echo esc_attr( $bg_color ); ?>" class="awbl-color-input" />
					<code><?php echo esc_html( $bg_color ); ?></code>
				</td>
			</tr>

			<!-- Badge Text Color -->
			<tr>
				<th scope="row">
					<label for="awbl_badge_text_color"><?php esc_html_e( 'Badge Text Color', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="color" name="awbl_badge_text_color" id="awbl_badge_text_color" value="<?php echo esc_attr( $text_color ); ?>" class="awbl-color-input" />
					<code><?php echo esc_html( $text_color ); ?></code>
				</td>
			</tr>

			<!-- Badge Border Color -->
			<tr>
				<th scope="row">
					<label for="awbl_badge_border_color"><?php esc_html_e( 'Badge Border Color', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="color" name="awbl_badge_border_color" id="awbl_badge_border_color" value="<?php echo esc_attr( $border_color ); ?>" class="awbl-color-input" />
					<code><?php echo esc_html( $border_color ); ?></code>
				</td>
			</tr>

			<!-- Lead Time Text Color -->
			<tr>
				<th scope="row">
					<label for="awbl_lead_time_color"><?php esc_html_e( 'Lead Time Text Color', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<input type="color" name="awbl_lead_time_color" id="awbl_lead_time_color" value="<?php echo esc_attr( $lead_color ); ?>" class="awbl-color-input" />
					<code><?php echo esc_html( $lead_color ); ?></code>
				</td>
			</tr>
		</table>
	</div>

	<div class="awbl-card">
		<h2><?php esc_html_e( 'Custom CSS', 'advanced-woocommerce-backorders' ); ?></h2>
		<p class="awbl-card-desc"><?php esc_html_e( 'Add custom CSS rules to adjust layout, typography, and theme styling. HTML tags are automatically stripped for security.', 'advanced-woocommerce-backorders' ); ?></p>

		<table class="form-table awbl-form-table">
			<tr>
				<th scope="row">
					<label for="awbl_custom_css"><?php esc_html_e( 'CSS Stylesheet', 'advanced-woocommerce-backorders' ); ?></label>
				</th>
				<td>
					<textarea name="awbl_custom_css" id="awbl_custom_css" rows="10" class="large-text code" placeholder=".available-on-backorder { font-size: 15px; }&#10;.awbl-lead-time { font-style: italic; }"><?php echo esc_textarea( $custom_css ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Available CSS selector targets: .awbl-wrapper, .available-on-backorder, .awbl-lead-time, .awbl-badge, .awbl-icon', 'advanced-woocommerce-backorders' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<div class="awbl-form-actions">
			<button type="submit" class="button button-primary button-large">
				<?php esc_html_e( 'Save Styling Settings', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>
</form>

<?php
/**
 * Tab 10: System Status & Diagnostic Logs View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $wp_version;

$wc_version    = defined( 'WC_VERSION' ) ? WC_VERSION : __( 'Not detected', 'advanced-woocommerce-backorders' );
$php_version   = phpversion();
$hpos_active   = ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) ? __( 'Enabled (Active)', 'advanced-woocommerce-backorders' ) : __( 'Disabled (Classic Posts)', 'advanced-woocommerce-backorders' );
$wc_manage_stock = ( 'yes' === get_option( 'woocommerce_manage_stock', 'yes' ) ) ? __( 'Yes', 'advanced-woocommerce-backorders' ) : __( 'No', 'advanced-woocommerce-backorders' );
$theme_info    = wp_get_theme();
$active_theme  = $theme_info->get( 'Name' ) . ' v' . $theme_info->get( 'Version' );
$recent_logs   = AWBL_Logger::get_recent_logs( 60 );
?>

<div class="awbl-card">
	<h2><?php esc_html_e( 'System & Environment Diagnostics', 'advanced-woocommerce-backorders' ); ?></h2>
	<p class="awbl-card-desc"><?php esc_html_e( 'Review compatibility and operating parameters for troubleshooting support.', 'advanced-woocommerce-backorders' ); ?></p>

	<table class="widefat striped" style="max-width:800px; border-radius:4px; overflow:hidden;">
		<tbody>
			<tr>
				<td style="width:240px; font-weight:600;"><?php esc_html_e( 'Plugin Version', 'advanced-woocommerce-backorders' ); ?></td>
				<td><code><?php echo esc_html( AWBL_VERSION ); ?></code></td>
			</tr>
			<tr>
				<td style="font-weight:600;"><?php esc_html_e( 'WordPress Version', 'advanced-woocommerce-backorders' ); ?></td>
				<td><code><?php echo esc_html( $wp_version ); ?></code></td>
			</tr>
			<tr>
				<td style="font-weight:600;"><?php esc_html_e( 'WooCommerce Version', 'advanced-woocommerce-backorders' ); ?></td>
				<td><code><?php echo esc_html( $wc_version ); ?></code></td>
			</tr>
			<tr>
				<td style="font-weight:600;"><?php esc_html_e( 'PHP Version', 'advanced-woocommerce-backorders' ); ?></td>
				<td><code><?php echo esc_html( $php_version ); ?></code></td>
			</tr>
			<tr>
				<td style="font-weight:600;"><?php esc_html_e( 'WooCommerce HPOS (Custom Orders Table)', 'advanced-woocommerce-backorders' ); ?></td>
				<td>
					<span class="dashicons dashicons-yes-alt" style="color:#16a34a; vertical-align:text-bottom;"></span>
					<strong><?php echo esc_html( $hpos_active ); ?></strong>
				</td>
			</tr>
			<tr>
				<td style="font-weight:600;"><?php esc_html_e( 'WooCommerce Manage Stock (Global)', 'advanced-woocommerce-backorders' ); ?></td>
				<td><code><?php echo esc_html( $wc_manage_stock ); ?></code></td>
			</tr>
			<tr>
				<td style="font-weight:600;"><?php esc_html_e( 'Active Theme', 'advanced-woocommerce-backorders' ); ?></td>
				<td><?php echo esc_html( $active_theme ); ?></td>
			</tr>
			<tr>
				<td style="font-weight:600;"><?php esc_html_e( 'Issue #39 Compatibility Shield', 'advanced-woocommerce-backorders' ); ?></td>
				<td>
					<span class="dashicons dashicons-shield" style="color:#2563eb; vertical-align:text-bottom;"></span>
					<strong><?php esc_html_e( 'Active (Guaranteed hook & availability filter fallback)', 'advanced-woocommerce-backorders' ); ?></strong>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div class="awbl-card">
	<div class="awbl-card-header-flex">
		<div>
			<h2><?php esc_html_e( 'Debug & Resolution Logs', 'advanced-woocommerce-backorders' ); ?></h2>
			<p class="awbl-card-desc"><?php esc_html_e( 'Recent log entries from WooCommerce logger (source: advanced-woocommerce-backorders). Enable in Tab 9 Tools to record events.', 'advanced-woocommerce-backorders' ); ?></p>
		</div>
		<div>
			<button type="button" class="button button-secondary" id="awbl-btn-clear-logs">
				<span class="dashicons dashicons-trash" style="vertical-align:text-bottom;"></span>
				<?php esc_html_e( 'Clear Log File', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>

	<div style="margin-top:10px;">
		<textarea id="awbl-log-viewer" readonly rows="16" class="large-text code" style="background:#1e293b; color:#f1f5f9; font-size:12px; font-family:monospace; line-height:1.5; padding:12px; border-radius:4px;"><?php echo esc_textarea( $recent_logs ); ?></textarea>
	</div>
</div>

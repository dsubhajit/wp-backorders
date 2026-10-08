<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Main Admin View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$dashboard_counts = AWBL_Admin::get_dashboard_counts();
?>
<div class="wrap awbl-admin-wrap">
	<div class="awbl-admin-header">
		<div class="awbl-header-title">
			<h1>
				<span class="dashicons dashicons-backup" style="font-size:32px; width:32px; height:32px; vertical-align:middle; margin-right:8px; color:#2271b1;"></span>
				<?php esc_html_e( 'Advanced WooCommerce Backorders & Lead Times', 'advanced-woocommerce-backorders' ); ?>
				<span class="awbl-version-badge">v<?php echo esc_html( AWBL_VERSION ); ?></span>
			</h1>
			<p class="awbl-header-sub">
				<?php esc_html_e( 'Professional management for WooCommerce backorders, dynamic lead times, multi-tier rule prioritization, and customized notifications.', 'advanced-woocommerce-backorders' ); ?>
			</p>
		</div>

		<!-- Dashboard Quick Counter Stats -->
		<div class="awbl-stats-cards">
			<div class="awbl-stat-card">
				<div class="awbl-stat-num"><?php echo esc_html( $dashboard_counts['on_backorder'] ); ?></div>
				<div class="awbl-stat-label"><?php esc_html_e( 'Products on Backorder', 'advanced-woocommerce-backorders' ); ?></div>
			</div>
			<div class="awbl-stat-card">
				<div class="awbl-stat-num"><?php echo esc_html( $dashboard_counts['allowed_backorders'] ); ?></div>
				<div class="awbl-stat-label"><?php esc_html_e( 'Allowing Backorders', 'advanced-woocommerce-backorders' ); ?></div>
			</div>
			<div class="awbl-stat-card">
				<div class="awbl-stat-num"><?php echo esc_html( $dashboard_counts['with_lead_time'] ); ?></div>
				<div class="awbl-stat-label"><?php esc_html_e( 'With Lead Times', 'advanced-woocommerce-backorders' ); ?></div>
			</div>
			<div class="awbl-stat-card">
				<div class="awbl-stat-num"><?php echo esc_html( $dashboard_counts['category_rules'] ); ?></div>
				<div class="awbl-stat-label"><?php esc_html_e( 'Category Rules', 'advanced-woocommerce-backorders' ); ?></div>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible" style="margin-top:15px;">
			<p><?php esc_html_e( 'Settings updated successfully.', 'advanced-woocommerce-backorders' ); ?></p>
		</div>
	<?php endif; ?>

	<!-- Admin Tabs Navigation -->
	<nav class="nav-tab-wrapper awbl-nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_key => $tab_title ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=awbl-settings&tab=' . $tab_key ) ); ?>" 
			   class="nav-tab <?php echo ( $current_tab === $tab_key ) ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $tab_title ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<!-- Tab Content Container -->
	<div class="awbl-tab-content-container">
		<?php
		$tab_file = AWBL_PLUGIN_DIR . 'admin/views/tab-' . str_replace( '_', '-', $current_tab ) . '.php';
		if ( file_exists( $tab_file ) ) {
			include $tab_file;
		} else {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Tab content not found.', 'advanced-woocommerce-backorders' ) . '</p></div>';
		}
		?>
	</div>
</div>

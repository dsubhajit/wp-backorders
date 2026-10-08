<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Uninstall Routine
 *
 * Fired when the plugin is deleted via the WordPress admin plugins screen.
 *
 * @package AWBL
 * @since   1.0.0
 */

// If uninstall not called from WordPress, exit
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Check if administrator explicitly opted to delete data on uninstall
$delete_data = get_option( 'awbl_delete_data_on_uninstall', 'no' );

if ( 'yes' === $delete_data ) {
	// List of all plugin options to delete
	$options = array(
		'awbl_enable_plugin',
		'awbl_enable_management',
		'awbl_global_backorder_behaviour',
		'awbl_global_override',
		'awbl_category_overrides_global',
		'awbl_treat_zero_stock_as_backorder',
		'awbl_hide_add_to_cart_if_no_backorder',
		'awbl_display_lead_time_single',
		'awbl_display_lead_time_cart',
		'awbl_display_lead_time_checkout',
		'awbl_display_lead_time_archive',
		'awbl_display_lead_time_order',
		'awbl_display_lead_time_email',
		'awbl_lead_time_for_instock',
		'awbl_lead_time_for_outofstock',
		'awbl_lead_time_for_backorder',
		'awbl_enable_stock_status_lead_times',
		'awbl_lead_time_instock',
		'awbl_lead_time_lowstock',
		'awbl_lead_time_outofstock',
		'awbl_lead_time_backorder',
		'awbl_global_default_lead_time',
		'awbl_single_product_placement',
		'awbl_single_product_hook_priority',
		'awbl_archive_placement',
		'awbl_archive_hook_priority',
		'awbl_cart_placement',
		'awbl_badge_style',
		'awbl_show_lead_time_icon',
		'awbl_category_rules',
		'awbl_msg_product',
		'awbl_msg_cart',
		'awbl_msg_checkout',
		'awbl_msg_checkout_item',
		'awbl_msg_order',
		'awbl_msg_email',
		'awbl_lead_time_prefix',
		'awbl_badge_bg_color',
		'awbl_badge_text_color',
		'awbl_badge_border_color',
		'awbl_lead_time_color',
		'awbl_custom_css',
		'awbl_enable_debug_log',
		'awbl_delete_data_on_uninstall',
		'awbl_email_customer_include',
		'awbl_email_admin_include',
	);

	foreach ( $options as $option_name ) {
		delete_option( $option_name );
	}

	// NOTE: We NEVER delete WooCommerce products, inventory stocks, or customer orders.
}

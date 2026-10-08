<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - AJAX Controller
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Ajax Class
 */
class AWBL_Ajax {

	/**
	 * Nonce action name.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'awbl_admin_nonce';

	/**
	 * Initialize AJAX hooks.
	 */
	public static function init() {
		add_action( 'wp_ajax_awbl_save_category_rule', array( __CLASS__, 'ajax_save_category_rule' ) );
		add_action( 'wp_ajax_awbl_delete_category_rule', array( __CLASS__, 'ajax_delete_category_rule' ) );
		add_action( 'wp_ajax_awbl_toggle_category_rule', array( __CLASS__, 'ajax_toggle_category_rule' ) );
		add_action( 'wp_ajax_awbl_bulk_update_lead_time', array( __CLASS__, 'ajax_bulk_update_lead_time' ) );
		add_action( 'wp_ajax_awbl_export_settings', array( __CLASS__, 'ajax_export_settings' ) );
		add_action( 'wp_ajax_awbl_import_settings', array( __CLASS__, 'ajax_import_settings' ) );
		add_action( 'wp_ajax_awbl_reset_settings', array( __CLASS__, 'ajax_reset_settings' ) );
		add_action( 'wp_ajax_awbl_clear_logs', array( __CLASS__, 'ajax_clear_logs' ) );
	}

	/**
	 * Verify request permissions and nonce.
	 */
	private static function check_permission() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'advanced-woocommerce-backorders' ) ), 403 );
		}
	}

	/**
	 * AJAX: Save or update category rule.
	 */
	public static function ajax_save_category_rule() {
		self::check_permission();

		$data = array(
			'id'          => isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '',
			'category_id' => isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0,
			'backorders'  => isset( $_POST['backorders'] ) ? sanitize_text_field( wp_unslash( $_POST['backorders'] ) ) : 'wc',
			'lead_time'   => isset( $_POST['lead_time'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_time'] ) ) : '',
			'message'     => isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '',
			'priority'    => isset( $_POST['priority'] ) ? intval( wp_unslash( $_POST['priority'] ) ) : 10,
			'status'      => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active',
		);

		$res = AWBL_Category_Rules::save( $data );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Category rule saved successfully.', 'advanced-woocommerce-backorders' ),
				'rule_id' => $res,
				'rules'   => AWBL_Category_Rules::get_all(),
			)
		);
	}

	/**
	 * AJAX: Delete category rule.
	 */
	public static function ajax_delete_category_rule() {
		self::check_permission();

		$rule_id = isset( $_POST['rule_id'] ) ? sanitize_key( wp_unslash( $_POST['rule_id'] ) ) : '';
		if ( empty( $rule_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid rule ID.', 'advanced-woocommerce-backorders' ) ) );
		}

		if ( AWBL_Category_Rules::delete( $rule_id ) ) {
			wp_send_json_success( array( 'message' => __( 'Rule deleted successfully.', 'advanced-woocommerce-backorders' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Rule could not be found.', 'advanced-woocommerce-backorders' ) ) );
		}
	}

	/**
	 * AJAX: Toggle category rule active/inactive.
	 */
	public static function ajax_toggle_category_rule() {
		self::check_permission();

		$rule_id = isset( $_POST['rule_id'] ) ? sanitize_key( wp_unslash( $_POST['rule_id'] ) ) : '';
		if ( empty( $rule_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid rule ID.', 'advanced-woocommerce-backorders' ) ) );
		}

		$new_status = AWBL_Category_Rules::toggle_status( $rule_id );
		if ( false !== $new_status ) {
			wp_send_json_success(
				array(
					'message'    => __( 'Rule status updated.', 'advanced-woocommerce-backorders' ),
					'new_status' => $new_status,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Could not update rule status.', 'advanced-woocommerce-backorders' ) ) );
		}
	}

	/**
	 * AJAX: Bulk update lead times for selected products.
	 */
	public static function ajax_bulk_update_lead_time() {
		self::check_permission();

		$product_ids = isset( $_POST['product_ids'] ) ? array_map( 'absint', (array) $_POST['product_ids'] ) : array();
		$lead_time   = isset( $_POST['lead_time'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_time'] ) ) : '';
		$action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : 'set'; // 'set' or 'clear'

		if ( empty( $product_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No products selected.', 'advanced-woocommerce-backorders' ) ) );
		}

		$updated = 0;
		foreach ( $product_ids as $pid ) {
			if ( 'clear' === $action_type ) {
				delete_post_meta( $pid, '_awbl_lead_time' );
			} else {
				update_post_meta( $pid, '_awbl_enable_custom', 'yes' );
				update_post_meta( $pid, '_awbl_lead_time_type', 'text' );
				update_post_meta( $pid, '_awbl_lead_time', $lead_time );
			}
			$updated++;
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: updated product count */
					_n( '%d product updated.', '%d products updated.', $updated, 'advanced-woocommerce-backorders' ),
					$updated
				),
				'count'   => $updated,
			)
		);
	}

	/**
	 * AJAX: Export settings as JSON download payload.
	 */
	public static function ajax_export_settings() {
		self::check_permission();

		$data = AWBL_Settings::export_settings();
		wp_send_json_success(
			array(
				'data'     => $data,
				'filename' => 'awbl-settings-' . gmdate( 'Y-m-d' ) . '.json',
			)
		);
	}

	/**
	 * AJAX: Import settings from JSON.
	 */
	public static function ajax_import_settings() {
		self::check_permission();

		$json_str = isset( $_POST['settings_json'] ) ? wp_unslash( $_POST['settings_json'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( empty( $json_str ) ) {
			wp_send_json_error( array( 'message' => __( 'No JSON data received.', 'advanced-woocommerce-backorders' ) ) );
		}

		$decoded = json_decode( $json_str, true );
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid JSON format.', 'advanced-woocommerce-backorders' ) ) );
		}

		$result = AWBL_Settings::import_settings( $decoded );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Settings imported successfully!', 'advanced-woocommerce-backorders' ) ) );
	}

	/**
	 * AJAX: Reset settings to default.
	 */
	public static function ajax_reset_settings() {
		self::check_permission();

		AWBL_Settings::reset_defaults();
		wp_send_json_success( array( 'message' => __( 'All settings have been restored to defaults.', 'advanced-woocommerce-backorders' ) ) );
	}

	/**
	 * AJAX: Clear debug log file.
	 */
	public static function ajax_clear_logs() {
		self::check_permission();

		if ( AWBL_Logger::clear_logs() ) {
			wp_send_json_success( array( 'message' => __( 'Log file cleared successfully.', 'advanced-woocommerce-backorders' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Could not clear log file.', 'advanced-woocommerce-backorders' ) ) );
		}
	}
}

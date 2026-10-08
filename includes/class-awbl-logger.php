<?php
/**
 * Advanced WooCommerce Backorders & Lead Times - Logger Class
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * AWBL_Logger Class
 */
class AWBL_Logger {

	/**
	 * Logger instance.
	 *
	 * @var WC_Logger|null
	 */
	private static $logger = null;

	/**
	 * Log context / source.
	 *
	 * @var string
	 */
	private static $source = 'advanced-woocommerce-backorders';

	/**
	 * Get WooCommerce Logger instance.
	 *
	 * @return WC_Logger|null
	 */
	public static function get_logger() {
		if ( null === self::$logger && function_exists( 'wc_get_logger' ) ) {
			self::$logger = wc_get_logger();
		}
		return self::$logger;
	}

	/**
	 * Check if debug logging is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$enabled = get_option( 'awbl_enable_debug_log', 'no' );
		return 'yes' === $enabled;
	}

	/**
	 * Log a message if debug logging is enabled.
	 *
	 * @param string $message Log message.
	 * @param string $level   Level: debug, info, notice, warning, error, critical, alert, emergency.
	 * @param array  $context Additional context.
	 */
	public static function log( $message, $level = 'debug', $context = array() ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		$logger = self::get_logger();
		if ( ! $logger ) {
			return;
		}

		$log_entry = '[AWBL] ' . $message;
		if ( ! empty( $context ) ) {
			// Redact any sensitive customer keys if present
			$safe_context = self::sanitize_context( $context );
			$log_entry   .= ' | Context: ' . wp_json_encode( $safe_context );
		}

		$logger->log( $level, $log_entry, array( 'source' => self::$source ) );
	}

	/**
	 * Helper for rule resolution logging.
	 *
	 * @param int    $product_id   Product ID.
	 * @param string $rule_source  Source (product, category, global, native).
	 * @param string $lead_time    Resolved lead time.
	 * @param string $stock_status Stock status.
	 * @param bool   $backorder_ok Whether backorders are allowed.
	 */
	public static function log_resolution( $product_id, $rule_source, $lead_time, $stock_status, $backorder_ok ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		self::log(
			sprintf(
				'Resolution for Product #%d: Source="%s", LeadTime="%s", StockStatus="%s", BackordersAllowed=%s',
				$product_id,
				$rule_source,
				$lead_time,
				$stock_status,
				$backorder_ok ? 'YES' : 'NO'
			),
			'info',
			array(
				'product_id'   => $product_id,
				'rule_source'  => $rule_source,
				'lead_time'    => $lead_time,
				'stock_status' => $stock_status,
			)
		);
	}

	/**
	 * Sanitize context array to avoid leaking sensitive information.
	 *
	 * @param array $context Raw context.
	 * @return array Sanitized context.
	 */
	private static function sanitize_context( $context ) {
		if ( ! is_array( $context ) ) {
			return array();
		}

		$sensitive_keys = array( 'password', 'token', 'secret', 'credit_card', 'cc', 'cvv', 'card_number' );
		foreach ( $context as $k => $v ) {
			if ( in_array( strtolower( $k ), $sensitive_keys, true ) ) {
				$context[ $k ] = '***REDACTED***';
			} elseif ( is_array( $v ) ) {
				$context[ $k ] = self::sanitize_context( $v );
			}
		}
		return $context;
	}

	/**
	 * Get recent log content for admin display.
	 *
	 * @param int $lines Number of lines.
	 * @return string
	 */
	public static function get_recent_logs( $lines = 100 ) {
		if ( ! function_exists( 'wc_get_log_file_path' ) ) {
			return __( 'Log system not available.', 'advanced-woocommerce-backorders' );
		}

		$log_path = wc_get_log_file_path( self::$source );
		if ( ! file_exists( $log_path ) || ! is_readable( $log_path ) ) {
			return __( 'No log entries found.', 'advanced-woocommerce-backorders' );
		}

		$file_content = file_get_contents( $log_path );
		if ( false === $file_content ) {
			return __( 'Unable to read log file.', 'advanced-woocommerce-backorders' );
		}

		$log_lines = explode( "\n", trim( $file_content ) );
		$total     = count( $log_lines );
		$offset    = max( 0, $total - $lines );
		$slice     = array_slice( $log_lines, $offset );

		return implode( "\n", $slice );
	}

	/**
	 * Clear current log file.
	 *
	 * @return bool
	 */
	public static function clear_logs() {
		if ( ! function_exists( 'wc_get_log_file_path' ) ) {
			return false;
		}

		$log_path = wc_get_log_file_path( self::$source );
		if ( file_exists( $log_path ) && is_writable( $log_path ) ) {
			return ( false !== file_put_contents( $log_path, '' ) );
		}
		return false;
	}
}

<?php
/**
 * Tab 6: Category Rules View
 *
 * @package AWBL
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$rules = AWBL_Category_Rules::get_all( false );

// Fetch product categories for selector
$categories = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	)
);
?>

<div class="awbl-card">
	<div class="awbl-card-header-flex">
		<div>
			<h2><?php esc_html_e( 'Category-Specific Backorder & Lead Time Rules', 'advanced-woocommerce-backorders' ); ?></h2>
			<p class="awbl-card-desc">
				<?php esc_html_e( 'Category rules automatically apply to all products in that category unless a product has a custom product-level rule (#1).', 'advanced-woocommerce-backorders' ); ?>
			</p>
		</div>
		<div>
			<button type="button" class="button button-primary button-large" id="awbl-btn-add-cat-rule">
				<span class="dashicons dashicons-plus-alt2" style="vertical-align:text-bottom;"></span>
				<?php esc_html_e( 'Add Category Rule', 'advanced-woocommerce-backorders' ); ?>
			</button>
		</div>
	</div>

	<!-- Rules Table -->
	<table class="wp-list-table widefat fixed striped table-view-list awbl-table" id="awbl-category-rules-table">
		<thead>
			<tr>
				<th style="width:200px;"><?php esc_html_e( 'Category', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:140px;"><?php esc_html_e( 'Backorders', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:150px;"><?php esc_html_e( 'Lead Time', 'advanced-woocommerce-backorders' ); ?></th>
				<th><?php esc_html_e( 'Message', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:80px; text-align:center;"><?php esc_html_e( 'Priority', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:90px; text-align:center;"><?php esc_html_e( 'Status', 'advanced-woocommerce-backorders' ); ?></th>
				<th style="width:130px; text-align:right;"><?php esc_html_e( 'Actions', 'advanced-woocommerce-backorders' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $rules ) ) : ?>
				<?php
				foreach ( $rules as $rule_id => $rule ) :
					$cat_term = get_term( $rule['category_id'], 'product_cat' );
					$cat_name = ( $cat_term && ! is_wp_error( $cat_term ) ) ? $cat_term->name : sprintf( __( 'Category #%d (Deleted)', 'advanced-woocommerce-backorders' ), $rule['category_id'] );
					$status   = isset( $rule['status'] ) ? $rule['status'] : 'active';
					$is_active = ( 'active' === $status );
					$b_mode   = isset( $rule['backorders'] ) ? $rule['backorders'] : 'wc';

					$b_label = __( 'Use WC Default', 'advanced-woocommerce-backorders' );
					$b_pill  = 'awbl-pill-wc';
					if ( 'yes' === $b_mode ) {
						$b_label = __( 'Allowed', 'advanced-woocommerce-backorders' );
						$b_pill  = 'awbl-pill-yes';
					} elseif ( 'notify' === $b_mode ) {
						$b_label = __( 'Allow + Notify', 'advanced-woocommerce-backorders' );
						$b_pill  = 'awbl-pill-notify';
					} elseif ( 'no' === $b_mode ) {
						$b_label = __( 'Do Not Allow', 'advanced-woocommerce-backorders' );
						$b_pill  = 'awbl-pill-no';
					}
					?>
					<tr id="awbl-rule-row-<?php echo esc_attr( $rule_id ); ?>" class="<?php echo $is_active ? '' : 'awbl-row-inactive'; ?>">
						<td>
							<strong><?php echo esc_html( $cat_name ); ?></strong>
						</td>
						<td>
							<span class="awbl-status-pill <?php echo esc_attr( $b_pill ); ?>"><?php echo esc_html( $b_label ); ?></span>
						</td>
						<td>
							<?php echo ! empty( $rule['lead_time'] ) ? esc_html( $rule['lead_time'] ) : '<span style="color:#aaa;">—</span>'; ?>
						</td>
						<td>
							<?php echo ! empty( $rule['message'] ) ? esc_html( $rule['message'] ) : '<span style="color:#aaa;">(Default message)</span>'; ?>
						</td>
						<td style="text-align:center;">
							<?php echo esc_html( isset( $rule['priority'] ) ? $rule['priority'] : 10 ); ?>
						</td>
						<td style="text-align:center;">
							<button type="button" class="button button-small awbl-btn-toggle-rule <?php echo $is_active ? 'button-primary' : 'button-secondary'; ?>" data-id="<?php echo esc_attr( $rule_id ); ?>">
								<?php echo $is_active ? esc_html__( 'Active', 'advanced-woocommerce-backorders' ) : esc_html__( 'Inactive', 'advanced-woocommerce-backorders' ); ?>
							</button>
						</td>
						<td style="text-align:right;">
							<button type="button" class="button button-small awbl-btn-edit-rule" 
								data-id="<?php echo esc_attr( $rule_id ); ?>"
								data-cat="<?php echo esc_attr( $rule['category_id'] ); ?>"
								data-bo="<?php echo esc_attr( $b_mode ); ?>"
								data-lt="<?php echo esc_attr( isset( $rule['lead_time'] ) ? $rule['lead_time'] : '' ); ?>"
								data-msg="<?php echo esc_attr( isset( $rule['message'] ) ? $rule['message'] : '' ); ?>"
								data-pri="<?php echo esc_attr( isset( $rule['priority'] ) ? $rule['priority'] : 10 ); ?>"
								data-status="<?php echo esc_attr( $status ); ?>">
								<?php esc_html_e( 'Edit', 'advanced-woocommerce-backorders' ); ?>
							</button>
							<button type="button" class="button button-small button-link-delete awbl-btn-del-rule" data-id="<?php echo esc_attr( $rule_id ); ?>">
								<?php esc_html_e( 'Delete', 'advanced-woocommerce-backorders' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr id="awbl-no-rules-row">
					<td colspan="7" style="text-align:center; padding:25px; color:#666;">
						<?php esc_html_e( 'No category rules created yet. Click "Add Category Rule" to set up category-specific backorders or lead times.', 'advanced-woocommerce-backorders' ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<!-- Add/Edit Category Rule Modal -->
<div id="awbl-category-rule-modal" class="awbl-modal-backdrop" style="display:none;">
	<div class="awbl-modal">
		<div class="awbl-modal-header">
			<h3 id="awbl-modal-title"><?php esc_html_e( 'Add Category Rule', 'advanced-woocommerce-backorders' ); ?></h3>
			<button type="button" class="awbl-modal-close">&times;</button>
		</div>
		<form id="awbl-category-rule-form">
			<input type="hidden" name="rule_id" id="awbl_form_rule_id" value="" />

			<div class="awbl-modal-body">
				<p class="form-field">
					<label for="awbl_form_cat_id"><strong><?php esc_html_e( 'Product Category', 'advanced-woocommerce-backorders' ); ?></strong> <span class="required">*</span></label>
					<select name="category_id" id="awbl_form_cat_id" required style="width:100%;">
						<option value=""><?php esc_html_e( '— Select Category —', 'advanced-woocommerce-backorders' ); ?></option>
						<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
							<?php foreach ( $categories as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?> products)</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</p>

				<p class="form-field">
					<label for="awbl_form_backorders"><strong><?php esc_html_e( 'Backorder Behavior', 'advanced-woocommerce-backorders' ); ?></strong></label>
					<select name="backorders" id="awbl_form_backorders" style="width:100%;">
						<option value="wc"><?php esc_html_e( 'Use WooCommerce Product Default', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="yes"><?php esc_html_e( 'Allow backorders', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="notify"><?php esc_html_e( 'Allow backorders and notify customer', 'advanced-woocommerce-backorders' ); ?></option>
						<option value="no"><?php esc_html_e( 'Do not allow backorders', 'advanced-woocommerce-backorders' ); ?></option>
					</select>
				</p>

				<p class="form-field">
					<label for="awbl_form_lead_time"><strong><?php esc_html_e( 'Lead Time', 'advanced-woocommerce-backorders' ); ?></strong></label>
					<input type="text" name="lead_time" id="awbl_form_lead_time" placeholder="<?php esc_attr_e( 'e.g. 2-3 weeks', 'advanced-woocommerce-backorders' ); ?>" style="width:100%;" />
					<span class="description"><?php esc_html_e( 'Example: 2-3 weeks, 3-4 business days', 'advanced-woocommerce-backorders' ); ?></span>
				</p>

				<p class="form-field">
					<label for="awbl_form_message"><strong><?php esc_html_e( 'Custom Backorder Message (Optional)', 'advanced-woocommerce-backorders' ); ?></strong></label>
					<input type="text" name="message" id="awbl_form_message" placeholder="<?php esc_attr_e( 'Available on backorder', 'advanced-woocommerce-backorders' ); ?>" style="width:100%;" />
					<span class="description"><?php esc_html_e( 'Leave empty to inherit standard backorder message.', 'advanced-woocommerce-backorders' ); ?></span>
				</p>

				<div style="display:flex; gap:15px;">
					<p class="form-field" style="flex:1;">
						<label for="awbl_form_priority"><strong><?php esc_html_e( 'Rule Priority', 'advanced-woocommerce-backorders' ); ?></strong></label>
						<input type="number" name="priority" id="awbl_form_priority" value="10" min="1" max="99" style="width:100%;" />
						<span class="description"><?php esc_html_e( 'Lower number = higher priority.', 'advanced-woocommerce-backorders' ); ?></span>
					</p>

					<p class="form-field" style="flex:1;">
						<label for="awbl_form_status"><strong><?php esc_html_e( 'Status', 'advanced-woocommerce-backorders' ); ?></strong></label>
						<select name="status" id="awbl_form_status" style="width:100%;">
							<option value="active"><?php esc_html_e( 'Active', 'advanced-woocommerce-backorders' ); ?></option>
							<option value="inactive"><?php esc_html_e( 'Inactive', 'advanced-woocommerce-backorders' ); ?></option>
						</select>
					</p>
				</div>
			</div>

			<div class="awbl-modal-footer">
				<button type="button" class="button button-secondary awbl-modal-cancel"><?php esc_html_e( 'Cancel', 'advanced-woocommerce-backorders' ); ?></button>
				<button type="submit" class="button button-primary" id="awbl-modal-save-btn"><?php esc_html_e( 'Save Rule', 'advanced-woocommerce-backorders' ); ?></button>
			</div>
		</form>
	</div>
</div>

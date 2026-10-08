/**
 * Advanced WooCommerce Backorders & Lead Times - Admin JavaScript
 *
 * @package AWBL
 * @since   1.0.0
 */

jQuery(document).ready(function($) {
	'use strict';

	var params = window.AWBL_Admin_Params || {};
	var ajaxUrl = params.ajax_url || '';
	var nonce = params.nonce || '';
	var i18n = params.i18n || {};

	/* ==========================================================================
	   1. Category Rules Modal & AJAX CRUD
	   ========================================================================== */
	var $catModal = $('#awbl-category-rule-modal');
	var $catForm  = $('#awbl-category-rule-form');

	// Open Add Modal
	$('#awbl-btn-add-cat-rule').on('click', function(e) {
		e.preventDefault();
		$('#awbl-modal-title').text('Add Category Rule');
		$('#awbl_form_rule_id').val('');
		$('#awbl_form_cat_id').val('');
		$('#awbl_form_backorders').val('wc');
		$('#awbl_form_lead_time').val('');
		$('#awbl_form_message').val('');
		$('#awbl_form_priority').val('10');
		$('#awbl_form_status').val('active');
		$catModal.fadeIn(150);
	});

	// Close Modal
	$('.awbl-modal-close, .awbl-modal-cancel').on('click', function(e) {
		e.preventDefault();
		$catModal.fadeOut(150);
	});

	// Edit Rule Click
	$(document).on('click', '.awbl-btn-edit-rule', function(e) {
		e.preventDefault();
		var $btn = $(this);
		$('#awbl-modal-title').text('Edit Category Rule');
		$('#awbl_form_rule_id').val($btn.data('id'));
		$('#awbl_form_cat_id').val($btn.data('cat'));
		$('#awbl_form_backorders').val($btn.data('bo'));
		$('#awbl_form_lead_time').val($btn.data('lt'));
		$('#awbl_form_message').val($btn.data('msg'));
		$('#awbl_form_priority').val($btn.data('pri'));
		$('#awbl_form_status').val($btn.data('status'));
		$catModal.fadeIn(150);
	});

	// Save Category Rule Form Submit
	$catForm.on('submit', function(e) {
		e.preventDefault();
		var $submitBtn = $('#awbl-modal-save-btn');
		$submitBtn.prop('disabled', true).text('Saving...');

		var postData = {
			action: 'awbl_save_category_rule',
			nonce: nonce,
			id: $('#awbl_form_rule_id').val(),
			category_id: $('#awbl_form_cat_id').val(),
			backorders: $('#awbl_form_backorders').val(),
			lead_time: $('#awbl_form_lead_time').val(),
			message: $('#awbl_form_message').val(),
			priority: $('#awbl_form_priority').val(),
			status: $('#awbl_form_status').val()
		};

		$.post(ajaxUrl, postData, function(response) {
			$submitBtn.prop('disabled', false).text('Save Rule');
			if (response.success) {
				$catModal.fadeOut(150);
				window.location.reload();
			} else {
				alert(response.data && response.data.message ? response.data.message : i18n.error);
			}
		}).fail(function() {
			$submitBtn.prop('disabled', false).text('Save Rule');
			alert(i18n.error);
		});
	});

	// Delete Category Rule
	$(document).on('click', '.awbl-btn-del-rule', function(e) {
		e.preventDefault();
		if (!confirm(i18n.confirm_delete)) {
			return;
		}

		var ruleId = $(this).data('id');
		var $row = $('#awbl-rule-row-' + ruleId);

		$.post(ajaxUrl, {
			action: 'awbl_delete_category_rule',
			nonce: nonce,
			rule_id: ruleId
		}, function(response) {
			if (response.success) {
				$row.fadeOut(250, function() {
					$(this).remove();
				});
			} else {
				alert(response.data && response.data.message ? response.data.message : i18n.error);
			}
		});
	});

	// Toggle Category Rule Active/Inactive
	$(document).on('click', '.awbl-btn-toggle-rule', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var ruleId = $btn.data('id');
		var $row = $('#awbl-rule-row-' + ruleId);

		$.post(ajaxUrl, {
			action: 'awbl_toggle_category_rule',
			nonce: nonce,
			rule_id: ruleId
		}, function(response) {
			if (response.success) {
				var newStatus = response.data.new_status;
				if (newStatus === 'active') {
					$btn.removeClass('button-secondary').addClass('button-primary').text('Active');
					$row.removeClass('awbl-row-inactive');
				} else {
					$btn.removeClass('button-primary').addClass('button-secondary').text('Inactive');
					$row.addClass('awbl-row-inactive');
				}
			}
		});
	});

	/* ==========================================================================
	   2. Bulk Lead Time Actions (Tab 5)
	   ========================================================================== */
	$('#awbl-select-all-products').on('change', function() {
		$('.awbl-product-cb').prop('checked', $(this).is(':checked'));
	});

	function getSelectedProductIds() {
		var ids = [];
		$('.awbl-product-cb:checked').each(function() {
			ids.push($(this).val());
		});
		return ids;
	}

	$('#awbl-btn-bulk-set-lt').on('click', function(e) {
		e.preventDefault();
		var ids = getSelectedProductIds();
		var leadTime = $('#awbl_bulk_lead_time_input').val();

		if (ids.length === 0) {
			alert('Please select at least one product.');
			return;
		}

		var $status = $('#awbl-bulk-status-msg');
		$status.css('color', '#2563eb').text('Updating...');

		$.post(ajaxUrl, {
			action: 'awbl_bulk_update_lead_time',
			nonce: nonce,
			product_ids: ids,
			lead_time: leadTime,
			action_type: 'set'
		}, function(response) {
			if (response.success) {
				$status.css('color', '#16a34a').text(response.data.message);
				setTimeout(function() {
					window.location.reload();
				}, 700);
			} else {
				$status.css('color', '#dc2626').text(response.data.message || i18n.error);
			}
		});
	});

	$('#awbl-btn-bulk-clear-lt').on('click', function(e) {
		e.preventDefault();
		var ids = getSelectedProductIds();

		if (ids.length === 0) {
			alert('Please select at least one product.');
			return;
		}

		var $status = $('#awbl-bulk-status-msg');
		$status.css('color', '#2563eb').text('Clearing...');

		$.post(ajaxUrl, {
			action: 'awbl_bulk_update_lead_time',
			nonce: nonce,
			product_ids: ids,
			action_type: 'clear'
		}, function(response) {
			if (response.success) {
				$status.css('color', '#16a34a').text(response.data.message);
				setTimeout(function() {
					window.location.reload();
				}, 700);
			} else {
				$status.css('color', '#dc2626').text(response.data.message || i18n.error);
			}
		});
	});

	/* ==========================================================================
	   3. Settings Export & Import (Tab 9)
	   ========================================================================== */
	$('#awbl-btn-export-settings').on('click', function(e) {
		e.preventDefault();
		$.post(ajaxUrl, {
			action: 'awbl_export_settings',
			nonce: nonce
		}, function(response) {
			if (response.success && response.data) {
				var jsonStr = JSON.stringify(response.data.data, null, 2);
				var blob = new Blob([jsonStr], { type: 'application/json' });
				var url  = URL.createObjectURL(blob);
				var a    = document.createElement('a');
				a.href   = url;
				a.download = response.data.filename || 'awbl-settings.json';
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
			}
		});
	});

	$('#awbl-btn-import-settings').on('click', function(e) {
		e.preventDefault();
		var fileInput = document.getElementById('awbl-import-file-input');
		if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
			alert('Please select a JSON file to import.');
			return;
		}

		var file = fileInput.files[0];
		var reader = new FileReader();
		var $feedback = $('#awbl-import-feedback');

		reader.onload = function(event) {
			var jsonContent = event.target.result;
			$feedback.css('color', '#2563eb').text('Importing...');

			$.post(ajaxUrl, {
				action: 'awbl_import_settings',
				nonce: nonce,
				settings_json: jsonContent
			}, function(response) {
				if (response.success) {
					$feedback.css('color', '#16a34a').text(response.data.message);
					setTimeout(function() {
						window.location.reload();
					}, 1000);
				} else {
					$feedback.css('color', '#dc2626').text(response.data.message || i18n.error);
				}
			}).fail(function() {
				$feedback.css('color', '#dc2626').text(i18n.error);
			});
		};

		reader.readAsText(file);
	});

	/* ==========================================================================
	   4. Reset Settings & Clear Logs
	   ========================================================================== */
	$('#awbl-btn-reset-settings').on('click', function(e) {
		e.preventDefault();
		if (!confirm(i18n.confirm_reset)) {
			return;
		}

		$.post(ajaxUrl, {
			action: 'awbl_reset_settings',
			nonce: nonce
		}, function(response) {
			if (response.success) {
				alert(response.data.message);
				window.location.reload();
			}
		});
	});

	$('#awbl-btn-clear-logs').on('click', function(e) {
		e.preventDefault();
		if (!confirm(i18n.confirm_clear)) {
			return;
		}

		$.post(ajaxUrl, {
			action: 'awbl_clear_logs',
			nonce: nonce
		}, function(response) {
			if (response.success) {
				$('#awbl-log-viewer').val('');
				alert(response.data.message);
			}
		});
	});

	/* ==========================================================================
	   5. Live Color Preview (Tab 8)
	   ========================================================================== */
	function updatePreviewBadge() {
		var bg    = $('#awbl_badge_bg_color').val();
		var text  = $('#awbl_badge_text_color').val();
		var bord  = $('#awbl_badge_border_color').val();
		var lead  = $('#awbl_lead_time_color').val();

		$('#awbl-live-preview-badge').css({
			'background-color': bg,
			'color': text,
			'border-color': bord
		});
		$('#awbl-live-preview-lead').css('color', lead);
	}

	$('#awbl_badge_bg_color, #awbl_badge_text_color, #awbl_badge_border_color, #awbl_lead_time_color').on('input change', updatePreviewBadge);

	/* ==========================================================================
	   6. Product Data Metabox Lead Time Type Toggle (Single Product Edit)
	   ========================================================================== */
	function toggleProductLeadTimeFields() {
		var selectedType = $('input[name="_awbl_lead_time_type"]:checked').val();
		if (selectedType === 'structured') {
			$('.awbl-field-text-mode').hide();
			$('.awbl-field-structured-mode').show();
		} else {
			$('.awbl-field-text-mode').show();
			$('.awbl-field-structured-mode').hide();
		}
	}

	$('input[name="_awbl_lead_time_type"]').on('change', toggleProductLeadTimeFields);
	toggleProductLeadTimeFields();
});

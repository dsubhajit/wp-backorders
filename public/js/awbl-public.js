/**
 * Advanced WooCommerce Backorders & Lead Times - Public JavaScript
 *
 * Handles variable product live updates when variation is selected/changed.
 *
 * @package AWBL
 * @since   1.0.0
 */

jQuery(document).ready(function($) {
	'use strict';

	var $variationsForm = $('form.variations_form');
	if (!$variationsForm.length) {
		return;
	}

	// Cache default parent product HTML
	var $wrapper = $('.awbl-wrapper.awbl-context-product');
	var defaultHtml = $wrapper.length ? $wrapper.prop('outerHTML') : '';

	// When a variation is found and selected
	$variationsForm.on('found_variation', function(event, variation) {
		if (!$wrapper.length) {
			$wrapper = $('.awbl-wrapper.awbl-context-product');
		}

		if (variation && typeof variation.awbl_badge_html !== 'undefined') {
			if ($wrapper.length) {
				if (variation.awbl_badge_html) {
					$wrapper.replaceWith(variation.awbl_badge_html);
					$wrapper = $('.awbl-wrapper.awbl-context-product');
				} else {
					$wrapper.hide();
				}
			} else if (variation.awbl_badge_html) {
				// If parent had no wrapper initially, insert before or after variation form
				$variationsForm.before(variation.awbl_badge_html);
				$wrapper = $('.awbl-wrapper.awbl-context-product');
			}
		}
	});

	// When variation selection is cleared/reset
	$variationsForm.on('reset_data', function() {
		if ($wrapper.length) {
			if (defaultHtml) {
				$wrapper.replaceWith(defaultHtml);
				$wrapper = $('.awbl-wrapper.awbl-context-product');
			} else {
				$wrapper.hide();
			}
		}
	});
});

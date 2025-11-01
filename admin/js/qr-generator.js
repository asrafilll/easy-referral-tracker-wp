/**
 * QR Generator Admin Script
 *
 * Handles QR code generation UI including live preview, download, and settings
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

jQuery(document).ready(function($) {

	// Initialize WordPress color picker
	if ($.fn.wpColorPicker) {
		$('.ert-color-picker').wpColorPicker({
			change: function(event, ui) {
				setTimeout(function() {
					updatePreview();
				}, 100);
			},
			clear: function() {
				setTimeout(function() {
					updatePreview();
				}, 100);
			}
		});
	}

	/**
	 * Update live preview with current settings
	 *
	 * @return {void}
	 */
	function updatePreview() {
		const baseUrl = $('#ert_qr_base_url').val() || window.location.origin;
		const size = parseInt($('#ert_qr_size').val()) || 300;
		const label = $('#ert_qr_label').val() || 'Scan to Download';
		const padding = parseInt($('#ert_qr_padding').val()) || 20;
		const borderRadius = parseInt($('#ert_qr_border_radius').val()) || 10;
		const containerColor = $('#ert_qr_container_color').val() || '#FFFFFF';
		const borderColor = $('#ert_qr_border_color').val() || '#E5E7EB';
		const referralCode = 'preview123'; // Example referral code

		// Build final URL
		const finalUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'r=' + referralCode;

		// Generate QR code URL using QR Server API
		const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&data=' + encodeURIComponent(finalUrl);

		// Update preview container styling
		const $previewContainer = $('#ert-preview-container .easyreferraltracker-qr-container');
		$previewContainer.css({
			'padding': padding + 'px',
			'background': containerColor,
			'border': '2px solid ' + borderColor,
			'border-radius': borderRadius + 'px',
			'box-shadow': '0 4px 6px rgba(0, 0, 0, 0.1)'
		});

		// Update QR code image
		$('#ert-preview-qr').attr('src', qrUrl).css({
			'width': size + 'px',
			'height': size + 'px'
		});

		$('#ert-preview-label').text(label);
		$('#ert-preview-url').text(finalUrl);

		// Update logo position and styling if exists
		const logoImg = $('#ert-preview-logo');
		if (logoImg.attr('src') && logoImg.attr('src').length > 0) {
			const logoSize = Math.round(size * 0.2);
			const logoTop = padding + Math.round((size - logoSize) / 2);
			const logoLeft = padding + Math.round((size - logoSize) / 2);
			logoImg.css({
				'width': logoSize + 'px',
				'height': logoSize + 'px',
				'top': logoTop + 'px',
				'left': logoLeft + 'px',
				'display': 'block',
				'object-fit': 'cover',
				'object-position': 'center'
			});
		} else {
			logoImg.css('display', 'none');
		}
	}

	// Update preview on input change
	$('#ert_qr_base_url, #ert_qr_size, #ert_qr_label, #ert_qr_padding, #ert_qr_border_radius').on('input change', updatePreview);

	// Initial preview - delay to ensure everything is loaded
	setTimeout(function() {
		updatePreview();
	}, 500);

	/**
	 * Copy shortcode to clipboard
	 *
	 * @return {void}
	 */
	$('#ert-copy-shortcode').on('click', function() {
		const shortcode = $('#ert-shortcode').text();
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(shortcode).then(function() {
				const btn = $('#ert-copy-shortcode');
				const originalText = btn.text();
				btn.text(btn.data('copied-text') || 'Copied!');
				setTimeout(function() {
					btn.text(originalText);
				}, 2000);
			}).catch(function(err) {
				alert('Shortcode: ' + shortcode);
			});
		} else {
			// Fallback for older browsers
			alert('Copy this shortcode: ' + shortcode);
		}
	});

	/**
	 * Download QR code as PNG
	 *
	 * @return {void}
	 */
	$('#ert-download-qr').on('click', function() {
		const qrImg = $('#ert-preview-qr');
		const qrSrc = qrImg.attr('src');

		if (!qrSrc || qrSrc.length === 0) {
			alert('Please wait for QR code to generate');
			return;
		}

		const link = document.createElement('a');
		link.href = qrSrc;
		link.download = 'easyreferraltracker-qr-code.png';
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);

		// Show success message
		$('#ert-download-success').fadeIn().delay(3000).fadeOut();
	});

	/**
	 * Copy direct link to clipboard
	 *
	 * @return {void}
	 */
	$('#ert-copy-link').on('click', function() {
		const url = $('#ert-preview-url').text();
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(url).then(function() {
				$('#ert-copy-success').fadeIn().delay(3000).fadeOut();
			});
		} else {
			alert('Copy this URL: ' + url);
		}
	});

	/**
	 * Logo Upload
	 */
	let logoUploader;

	$('#ert-upload-logo-btn').on('click', function(e) {
		e.preventDefault();

		// Check if wp.media is available
		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			alert('Media uploader not available. Please refresh the page.');
			return;
		}

		if (logoUploader) {
			logoUploader.open();
			return;
		}

		logoUploader = wp.media({
			title: 'Choose Logo',
			button: {
				text: 'Use this logo'
			},
			multiple: false,
			library: {
				type: 'image'
			}
		});

		logoUploader.on('select', function() {
			const attachment = logoUploader.state().get('selection').first().toJSON();
			$('#ert_qr_logo').val(attachment.id);
			$('#ert-logo-preview').html('<img src="' + attachment.url + '" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px; background: white; border-radius: 4px;">');
			$('#ert-preview-logo').attr('src', attachment.url);

			// Show/update remove button
			if ($('#ert-remove-logo-btn').length === 0) {
				$('#ert-upload-logo-btn').after('<button type="button" class="button" id="ert-remove-logo-btn" style="margin-left: 5px;">Remove Logo</button>');
			}

			updatePreview();
		});

		logoUploader.open();
	});

	/**
	 * Remove Logo (delegated event handler)
	 */
	$(document).on('click', '#ert-remove-logo-btn', function(e) {
		e.preventDefault();
		$('#ert_qr_logo').val('');
		$('#ert-logo-preview').html('');
		$('#ert-preview-logo').attr('src', '').hide();
		$(this).remove();
		updatePreview();
	});
});

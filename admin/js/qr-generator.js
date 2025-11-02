/**
 * QR Generator Admin Script
 *
 * Handles QR code generation UI including live preview, download, and settings
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

jQuery(document).ready(function($) {

	/**
	 * Color picker synchronization
	 */
	function setupColorPickers() {
		// Sync color input with text input for container color
		$('#ert_qr_container_color').on('input change', function() {
			const color = $(this).val();
			$('#ert_qr_container_color_text').val(color);
			updatePreview();
		});

		$('#ert_qr_container_color_text').on('input change', function() {
			const color = $(this).val();
			if (/^#[0-9A-Fa-f]{6}$/i.test(color)) {
				$('#ert_qr_container_color').val(color);
				updatePreview();
			}
		});

		// Sync color input with text input for border color
		$('#ert_qr_border_color').on('input change', function() {
			const color = $(this).val();
			$('#ert_qr_border_color_text').val(color);
			updatePreview();
		});

		$('#ert_qr_border_color_text').on('input change', function() {
			const color = $(this).val();
			if (/^#[0-9A-Fa-f]{6}$/i.test(color)) {
				$('#ert_qr_border_color').val(color);
				updatePreview();
			}
		});
	}

	// Initialize color pickers
	setupColorPickers();

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

	}

	// Update preview on input change (color inputs handled in setupColorPickers)
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

});

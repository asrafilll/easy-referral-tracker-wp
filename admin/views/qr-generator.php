<?php
/**
 * QR Generator View Template
 *
 * QR code generation page with live preview and settings
 *
 * @package EasyReferralTracker
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Handle form submission
if (isset($_POST['ert_qr_save']) && check_admin_referer('ert_qr_settings')) {
	update_option('ert_qr_base_url', esc_url_raw($_POST['ert_qr_base_url']));
	update_option('ert_qr_size', absint($_POST['ert_qr_size']));
	update_option('ert_qr_label', sanitize_text_field($_POST['ert_qr_label']));
	update_option('ert_qr_logo', absint($_POST['ert_qr_logo']));
	update_option('ert_qr_padding', absint($_POST['ert_qr_padding']));
	update_option('ert_qr_border_radius', absint($_POST['ert_qr_border_radius']));
	update_option('ert_qr_container_color', sanitize_hex_color($_POST['ert_qr_container_color']));
	update_option('ert_qr_border_color', sanitize_hex_color($_POST['ert_qr_border_color']));

	add_settings_error('ert_messages', 'ert_message', __('QR Code settings saved successfully!', 'easyreferraltracker'), 'updated');
}

// Get current settings
$base_url = get_option('ert_qr_base_url', home_url('/download'));
$qr_size = get_option('ert_qr_size', 300);
$qr_label = get_option('ert_qr_label', 'Scan to Download');
$qr_logo = get_option('ert_qr_logo', 0);
$logo_url = $qr_logo ? wp_get_attachment_url($qr_logo) : '';
$qr_padding = get_option('ert_qr_padding', 20);
$qr_border_radius = get_option('ert_qr_border_radius', 10);
$qr_container_color = get_option('ert_qr_container_color', '#FFFFFF');
$qr_border_color = get_option('ert_qr_border_color', '#E5E7EB');
?>

<div class="wrap easyreferraltracker-qr-generator">
	<h1><?php esc_html_e('QR Code Generator', 'easyreferraltracker'); ?></h1>
	<p><?php esc_html_e('Create dynamic QR codes that automatically include referral codes for each visitor.', 'easyreferraltracker'); ?></p>

	<?php settings_errors('ert_messages'); ?>

	<div class="ert-qr-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">

		<!-- Left Column: Settings -->
		<div class="ert-qr-settings">
			<form method="post" action="">
				<?php wp_nonce_field('ert_qr_settings'); ?>
				<input type="hidden" name="ert_qr_save" value="1">

				<div class="ert-section" style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
					<h2 style="margin-top: 0;"><?php esc_html_e('QR Code Settings', 'easyreferraltracker'); ?></h2>

					<table class="form-table">
						<!-- Base URL -->
						<tr>
							<th scope="row">
								<label for="ert_qr_base_url">
									<?php esc_html_e('Base URL', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="url"
									   id="ert_qr_base_url"
									   name="ert_qr_base_url"
									   value="<?php echo esc_attr($base_url); ?>"
									   class="regular-text"
									   placeholder="https://yoursite.com/download"
									   required>
								<p class="description">
									<?php esc_html_e('The URL where users will be sent. Referral code will be added automatically.', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- QR Code Size -->
						<tr>
							<th scope="row">
								<label for="ert_qr_size">
									<?php esc_html_e('QR Code Size', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="number"
									   id="ert_qr_size"
									   name="ert_qr_size"
									   value="<?php echo esc_attr($qr_size); ?>"
									   min="100"
									   max="500"
									   step="50"
									   style="width: 100px;">
								<span>pixels</span>
								<p class="description">
									<?php esc_html_e('Size of the QR code (100-500 pixels). Default: 300', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Label Text -->
						<tr>
							<th scope="row">
								<label for="ert_qr_label">
									<?php esc_html_e('Label Text', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="text"
									   id="ert_qr_label"
									   name="ert_qr_label"
									   value="<?php echo esc_attr($qr_label); ?>"
									   class="regular-text"
									   placeholder="Scan to Download">
								<p class="description">
									<?php esc_html_e('Text displayed below the QR code.', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Logo Upload -->
						<tr>
							<th scope="row">
								<label for="ert_qr_logo">
									<?php esc_html_e('Center Logo', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<div class="ert-logo-upload">
									<input type="hidden" id="ert_qr_logo" name="ert_qr_logo" value="<?php echo esc_attr($qr_logo); ?>">

									<div id="ert-logo-preview" style="margin-bottom: 10px;">
										<?php if ($logo_url) : ?>
											<img src="<?php echo esc_url($logo_url); ?>" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px; background: white;">
										<?php endif; ?>
									</div>

									<button type="button" class="button" id="ert-upload-logo-btn">
										<?php echo $logo_url ? esc_html__('Change Logo', 'easyreferraltracker') : esc_html__('Upload Logo', 'easyreferraltracker'); ?>
									</button>

									<?php if ($logo_url) : ?>
										<button type="button" class="button" id="ert-remove-logo-btn">
											<?php esc_html_e('Remove Logo', 'easyreferraltracker'); ?>
										</button>
									<?php endif; ?>

									<p class="description">
										<?php esc_html_e('Optional: Upload your logo to display in the center of the QR code. Recommended: Square image, transparent background.', 'easyreferraltracker'); ?>
									</p>
								</div>
							</td>
						</tr>
					</table>

					<h3 style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;"><?php esc_html_e('Container Styling', 'easyreferraltracker'); ?></h3>
					<p class="description" style="margin-bottom: 20px;"><?php esc_html_e('Customize the appearance of the QR code container', 'easyreferraltracker'); ?></p>

					<table class="form-table">
						<!-- Container Padding -->
						<tr>
							<th scope="row">
								<label for="ert_qr_padding">
									<?php esc_html_e('Container Padding', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="number"
									   id="ert_qr_padding"
									   name="ert_qr_padding"
									   value="<?php echo esc_attr($qr_padding); ?>"
									   min="0"
									   max="100"
									   step="5"
									   style="width: 100px;">
								<span>pixels</span>
								<p class="description">
									<?php esc_html_e('Space around the QR code inside the container (0-100 pixels). Default: 20', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Border Radius -->
						<tr>
							<th scope="row">
								<label for="ert_qr_border_radius">
									<?php esc_html_e('Border Radius', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="number"
									   id="ert_qr_border_radius"
									   name="ert_qr_border_radius"
									   value="<?php echo esc_attr($qr_border_radius); ?>"
									   min="0"
									   max="50"
									   step="5"
									   style="width: 100px;">
								<span>pixels</span>
								<p class="description">
									<?php esc_html_e('Rounded corners for the container (0-50 pixels). 0 = square, 50 = very rounded. Default: 10', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Container Color -->
						<tr>
							<th scope="row">
								<label for="ert_qr_container_color">
									<?php esc_html_e('Container Background', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="text"
									   id="ert_qr_container_color"
									   name="ert_qr_container_color"
									   value="<?php echo esc_attr($qr_container_color); ?>"
									   class="ert-color-picker"
									   data-default-color="#FFFFFF">
								<p class="description">
									<?php esc_html_e('Background color of the container. Default: White (#FFFFFF)', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>

						<!-- Border Color -->
						<tr>
							<th scope="row">
								<label for="ert_qr_border_color">
									<?php esc_html_e('Border Color', 'easyreferraltracker'); ?>
								</label>
							</th>
							<td>
								<input type="text"
									   id="ert_qr_border_color"
									   name="ert_qr_border_color"
									   value="<?php echo esc_attr($qr_border_color); ?>"
									   class="ert-color-picker"
									   data-default-color="#E5E7EB">
								<p class="description">
									<?php esc_html_e('Border color of the container. Default: Light gray (#E5E7EB)', 'easyreferraltracker'); ?>
								</p>
							</td>
						</tr>
					</table>

					<?php submit_button(__('Save and Generate QR Code', 'easyreferraltracker'), 'primary large'); ?>
				</div>
			</form>

			<!-- Shortcode Info -->
			<div class="ert-section" style="background: #f0f6fc; padding: 25px; border-radius: 8px; margin-top: 20px; border: 1px solid #c3e0f7;">
				<h3 style="margin-top: 0;"><?php esc_html_e('How to Use', 'easyreferraltracker'); ?></h3>

				<p><strong><?php esc_html_e('Step 1:', 'easyreferraltracker'); ?></strong> <?php esc_html_e('Copy the shortcode below', 'easyreferraltracker'); ?></p>

				<div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #ddd;">
					<code id="ert-shortcode" style="font-size: 14px; user-select: all;">[easyreferraltracker_qr]</code>
					<button type="button" class="button button-small" id="ert-copy-shortcode" style="margin-left: 10px;" data-copied-text="<?php esc_attr_e('Copied!', 'easyreferraltracker'); ?>">
						<?php esc_html_e('Copy', 'easyreferraltracker'); ?>
					</button>
				</div>

				<p><strong><?php esc_html_e('Step 2:', 'easyreferraltracker'); ?></strong> <?php esc_html_e('Paste it anywhere on your site', 'easyreferraltracker'); ?></p>

				<p><strong><?php esc_html_e('Step 3:', 'easyreferraltracker'); ?></strong> <?php esc_html_e('Each visitor will see a personalized QR code with their referral code!', 'easyreferraltracker'); ?></p>
			</div>
		</div>

		<!-- Right Column: Live Preview -->
		<div class="ert-qr-preview-section">
			<div class="ert-section" style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
				<h2 style="margin-top: 0;"><?php esc_html_e('Live Preview', 'easyreferraltracker'); ?></h2>

				<div id="ert-preview-container" style="text-align: center; padding: 30px; background: #f9f9f9; border-radius: 8px; min-height: 400px;">
					<div class="easyreferraltracker-qr-container" style="display: inline-block; position: relative;">
						<img id="ert-preview-qr" src="" alt="QR Code Preview">
						<img id="ert-preview-logo" src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="display: <?php echo $logo_url ? 'block' : 'none'; ?>; position: absolute;">
					</div>
					<p id="ert-preview-label"></p>
					<p style="margin-top: 10px; font-size: 13px; color: #666;">
						<?php esc_html_e('Preview URL:', 'easyreferraltracker'); ?>
						<code id="ert-preview-url" style="background: white; padding: 4px 8px; border-radius: 3px; border: 1px solid #ddd; word-break: break-all;"></code>
					</p>
				</div>

				<div style="margin-top: 20px; text-align: center;">
					<button type="button" class="button button-primary button-large" id="ert-download-qr">
						<?php esc_html_e('Download QR Code as PNG', 'easyreferraltracker'); ?>
					</button>

					<button type="button" class="button button-large" id="ert-copy-link" style="margin-left: 10px;">
						<?php esc_html_e('Copy Direct Link', 'easyreferraltracker'); ?>
					</button>
				</div>

				<div id="ert-download-success" style="display: none; margin-top: 15px; padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724; text-align: center;">
					<?php esc_html_e('QR Code downloaded successfully!', 'easyreferraltracker'); ?>
				</div>

				<div id="ert-copy-success" style="display: none; margin-top: 15px; padding: 12px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; color: #0c5460; text-align: center;">
					<?php esc_html_e('Link copied to clipboard!', 'easyreferraltracker'); ?>
				</div>
			</div>
		</div>
	</div>
</div>

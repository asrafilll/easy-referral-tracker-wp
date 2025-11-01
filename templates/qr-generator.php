<?php
/**
 * QR Code Generator Template
 * File: templates/qr-generator.php
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
                                        <?php if ($logo_url): ?>
                                            <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px; background: white;">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button type="button" class="button" id="ert-upload-logo-btn">
                                        <?php echo $logo_url ? esc_html__('Change Logo', 'easyreferraltracker') : esc_html__('Upload Logo', 'easyreferraltracker'); ?>
                                    </button>
                                    
                                    <?php if ($logo_url): ?>
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
                    <button type="button" class="button button-small" id="ert-copy-shortcode" style="margin-left: 10px;">
                        <?php esc_html_e('Copy', 'easyreferraltracker'); ?>
                    </button>
                </div>
                
                <p><strong><?php esc_html_e('Step 2:', 'easyreferraltracker'); ?></strong> <?php esc_html_e('Paste it anywhere on your site:', 'easyreferraltracker'); ?></p>
                <ul style="line-height: 1.8;">
                    <li><?php esc_html_e('In pages or posts (Block Editor or Classic Editor)', 'easyreferraltracker'); ?></li>
                    <li><?php esc_html_e('In widgets (Appearance > Widgets)', 'easyreferraltracker'); ?></li>
                    <li><?php esc_html_e('In your theme template files using:', 'easyreferraltracker'); ?> 
                        <code style="background: white; padding: 2px 6px; border-radius: 3px;">&lt;?php echo do_shortcode('[easyreferraltracker_qr]'); ?&gt;</code>
                    </li>
                </ul>
                
                <p><strong><?php esc_html_e('Step 3:', 'easyreferraltracker'); ?></strong> <?php esc_html_e('Each visitor will see a personalized QR code with their referral code!', 'easyreferraltracker'); ?></p>
                
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #c3e0f7;">
                
                <h4><?php esc_html_e('Custom Shortcode Options:', 'easyreferraltracker'); ?></h4>
                <p><?php esc_html_e('You can override default settings:', 'easyreferraltracker'); ?></p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd; font-family: monospace; font-size: 13px; line-height: 1.8;">
                    <?php esc_html_e('Custom size:', 'easyreferraltracker'); ?><br>
                    <code>[easyreferraltracker_qr size="400"]</code><br><br>
                    
                    <?php esc_html_e('Custom URL:', 'easyreferraltracker'); ?><br>
                    <code>[easyreferraltracker_qr url="https://yoursite.com/special"]</code><br><br>
                    
                    <?php esc_html_e('Custom label:', 'easyreferraltracker'); ?><br>
                    <code>[easyreferraltracker_qr label="Download Now"]</code><br><br>
                    
                    <?php esc_html_e('Custom styling:', 'easyreferraltracker'); ?><br>
                    <code>[easyreferraltracker_qr padding="30" border_radius="20"]</code><br><br>
                    
                    <?php esc_html_e('Custom colors:', 'easyreferraltracker'); ?><br>
                    <code>[easyreferraltracker_qr container_color="#F3F4F6" border_color="#6366F1"]</code><br><br>
                    
                    <?php esc_html_e('Combine multiple options:', 'easyreferraltracker'); ?><br>
                    <code>[easyreferraltracker_qr size="350" padding="25" border_radius="15" label="Get the App"]</code>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Live Preview & Download -->
        <div class="ert-qr-preview-section">
            <div class="ert-section" style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0;"><?php esc_html_e('Live Preview', 'easyreferraltracker'); ?></h2>
                
                <div id="ert-preview-container" style="text-align: center; padding: 30px; background: #f9f9f9; border-radius: 8px; min-height: 400px;">
                    <div class="easyreferraltracker-qr-container" style="display: inline-block; position: relative; padding: 20px; background: #FFFFFF; border: 2px solid #E5E7EB; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <img id="ert-preview-qr" src="" alt="QR Code Preview" style="display: block; width: 300px; height: 300px;">
                        <img id="ert-preview-logo" src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="display: <?php echo $logo_url ? 'block' : 'none'; ?>; position: absolute; top: 70px; left: 70px; width: 60px; height: 60px; border-radius: 4px; object-fit: cover; object-position: center;">
                    </div>
                    <p id="ert-preview-label" style="margin-top: 15px; font-size: 16px; color: #333; font-weight: 500;"></p>
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
            
            <!-- How It Works -->
            <div class="ert-section" style="background: #fff3cd; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #ffc107;">
                <h3 style="margin-top: 0; color: #856404;"><?php esc_html_e('How It Works', 'easyreferraltracker'); ?></h3>
                <ol style="line-height: 2; color: #856404;">
                    <li><?php esc_html_e('Visitor arrives with referral code: yoursite.com/?r=john123', 'easyreferraltracker'); ?></li>
                    <li><?php esc_html_e('QR code automatically updates to include: /download?r=john123', 'easyreferraltracker'); ?></li>
                    <li><?php esc_html_e('Visitor scans QR code with their phone', 'easyreferraltracker'); ?></li>
                    <li><?php esc_html_e('Phone opens your download page with referral code', 'easyreferraltracker'); ?></li>
                    <li><?php esc_html_e('Referral is tracked and attributed correctly!', 'easyreferraltracker'); ?></li>
                </ol>
                <p style="margin: 15px 0 0 0; color: #856404; font-weight: 500;">
                    <?php esc_html_e('Each visitor gets a personalized QR code. No manual work required!', 'easyreferraltracker'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
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
    
    // Live Preview Update
    function updatePreview() {
        const baseUrl = $('#ert_qr_base_url').val() || '<?php echo esc_js(home_url()); ?>';
        const size = parseInt($('#ert_qr_size').val()) || 300;
        const label = $('#ert_qr_label').val() || 'Scan to Download';
        const padding = parseInt($('#ert_qr_padding').val()) || 20;
        const borderRadius = parseInt($('#ert_qr_border_radius').val()) || 10;
        const containerColor = $('#ert_qr_container_color').val() || '#FFFFFF';
        const borderColor = $('#ert_qr_border_color').val() || '#E5E7EB';
        const referralCode = 'preview123'; // Example referral code
        
        // Build final URL
        const finalUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'r=' + referralCode;
        
        // Generate QR code URL using QR Server API (more reliable than Google Charts)
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
    
    // Copy shortcode
    $('#ert-copy-shortcode').on('click', function() {
        const shortcode = $('#ert-shortcode').text();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(shortcode).then(function() {
                const btn = $('#ert-copy-shortcode');
                const originalText = btn.text();
                btn.text('<?php esc_html_e('Copied!', 'easyreferraltracker'); ?>');
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
    
    // Download QR Code
    $('#ert-download-qr').on('click', function() {
        const qrImg = $('#ert-preview-qr');
        const qrSrc = qrImg.attr('src');
        
        if (!qrSrc || qrSrc.length === 0) {
            alert('<?php esc_html_e('Please wait for QR code to generate', 'easyreferraltracker'); ?>');
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
    
    // Copy direct link
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
    
    // Logo Upload
    let logoUploader;
    
    $('#ert-upload-logo-btn').on('click', function(e) {
        e.preventDefault();
        
        // Check if wp.media is available
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            alert('<?php esc_html_e('Media uploader not available. Please refresh the page.', 'easyreferraltracker'); ?>');
            return;
        }
        
        if (logoUploader) {
            logoUploader.open();
            return;
        }
        
        logoUploader = wp.media({
            title: '<?php esc_html_e('Choose Logo', 'easyreferraltracker'); ?>',
            button: {
                text: '<?php esc_html_e('Use this logo', 'easyreferraltracker'); ?>'
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
                $('#ert-upload-logo-btn').after('<button type="button" class="button" id="ert-remove-logo-btn" style="margin-left: 5px;"><?php esc_html_e('Remove Logo', 'easyreferraltracker'); ?></button>');
            }
            
            updatePreview();
        });
        
        logoUploader.open();
    });
    
    // Remove Logo (delegated event handler)
    $(document).on('click', '#ert-remove-logo-btn', function(e) {
        e.preventDefault();
        $('#ert_qr_logo').val('');
        $('#ert-logo-preview').html('');
        $('#ert-preview-logo').attr('src', '').hide();
        $(this).remove();
        updatePreview();
    });
});
</script>

<style>
.easyreferraltracker-qr-generator .ert-section h2 {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
}

.easyreferraltracker-qr-generator code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
}

@media (max-width: 1200px) {
    .ert-qr-layout {
        grid-template-columns: 1fr !important;
    }
}
</style>
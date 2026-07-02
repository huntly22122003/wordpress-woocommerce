<?php
/**
 * Template trang cài đặt admin
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><span class="dashicons dashicons-format-image"></span> <?php _e('Side Banners for WooCommerce', 'side-banners-woo'); ?></h1>
    <p><?php _e('Cấu hình banner quảng cáo 2 bên cho website của bạn.', 'side-banners-woo'); ?></p>

    <form method="post" action="options.php" class="sbw-settings-form">
        <?php settings_fields('sbw_settings'); ?>

        <div class="sbw-admin-container">
            <!-- BANNER TRÁI -->
            <div class="sbw-section">
                <h2><span class="dashicons dashicons-arrow-left-alt"></span> <?php _e('Banner bên trái', 'side-banners-woo'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Kích hoạt', 'side-banners-woo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sbw_left_banner_enabled" value="1" <?php checked(get_option('sbw_left_banner_enabled', 1)); ?>>
                                <?php _e('Hiển thị banner trái', 'side-banners-woo'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Hình ảnh banner', 'side-banners-woo'); ?></th>
                        <td>
                            <div class="sbw-image-upload-wrapper">
                                <input type="hidden" name="sbw_left_banner_image_id" class="sbw-image-id-input" value="<?php echo esc_attr(get_option('sbw_left_banner_image_id', 0)); ?>">
                                <div class="sbw-image-preview">
                                    <?php 
                                    $left_image_id = get_option('sbw_left_banner_image_id', 0);
                                    if ($left_image_id) {
                                        echo wp_get_attachment_image($left_image_id, 'medium', false, array('style' => 'max-width:150px;height:auto;'));
                                    }
                                    ?>
                                </div>
                                <button type="button" class="button sbw-upload-image"><?php _e('Chọn ảnh', 'side-banners-woo'); ?></button>
                                <button type="button" class="button sbw-remove-image" style="display:<?php echo $left_image_id ? 'inline-block' : 'none'; ?>;"><?php _e('Xóa ảnh', 'side-banners-woo'); ?></button>
                                <p class="description"><?php _e('Khuyến nghị kích thước: 160x600px', 'side-banners-woo'); ?></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Link banner (không bắt buộc)', 'side-banners-woo'); ?></th>
                        <td>
                            <input type="text" name="sbw_left_banner_link" value="<?php echo esc_attr(get_option('sbw_left_banner_link', '#')); ?>"
                                   class="regular-text" placeholder="https://your-link.com (để trống nếu không cần link)">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Kích thước', 'side-banners-woo'); ?></th>
                        <td>
                            <input type="number" name="sbw_left_banner_width" value="<?php echo esc_attr(get_option('sbw_left_banner_width', 160)); ?>"
                                   class="small-text" step="1" min="100"> px
                            <span style="margin: 0 10px;">x</span>
                            <input type="number" name="sbw_left_banner_height" value="<?php echo esc_attr(get_option('sbw_left_banner_height', 600)); ?>"
                                   class="small-text" step="1" min="100"> px
                        </td>
                    </tr>
                </table>
            </div>

            <!-- BANNER PHẢI -->
            <div class="sbw-section">
                <h2><span class="dashicons dashicons-arrow-right-alt"></span> <?php _e('Banner bên phải', 'side-banners-woo'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Kích hoạt', 'side-banners-woo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sbw_right_banner_enabled" value="1" <?php checked(get_option('sbw_right_banner_enabled', 1)); ?>>
                                <?php _e('Hiển thị banner phải', 'side-banners-woo'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Hình ảnh banner', 'side-banners-woo'); ?></th>
                        <td>
                            <div class="sbw-image-upload-wrapper">
                                <input type="hidden" name="sbw_right_banner_image_id" class="sbw-image-id-input" value="<?php echo esc_attr(get_option('sbw_right_banner_image_id', 0)); ?>">
                                <div class="sbw-image-preview">
                                    <?php 
                                    $right_image_id = get_option('sbw_right_banner_image_id', 0);
                                    if ($right_image_id) {
                                        echo wp_get_attachment_image($right_image_id, 'medium', false, array('style' => 'max-width:150px;height:auto;'));
                                    }
                                    ?>
                                </div>
                                <button type="button" class="button sbw-upload-image"><?php _e('Chọn ảnh', 'side-banners-woo'); ?></button>
                                <button type="button" class="button sbw-remove-image" style="display:<?php echo $right_image_id ? 'inline-block' : 'none'; ?>;"><?php _e('Xóa ảnh', 'side-banners-woo'); ?></button>
                                <p class="description"><?php _e('Khuyến nghị kích thước: 160x600px', 'side-banners-woo'); ?></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Link banner (không bắt buộc)', 'side-banners-woo'); ?></th>
                        <td>
                            <input type="text" name="sbw_right_banner_link" value="<?php echo esc_attr(get_option('sbw_right_banner_link', '#')); ?>"
                                   class="regular-text" placeholder="https://your-link.com (để trống nếu không cần link)">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Kích thước', 'side-banners-woo'); ?></th>
                        <td>
                            <input type="number" name="sbw_right_banner_width" value="<?php echo esc_attr(get_option('sbw_right_banner_width', 160)); ?>"
                                   class="small-text" step="1" min="100"> px
                            <span style="margin: 0 10px;">x</span>
                            <input type="number" name="sbw_right_banner_height" value="<?php echo esc_attr(get_option('sbw_right_banner_height', 600)); ?>"
                                   class="small-text" step="1" min="100"> px
                        </td>
                    </tr>
                </table>
            </div>

            <!-- CÀI ĐẶT CHUNG -->
            <div class="sbw-section">
                <h2><span class="dashicons dashicons-admin-settings"></span> <?php _e('Cài đặt chung', 'side-banners-woo'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Hiển thị trên Mobile', 'side-banners-woo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sbw_show_on_mobile" value="1" <?php checked(get_option('sbw_show_on_mobile', 0)); ?>>
                                <?php _e('Hiển thị banner trên thiết bị di động', 'side-banners-woo'); ?>
                            </label>
                            <p class="description"><?php _e('Không khuyến khích vì chiếm diện tích màn hình', 'side-banners-woo'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Chỉ hiển thị trên Shop', 'side-banners-woo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sbw_show_only_shop" value="1" <?php checked(get_option('sbw_show_only_shop', 0)); ?>>
                                <?php _e('Chỉ hiển thị trên trang Shop và sản phẩm', 'side-banners-woo'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Vị trí banner', 'side-banners-woo'); ?></th>
                        <td>
                            <select name="sbw_banner_position">
                                <option value="middle" <?php selected(get_option('sbw_banner_position', 'middle'), 'middle'); ?>><?php _e('Giữa màn hình', 'side-banners-woo'); ?></option>
                                <option value="top" <?php selected(get_option('sbw_banner_position', 'middle'), 'top'); ?>><?php _e('Đầu trang', 'side-banners-woo'); ?></option>
                                <option value="bottom" <?php selected(get_option('sbw_banner_position', 'middle'), 'bottom'); ?>><?php _e('Cuối trang', 'side-banners-woo'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Nút đóng banner', 'side-banners-woo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sbw_close_button" value="1" <?php checked(get_option('sbw_close_button', 1)); ?>>
                                <?php _e('Hiển thị nút đóng banner', 'side-banners-woo'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button(__('Lưu cài đặt', 'side-banners-woo'), 'primary', 'submit', true); ?>
    </form>
</div>

<style>
.sbw-admin-container {
    max-width: 900px;
}
.sbw-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.sbw-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}
.sbw-section h2 .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    vertical-align: middle;
}
.sbw-image-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.sbw-image-upload-wrapper .sbw-image-preview {
    min-height: 50px;
}
.sbw-image-upload-wrapper .sbw-image-preview img {
    max-width: 150px;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px;
}
.sbw-settings-form .form-table th {
    width: 150px;
}
.sbw-settings-form .description {
    color: #666;
    font-style: italic;
    margin-top: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Upload image
    $('.sbw-upload-image').on('click', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.sbw-image-upload-wrapper');
        var input = wrapper.find('.sbw-image-id-input');
        var preview = wrapper.find('.sbw-image-preview');
        var removeBtn = wrapper.find('.sbw-remove-image');

        var mediaUploader = wp.media({
            title: '<?php _e('Chọn ảnh banner', 'side-banners-woo'); ?>',
            button: {
                text: '<?php _e('Chọn ảnh', 'side-banners-woo'); ?>'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            input.val(attachment.id);
            // Hiển thị preview
            var img = '<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />';
            preview.html(img);
            removeBtn.show();
        });

        mediaUploader.open();
    });

    // Remove image
    $('.sbw-remove-image').on('click', function(e) {
        e.preventDefault();
        var wrapper = $(this).closest('.sbw-image-upload-wrapper');
        wrapper.find('.sbw-image-id-input').val('');
        wrapper.find('.sbw-image-preview').empty();
        $(this).hide();
    });
});
</script>
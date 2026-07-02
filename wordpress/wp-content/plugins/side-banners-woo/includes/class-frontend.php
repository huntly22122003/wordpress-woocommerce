<?php
/**
 * Xử lý hiển thị banner và script phía frontend
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBW_Frontend {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'display_banners'));
    }

    public function enqueue_scripts() {
        if (!sbw_should_display_banners()) {
            return;
        }

        wp_enqueue_style(
            'sbw-banner-style',
            SBW_PLUGIN_URL . 'assets/banner-style.css',
            array(),
            SBW_VERSION
        );

        wp_enqueue_script(
            'sbw-banner-script',
            SBW_PLUGIN_URL . 'assets/banner-script.js',
            array('jquery'),
            SBW_VERSION,
            true
        );

        wp_localize_script('sbw-banner-script', 'sbw_data', array(
            'close_button'    => get_option('sbw_close_button', 1),
            'left_enabled'    => get_option('sbw_left_banner_enabled', 1),
            'right_enabled'   => get_option('sbw_right_banner_enabled', 1),
            'show_on_mobile'  => get_option('sbw_show_on_mobile', 0),
        ));
    }

    public function display_banners() {
        if (!sbw_should_display_banners()) {
            return;
        }

        // Lấy ID ảnh và chuyển thành URL
        $left_image_id  = get_option('sbw_left_banner_image_id', 0);
        $left_image     = $left_image_id ? wp_get_attachment_url($left_image_id) : '';

        $right_image_id = get_option('sbw_right_banner_image_id', 0);
        $right_image    = $right_image_id ? wp_get_attachment_url($right_image_id) : '';

        $left_link   = get_option('sbw_left_banner_link', '#');
        $right_link  = get_option('sbw_right_banner_link', '#');

        $left_enabled  = get_option('sbw_left_banner_enabled', 1);
        $right_enabled = get_option('sbw_right_banner_enabled', 1);

        $left_width  = get_option('sbw_left_banner_width', 160);
        $left_height = get_option('sbw_left_banner_height', 600);
        $right_width = get_option('sbw_right_banner_width', 160);
        $right_height = get_option('sbw_right_banner_height', 600);

        $position     = get_option('sbw_banner_position', 'middle');
        $close_button = get_option('sbw_close_button', 1);
        $position_class = 'sbw-position-' . $position;

        ?>
        <div class="sbw-banners-wrapper <?php echo esc_attr($position_class); ?>">

            <?php if ($left_enabled && !empty($left_image)) : ?>
            <div class="sbw-banner sbw-banner-left" id="sbw-left-banner">
                <div class="sbw-banner-inner">
                    <?php 
                    // Nếu link khác rỗng và khác '#' thì tạo thẻ a, ngược lại chỉ hiển thị ảnh
                    if (!empty($left_link) && $left_link != '#') : ?>
                        <a href="<?php echo esc_url($left_link); ?>" target="_blank" rel="nofollow">
                            <img src="<?php echo esc_url($left_image); ?>"
                                 alt="<?php esc_attr_e('Banner quảng cáo trái', 'side-banners-woo'); ?>"
                                 width="<?php echo esc_attr($left_width); ?>"
                                 height="<?php echo esc_attr($left_height); ?>"
                                 loading="lazy">
                        </a>
                    <?php else : ?>
                        <img src="<?php echo esc_url($left_image); ?>"
                             alt="<?php esc_attr_e('Banner quảng cáo trái', 'side-banners-woo'); ?>"
                             width="<?php echo esc_attr($left_width); ?>"
                             height="<?php echo esc_attr($left_height); ?>"
                             loading="lazy">
                    <?php endif; ?>

                    <?php if ($close_button) : ?>
                    <button class="sbw-close-btn" data-banner="left" aria-label="<?php esc_attr_e('Đóng banner trái', 'side-banners-woo'); ?>">×</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($right_enabled && !empty($right_image)) : ?>
            <div class="sbw-banner sbw-banner-right" id="sbw-right-banner">
                <div class="sbw-banner-inner">
                    <?php if (!empty($right_link) && $right_link != '#') : ?>
                        <a href="<?php echo esc_url($right_link); ?>" target="_blank" rel="nofollow">
                            <img src="<?php echo esc_url($right_image); ?>"
                                 alt="<?php esc_attr_e('Banner quảng cáo phải', 'side-banners-woo'); ?>"
                                 width="<?php echo esc_attr($right_width); ?>"
                                 height="<?php echo esc_attr($right_height); ?>"
                                 loading="lazy">
                        </a>
                    <?php else : ?>
                        <img src="<?php echo esc_url($right_image); ?>"
                             alt="<?php esc_attr_e('Banner quảng cáo phải', 'side-banners-woo'); ?>"
                             width="<?php echo esc_attr($right_width); ?>"
                             height="<?php echo esc_attr($right_height); ?>"
                             loading="lazy">
                    <?php endif; ?>

                    <?php if ($close_button) : ?>
                    <button class="sbw-close-btn" data-banner="right" aria-label="<?php esc_attr_e('Đóng banner phải', 'side-banners-woo'); ?>">×</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php
    }
}
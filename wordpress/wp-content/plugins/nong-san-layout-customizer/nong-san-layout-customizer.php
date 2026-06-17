<?php
/**
 * Plugin Name: Nông Sản Layout Customizer
 * Plugin URI: https://nongsanvuive.com/
 * Description: Tùy chỉnh layout trang chủ - căn chỉnh ảnh và dàn đều bố cục
 * Version: 1.1.0
 * Author: Nông sản vui vẻ
 * Text Domain: nong-san-layout
 */

// Ngăn chặn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lớp chính của Plugin Layout
 */
class Nong_San_Layout_Customizer {

    /**
     * Constructor: Khởi tạo các hook
     */
    public function __construct() {
        // Thêm CSS tùy chỉnh cho layout
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_layout_styles' ) );
        
        // Thêm CSS vào admin để tùy chỉnh giao diện backend
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        
        // Thêm các tùy chọn layout vào Customizer
        add_action( 'customize_register', array( $this, 'register_customizer_options' ) );
        
        // Thêm filter để tùy chỉnh HTML của trang chủ
        add_filter( 'storefront_homepage_hero_heading', array( $this, 'customize_hero_section' ) );
        
        // Thêm class vào body để tùy chỉnh layout
        add_filter( 'body_class', array( $this, 'add_layout_body_class' ) );
    }

    /**
     * Enqueue CSS cho frontend
     */
    public function enqueue_layout_styles() {
        // Áp dụng cho tất cả các trang
        wp_enqueue_style(
            'nong-san-layout-style',
            plugin_dir_url( __FILE__ ) . 'assets/layout-style.css',
            array(),
            '1.1.0'
        );
        
        // Thêm CSS inline cho các tùy chỉnh nhanh
        wp_add_inline_style( 'nong-san-layout-style', $this->get_dynamic_css() );
    }

    /**
     * Enqueue CSS cho admin
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'nong-san-layout-admin',
            plugin_dir_url( __FILE__ ) . 'assets/admin-style.css',
            array(),
            '1.1.0'
        );
    }

    /**
     * Lấy CSS động từ các tùy chọn
     */
    private function get_dynamic_css() {
        $image_size = get_theme_mod( 'nong_san_image_size', 'medium' );
        $layout_style = get_theme_mod( 'nong_san_layout_style', 'grid' );
        $spacing = get_theme_mod( 'nong_san_spacing', '20' );
        $full_width = get_theme_mod( 'nong_san_full_width', true );
        
        $css = "
            /* NÔNG SẢN LAYOUT CUSTOMIZER - CORE STYLES */
            
            /* ==========================================
               1. LAYOUT TỔNG - FULL WIDTH
               ========================================== */";
        
        if ( $full_width ) {
            $css .= "
            /* Full width cho trang chủ */
            .home .site-content .col-full,
            .page-template-template-homepage .site-content .col-full {
                max-width: 100% !important;
                padding: 0 30px !important;
                margin: 0 auto !important;
                width: 100% !important;
            }
            
            /* Loại bỏ giới hạn width cho các block */
            .home .wp-block-group.alignfull,
            .page-template-template-homepage .wp-block-group.alignfull {
                padding-left: 30px !important;
                padding-right: 30px !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            
            /* Dàn đều các cột */
            .home .wp-block-columns,
            .page-template-template-homepage .wp-block-columns {
                margin-left: 0 !important;
                margin-right: 0 !important;
                gap: 20px !important;
            }
            
            /* Căn chỉnh content trong columns */
            .home .wp-block-column,
            .page-template-template-homepage .wp-block-column {
                padding: 0 !important;
            }
            
            /* Điều chỉnh container của cover block */
            .home .wp-block-cover,
            .page-template-template-homepage .wp-block-cover {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            ";
        }
        
        $css .= "
            /* ==========================================
               2. CĂN CHỈNH ẢNH SẢN PHẨM
               ========================================== */
            .woocommerce ul.products {
                display: grid !important;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important;
                gap: {$spacing}px !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .woocommerce ul.products li.product {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                transition: all 0.3s ease;
            }
            
            .woocommerce ul.products li.product:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            }
            
            /* Căn chỉnh ảnh đồng đều */
            .woocommerce ul.products li.product img {
                width: 100% !important;
                height: 250px !important;
                object-fit: cover !important;
                object-position: center !important;
                transition: transform 0.5s ease !important;
                border-radius: 8px 8px 0 0 !important;
            }
            
            .woocommerce ul.products li.product:hover img {
                transform: scale(1.05);
            }
            
            /* ==========================================
               3. CĂN CHỈNH ẢNH COVER TRONG BLOCKS
               ========================================== */
            .wp-block-cover {
                min-height: 400px;
                border-radius: 12px;
                overflow: hidden;
                margin-bottom: 20px !important;
            }
            
            .wp-block-cover .wp-block-cover__image-background {
                object-fit: cover !important;
                object-position: center !important;
            }
            
            /* Nội dung trong cover */
            .wp-block-cover .wp-block-cover__inner-container {
                max-width: 1200px !important;
                padding: 20px !important;
                margin: 0 auto !important;
            }
            
            /* ==========================================
               4. DÀN ĐỀU NỘI DUNG CÁC CỘT
               ========================================== */
            .home .wp-block-columns .wp-block-column,
            .page-template-template-homepage .wp-block-columns .wp-block-column {
                flex: 1 !important;
                min-width: 0 !important;
            }
            
            /* Căn chỉnh ảnh trong columns */
            .wp-block-columns .wp-block-column figure {
                margin: 0 !important;
                overflow: hidden;
                border-radius: 8px;
                height: 100%;
            }
            
            .wp-block-columns .wp-block-column figure img {
                width: 100% !important;
                height: 300px !important;
                object-fit: cover !important;
                transition: transform 0.4s ease !important;
            }
            
            .wp-block-columns .wp-block-column figure:hover img {
                transform: scale(1.03);
            }
            
            /* ==========================================
               5. LAYOUT CHO CÁC SECTION SẢN PHẨM
               ========================================== */
            .storefront-product-section {
                padding: 20px 0 !important;
            }
            
            .storefront-product-section .section-title {
                font-size: 28px !important;
                font-weight: 700 !important;
                margin-bottom: 25px !important;
                color: #2E7D32 !important;
            }
            
            /* ==========================================
               6. RESPONSIVE
               ========================================== */
            @media (max-width: 992px) {
                .home .wp-block-columns,
                .page-template-template-homepage .wp-block-columns {
                    flex-direction: column !important;
                }
                
                .home .wp-block-columns .wp-block-column {
                    width: 100% !important;
                    flex-basis: 100% !important;
                }
                
                .wp-block-columns .wp-block-column figure img {
                    height: 250px !important;
                }
            }
            
            @media (max-width: 768px) {
                .home .site-content .col-full,
                .page-template-template-homepage .site-content .col-full {
                    padding: 0 15px !important;
                }
                
                .woocommerce ul.products {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 10px !important;
                }
                
                .woocommerce ul.products li.product img {
                    height: 180px !important;
                }
                
                .wp-block-cover {
                    min-height: 250px !important;
                }
                
                .wp-block-columns .wp-block-column figure img {
                    height: 200px !important;
                }
                
                .home .wp-block-group.alignfull,
                .page-template-template-homepage .wp-block-group.alignfull {
                    padding-left: 15px !important;
                    padding-right: 15px !important;
                }
            }
            
            @media (max-width: 480px) {
                .woocommerce ul.products {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 8px !important;
                }
                
                .woocommerce ul.products li.product img {
                    height: 140px !important;
                }
            }
        ";
        
        // Thêm tùy chỉnh layout style
        if ( $layout_style === 'masonry' ) {
            $css .= "
                .woocommerce ul.products {
                    display: block !important;
                    column-count: 4;
                    column-gap: {$spacing}px;
                }
                
                .woocommerce ul.products li.product {
                    break-inside: avoid;
                    margin-bottom: {$spacing}px !important;
                }
                
                @media (max-width: 768px) {
                    .woocommerce ul.products {
                        column-count: 2;
                    }
                }
            ";
        }
        
        return $css;
    }

    /**
     * Đăng ký các tùy chọn trong Customizer
     */
    public function register_customizer_options( $wp_customize ) {
        // Thêm section mới
        $wp_customize->add_section( 'nong_san_layout_section', array(
            'title'    => __( 'Layout Trang Chủ', 'nong-san-layout' ),
            'priority' => 30,
        ) );
        
        // Tùy chọn Full Width
        $wp_customize->add_setting( 'nong_san_full_width', array(
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
        ) );
        
        $wp_customize->add_control( 'nong_san_full_width', array(
            'label'    => __( 'Hiển thị Full Width', 'nong-san-layout' ),
            'section'  => 'nong_san_layout_section',
            'type'     => 'checkbox',
            'description' => 'Bỏ chọn để hiển thị layout có giới hạn width',
        ) );
        
        // Tùy chọn kích thước ảnh
        $wp_customize->add_setting( 'nong_san_image_size', array(
            'default'           => 'medium',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        
        $wp_customize->add_control( 'nong_san_image_size', array(
            'label'    => __( 'Kích thước ảnh', 'nong-san-layout' ),
            'section'  => 'nong_san_layout_section',
            'type'     => 'select',
            'choices'  => array(
                'thumbnail' => 'Nhỏ',
                'medium'    => 'Trung bình',
                'large'     => 'Lớn',
                'full'      => 'Full',
            ),
        ) );
        
        // Tùy chọn style layout
        $wp_customize->add_setting( 'nong_san_layout_style', array(
            'default'           => 'grid',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        
        $wp_customize->add_control( 'nong_san_layout_style', array(
            'label'    => __( 'Style layout', 'nong-san-layout' ),
            'section'  => 'nong_san_layout_section',
            'type'     => 'select',
            'choices'  => array(
                'grid'     => 'Lưới đều',
                'masonry'  => 'Masonry',
                'list'     => 'Danh sách',
            ),
        ) );
        
        // Tùy chọn khoảng cách
        $wp_customize->add_setting( 'nong_san_spacing', array(
            'default'           => '20',
            'sanitize_callback' => 'absint',
        ) );
        
        $wp_customize->add_control( 'nong_san_spacing', array(
            'label'    => __( 'Khoảng cách (px)', 'nong-san-layout' ),
            'section'  => 'nong_san_layout_section',
            'type'     => 'number',
            'input_attrs' => array(
                'min'  => 5,
                'max'  => 50,
                'step' => 1,
            ),
        ) );
    }

    /**
     * Thêm class vào body để tùy chỉnh
     */
    public function add_layout_body_class( $classes ) {
        if ( is_front_page() || is_home() ) {
            $classes[] = 'nong-san-full-layout';
        }
        return $classes;
    }

    /**
     * Tùy chỉnh section hero
     */
    public function customize_hero_section( $heading ) {
        return $heading;
    }
}

// Khởi tạo plugin
new Nong_San_Layout_Customizer();

/**
 * Tạo thư mục assets khi kích hoạt plugin
 */
register_activation_hook( __FILE__, 'nong_san_layout_activate' );
function nong_san_layout_activate() {
    $plugin_dir = plugin_dir_path( __FILE__ );
    
    if ( ! file_exists( $plugin_dir . 'assets' ) ) {
        mkdir( $plugin_dir . 'assets', 0755 );
    }
}
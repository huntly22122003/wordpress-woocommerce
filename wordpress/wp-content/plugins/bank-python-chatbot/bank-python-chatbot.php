<?php
/**
 * Plugin Name: Farm Fresh Chatbot - Nông Sản Sạch
 * Plugin URI: https://yourwebsite.com/
 * Description: Tích hợp chatbot AI hỗ trợ bán nông sản, rau củ quả sạch
 * Version: 2.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: farm-fresh-chatbot
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa hằng số
define('BPC_VERSION', '2.0.0');
define('BPC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BPC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BPC_API_URL', 'http://localhost:8000/api/chat'); // URL FastAPI backend

/**
 * Class Farm_Fresh_Chatbot
 * Main plugin class - Chuyên cho shop nông sản
 */
class Farm_Fresh_Chatbot {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Enqueue scripts và styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Đăng ký shortcode
        add_shortcode('farm_chatbot', [$this, 'render_chatbot']);
        add_shortcode('farm_chatbot_floating', [$this, 'render_floating_chatbot']);
        
        // Đăng ký widget - ĐÃ SỬA: Kiểm tra an toàn
        add_action('widgets_init', [$this, 'register_widget']);
        
        // Thêm vào footer cho floating mode
        add_action('wp_footer', [$this, 'render_floating_if_enabled']);
        
        // AJAX handlers
        add_action('wp_ajax_bpc_send_message', [$this, 'handle_ajax_message']);
        add_action('wp_ajax_nopriv_bpc_send_message', [$this, 'handle_ajax_message']);
        
        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // WooCommerce hooks (nếu WooCommerce active)
        add_action('woocommerce_after_single_product', [$this, 'add_to_product_page']);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'add_product_support_button']);
    }
    
    /**
     * Enqueue CSS và JS
     */
    public function enqueue_assets() {
        if (!is_admin()) {
            wp_enqueue_style(
                'bpc-chatbot-style',
                BPC_PLUGIN_URL . 'assets/css/chatbot-style.css',
                [],
                BPC_VERSION
            );
            
            wp_enqueue_script(
                'bpc-chatbot-script',
                BPC_PLUGIN_URL . 'assets/js/chatbot-script.js',
                ['jquery'],
                BPC_VERSION,
                true
            );
            
            // Truyền dữ liệu từ PHP sang JS - Nội dung NÔNG SẢN
            wp_localize_script('bpc-chatbot-script', 'bpc_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bpc_chat_nonce'),
                'api_url' => BPC_API_URL,
                'site_url' => get_site_url(),
                'is_user_logged_in' => is_user_logged_in(),
                'user_name' => is_user_logged_in() ? wp_get_current_user()->display_name : '',
                'initial_message' => __('🌱 Chào bạn! Tôi là trợ lý của cửa hàng nông sản. Bạn cần tư vấn về rau củ, trái cây hay đặt hàng hôm nay ạ? 🍃', 'farm-fresh-chatbot')
            ]);
        }
    }
    
    /**
     * Render chatbot chính - GIAO DIỆN NÔNG SẢN
     */
    public function render_chatbot($atts = []) {
        $atts = shortcode_atts([
            'title' => __('🌾 Nông Sản Sạch - Chatbot', 'farm-fresh-chatbot'),
            'height' => '500px',
            'width' => '100%',
            'show_header' => 'yes',
            'theme' => 'dark'
        ], $atts);
        
        ob_start();
        ?>
        <div class="bpc-chatbot-container farm-theme" 
             data-title="<?php echo esc_attr($atts['title']); ?>"
             data-height="<?php echo esc_attr($atts['height']); ?>"
             data-width="<?php echo esc_attr($atts['width']); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>">
            
            <?php if ($atts['show_header'] === 'yes') : ?>
            <div class="bpc-chat-header">
                <div class="bpc-header-content">
                    <div class="bpc-header-text">
                        <h3><?php echo esc_html($atts['title']); ?></h3>
                      
                    </div>
                    <button class="bpc-clear-chat-btn" id="bpcClearChatBtn" title="Xoá lịch sử chat">🗑️</button>
                    <button class="bpc-minimize-btn" onclick="toggleChatbot()">−</button>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="bpc-chat-messages" id="bpc-messages">
                <div class="bpc-message bot">
                    <div class="bpc-message-content">
                        <?php _e('🌻 Chào bạn! Tôi là trợ lý của shop nông sản. Hôm nay bạn muốn tìm rau sạch, trái cây tươi hay cần tư vấn món gì ạ? 🥬🍅', 'farm-fresh-chatbot'); ?>
                    </div>
                </div>
            </div>
            
            <div class="bpc-chat-input-area">
                <div class="bpc-input-wrapper">
                    <textarea 
                        id="bpc-message-input" 
                        class="bpc-message-input" 
                        placeholder="<?php esc_attr_e('Nhập câu hỏi về sản phẩm nông sản...', 'farm-fresh-chatbot'); ?>"
                        rows="1"
                    ></textarea>
               <button id="bpc-send-btn" class="bpc-send-btn">
    <svg class="bpc-send-icon" viewBox="0 0 24 24" width="18" height="18">
        <line x1="22" y1="2" x2="11" y2="13" stroke="currentColor" stroke-width="2"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2" stroke="currentColor" stroke-width="2" fill="none"/>
    </svg>
</button>
                </div>
                <div class="bpc-input-footer">
                    <span class="bpc-powered-by">🌾 Nông sản sạch - An toàn cho sức khỏe</span>
                </div>
            </div>
        </div>
        
        <script>
            if (typeof initChatbot === 'function') {
                initChatbot('<?php echo esc_js($atts['title']); ?>');
            }
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render floating chatbot button - Style nông sản
     */
    public function render_floating_chatbot($atts = []) {
        $atts = shortcode_atts([
            'position' => 'bottom-right',
            'button_color' => '#2d6a4f', // Màu xanh lá nông sản
            'welcome_message' => '🌱 Cần tư vấn rau củ sạch?'
        ], $atts);
        
        set_transient('bpc_floating_enabled', true, 0);
        
        ob_start();
        ?>
        <div class="bpc-floating-trigger" data-position="<?php echo esc_attr($atts['position']); ?>">
            <div class="bpc-floating-button" style="background: <?php echo esc_attr($atts['button_color']); ?>">
                <span class="bpc-floating-icon">🥬</span>
                <span class="bpc-floating-text"><?php echo esc_html($atts['welcome_message']); ?></span>
                <span class="bpc-notification-badge" style="display: none;">1</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render floating widget (gọi từ footer)
     */
    public function render_floating_if_enabled() {
        if (get_transient('bpc_floating_enabled')) {
            delete_transient('bpc_floating_enabled');
            echo $this->render_chatbot(['show_header' => 'yes', 'height' => '550px']);
        }
    }
    
    /**
     * AJAX handler cho messages (fallback)
     */
    public function handle_ajax_message() {
        check_ajax_referer('bpc_chat_nonce', 'nonce');
        
        $session_id = sanitize_text_field($_POST['session_id']);
        $message = sanitize_textarea_field($_POST['message']);
        
        // Gọi API Python
        $response = wp_remote_post(BPC_API_URL, [
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'session_id' => $session_id,
                'message' => $message
            ])
        ]);
        
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Lỗi kết nối đến AI server']);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        wp_send_json_success(['reply' => $data['reply'] ?? '🌿 Rất tiếc, tôi chưa hiểu ý bạn. Bạn có thể hỏi về các loại rau củ, trái cây hoặc cách đặt hàng nhé!']);
    }
    
    /**
     * Register Widget - ĐÃ SỬA: Không gây lỗi nếu thiếu file
     */
    public function register_widget() {
        // Kiểm tra file widget.php tồn tại
        $widget_file = BPC_PLUGIN_DIR . 'widget.php';
        
        if (file_exists($widget_file)) {
            require_once $widget_file;
            
            // Kiểm tra class tồn tại trước khi register
            if (class_exists('Farm_Fresh_Chatbot_Widget')) {
                register_widget('Farm_Fresh_Chatbot_Widget');
            } else {
                // Log lỗi nhẹ nhàng trong debug mode, không gây crash
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Farm Fresh Chatbot: Widget class not found. Please check widget.php file.');
                }
            }
        }
        // Nếu không có file widget.php, bỏ qua hoàn toàn (không lỗi)
    }
    
    /**
     * Thêm vào trang sản phẩm WooCommerce
     */
    public function add_to_product_page() {
        if (function_exists('is_product') && is_product()) {
            echo '<div class="bpc-product-chat farm-section">';
            echo $this->render_chatbot(['height' => '400px', 'title' => __('🌽 Hỏi về sản phẩm nông sản', 'farm-fresh-chatbot')]);
            echo '</div>';
        }
    }
    
    /**
     * Thêm nút hỗ trợ bên cạnh nút Add to Cart
     */
    public function add_product_support_button() {
        ?>
        <button type="button" class="bpc-product-support-btn" onclick="openProductChat()">
            🥬 <?php _e('Hỏi về nông sản này', 'farm-fresh-chatbot'); ?>
        </button>
        <style>
            .bpc-product-support-btn {
                margin-top: 10px;
                padding: 10px 20px;
                background: #2d6a4f;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                width: 100%;
                font-weight: 600;
                transition: all 0.3s;
            }
            .bpc-product-support-btn:hover {
                background: #1b4d3a;
                transform: translateY(-2px);
            }
            /* Dark theme override cho chat container */
            .bpc-chatbot-container.farm-theme {
                background: #0d1f12;
                border: 1px solid #2d6a4f;
            }
            .farm-theme .bpc-chat-header {
                background: #0a170e;
                border-bottom-color: #2d6a4f;
            }
            .farm-theme .bpc-status-dot {
                background: #74c69d;
            }
            .farm-theme .bpc-send-btn:hover {
                background: #74c69d;
                color: #0d1f12;
            }
        </style>
        <?php
    }
    
    /**
     * Thêm menu admin
     */
    public function add_admin_menu() {
        add_options_page(
            __('Farm Fresh Chatbot Settings', 'farm-fresh-chatbot'),
            __('🌾 Farm Chatbot', 'farm-fresh-chatbot'),
            'manage_options',
            'farm-fresh-chatbot',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (isset($_POST['submit'])) {
            update_option('bpc_api_url', sanitize_url($_POST['api_url']));
            update_option('bpc_floating_position', sanitize_text_field($_POST['floating_position']));
            echo '<div class="notice notice-success"><p>✅ Cập nhật thành công!</p></div>';
        }
        
        $api_url = get_option('bpc_api_url', BPC_API_URL);
        $floating_position = get_option('bpc_floating_position', 'bottom-right');
        ?>
        <div class="wrap">
            <h1>🌾 Farm Fresh Chatbot - Nông Sản Sạch</h1>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th><label for="api_url">🚀 API URL (Python Backend)</label></th>
                        <td>
                            <input type="url" id="api_url" name="api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text">
                            <p class="description">URL của FastAPI backend (mặc định: http://localhost:8000/api/chat)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="floating_position">📍 Vị trí floating button</label></th>
                        <td>
                            <select name="floating_position" id="floating_position">
                                <option value="bottom-right" <?php selected($floating_position, 'bottom-right'); ?>>Góc phải dưới</option>
                                <option value="bottom-left" <?php selected($floating_position, 'bottom-left'); ?>>Góc trái dưới</option>
                            </select>
                         </td>
                     </tr>
                 </table>
                <?php submit_button('Lưu cài đặt', 'primary', 'submit'); ?>
            </form>
            <hr>
            <h2>🌱 Cách sử dụng shortcode:</h2>
            <p><strong>📌 Shortcode cơ bản:</strong> <code>[farm_chatbot]</code> - Nhúng chatbot vào trang/bài viết</p>
            <p><strong>💫 Shortcode floating:</strong> <code>[farm_chatbot_floating]</code> - Button nổi góc màn hình</p>
            <p><strong>🧩 Widget:</strong> Vào Appearance → Widgets, kéo "Farm Fresh Chatbot" vào sidebar (nếu có file widget.php)</p>
            <p><strong>🛒 WooCommerce:</strong> Tự động xuất hiện trong trang sản phẩm nông sản</p>
            <hr>
            <p><em>🌿 <strong>Gợi ý:</strong> Kết hợp với CSS dark theme để tạo phong cách hiện đại, sang trọng cho shop nông sản.</em></p>
        </div>
        <?php
    }
}

// Tự động thêm floating chatbot nếu muốn (có thể bật/tắt trong setting)
add_action('wp_footer', 'auto_add_farming_chatbot');
function auto_add_farming_chatbot() {
    if (!is_admin() && get_option('bpc_auto_floating', true)) {
        echo '<div style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">';
        echo do_shortcode('[farm_chatbot]');
        echo '</div>';
    }
}

// Khởi tạo plugin
Farm_Fresh_Chatbot::get_instance();
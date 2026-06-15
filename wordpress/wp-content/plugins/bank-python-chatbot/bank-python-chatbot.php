<?php
/**
 * Plugin Name: Bank Python Chatbot
 * Plugin URI: https://yourwebsite.com/
 * Description: Tích hợp chatbot AI từ Python FastAPI vào WordPress/WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: bank-python-chatbot
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Định nghĩa hằng số
define('BPC_VERSION', '1.0.0');
define('BPC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BPC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BPC_API_URL', 'http://localhost:8000/api/chat'); // URL FastAPI backend

/**
 * Class Bank_Python_Chatbot
 * Main plugin class
 */
class Bank_Python_Chatbot {
    
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
        add_shortcode('bank_chatbot', [$this, 'render_chatbot']);
        add_shortcode('bank_chatbot_floating', [$this, 'render_floating_chatbot']);
        
        // Đăng ký widget
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
        // Chỉ load khi cần thiết (tối ưu performance)
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
            
            // Truyền dữ liệu từ PHP sang JS
            wp_localize_script('bpc-chatbot-script', 'bpc_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bpc_chat_nonce'),
                'api_url' => BPC_API_URL,
                'site_url' => get_site_url(),
                'is_user_logged_in' => is_user_logged_in(),
                'user_name' => is_user_logged_in() ? wp_get_current_user()->display_name : '',
                'initial_message' => __('Xin chào! Tôi là trợ lý ảo của ngân hàng. Tôi có thể giúp gì cho anh/chị hôm nay? 💁‍♂️', 'bank-python-chatbot')
            ]);
        }
    }
    
    /**
     * Render chatbot chính
     */
    public function render_chatbot($atts = []) {
        $atts = shortcode_atts([
            'title' => __('Ngân Hàng BankBot', 'bank-python-chatbot'),
            'height' => '500px',
            'width' => '100%',
            'show_header' => 'yes',
            'theme' => 'light'
        ], $atts);
        
        ob_start();
        ?>
        <div class="bpc-chatbot-container" 
             data-title="<?php echo esc_attr($atts['title']); ?>"
             data-height="<?php echo esc_attr($atts['height']); ?>"
             data-width="<?php echo esc_attr($atts['width']); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>">
            
            <?php if ($atts['show_header'] === 'yes') : ?>
            <div class="bpc-chat-header">
                <div class="bpc-header-content">
                    <span class="bpc-icon">🏦</span>
                    <div class="bpc-header-text">
                        <h3><?php echo esc_html($atts['title']); ?></h3>
                        <div class="bpc-status">
                            <span class="bpc-status-dot"></span>
                            <?php _e('Online | Hỗ trợ 24/7', 'bank-python-chatbot'); ?>
                        </div>
                    </div>
                    <!-- ===== THÊM NÚT XOÁ CHAT ===== -->
                    <button class="bpc-clear-chat-btn" id="bpcClearChatBtn" title="Xoá lịch sử chat">🗑️</button>
                    <!-- ===== KẾT THÚC THÊM ===== -->
                    <button class="bpc-minimize-btn" onclick="toggleChatbot()">−</button>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="bpc-chat-messages" id="bpc-messages">
                <div class="bpc-message bot">
                    <div class="bpc-message-content">
                        <?php _e('Xin chào! Tôi là trợ lý ảo của ngân hàng. Tôi có thể giúp gì cho anh/chị hôm nay? 💁‍♂️', 'bank-python-chatbot'); ?>
                    </div>
                </div>
            </div>
            
            <div class="bpc-chat-input-area">
                <div class="bpc-input-wrapper">
                    <textarea 
                        id="bpc-message-input" 
                        class="bpc-message-input" 
                        placeholder="<?php esc_attr_e('Nhập câu hỏi của bạn...', 'bank-python-chatbot'); ?>"
                        rows="1"
                    ></textarea>
                    <button id="bpc-send-btn" class="bpc-send-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
                <div class="bpc-input-footer">
                    <span class="bpc-powered-by">Powered by AI</span>
                </div>
            </div>
        </div>
        
        <script>
            // Khởi tạo chatbot cho instance này
            if (typeof initChatbot === 'function') {
                initChatbot('<?php echo esc_js($atts['title']); ?>');
            }
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render floating chatbot button
     */
    public function render_floating_chatbot($atts = []) {
        $atts = shortcode_atts([
            'position' => 'bottom-right',
            'button_color' => '#1e3c72',
            'welcome_message' => 'Cần giúp đỡ? Chat với tôi!'
        ], $atts);
        
        set_transient('bpc_floating_enabled', true, 0);
        
        ob_start();
        ?>
        <div class="bpc-floating-trigger" data-position="<?php echo esc_attr($atts['position']); ?>">
            <div class="bpc-floating-button" style="background: <?php echo esc_attr($atts['button_color']); ?>">
                <span class="bpc-floating-icon">💬</span>
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
     * AJAX handler cho messages (fallback khi không gọi trực tiếp được)
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
        
        wp_send_json_success(['reply' => $data['reply'] ?? 'Xin lỗi, tôi chưa hiểu câu hỏi của anh/chị.']);
    }
    
    /**
     * Register Widget
     */
    public function register_widget() {
        require_once BPC_PLUGIN_DIR . 'widget.php';
        register_widget('Bank_Python_Chatbot_Widget');
    }
    
    /**
     * Thêm vào trang sản phẩm WooCommerce
     */
    public function add_to_product_page() {
        if (function_exists('is_product') && is_product()) {
            echo '<div class="bpc-product-chat">';
            echo $this->render_chatbot(['height' => '400px', 'title' => __('Hỗ trợ sản phẩm', 'bank-python-chatbot')]);
            echo '</div>';
        }
    }
    
    /**
     * Thêm nút hỗ trợ bên cạnh nút Add to Cart
     */
    public function add_product_support_button() {
        ?>
        <button type="button" class="bpc-product-support-btn" onclick="openProductChat()">
            💬 <?php _e('Hỏi về sản phẩm', 'bank-python-chatbot'); ?>
        </button>
        <style>
            .bpc-product-support-btn {
                margin-top: 10px;
                padding: 10px 20px;
                background: #f0f0f0;
                border: 1px solid #ddd;
                border-radius: 5px;
                cursor: pointer;
                width: 100%;
            }
        </style>
        <?php
    }
    
    /**
     * Thêm menu admin
     */
    public function add_admin_menu() {
        add_options_page(
            __('Bank Python Chatbot Settings', 'bank-python-chatbot'),
            __('Bank Chatbot', 'bank-python-chatbot'),
            'manage_options',
            'bank-python-chatbot',
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
            echo '<div class="notice notice-success"><p>Cập nhật thành công!</p></div>';
        }
        
        $api_url = get_option('bpc_api_url', BPC_API_URL);
        $floating_position = get_option('bpc_floating_position', 'bottom-right');
        ?>
        <div class="wrap">
            <h1><?php _e('Bank Python Chatbot Settings', 'bank-python-chatbot'); ?></h1>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th><label for="api_url">API URL (Python Backend)</label></th>
                        <td>
                            <input type="url" id="api_url" name="api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text">
                            <p class="description">URL của FastAPI backend (mặc định: http://localhost:8000/api/chat)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="floating_position">Vị trí floating button</label></th>
                        <td>
                            <select name="floating_position" id="floating_position">
                                <option value="bottom-right" <?php selected($floating_position, 'bottom-right'); ?>>Góc phải dưới</option>
                                <option value="bottom-left" <?php selected($floating_position, 'bottom-left'); ?>>Góc trái dưới</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <hr>
            <h2>Cách sử dụng:</h2>
            <p><strong>Shortcode:</strong> <code>[bank_chatbot]</code> - Nhúng chatbot vào trang/bài viết</p>
            <p><strong>Shortcode floating:</strong> <code>[bank_chatbot_floating]</code> - Button nổi góc màn hình</p>
            <p><strong>Widget:</strong> Vào Appearance → Widgets, kéo "Bank Python Chatbot" vào sidebar</p>
            <p><strong>WooCommerce:</strong> Tự động xuất hiện trong trang sản phẩm</p>
        </div>
        <?php
    }
}

add_action('wp_footer', 'auto_add_floating_chatbot');
function auto_add_floating_chatbot() {
    // Chỉ hiển thị trên frontend, không hiển thị trong admin
    if (!is_admin()) {
        echo '<div style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">';
        echo do_shortcode('[bank_chatbot]');
        echo '</div>';
    }
}

// Khởi tạo plugin
Bank_Python_Chatbot::get_instance();
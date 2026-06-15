// Bank Python Chatbot - Main JavaScript
(function($) {
    'use strict';
    
    let sessionId = localStorage.getItem('bpc_session_id');
    let isTyping = false;
    let currentChatbot = null;
    
    // Khởi tạo session ID
    if (!sessionId) {
        sessionId = 'wp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('bpc_session_id', sessionId);
    }
    
    // Hàm gửi tin nhắn
    window.sendChatMessage = async function(message, containerId) {
        const messagesContainer = document.getElementById(containerId || 'bpc-messages');
        if (!messagesContainer) return;
        
        // Hiển thị tin nhắn user
        addMessage(message, 'user', messagesContainer);
        
        // Hiển thị typing indicator
        showTypingIndicator(messagesContainer);
        
        try {
            // Thử gọi API Python trực tiếp
            let response = await fetch(bpc_ajax.api_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    message: message
                })
            });
            
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            
            const data = await response.json();
            removeTypingIndicator(messagesContainer);
            addMessage(data.reply, 'bot', messagesContainer);
            
        } catch (error) {
            console.error('Direct API error:', error);
            
            // Fallback: gọi qua AJAX WordPress
            try {
                const ajaxResponse = await $.ajax({
                    url: bpc_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bpc_send_message',
                        nonce: bpc_ajax.nonce,
                        session_id: sessionId,
                        message: message
                    }
                });
                
                removeTypingIndicator(messagesContainer);
                
                if (ajaxResponse.success) {
                    addMessage(ajaxResponse.data.reply, 'bot', messagesContainer);
                } else {
                    addMessage('Xin lỗi, tôi đang gặp sự cố. Vui lòng thử lại sau! 😅', 'bot', messagesContainer);
                }
            } catch (ajaxError) {
                removeTypingIndicator(messagesContainer);
                addMessage('Không thể kết nối đến server. Vui lòng kiểm tra lại kết nối! 🔌', 'bot', messagesContainer);
            }
        }
    };
    
    // Hàm thêm tin nhắn vào container
    function addMessage(text, sender, container) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `bpc-message ${sender}`;
        messageDiv.innerHTML = `<div class="bpc-message-content">${escapeHtml(text)}</div>`;
        container.appendChild(messageDiv);
        
        // Scroll xuống cuối
        container.scrollTop = container.scrollHeight;
        
        // Lưu vào localStorage (optional)
        saveMessageToHistory(sender, text);
    }
    
    // Hàm hiển thị typing indicator
    function showTypingIndicator(container) {
        if (isTyping) return;
        isTyping = true;
        
        const typingDiv = document.createElement('div');
        typingDiv.className = 'bpc-message bot';
        typingDiv.id = 'bpc-typing-indicator';
        typingDiv.innerHTML = `
            <div class="bpc-typing-indicator">
                <span></span><span></span><span></span>
            </div>
        `;
        container.appendChild(typingDiv);
        container.scrollTop = container.scrollHeight;
    }
    
    // Hàm xóa typing indicator
    function removeTypingIndicator(container) {
        const indicator = document.getElementById('bpc-typing-indicator');
        if (indicator) {
            indicator.remove();
        }
        isTyping = false;
    }
    
    // Escape HTML để tránh XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Lưu lịch sử chat (tối đa 50 tin nhắn)
    function saveMessageToHistory(sender, message) {
        let history = localStorage.getItem('bpc_chat_history');
        history = history ? JSON.parse(history) : [];
        
        history.push({
            sender: sender,
            message: message,
            timestamp: Date.now()
        });
        
        // Giữ lại 50 tin nhắn gần nhất
        if (history.length > 50) {
            history = history.slice(-50);
        }
        
        localStorage.setItem('bpc_chat_history', JSON.stringify(history));
    }
    
    // Toggle chat window (cho floating mode)
    window.toggleChatbot = function() {
        const container = document.querySelector('.bpc-chatbot-container');
        if (container) {
            container.classList.toggle('minimized');
            
            const minimizeBtn = document.querySelector('.bpc-minimize-btn');
            if (minimizeBtn) {
                minimizeBtn.textContent = container.classList.contains('minimized') ? '+' : '−';
            }
        }
    };
    
    // ===== THÊM HÀM XOÁ LỊCH SỬ CHAT =====
    window.clearChatHistory = function() {
        if (confirm('🗑️ Bạn có chắc chắn muốn xoá toàn bộ lịch sử chat?')) {
            // Xoá localStorage
            localStorage.removeItem('bpc_chat_history');
            localStorage.removeItem('bpc_session_id');
            
            // Tạo session mới
            sessionId = 'wp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('bpc_session_id', sessionId);
            
            // Xoá toàn bộ tin nhắn trong container
            const messagesContainer = document.getElementById('bpc-messages');
            if (messagesContainer) {
                // Giữ lại tin nhắn chào mừng
                messagesContainer.innerHTML = `
                    <div class="bpc-message bot">
                        <div class="bpc-message-content">
                            ${bpc_ajax.initial_message || 'Xin chào! Tôi là trợ lý ảo của ngân hàng. Tôi có thể giúp gì cho anh/chị hôm nay? 💁‍♂️'}
                        </div>
                    </div>
                `;
            }
            
            // Thông báo
            alert('✅ Đã xoá lịch sử chat!');
        }
    };
    // ===== KẾT THÚC THÊM HÀM XOÁ =====
    
    // Khởi tạo chatbot cho một instance
    window.initChatbot = function(title) {
        const input = document.getElementById('bpc-message-input');
        const sendBtn = document.getElementById('bpc-send-btn');
        
        // ===== THÊM XỬ LÝ NÚT XOÁ =====
        const clearBtn = document.getElementById('bpcClearChatBtn');
        if (clearBtn) {
            clearBtn.onclick = function(e) {
                e.stopPropagation();
                window.clearChatHistory();
            };
        }
        // ===== KẾT THÚC THÊM =====
        
        if (!input || !sendBtn) return;
        
        // Hàm gửi tin nhắn
        const sendMessage = () => {
            const message = input.value.trim();
            if (message === '') return;
            
            sendChatMessage(message, 'bpc-messages');
            input.value = '';
            input.style.height = 'auto';
        };
        
        // Event listeners
        sendBtn.addEventListener('click', sendMessage);
        
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // Auto-resize textarea
        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        
        // Focus vào input
        input.focus();
    };
    
    // Khởi tạo floating button
    $(document).ready(function() {
        // Xử lý floating button
        $('.bpc-floating-button').on('click', function() {
            const container = $('.bpc-chatbot-container');
            if (container.length === 0) {
                // Nếu chưa có container, tạo mới
                location.reload(); // Hoặc load dynamic
            } else {
                container.toggleClass('minimized');
                if (!container.hasClass('minimized')) {
                    container.find('.bpc-message-input').focus();
                }
            }
        });
        
        // Auto-initialize cho tất cả chatbot containers
        if ($('.bpc-chatbot-container').length) {
            initChatbot();
        }
        
        // Load chat history nếu có
        const history = localStorage.getItem('bpc_chat_history');
        if (history && $('.bpc-chat-messages').children().length <= 1) {
            const messages = JSON.parse(history);
            const container = document.getElementById('bpc-messages');
            if (container) {
                // Chỉ load 10 tin nhắn gần nhất
                messages.slice(-10).forEach(msg => {
                    addMessage(msg.message, msg.sender, container);
                });
            }
        }
    });
    
    // Product support button
    window.openProductChat = function() {
        const productName = $('.product_title').text();
        const message = `Tôi cần hỗ trợ về sản phẩm: ${productName}`;
        $('#bpc-message-input').val(message);
        sendChatMessage(message, 'bpc-messages');
        
        // Scroll đến chatbot
        $('html, body').animate({
            scrollTop: $('.bpc-product-chat').offset().top - 100
        }, 500);
    };
    
})(jQuery);
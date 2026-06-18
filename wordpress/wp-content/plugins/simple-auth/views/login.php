<?php
// Xử lý login
$login_error = sa_handle_login();

// Lấy URL gốc của site
$site_url = home_url('/');
$register_url = home_url('/sa-register/');
$forgot_url = home_url('/sa-forgot/');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nông Trại Xanh | Đăng Nhập</title>
    <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . '../assets/css/style-login.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Thêm style cho nút đăng ký */
        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .btn-register {
            width: 100%;
            background: linear-gradient(95deg, #f0a34b, #e8891a);
            border: none;
            padding: 14px 20px;
            border-radius: 50px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 14px rgba(232, 137, 26, 0.25);
        }
        
        .btn-register:hover {
            background: linear-gradient(95deg, #f5b15a, #f09a2e);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(232, 137, 26, 0.35);
        }
        
        .btn-register:active {
            transform: translateY(0px);
        }
    </style>
</head>
<body>
    <div class="farm-wrapper" id="mainContent">
        <div class="farm-container">
            <div class="farm-hero">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="logo-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h1>Nông Sản<br>Sạch</h1>
                    <div class="hero-line"></div>
                    <p>Nông sản tươi ngon -<br>Giao hàng tận nhà</p>
                    <div class="hero-features">
                        <span><i class="fas fa-leaf"></i> Hữu cơ</span>
                        <span><i class="fas fa-tractor"></i> Canh tác bền vững</span>
                        <span><i class="fas fa-apple-alt"></i> Nông sản sạch</span>
                    </div>
                </div>
            </div>
            
            <div class="farm-login">
                <div class="login-card">
                    <div class="login-header">
                        <div class="header-badge">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <h2>Đăng nhập</h2>
                        <p>Vui lòng đăng nhập để mua sắm</p>
                    </div>
                    
                    <form method="POST" action="" class="login-form">
                        <div class="input-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" placeholder="nongdan@nongsan.vn" required>
                        </div>
                        
                        <div class="input-group">
                            <label><i class="fas fa-lock"></i> Mật khẩu</label>
                            <div class="password-field">
                                <input type="password" name="password" id="password" placeholder="••••••••" required>
                                <span class="toggle-eye" onclick="togglePassword()">
                                    <i class="far fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox">
                                <input type="checkbox" name="remember_me">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="#" class="forgot" id="forgotPasswordBtn">Quên mật khẩu?</a>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="sa_login_submit" class="btn-login">
                                <i class="fas fa-plug"></i> Đăng nhập
                            </button>
                            <button type="button" class="btn-register" id="flipToRegisterBtn">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </button>
                        </div>
                        
                        <?php if ($login_error): ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $login_error; ?>
                        </div>
                        <?php endif; ?>
                    </form>
                    
                    <div class="register-prompt">
                        <div class="demo-info">
                            <i class="fas fa-info-circle"></i> Demo: nongdan@nongsan.vn / 123456
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="farm-footer">
            <p><i class="fas fa-tree"></i> Nông sản Việt - Tinh hoa từ đất mẹ <i class="fas fa-hand-holding-heart"></i></p>
        </footer>
    </div>

    <script>
        // Biến toàn cục từ PHP
        var registerUrl = '<?php echo $register_url; ?>';
        var forgotUrl = '<?php echo $forgot_url; ?>';
        
        function togglePassword() {
            var pwd = document.getElementById("password");
            var eye = document.querySelector(".toggle-eye i");
            if (pwd.type === "password") {
                pwd.type = "text";
                eye.classList.remove("fa-eye-slash");
                eye.classList.add("fa-eye");
            } else {
                pwd.type = "password";
                eye.classList.remove("fa-eye");
                eye.classList.add("fa-eye-slash");
            }
        }
        
        let isTransitioning = false;
        
        function transitionTo(targetUrl) {
            if (isTransitioning) return;
            if (!targetUrl || targetUrl === '#') {
                console.error('Invalid URL:', targetUrl);
                return;
            }
            
            isTransitioning = true;
            
            // Thêm hiệu ứng fade out
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.add('fade-out');
            }
            
            // Tạo lớp chuyển tiếp
            const transitionLayer = document.createElement('div');
            transitionLayer.className = 'page-transition';
            document.body.appendChild(transitionLayer);
            
            setTimeout(() => transitionLayer.classList.add('active'), 10);
            
            // Chuyển hướng sau hiệu ứng
            setTimeout(() => { 
                window.location.href = targetUrl; 
            }, 400);
        }
        
        // Xử lý nút đăng ký với kiểm tra
        const registerBtn = document.getElementById('flipToRegisterBtn');
        if (registerBtn) {
            registerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Chuyển đến trang đăng ký:', registerUrl);
                transitionTo(registerUrl);
            });
        }
        
        // Xử lý nút quên mật khẩu
        const forgotBtn = document.getElementById('forgotPasswordBtn');
        if (forgotBtn) {
            forgotBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Chuyển đến trang quên mật khẩu:', forgotUrl);
                transitionTo(forgotUrl);
            });
        }
        
        // Hiệu ứng fade in khi load trang
        window.addEventListener('load', function() {
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.add('fade-in');
                setTimeout(() => mainContent.classList.remove('fade-in'), 500);
            }
        });
        
        // Prefetch các trang để tăng tốc độ
        if ('IntersectionObserver' in window) {
            const links = [registerUrl, forgotUrl];
            links.forEach(link => {
                if (link && link !== '#') {
                    const linkTag = document.createElement('link');
                    linkTag.rel = 'prefetch';
                    linkTag.href = link;
                    linkTag.as = 'document';
                    document.head.appendChild(linkTag);
                }
            });
        }
        
        // Xử lý form submit - giữ nguyên logic
        const loginForm = document.querySelector('.login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const email = this.querySelector('input[name="email"]').value;
                const password = this.querySelector('input[name="password"]').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Vui lòng nhập đầy đủ email và mật khẩu');
                    return false;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Vui lòng nhập email hợp lệ');
                    return false;
                }
            });
        }
    </script>
</body>
</html>
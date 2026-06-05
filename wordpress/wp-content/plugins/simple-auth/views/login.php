<?php
session_start();

if (!function_exists('sa_handle_login_view')) {
    function sa_handle_login_view() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sa_login_submit'])) {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember_me']);
            
            $valid_users = [
                'nongdan@nongsan.vn' => '123456',
                'traiviet@farm.vn' => 'traicay2025',
                'admin@nongsam.vn' => 'farm2025'
            ];
            
            if (empty($email) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ email và mật khẩu!";
            } elseif (isset($valid_users[$email]) && $valid_users[$email] === $password) {
                if ($remember) setcookie("farm_user", $email, time() + 86400 * 7, "/");
                $_SESSION['farm_loggedin'] = true;
                $_SESSION['farm_email'] = $email;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Email hoặc mật khẩu không đúng!<br><small>(Gợi ý: nongdan@nongsan.vn / 123456)</small>";
            }
        }
        return $error ?? null;
    }
}
$login_error = sa_handle_login_view();
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
        /* Animation chuyển cảnh nghiêm túc */
        .fade-out {
            animation: fadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-8px); }
        }
        .fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(12px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .page-transition {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #2b6e2f;
            z-index: 10000;
            transform: scaleY(0);
            transform-origin: center;
            pointer-events: none;
        }
        .page-transition.active {
            animation: pageReveal 0.45s cubic-bezier(0.65, 0, 0.35, 1) forwards;
        }
        @keyframes pageReveal {
            0% { transform: scaleY(0); opacity: 0; }
            30% { transform: scaleY(1); opacity: 1; }
            70% { transform: scaleY(1); opacity: 1; }
            100% { transform: scaleY(0); opacity: 0; }
        }
        .no-click { pointer-events: none; }
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
                            <a href="#" class="forgot" id="flipToRegisterBtn">Chưa có tài khoản? Đăng ký</a>
                        </div>
                        
                        <button type="submit" name="sa_login_submit" class="btn-login">
                            <i class="fas fa-plug"></i> Đăng nhập
                        </button>
                        
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
            isTransitioning = true;
            document.getElementById('mainContent').classList.add('fade-out');
            const transitionLayer = document.createElement('div');
            transitionLayer.className = 'page-transition';
            document.body.appendChild(transitionLayer);
            setTimeout(() => transitionLayer.classList.add('active'), 10);
            setTimeout(() => { window.location.href = targetUrl; }, 400);
        }
        
        document.getElementById('flipToRegisterBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            transitionTo('http://localhost/sa-register/');
        });
        
        window.addEventListener('load', function() {
            const mainContent = document.getElementById('mainContent');
            mainContent.classList.add('fade-in');
            setTimeout(() => mainContent.classList.remove('fade-in'), 500);
        });
        
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = 'http://localhost/sa-register/';
        document.head.appendChild(link);
    </script>
</body>
</html>
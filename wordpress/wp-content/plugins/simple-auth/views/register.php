<?php

// Xử lý đăng ký bước 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sa_register_submit'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Vui lòng nhập tên đăng nhập!';
    }
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập email!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ!';
    }
    if (empty($password)) {
        $errors[] = 'Vui lòng nhập mật khẩu!';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
    }
    
    if (empty($errors)) {
        // Tạo OTP ngẫu nhiên 6 số
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Lưu vào session để xác thực sau
        $_SESSION['temp_register'] = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'otp' => $otp,
            'otp_expiry' => time() + 300
        ];
        
        $message = "Đã gửi mã OTP đến email của bạn! (Mã test: $otp)";
        $step = 'otp';
    } else {
        $error = implode('<br>', $errors);
        $step = 'register';
    }
}

// Xử lý xác nhận OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sa_otp_submit'])) {
    $otp_input = trim($_POST['otp'] ?? '');
    
    if (isset($_SESSION['temp_register']) && $_SESSION['temp_register']['otp'] === $otp_input) {
        if (time() <= $_SESSION['temp_register']['otp_expiry']) {
            $success = "Đăng ký thành công! Chuyển về trang đăng nhập...";
            
            // TODO: Lưu vào database tại đây
            
            unset($_SESSION['temp_register']);
            $step = 'success';
        } else {
            $error = "Mã OTP đã hết hạn! Vui lòng đăng ký lại.";
            $step = 'register';
            unset($_SESSION['temp_register']);
        }
    } else {
        $error = "Mã OTP không đúng! Vui lòng thử lại.";
        $step = 'otp';
    }
}

// Mặc định hiển thị form đăng ký
if (!isset($step)) {
    $step = 'register';
}

// Xử lý redirect với hiệu ứng chuyển trang
if ($step === 'success' && isset($success)) {
    // Tự động chuyển về login sau 2 giây
    echo '<script>
        setTimeout(function() {
            let mainContent = document.getElementById("mainContent");
            if (mainContent) mainContent.classList.add("fade-out");
            const transitionLayer = document.createElement("div");
            transitionLayer.className = "page-transition";
            document.body.appendChild(transitionLayer);
            setTimeout(() => transitionLayer.classList.add("active"), 10);
            setTimeout(() => { window.location.href = "http://localhost/sa-login/"; }, 400);
        }, 1500);
    </script>';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nông Sản Sạch | Đăng Ký</title>
    <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . '../assets/css/style-register.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
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
            
            <div class="farm-register">
                <div class="register-card">
                    <div class="register-header">
                        <div class="header-badge">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2>Đăng ký</h2>
                        <p>Tạo tài khoản để mua sắm dễ dàng</p>
                    </div>
                    
                    <!-- Progress Bar - chỉ 2 bước -->
                    <div class="progress-container">
                        <div class="progress-steps">
                            <div class="progress-line" id="progressLine"></div>
                            <div class="step" id="step1">
                                <div class="step-circle">1</div>
                                <div class="step-label">Thông tin</div>
                            </div>
                            <div class="step" id="step2">
                                <div class="step-circle">2</div>
                                <div class="step-label">Xác thực OTP</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bước 1: Thông tin cơ bản -->
                    <div id="step1Content" class="step-content <?php echo $step !== 'register' ? 'hidden' : ''; ?>">
                        <form id="sa-register-form" method="POST">
                            <div class="input-group">
                                <label><i class="fas fa-user"></i> Họ và tên</label>
                                <input type="text" name="username" placeholder="Nguyễn Văn A" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                            
                            <div class="input-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" placeholder="nongdan@nongsan.vn" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="input-group">
                                <label><i class="fas fa-lock"></i> Mật khẩu</label>
                                <div class="password-field">
                                    <input type="password" name="password" id="password" placeholder="•••••••• (tối thiểu 6 ký tự)" required>
                                    <span class="toggle-eye" onclick="togglePassword()">
                                        <i class="far fa-eye-slash"></i>
                                    </span>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="sa_register_submit" class="btn-register">
                                <i class="fas fa-paper-plane"></i> Tiếp theo - Gửi OTP
                            </button>
                        </form>
                    </div>
                    
                    <!-- Bước 2: Nhập OTP -->
                    <div id="step2Content" class="step-content <?php echo $step !== 'otp' ? 'hidden' : ''; ?>">
                        <form id="sa-otp-form" method="POST">
                            <div class="input-group">
                                <label><i class="fas fa-key"></i> Mã OTP (6 số)</label>
                                <input type="text" name="otp" maxlength="6" placeholder="Nhập 6 số OTP" required autocomplete="off" style="text-align: center; font-size: 1.2rem; letter-spacing: 5px;">
                            </div>
                            
                            <button type="submit" name="sa_otp_submit" class="btn-register" style="margin-top: 1rem;">
                                <i class="fas fa-check-circle"></i> Xác thực OTP
                            </button>
                            <button type="button" id="backToStep1Btn" class="btn-back" style="margin-top: 0.8rem;">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </button>
                        </form>
                    </div>
                    
                    <!-- Bước thành công -->
                    <div id="step3Content" class="step-content <?php echo $step !== 'success' ? 'hidden' : ''; ?>">
                        <div style="text-align: center; padding: 2rem 0;">
                            <div style="width: 80px; height: 80px; background: #4cae3c; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fas fa-check" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h3 style="color: #2d5a3b; margin-bottom: 0.5rem;">Đăng ký thành công!</h3>
                            <p style="color: #8b7a5a;"><?php echo $success ?? 'Tài khoản đã được tạo thành công!'; ?></p>
                        </div>
                    </div>
                    
                    <?php if (isset($error)): ?>
                    <div class="message-area">
                        <div class="message error">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($message)): ?>
                    <div class="message-area">
                        <div class="message success">
                            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="login-prompt">
                        <p>Đã có tài khoản? <a href="#" id="flipToLoginBtn">Đăng nhập ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="farm-footer">
            <p><i class="fas fa-tree"></i> Nông sản Việt - Tinh hoa từ đất mẹ <i class="fas fa-hand-holding-heart"></i></p>
        </footer>
    </div>

    <script>
        // DOM elements
        const step1El = document.getElementById('step1');
        const step2El = document.getElementById('step2');
        const step1Content = document.getElementById('step1Content');
        const step2Content = document.getElementById('step2Content');
        const step3Content = document.getElementById('step3Content');
        const progressLine = document.getElementById('progressLine');
        
        let currentStep = <?php echo $step === 'register' ? 1 : ($step === 'otp' ? 2 : 3); ?>;
        
        function updateProgress(step) {
            const steps = [step1El, step2El];
            steps.forEach((s, idx) => {
                s.classList.remove('active', 'completed');
                if (idx + 1 < step) {
                    s.classList.add('completed');
                } else if (idx + 1 === step) {
                    s.classList.add('active');
                }
            });
            const percent = step === 2 ? 100 : 0;
            progressLine.style.width = percent + '%';
        }
        
        function goToStep(step) {
            currentStep = step;
            updateProgress(step);
            
            step1Content.classList.add('hidden');
            step2Content.classList.add('hidden');
            step3Content.classList.add('hidden');
            
            if (step === 1) step1Content.classList.remove('hidden');
            if (step === 2) step2Content.classList.remove('hidden');
            if (step === 3) step3Content.classList.remove('hidden');
        }
        
        // Password strength
        function checkPasswordStrength() {
            const password = document.getElementById('password');
            if (!password) return;
            const strengthFill = document.getElementById('strengthFill');
            let strength = 0;
            
            if (password.value.length >= 6) strength = 25;
            if (password.value.length >= 8) strength = 50;
            if (/[A-Z]/.test(password.value)) strength += 25;
            if (/[0-9]/.test(password.value)) strength += 25;
            if (strength > 100) strength = 100;
            
            strengthFill.style.width = strength + '%';
            if (strength < 30) strengthFill.style.backgroundColor = '#e74c3c';
            else if (strength < 60) strengthFill.style.backgroundColor = '#f39c12';
            else strengthFill.style.backgroundColor = '#27ae60';
        }
        
        // Toggle password
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.querySelector('.toggle-eye i');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                pwd.type = 'password';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }
        
        // Transition effect giống login
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
        
        // Event listeners
        document.getElementById('backToStep1Btn')?.addEventListener('click', () => {
            transitionTo(window.location.pathname);
        });
        
        document.getElementById('flipToLoginBtn')?.addEventListener('click', (e) => {
            e.preventDefault();
            transitionTo('http://localhost/sa-login/');
        });
        
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', checkPasswordStrength);
        }
        
        // Animation khi load
        window.addEventListener('load', () => {
            const mainContent = document.getElementById('mainContent');
            mainContent.classList.add('fade-in');
            setTimeout(() => mainContent.classList.remove('fade-in'), 500);
            updateProgress(currentStep);
        });
        
        // Prefetch login page
        const prefetch = document.createElement('link');
        prefetch.rel = 'prefetch';
        prefetch.href = 'http://localhost/sa-login/';
        document.head.appendChild(prefetch);
    </script>
</body>
</html>
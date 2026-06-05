<?php
session_start();

// Khởi tạo step nếu chưa có
if (!isset($_SESSION['register_step'])) {
    $_SESSION['register_step'] = 1;
}
if (!isset($_SESSION['register_data'])) {
    $_SESSION['register_data'] = [];
}

// Xử lý AJAX gửi OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_send_otp'])) {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($email) || empty($full_name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
        exit();
    }
    
    if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ!']);
        exit();
    }
    
    // Lưu dữ liệu tạm vào session
    $_SESSION['register_data']['full_name'] = $full_name;
    $_SESSION['register_data']['email'] = $email;
    $_SESSION['register_data']['phone'] = $phone;
    
    // Tạo OTP (6 số)
    $otp_code = sprintf("%06d", mt_rand(1, 999999));
    $_SESSION['sa_otp_' . $email] = [
        'code' => $otp_code,
        'expiry' => time() + 180, // 3 phút
        'full_name' => $full_name,
        'phone' => $phone
    ];
    
    // Trong môi trường thực tế: gửi email tại đây
    // mail($email, "Mã OTP đăng ký", "Mã OTP của bạn là: " . $otp_code);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Mã OTP đã được gửi đến email của bạn',
        'debug_otp' => $otp_code // Chỉ dùng để test, xóa khi deploy
    ]);
    exit();
}

// Xử lý xác nhận OTP (Bước 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_verify_otp'])) {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($email) || empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã OTP!']);
        exit();
    }
    
    if (isset($_SESSION['sa_otp_' . $email]) && $_SESSION['sa_otp_' . $email]['code'] === $otp) {
        if (time() <= $_SESSION['sa_otp_' . $email]['expiry']) {
            $_SESSION['register_step'] = 3;
            echo json_encode(['success' => true, 'message' => 'Xác thực thành công!']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Mã OTP đã hết hạn! Vui lòng gửi lại.']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Mã OTP không đúng!']);
        exit();
    }
}

// Xử lý tạo mật khẩu (Bước 3)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_create_password'])) {
    header('Content-Type: application/json');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mật khẩu!']);
        exit();
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự!']);
        exit();
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit();
    }
    
    // Lưu tài khoản (giả lập, thực tế lưu database)
    $email = $_SESSION['register_data']['email'] ?? '';
    $full_name = $_SESSION['register_data']['full_name'] ?? '';
    $phone = $_SESSION['register_data']['phone'] ?? '';
    
    // TODO: Lưu vào database tại đây
    
    // Xóa session sau khi đăng ký thành công
    unset($_SESSION['sa_otp_' . $email]);
    unset($_SESSION['register_step']);
    unset($_SESSION['register_data']);
    
    echo json_encode(['success' => true, 'message' => 'Đăng ký thành công! Chuyển về trang đăng nhập...']);
    exit();
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
    <style>
        /* ========== PROGRESS BAR STYLES ========== */
        .progress-container {
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 2rem;
        }
        
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 28px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #e2e8d5;
            z-index: 1;
        }
        
        .progress-line {
            position: absolute;
            top: 28px;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(95deg, #d4752e, #b85f20);
            z-index: 2;
            transition: width 0.4s ease;
        }
        
        .step {
            position: relative;
            z-index: 3;
            text-align: center;
            flex: 1;
        }
        
        .step-circle {
            width: 56px;
            height: 56px;
            background: #f0e8dc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 700;
            font-size: 1.2rem;
            color: #9b6a3a;
            transition: all 0.3s ease;
            border: 2px solid #e2e8d5;
        }
        
        .step.active .step-circle {
            background: linear-gradient(95deg, #d4752e, #b85f20);
            color: white;
            border-color: #d4752e;
            box-shadow: 0 4px 12px rgba(212, 117, 46, 0.3);
            transform: scale(1.05);
        }
        
        .step.completed .step-circle {
            background: #4cae3c;
            color: white;
            border-color: #4cae3c;
        }
        
        .step-label {
            font-size: 0.75rem;
            color: #8b7a5a;
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: #d4752e;
            font-weight: 600;
        }
        
        /* Step content */
        .step-content {
            animation: fadeInStep 0.4s ease;
        }
        
        @keyframes fadeInStep {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* OTP input group */
        .otp-input-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 1.5rem 0;
        }
        
        .otp-input {
            width: 55px;
            height: 65px;
            text-align: center;
            font-size: 1.8rem;
            font-weight: 600;
            border: 2px solid #e2e8d5;
            border-radius: 16px;
            background: #ffffff;
            transition: all 0.2s;
        }
        
        .otp-input:focus {
            border-color: #d4752e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(212, 117, 46, 0.2);
            transform: translateY(-2px);
        }
        
        .timer-text {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #e67e22;
            font-weight: 500;
        }
        
        .resend-otp {
            text-align: center;
            margin-top: 1rem;
        }
        
        .resend-otp button {
            background: none;
            border: none;
            color: #d4752e;
            cursor: pointer;
            font-weight: 600;
            text-decoration: underline;
        }
        
        .resend-otp button:disabled {
            color: #ccc;
            cursor: not-allowed;
            text-decoration: none;
        }
        
        /* Password strength */
        .password-strength {
            margin-top: 8px;
            font-size: 0.7rem;
        }
        
        .strength-bar {
            height: 4px;
            background: #e2e8d5;
            border-radius: 4px;
            margin-top: 5px;
            width: 100%;
        }
        
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s;
        }
        
        /* Animation chuyển cảnh */
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
            background: #c4872e;
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
        
        .hidden {
            display: none;
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
            
            <div class="farm-register">
                <div class="register-card">
                    <div class="register-header">
                        <div class="header-badge">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2>Đăng ký</h2>
                        <p>Tạo tài khoản để mua sắm dễ dàng</p>
                    </div>
                    
                    <!-- Progress Bar -->
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
                            <div class="step" id="step3">
                                <div class="step-circle">3</div>
                                <div class="step-label">Mật khẩu</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bước 1: Thông tin cơ bản -->
                    <div id="step1Content" class="step-content">
                        <div class="input-group">
                            <label><i class="fas fa-user"></i> Họ và tên</label>
                            <input type="text" id="full_name" placeholder="Nguyễn Văn A" required>
                        </div>
                        
                        <div class="input-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" placeholder="nongdan@nongsan.vn" required>
                        </div>
                        
                        <div class="input-group">
                            <label><i class="fas fa-phone-alt"></i> Số điện thoại</label>
                            <input type="text" id="phone" placeholder="0912345678" required>
                        </div>
                        
                        <button type="button" id="nextToOtpBtn" class="btn-register">
                            <i class="fas fa-paper-plane"></i> Tiếp theo - Gửi OTP
                        </button>
                    </div>
                    
                    <!-- Bước 2: Nhập OTP -->
                    <div id="step2Content" class="step-content hidden">
                        <div class="input-group">
                            <label><i class="fas fa-key"></i> Mã OTP (6 số)</label>
                            <div class="otp-input-group" id="otpContainer">
                                <input type="text" class="otp-input" maxlength="1" data-index="0">
                                <input type="text" class="otp-input" maxlength="1" data-index="1">
                                <input type="text" class="otp-input" maxlength="1" data-index="2">
                                <input type="text" class="otp-input" maxlength="1" data-index="3">
                                <input type="text" class="otp-input" maxlength="1" data-index="4">
                                <input type="text" class="otp-input" maxlength="1" data-index="5">
                            </div>
                        </div>
                        
                        <div class="timer-text" id="timerText">Thời gian còn lại: 03:00</div>
                        
                        <div class="resend-otp">
                            <button id="resendOtpBtn" disabled>Gửi lại mã OTP</button>
                        </div>
                        
                        <button type="button" id="verifyOtpBtn" class="btn-register" style="margin-top: 1rem;">
                            <i class="fas fa-check-circle"></i> Xác thực OTP
                        </button>
                        <button type="button" id="backToStep1Btn" class="btn-back" style="margin-top: 0.8rem;">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </button>
                    </div>
                    
                    <!-- Bước 3: Tạo mật khẩu -->
                    <div id="step3Content" class="step-content hidden">
                        <div class="input-group">
                            <label><i class="fas fa-lock"></i> Mật khẩu</label>
                            <div class="password-field">
                                <input type="password" id="password" placeholder="•••••••• (tối thiểu 6 ký tự)" required>
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
                        
                        <div class="input-group">
                            <label><i class="fas fa-check-circle"></i> Xác nhận mật khẩu</label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" placeholder="Nhập lại mật khẩu" required>
                                <span class="toggle-eye" onclick="toggleConfirmPassword()">
                                    <i class="far fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                        
                        <button type="button" id="finishRegisterBtn" class="btn-register">
                            <i class="fas fa-seedling"></i> Hoàn tất đăng ký
                        </button>
                        <button type="button" id="backToStep2Btn" class="btn-back" style="margin-top: 0.8rem;">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </button>
                    </div>
                    
                    <div id="messageArea" class="message-area" style="margin-top: 1rem;"></div>
                    
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
        // State
        let currentStep = 1;
        let timerInterval = null;
        let timeLeft = 180; // 3 phút
        let currentEmail = '';
        
        // DOM elements
        const step1El = document.getElementById('step1');
        const step2El = document.getElementById('step2');
        const step3El = document.getElementById('step3');
        const step1Content = document.getElementById('step1Content');
        const step2Content = document.getElementById('step2Content');
        const step3Content = document.getElementById('step3Content');
        const progressLine = document.getElementById('progressLine');
        const messageArea = document.getElementById('messageArea');
        
        // Helper functions
        function showMessage(msg, type) {
            messageArea.innerHTML = '<div class="message ' + type + '"><i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle') + '"></i> ' + msg + '</div>';
            setTimeout(() => { messageArea.innerHTML = ''; }, 3000);
        }
        
        function updateProgress(step) {
            const steps = [step1El, step2El, step3El];
            steps.forEach((s, idx) => {
                s.classList.remove('active', 'completed');
                if (idx + 1 < step) {
                    s.classList.add('completed');
                } else if (idx + 1 === step) {
                    s.classList.add('active');
                }
            });
            const percent = ((step - 1) / 2) * 100;
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
        
        // Auto-focus OTP inputs
        function initOtpInputs() {
            const inputs = document.querySelectorAll('.otp-input');
            inputs.forEach((input, idx) => {
                input.addEventListener('input', (e) => {
                    if (e.target.value.length === 1 && idx < 5) {
                        inputs[idx + 1].focus();
                    }
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && idx > 0 && !e.target.value) {
                        inputs[idx - 1].focus();
                    }
                });
            });
        }
        
        function getOtpValue() {
            let otp = '';
            document.querySelectorAll('.otp-input').forEach(input => {
                otp += input.value;
            });
            return otp;
        }
        
        function startTimer() {
            if (timerInterval) clearInterval(timerInterval);
            timeLeft = 180;
            
            timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    document.getElementById('timerText').innerHTML = 'Mã OTP đã hết hạn! Vui lòng gửi lại.';
                    document.getElementById('resendOtpBtn').disabled = false;
                    return;
                }
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                document.getElementById('timerText').innerHTML = `Thời gian còn lại: ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }, 1000);
        }
        
        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }
        
        // Password strength
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthFill = document.getElementById('strengthFill');
            let strength = 0;
            
            if (password.length >= 6) strength = 25;
            if (password.length >= 8) strength = 50;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (strength > 100) strength = 100;
            
            strengthFill.style.width = strength + '%';
            if (strength < 30) strengthFill.style.backgroundColor = '#e74c3c';
            else if (strength < 60) strengthFill.style.backgroundColor = '#f39c12';
            else strengthFill.style.backgroundColor = '#27ae60';
        }
        
        // Toggle password
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.querySelector('#password + .toggle-eye i, .password-field .toggle-eye i');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                if(eye) eye.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                pwd.type = 'password';
                if(eye) eye.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }
        
        function toggleConfirmPassword() {
            const pwd = document.getElementById('confirm_password');
            const eye = document.querySelectorAll('.toggle-eye')[1] || document.querySelector('.toggle-eye');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                if(eye) eye.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                pwd.type = 'password';
                if(eye) eye.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }
        
        // Gửi OTP từ bước 1
        async function sendOtp() {
            const full_name = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            if (!full_name || !email || !phone) {
                showMessage('Vui lòng nhập đầy đủ thông tin!', 'error');
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Email không hợp lệ!', 'error');
                return;
            }
            
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!phoneRegex.test(phone)) {
                showMessage('Số điện thoại không hợp lệ (10-11 số)!', 'error');
                return;
            }
            
            currentEmail = email;
            
            const formData = new FormData();
            formData.append('ajax_send_otp', '1');
            formData.append('full_name', full_name);
            formData.append('email', email);
            formData.append('phone', phone);
            
            try {
                const response = await fetch(window.location.href, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message + (result.debug_otp ? ' (Mã test: ' + result.debug_otp + ')' : ''), 'success');
                    // Reset OTP inputs
                    document.querySelectorAll('.otp-input').forEach(input => input.value = '');
                    startTimer();
                    goToStep(2);
                    document.getElementById('resendOtpBtn').disabled = true;
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Lỗi kết nối!', 'error');
            }
        }
        
        // Xác thực OTP
        async function verifyOtp() {
            const otp = getOtpValue();
            if (otp.length !== 6) {
                showMessage('Vui lòng nhập đủ 6 số OTP!', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('ajax_verify_otp', '1');
            formData.append('email', currentEmail);
            formData.append('otp', otp);
            
            try {
                const response = await fetch(window.location.href, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    stopTimer();
                    goToStep(3);
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Lỗi xác thực!', 'error');
            }
        }
        
        // Gửi lại OTP
        async function resendOtp() {
            const full_name = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            const formData = new FormData();
            formData.append('ajax_send_otp', '1');
            formData.append('full_name', full_name);
            formData.append('email', email);
            formData.append('phone', phone);
            
            try {
                const response = await fetch(window.location.href, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showMessage('Đã gửi lại mã OTP!', 'success');
                    document.querySelectorAll('.otp-input').forEach(input => input.value = '');
                    startTimer();
                    document.getElementById('resendOtpBtn').disabled = true;
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Lỗi gửi lại OTP!', 'error');
            }
        }
        
        // Hoàn tất đăng ký
        async function finishRegistration() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (!password || !confirmPassword) {
                showMessage('Vui lòng nhập mật khẩu!', 'error');
                return;
            }
            
            if (password.length < 6) {
                showMessage('Mật khẩu phải có ít nhất 6 ký tự!', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showMessage('Mật khẩu xác nhận không khớp!', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('ajax_create_password', '1');
            formData.append('password', password);
            formData.append('confirm_password', confirmPassword);
            
            try {
                const response = await fetch(window.location.href, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    // Chuyển về login sau 1.5 giây
                    setTimeout(() => {
                        transitionToLogin();
                    }, 1500);
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Lỗi đăng ký!', 'error');
            }
        }
        
        // Animation chuyển về login
        let isTransitioning = false;
        function transitionToLogin() {
            if (isTransitioning) return;
            isTransitioning = true;
            document.getElementById('mainContent').classList.add('fade-out');
            const transitionLayer = document.createElement('div');
            transitionLayer.className = 'page-transition';
            document.body.appendChild(transitionLayer);
            setTimeout(() => transitionLayer.classList.add('active'), 10);
            setTimeout(() => { window.location.href = 'http://localhost/sa-login/'; }, 400);
        }
        
        // Event listeners
        document.getElementById('nextToOtpBtn')?.addEventListener('click', sendOtp);
        document.getElementById('verifyOtpBtn')?.addEventListener('click', verifyOtp);
        document.getElementById('resendOtpBtn')?.addEventListener('click', resendOtp);
        document.getElementById('finishRegisterBtn')?.addEventListener('click', finishRegistration);
        document.getElementById('backToStep1Btn')?.addEventListener('click', () => {
            stopTimer();
            goToStep(1);
        });
        document.getElementById('backToStep2Btn')?.addEventListener('click', () => goToStep(2));
        document.getElementById('flipToLoginBtn')?.addEventListener('click', (e) => {
            e.preventDefault();
            transitionToLogin();
        });
        document.getElementById('password')?.addEventListener('input', checkPasswordStrength);
        
        // Khởi tạo OTP inputs
        initOtpInputs();
        
        // Animation khi load
        window.addEventListener('load', () => {
            document.getElementById('mainContent').classList.add('fade-in');
            setTimeout(() => document.getElementById('mainContent')?.classList.remove('fade-in'), 500);
        });
        
        // Prefetch
        const prefetch = document.createElement('link');
        prefetch.rel = 'prefetch';
        prefetch.href = 'http://localhost/sa-login/';
        document.head.appendChild(prefetch);
        
        // Khởi tạo progress
        updateProgress(1);
    </script>
</body>
</html>
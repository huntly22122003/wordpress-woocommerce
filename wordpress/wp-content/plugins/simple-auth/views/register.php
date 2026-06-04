<form
    id="sa-register-form"
    method="POST"
>

```
<div>
    <label>Họ và tên</label>
    <input
        type="text"
        name="full_name"
        required
    >
</div>

<div>
    <label>Giới tính</label>

    <select
        name="gender"
        required
    >
        <option value="">
            Chọn giới tính
        </option>

        <option value="male">
            Nam
        </option>

        <option value="female">
            Nữ
        </option>

        <option value="other">
            Khác
        </option>
    </select>
</div>

<div>
    <label>Tuổi</label>

    <input
        type="number"
        name="age"
        min="1"
        max="120"
        required
    >
</div>

<div>

    <label>Email</label>

    <input
        type="email"
        id="email"
        name="email"
        required
    >

    <button
        type="button"
        id="send-otp-btn"
    >
        Gửi mã OTP
    </button>

</div>

<div>

    <label>Mã OTP</label>

    <input
        type="text"
        name="otp"
        maxlength="6"
        required
    >

</div>

<div>

    <label>Số điện thoại</label>

    <input
        type="text"
        name="phone"
        required
    >

</div>

<div>

    <label>Mật khẩu</label>

    <input
        type="password"
        name="password"
        required
    >

</div>

<button
    type="submit"
    name="sa_register_submit"
>
    Đăng ký
</button>
```

</form>

<div
    id="sa-message"
    style="
        margin-top:15px;
        font-weight:bold;
    "
></div>

<script>

const otpButton =
    document.getElementById(
        'send-otp-btn'
    );

const messageBox =
    document.getElementById(
        'sa-message'
    );

otpButton.addEventListener(
    'click',
    async function () {

        const email =
            document
            .getElementById(
                'email'
            )
            .value
            .trim();

        if (!email) {

            messageBox.innerText =
                'Vui lòng nhập email';

            return;
        }

        otpButton.disabled = true;

        otpButton.innerText =
            'Đang gửi...';

        const form =
            new FormData();

        form.append(
            'action',
            'sa_send_otp'
        );

        form.append(
            'email',
            email
        );

        try {

            const response =
                await fetch(
                    '/wp-admin/admin-ajax.php',
                    {
                        method: 'POST',
                        body: form
                    }
                );

            const result =
                await response.json();

            messageBox.innerText =
                result.message;

            if (result.success) {

                let countdown = 60;

                otpButton.innerText =
                    countdown + 's';

                const timer =
                    setInterval(
                        function () {

                            countdown--;

                            otpButton.innerText =
                                countdown + 's';

                            if (
                                countdown <= 0
                            ) {

                                clearInterval(
                                    timer
                                );

                                otpButton.disabled =
                                    false;

                                otpButton.innerText =
                                    'Gửi mã OTP';
                            }

                        },
                        1000
                    );

            } else {

                otpButton.disabled =
                    false;

                otpButton.innerText =
                    'Gửi mã OTP';
            }

        } catch (error) {

            messageBox.innerText =
                'Lỗi kết nối';

            otpButton.disabled =
                false;

            otpButton.innerText =
                'Gửi mã OTP';
        }

    }
);

</script>

<?php if (!defined('ABSPATH')) exit; ?>

<form method="post">

    <h2>Đặt lại mật khẩu</h2>

    <input
        type="text"
        name="otp"
        placeholder="OTP"
        required
    >

    <input
        type="password"
        name="password"
        placeholder="Mật khẩu mới"
        required
    >

    <input
        type="password"
        name="password_confirmation"
        placeholder="Xác nhận mật khẩu"
        required
    >

    <button
        type="submit"
        name="sa_reset_submit"
    >
        Đổi mật khẩu
    </button>

</form>
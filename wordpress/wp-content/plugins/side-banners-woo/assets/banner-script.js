/**
 * Side Banners for WooCommerce - Script
 */

jQuery(document).ready(function($) {
    
    // ======= ĐÓNG BANNER =======
    $('.sbw-close-btn').on('click', function(e) {
        e.preventDefault();
        var banner = $(this).closest('.sbw-banner');
        var bannerId = banner.attr('id');
        
        // Ẩn banner
        banner.addClass('hidden');
        
        // Lưu vào cookie
        var expiryDate = new Date();
        expiryDate.setTime(expiryDate.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 ngày
        document.cookie = bannerId + '_closed=true; expires=' + expiryDate.toUTCString() + '; path=/';
        
        // Tự động điều chỉnh padding
        var remainingBanners = $('.sbw-banner:not(.hidden)').length;
        if (remainingBanners === 0) {
            $('.site-content').css({
                'padding-left': '20px',
                'padding-right': '20px'
            });
        }
    });
    
    // ======= KIỂM TRA COOKIE KHI TẢI TRANG =======
    function getCookie(name) {
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return null;
    }
    
    // Ẩn banner nếu đã đóng
    $('.sbw-banner').each(function() {
        var bannerId = $(this).attr('id');
        if (getCookie(bannerId + '_closed') === 'true') {
            $(this).addClass('hidden');
        }
    });
    
    // ======= XỬ LÝ RESPONSIVE ========
    function handleResponsive() {
        var isMobile = window.innerWidth <= 768;
        var showOnMobile = typeof sbw_data !== 'undefined' && sbw_data.show_on_mobile;
        
        if (isMobile && !showOnMobile) {
            $('.sbw-banners-wrapper').addClass('mobile-hide');
        } else {
            $('.sbw-banners-wrapper').removeClass('mobile-hide');
        }
    }
    
    // Gọi khi load và resize
    handleResponsive();
    $(window).on('resize', handleResponsive);
    
    // ======= KEYBOARD ACCESSIBILITY =======
    $('.sbw-close-btn').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });
    
    // ======= CẬP NHẬT LẠI KHI AJAX LOAD =======
    $(document).on('wc_fragments_refreshed', function() {
        // Kiểm tra lại cookie sau khi refresh giỏ hàng
        $('.sbw-banner').each(function() {
            var bannerId = $(this).attr('id');
            if (getCookie(bannerId + '_closed') === 'true') {
                $(this).addClass('hidden');
            }
        });
    });
    
    console.log('Side Banners for WooCommerce loaded successfully!');
});
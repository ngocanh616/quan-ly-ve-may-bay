/**
 * File: main.js
 * Mô tả: JavaScript chung cho website
 */

$(document).ready(function() {
    
    // Tự động ẩn alert sau 5 giây
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Smooth scroll cho các anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Xác nhận trước khi xóa
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Bạn có chắc chắn muốn xóa?')) {
            e.preventDefault();
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        let valid = true;
        $(this).find('[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                valid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
        }
    });
    
    // Remove validation class khi nhập
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
    
});

// Sticky navbar với hiệu ứng scroll
$(window).on('scroll', function() {
    const navbar = $('.navbar-aviation');
    if ($(window).scrollTop() > 50) {
        navbar.addClass('scrolled');
    } else {
        navbar.removeClass('scrolled');
    }
});
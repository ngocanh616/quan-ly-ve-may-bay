<?php
/**
 * File: dangnhap.php
 * Mô tả: Trang đăng nhập hệ thống
 */

require_once 'config.php';

// Nếu đã đăng nhập rồi thì chuyển hướng
if (is_logged_in()) {
    if (is_admin()) {
        redirect('/QLVeMayBay/admin/index.php');
    } else {
        redirect('/QLVeMayBay/index.php');
    }
}

// Xử lý đăng nhập
if (isset($_POST['dangnhap'])) {
    $tenDangNhap = escape($_POST['tenDangNhap']);
    $matKhau = escape($_POST['matKhau']);
    
    // Validate
    if (empty($tenDangNhap) || empty($matKhau)) {
        show_message('Vui lòng nhập đầy đủ thông tin!', 'danger');
    } else {
        // Kiểm tra user trong database 
        $sql = "SELECT * FROM NguoiDung WHERE TenDangNhap = '$tenDangNhap' AND MatKhau = '$matKhau'";
        $user = fetch_one($sql);
        
        if ($user) {
            // Lưu session
            $_SESSION['MaNguoiDung'] = $user['MaNguoiDung'];
            $_SESSION['TenDangNhap'] = $user['TenDangNhap'];
            $_SESSION['HoTen'] = $user['HoTen'];
            $_SESSION['VaiTro'] = $user['VaiTro'];
            
            show_message('Đăng nhập thành công!', 'success');
            
            // Chuyển hướng theo vai trò
            if ($user['VaiTro'] == 'admin') {
                redirect('/QLVeMayBay/admin/index.php');
            } else {
                redirect('/QLVeMayBay/index.php'); // Khách hàng về trang chủ
            }
        } else {
            show_message('Tên đăng nhập hoặc mật khẩu không đúng!', 'danger');
        }
    }
}

$pageTitle = 'Đăng Nhập';
require_once 'header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card">
                    <!-- Icon -->
                    <div class="auth-icon">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="auth-title">Đăng Nhập</h2>
                    <p class="auth-subtitle">Chào mừng bạn quay trở lại!</p>
                    
                    <!-- Form -->
                    <form method="POST" action="" class="auth-form">
                        <div class="form-group mb-3">
                            <label for="tenDangNhap" class="form-label">
                                <i class="bi bi-person me-2"></i>Tên đăng nhập
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="tenDangNhap" 
                                   name="tenDangNhap" 
                                   placeholder="Nhập tên đăng nhập"
                                   required>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="matKhau" class="form-label">
                                <i class="bi bi-lock me-2"></i>Mật khẩu
                            </label>
                            <div class="password-wrapper">
                                <input type="password" 
                                       class="form-control" 
                                       id="matKhau" 
                                       name="matKhau" 
                                       placeholder="Nhập mật khẩu"
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Link quên mật khẩu -->
                        <div class="text-end mb-4">
                            <a href="quenmatkhau.php" class="forgot-password-link">
                                <i class="bi bi-question-circle me-1"></i>Quên mật khẩu?
                            </a>
                        </div>

                        <button type="submit" name="dangnhap" class="btn btn-primary btn-auth w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng Nhập
                        </button>
                        
                        <div class="auth-footer">
                            <p class="mb-0">Chưa có tài khoản? 
                                <a href="dangky.php" class="auth-link">Đăng ký ngay</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function togglePassword() {
    const passwordInput = document.getElementById('matKhau');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}
</script>

<?php require_once 'footer.php'; ?>

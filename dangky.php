<?php
/**
 * File: dangky.php
 * Mô tả: Trang đăng ký tài khoản
 */

require_once 'config.php';

// Nếu đã đăng nhập rồi thì chuyển về trang chủ
if (is_logged_in()) {
    redirect('/QLVeMayBay/index.php');
}

// Xử lý đăng ký
if (isset($_POST['dangky'])) {
    $tenDangNhap = escape($_POST['tenDangNhap']);
    $matKhau = escape($_POST['matKhau']);
    $xacNhanMatKhau = escape($_POST['xacNhanMatKhau']);
    $hoTen = escape($_POST['hoTen']);
    $email = escape($_POST['email']);
    $soDienThoai = escape($_POST['soDienThoai']);
    
    // Validate
    $errors = [];
    
    if (empty($tenDangNhap) || empty($matKhau) || empty($hoTen) || empty($email)) {
        $errors[] = 'Vui lòng điền đầy đủ các trường bắt buộc!';
    }
    
    if ($matKhau !== $xacNhanMatKhau) {
        $errors[] = 'Mật khẩu xác nhận không khớp!';
    }
    
    if (strlen($matKhau) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
    }
    
    if (!is_valid_email($email)) {
        $errors[] = 'Email không hợp lệ!';
    }
    
    if (!empty($soDienThoai) && !is_valid_phone($soDienThoai)) {
        $errors[] = 'Số điện thoại không hợp lệ!';
    }
    
    // Kiểm tra tên đăng nhập đã tồn tại chưa
    $checkUser = fetch_one("SELECT MaNguoiDung FROM NguoiDung WHERE TenDangNhap = '$tenDangNhap'");
    if ($checkUser) {
        $errors[] = 'Tên đăng nhập đã tồn tại!';
    }
    
    // Kiểm tra email đã tồn tại chưa
    $checkEmail = fetch_one("SELECT MaNguoiDung FROM NguoiDung WHERE Email = '$email'");
    if ($checkEmail) {
        $errors[] = 'Email đã được sử dụng!';
    }
    
    if (empty($errors)) {
        // Insert user mới (mật khẩu không mã hóa)
        $sql = "INSERT INTO NguoiDung (TenDangNhap, MatKhau, HoTen, Email, SoDienThoai, VaiTro) 
                VALUES ('$tenDangNhap', '$matKhau', '$hoTen', '$email', '$soDienThoai', 'khachhang')";
        
        if (query($sql)) {
            show_message('Đăng ký thành công! Vui lòng đăng nhập.', 'success');
            redirect('/QLVeMayBay/dangnhap.php');
        } else {
            show_message('Có lỗi xảy ra, vui lòng thử lại!', 'danger');
        }
    } else {
        foreach ($errors as $error) {
            show_message($error, 'danger');
            break; // Chỉ hiển thị lỗi đầu tiên
        }
    }
}

$pageTitle = 'Đăng Ký';
require_once 'header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="auth-card">
                    <!-- Icon -->
                    <div class="auth-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="auth-title">Đăng Ký</h2>
                    <p class="auth-subtitle">Tạo tài khoản mới để sử dụng dịch vụ</p>
                    
                    <!-- Form -->
                    <form method="POST" action="" class="auth-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="hoTen" class="form-label">
                                        <i class="bi bi-person me-2"></i>Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="hoTen" 
                                           name="hoTen" 
                                           placeholder="Nguyễn Văn A"
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tenDangNhap" class="form-label">
                                        <i class="bi bi-person-badge me-2"></i>Tên đăng nhập <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tenDangNhap" 
                                           name="tenDangNhap" 
                                           placeholder="username"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-2"></i>Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           placeholder="email@example.com"
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="soDienThoai" class="form-label">
                                        <i class="bi bi-telephone me-2"></i>Số điện thoại
                                    </label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="soDienThoai" 
                                           name="soDienThoai" 
                                           placeholder="0123456789"
                                           pattern="[0-9]{10}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="matKhau" class="form-label">
                                        <i class="bi bi-lock me-2"></i>Mật khẩu <span class="text-danger">*</span>
                                    </label>
                                    <div class="password-wrapper">
                                        <input type="password" 
                                               class="form-control" 
                                               id="matKhau" 
                                               name="matKhau" 
                                               placeholder="Ít nhất 6 ký tự"
                                               required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('matKhau', 'toggleIcon1')">
                                            <i class="bi bi-eye" id="toggleIcon1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="xacNhanMatKhau" class="form-label">
                                        <i class="bi bi-lock-fill me-2"></i>Xác nhận mật khẩu <span class="text-danger">*</span>
                                    </label>
                                    <div class="password-wrapper">
                                        <input type="password" 
                                               class="form-control" 
                                               id="xacNhanMatKhau" 
                                               name="xacNhanMatKhau" 
                                               placeholder="Nhập lại mật khẩu"
                                               required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('xacNhanMatKhau', 'toggleIcon2')">
                                            <i class="bi bi-eye" id="toggleIcon2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="dangky" class="btn btn-primary btn-auth w-100 mb-3">
                            <i class="bi bi-person-plus me-2"></i>Đăng Ký
                        </button>
                        
                        <div class="auth-footer">
                            <p class="mb-0">Đã có tài khoản? 
                                <a href="dangnhap.php" class="auth-link">Đăng nhập ngay</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
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

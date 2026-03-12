<?php
/**
 * File: quenmatkhau.php
 * Mô tả: Trang khôi phục mật khẩu (chỉ dùng Email)
 */

require_once 'config.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (is_logged_in()) {
    redirect('/QuanLyVeMayBay/index.php');
}

// Xử lý hủy bỏ reset password (khi ấn quay lại hoặc vào trang mới)
if (isset($_GET['reset'])) {
    unset($_SESSION['reset_user_id']);
    unset($_SESSION['reset_email']);
    redirect('/QuanLyVeMayBay/quenmatkhau.php');
}

// Kiểm tra xem có vừa load trang mới không (không phải submit form)
// Nếu vào trang lần đầu và có session cũ, xóa đi
if ($_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_GET['step'])) {
    if (isset($_SESSION['reset_user_id'])) {
        // Xóa session cũ khi load trang mới
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_email']);
    }
}

$step = 1; // Bước hiện tại

// Xử lý bước 1: Nhập email
if (isset($_POST['step1'])) {
    $email = escape($_POST['email']);
    
    if (empty($email)) {
        show_message('Vui lòng nhập email!', 'danger');
    } elseif (!is_valid_email($email)) {
        show_message('Email không hợp lệ!', 'danger');
    } else {
        // Tìm user theo email
        $sql = "SELECT * FROM NguoiDung WHERE Email = '$email'";
        $user = fetch_one($sql);
        
        if ($user) {
            // Lưu thông tin vào session
            $_SESSION['reset_user_id'] = $user['MaNguoiDung'];
            $_SESSION['reset_email'] = $user['Email'];
            $_SESSION['reset_username'] = $user['TenDangNhap'];
            $step = 2;
        } else {
            show_message('Không tìm thấy tài khoản với email này!', 'danger');
        }
    }
}

// Xử lý bước 2: Đặt mật khẩu mới
if (isset($_POST['step2'])) {
    $newPassword = escape($_POST['newPassword']);
    $confirmPassword = escape($_POST['confirmPassword']);
    
    if (empty($newPassword) || empty($confirmPassword)) {
        show_message('Vui lòng nhập đầy đủ thông tin!', 'danger');
        $step = 2;
    } elseif (strlen($newPassword) < 6) {
        show_message('Mật khẩu phải có ít nhất 6 ký tự!', 'danger');
        $step = 2;
    } elseif ($newPassword !== $confirmPassword) {
        show_message('Mật khẩu xác nhận không khớp!', 'danger');
        $step = 2;
    } else {
        // Cập nhật mật khẩu mới
        $userId = $_SESSION['reset_user_id'];
        $sql = "UPDATE NguoiDung SET MatKhau = '$newPassword' WHERE MaNguoiDung = $userId";
        
        if (query($sql)) {
            // Xóa session reset
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_username']);
            
            show_message('Đặt lại mật khẩu thành công! Vui lòng đăng nhập.', 'success');
            redirect('/QuanLyVeMayBay/dangnhap.php');
        } else {
            show_message('Có lỗi xảy ra, vui lòng thử lại!', 'danger');
            $step = 2;
        }
    }
}

// Kiểm tra xem có session reset không (chỉ hiển thị bước 2 khi vừa submit form)
if (isset($_SESSION['reset_user_id']) && isset($_POST['step1'])) {
    $step = 2;
}

$pageTitle = 'Quên Mật Khẩu';
$loadAuthCSS = true;
require_once 'header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card">
                    <!-- Icon -->
                    <div class="auth-icon">
                        <i class="bi bi-key"></i>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="auth-title">Quên Mật Khẩu</h2>
                    
                    <?php if ($step == 1): ?>
                        <!-- Bước 1: Nhập email -->
                        <p class="auth-subtitle">Nhập email đã đăng ký để khôi phục tài khoản</p>
                        
                        <form method="POST" action="" class="auth-form">
                            <div class="form-group mb-4">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email đăng ký
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="email@example.com"
                                       required>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Nhập email bạn đã sử dụng khi đăng ký tài khoản
                                </small>
                            </div>
                            
                            <button type="submit" name="step1" class="btn btn-primary btn-auth w-100 mb-3">
                                <i class="bi bi-arrow-right me-2"></i>Tiếp tục
                            </button>
                            
                            <div class="auth-footer">
                                <p class="mb-0">
                                    <a href="dangnhap.php" class="auth-link">
                                        <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
                                    </a>
                                </p>
                            </div>
                        </form>
                        
                    <?php else: ?>
                        <!-- Bước 2: Đặt mật khẩu mới -->
                        <p class="auth-subtitle">
                            <strong><?php echo $_SESSION['reset_email']; ?></strong>
                        </p>
                        <p class="text-muted small mb-3">
                            Tên đăng nhập: <strong><?php echo $_SESSION['reset_username']; ?></strong>
                        </p>
                        
                        <form method="POST" action="" class="auth-form">
                            <div class="form-group mb-3">
                                <label for="newPassword" class="form-label">
                                    <i class="bi bi-lock me-2"></i>Mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <div class="password-wrapper">
                                    <input type="password" 
                                           class="form-control" 
                                           id="newPassword" 
                                           name="newPassword" 
                                           placeholder="Ít nhất 6 ký tự"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('newPassword', 'toggleIcon1')">
                                        <i class="bi bi-eye" id="toggleIcon1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label for="confirmPassword" class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>Xác nhận mật khẩu <span class="text-danger">*</span>
                                </label>
                                <div class="password-wrapper">
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirmPassword" 
                                           name="confirmPassword" 
                                           placeholder="Nhập lại mật khẩu mới"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', 'toggleIcon2')">
                                        <i class="bi bi-eye" id="toggleIcon2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" name="step2" class="btn btn-primary btn-auth w-100 mb-2">
                                <i class="bi bi-check-circle me-2"></i>Đặt lại mật khẩu
                            </button>
                            
                            <a href="quenmatkhau.php?reset=1" class="btn btn-outline-secondary w-100 mb-3">
                                <i class="bi bi-x-circle me-2"></i>Hủy bỏ
                            </a>
                            
                            <div class="auth-footer">
                                <p class="mb-0">
                                    <a href="dangnhap.php" class="auth-link">
                                        <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
                                    </a>
                                </p>
                            </div>
                        </form>
                    <?php endif; ?>
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

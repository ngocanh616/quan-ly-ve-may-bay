<?php
/**
 * File: khachhang/thongtin.php
 * Mô tả: Trang thông tin cá nhân khách hàng
 */

require_once '../config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    $_SESSION['message'] = 'Vui lòng đăng nhập!';
    $_SESSION['message_type'] = 'warning';
    header('Location: ../dangnhap.php');
    exit();
}

$manguoidung = $_SESSION['MaNguoiDung'];

// Lấy thông tin người dùng
$user = fetch_one("SELECT * FROM NguoiDung WHERE MaNguoiDung = $manguoidung");

if (!$user) {
    $_SESSION['message'] = 'Không tìm thấy thông tin người dùng!';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Xử lý cập nhật thông tin
if (isset($_POST['cap_nhat'])) {
    $hoTen = trim(escape($_POST['hoten']));
    $email = trim(escape($_POST['email']));
    $sdt = trim(escape($_POST['sdt']));
    $diaChi = trim(escape($_POST['diachi']));
    
    $errors = [];
    
    // Validate
    if (empty($hoTen)) {
        $errors[] = 'Họ tên không được để trống!';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ!';
    }
    
    if (!empty($sdt) && !preg_match('/^0[0-9]{9}$/', $sdt)) {
        $errors[] = 'Số điện thoại phải có 10 số, bắt đầu bằng 0!';
    }
    
    // Kiểm tra email trùng (trừ email của chính mình)
    $emailCheck = fetch_one("SELECT MaNguoiDung FROM NguoiDung WHERE Email = '$email' AND MaNguoiDung != $manguoidung");
    if ($emailCheck) {
        $errors[] = 'Email đã được sử dụng bởi tài khoản khác!';
    }
    
    if (empty($errors)) {
        $sql = "UPDATE NguoiDung 
                SET HoTen = '$hoTen', 
                    Email = '$email', 
                    SoDienThoai = '$sdt', 
                    DiaChi = '$diaChi'
                WHERE MaNguoiDung = $manguoidung";
        
        if (query($sql)) {
            // Cập nhật session
            $_SESSION['HoTen'] = $hoTen;
            $_SESSION['Email'] = $email;
            
            $_SESSION['message'] = 'Cập nhật thông tin thành công!';
            $_SESSION['message_type'] = 'success';
            
            // Reload lại thông tin
            $user = fetch_one("SELECT * FROM NguoiDung WHERE MaNguoiDung = $manguoidung");
        } else {
            $_SESSION['message'] = 'Có lỗi xảy ra khi cập nhật!';
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

// Xử lý đổi mật khẩu
if (isset($_POST['doi_mat_khau'])) {
    $matKhauCu = $_POST['matkhau_cu'];
    $matKhauMoi = $_POST['matkhau_moi'];
    $xacNhanMatKhau = $_POST['xacnhan_matkhau'];
    
    $errors = [];
    
    // Kiểm tra mật khẩu cũ
    if (!password_verify($matKhauCu, $user['MatKhau'])) {
        $errors[] = 'Mật khẩu cũ không đúng!';
    }
    
    // Validate mật khẩu mới
    if (strlen($matKhauMoi) < 6) {
        $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
    }
    
    if ($matKhauMoi !== $xacNhanMatKhau) {
        $errors[] = 'Xác nhận mật khẩu không khớp!';
    }
    
    if (empty($errors)) {
        $matKhauHash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
        $sql = "UPDATE NguoiDung SET MatKhau = '$matKhauHash' WHERE MaNguoiDung = $manguoidung";
        
        if (query($sql)) {
            $_SESSION['message'] = 'Đổi mật khẩu thành công!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Có lỗi xảy ra khi đổi mật khẩu!';
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

// Thống kê
$tongVeDat = fetch_one("SELECT COUNT(*) as total FROM DatVe WHERE MaNguoiDung = $manguoidung")['total'];
$tongTienChi = fetch_one("SELECT SUM(TongTien) as total FROM DatVe WHERE MaNguoiDung = $manguoidung AND TrangThai = 'hoanthanh'")['total'] ?? 0;

$pageTitle = 'Thông Tin Cá Nhân';
$loadKhachHangCSS = true;
require_once '../header.php';
?>

<div class="profile-page">
    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <?php echo strtoupper(substr($user['HoTen'], 0, 1)); ?>
                        </div>
                        <h5 class="user-name"><?php echo $user['HoTen']; ?></h5>
                        <p class="user-email"><?php echo $user['Email']; ?></p>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <i class="bi bi-ticket-perforated"></i>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $tongVeDat; ?></div>
                                <div class="stat-label">Vé đã đặt</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="bi bi-cash-stack"></i>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo number_format($tongTienChi / 1000000, 1); ?>M</div>
                                <div class="stat-label">Tổng chi tiêu</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-menu">
                        <a href="#thong-tin" class="menu-item active" data-tab="thong-tin">
                            <i class="bi bi-person-circle"></i>
                            <span>Thông tin cá nhân</span>
                        </a>
                        <a href="#doi-mat-khau" class="menu-item" data-tab="doi-mat-khau">
                            <i class="bi bi-shield-lock"></i>
                            <span>Đổi mật khẩu</span>
                        </a>
                        <a href="lichsu.php" class="menu-item">
                            <i class="bi bi-clock-history"></i>
                            <span>Lịch sử đặt vé</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Tab: Thông tin cá nhân -->
                <div class="tab-content active" id="tab-thong-tin">
                    <div class="profile-card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="bi bi-person-circle me-2"></i>Thông tin cá nhân
                            </h4>
                            <p class="card-subtitle">Quản lý thông tin cá nhân của bạn</p>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-person me-2"></i>Họ và tên <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" name="hoten" 
                                                   value="<?php echo $user['HoTen']; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-envelope me-2"></i>Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo $user['Email']; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-telephone me-2"></i>Số điện thoại
                                            </label>
                                            <input type="tel" class="form-control" name="sdt" 
                                                   value="<?php echo $user['SoDienThoai']; ?>" 
                                                   placeholder="0901234567">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-shield-check me-2"></i>Tên đăng nhập
                                            </label>
                                            <input type="text" class="form-control" 
                                                   value="<?php echo $user['TenDangNhap']; ?>" disabled>
                                            <small class="text-muted">Tên đăng nhập không thể thay đổi</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-geo-alt me-2"></i>Địa chỉ
                                            </label>
                                            <textarea class="form-control" name="diachi" rows="3" 
                                                      placeholder="Nhập địa chỉ của bạn"><?php echo $user['DiaChi']; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <button type="submit" name="cap_nhat" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Cập nhật thông tin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tab: Đổi mật khẩu -->
                <div class="tab-content" id="tab-doi-mat-khau">
                    <div class="profile-card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu
                            </h4>
                            <p class="card-subtitle">Thay đổi mật khẩu để bảo mật tài khoản</p>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-key me-2"></i>Mật khẩu cũ <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" class="form-control" name="matkhau_cu" 
                                                   placeholder="Nhập mật khẩu cũ" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-key-fill me-2"></i>Mật khẩu mới <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" class="form-control" name="matkhau_moi" 
                                                   placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">
                                                <i class="bi bi-check-circle me-2"></i>Xác nhận mật khẩu <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" class="form-control" name="xacnhan_matkhau" 
                                                   placeholder="Nhập lại mật khẩu mới" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Lưu ý:</strong> Mật khẩu mới phải có ít nhất 6 ký tự và khác mật khẩu cũ.
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <button type="submit" name="doi_mat_khau" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-2"></i>Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Fix màu chữ trên card header - background xanh */
.profile-card .card-header {
    background: linear-gradient(135deg, #0080FF 0%, #006BA6 100%);
    color: white !important;
}

.profile-card .card-header .card-title {
    color: white !important;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.profile-card .card-header .card-subtitle {
    color: rgba(255, 255, 255, 0.85) !important;
    font-size: 0.95rem;
    margin: 0;
}

.profile-card .card-header i {
    color: white !important;
}

</style>

<script>
// Tab switching
document.querySelectorAll('.menu-item[data-tab]').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class
        document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        
        // Add active class
        this.classList.add('active');
        const tabId = this.getAttribute('data-tab');
        document.getElementById('tab-' + tabId).classList.add('active');
    });
});
</script>

<?php require_once '../footer.php'; ?>

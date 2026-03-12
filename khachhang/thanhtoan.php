<?php
/**
 * File: khachhang/thanhtoan.php
 * Mô tả: Trang thanh toán đặt vé
 */

require_once '../config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    $_SESSION['message'] = 'Vui lòng đăng nhập!';
    $_SESSION['message_type'] = 'warning';
    header('Location: ../dangnhap.php');
    exit();
}

// Lấy mã đặt vé từ session hoặc URL
$madatve = isset($_GET['madatve']) ? (int)$_GET['madatve'] : 0;

if (!$madatve) {
    $_SESSION['message'] = 'Không tìm thấy thông tin đặt vé!';
    $_SESSION['message_type'] = 'danger';
    header('Location: chuyenbay.php');
    exit();
}

// Lấy thông tin đặt vé
$datve = fetch_one("SELECT dv.*, cb.MaChuyenBayText, cb.HangBay, cb.SanBayDi, cb.SanBayDen, cb.ThoiGianBay, cb.GiaVe
                    FROM DatVe dv
                    INNER JOIN ChuyenBay cb ON dv.MaChuyenBay = cb.MaChuyenBay
                    WHERE dv.MaDatVe = $madatve AND dv.MaNguoiDung = {$_SESSION['MaNguoiDung']}");

if (!$datve) {
    $_SESSION['message'] = 'Đặt vé không tồn tại hoặc không thuộc về bạn!';
    $_SESSION['message_type'] = 'danger';
    header('Location: chuyenbay.php');
    exit();
}

// Lấy danh sách hành khách
$hanhkhach = fetch_all("SELECT * FROM ChiTietDatVe WHERE MaDatVe = $madatve");

// Xử lý thanh toán
if (isset($_POST['thanhtoan'])) {
    $phuongthuc = escape($_POST['phuongthuc']);
    
    // Cập nhật trạng thái đặt vé
    $sql = "UPDATE DatVe SET TrangThai = 'dathanhtoan' WHERE MaDatVe = $madatve";
    
    if (query($sql)) {
        $_SESSION['message'] = 'Thanh toán thành công! Cảm ơn bạn đã đặt vé.';
        $_SESSION['message_type'] = 'success';
        header('Location: lichsu.php');
        exit();
    } else {
        $_SESSION['message'] = 'Có lỗi xảy ra khi thanh toán!';
        $_SESSION['message_type'] = 'danger';
    }
}

$pageTitle = 'Thanh Toán - Sky Airline';
$loadKhachHangCSS = true;
require_once '../header.php';
?>

<div class="payment-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <h1 class="page-title">
                <i class="bi bi-credit-card-fill me-3"></i>
                Thanh Toán
            </h1>
            <p class="page-subtitle">Mã đặt vé: <strong>#<?php echo str_pad($madatve, 6, '0', STR_PAD_LEFT); ?></strong></p>
        </div>

        <div class="row">
            <!-- Thông tin đặt vé -->
            <div class="col-lg-7">
                <div class="payment-card mb-4">
                    <h3 class="section-title">
                        <i class="bi bi-info-circle me-2"></i>Thông tin đặt vé
                    </h3>
                    
                    <div class="booking-summary">
                        <div class="summary-item">
                            <span class="label">Chuyến bay:</span>
                            <span class="value"><strong><?php echo $datve['MaChuyenBayText']; ?></strong></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Hãng bay:</span>
                            <span class="value"><?php echo $datve['HangBay']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Tuyến bay:</span>
                            <span class="value"><?php echo $datve['SanBayDi']; ?> → <?php echo $datve['SanBayDen']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Thời gian:</span>
                            <span class="value"><?php echo date('d/m/Y H:i', strtotime($datve['ThoiGianBay'])); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Số lượng vé:</span>
                            <span class="value"><?php echo $datve['SoLuongVe']; ?> vé</span>
                        </div>
                    </div>
                </div>

                <div class="payment-card">
                    <h3 class="section-title">
                        <i class="bi bi-people me-2"></i>Danh sách hành khách
                    </h3>
                    
                    <div class="passengers-list">
                        <?php foreach ($hanhkhach as $index => $hk): ?>
                            <div class="passenger-item">
                                <div class="passenger-number"><?php echo $index + 1; ?></div>
                                <div class="passenger-info">
                                    <div class="passenger-name"><?php echo $hk['HoTenHanhKhach']; ?></div>
                                    <div class="passenger-details">
                                        CMND: <?php echo $hk['CMND']; ?> | Ghế: <strong><?php echo $hk['SoGhe']; ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Phương thức thanh toán -->
            <div class="col-lg-5">
                <div class="payment-card sticky-card">
                    <h3 class="section-title">
                        <i class="bi bi-wallet2 me-2"></i>Phương thức thanh toán
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="phuongthuc" value="Thẻ ATM" checked>
                                <div class="method-card">
                                    <i class="bi bi-credit-card-2-front method-icon"></i>
                                    <span class="method-name">Thẻ ATM nội địa</span>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="phuongthuc" value="Ví điện tử">
                                <div class="method-card">
                                    <i class="bi bi-phone method-icon"></i>
                                    <span class="method-name">Ví điện tử (Momo, ZaloPay)</span>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="phuongthuc" value="Chuyển khoản">
                                <div class="method-card">
                                    <i class="bi bi-bank method-icon"></i>
                                    <span class="method-name">Chuyển khoản ngân hàng</span>
                                </div>
                            </label>

                            <label class="payment-method">
                                <input type="radio" name="phuongthuc" value="Thanh toán tại quầy">
                                <div class="method-card">
                                    <i class="bi bi-shop method-icon"></i>
                                    <span class="method-name">Thanh toán tại quầy</span>
                                </div>
                            </label>
                        </div>

                        <div class="price-breakdown mt-4">
                            <div class="breakdown-item">
                                <span>Giá vé (x<?php echo $datve['SoLuongVe']; ?>):</span>
                                <span><?php echo number_format($datve['GiaVe'] * $datve['SoLuongVe'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="breakdown-item">
                                <span>Phí dịch vụ:</span>
                                <span>0 đ</span>
                            </div>
                            <div class="breakdown-item total">
                                <span>Tổng thanh toán:</span>
                                <span><?php echo number_format($datve['TongTien'], 0, ',', '.'); ?> đ</span>
                            </div>
                        </div>

                        <button type="submit" name="thanhtoan" class="btn btn-success btn-lg w-100 mt-4">
                            <i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán
                        </button>

                        <a href="lichsu.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-arrow-left me-2"></i>Thanh toán sau
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>

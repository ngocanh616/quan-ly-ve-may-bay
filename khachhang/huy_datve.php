<?php
/**
 * File: khachhang/huy_datve.php
 * Mô tả: Xử lý hủy đặt vé (Backend processing only)
 */

require_once '../config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    $_SESSION['message'] = 'Vui lòng đăng nhập!';
    $_SESSION['message_type'] = 'warning';
    header('Location: ../dangnhap.php');
    exit();
}

// Lấy mã đặt vé
$madatve = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$madatve) {
    $_SESSION['message'] = 'Mã đặt vé không hợp lệ!';
    $_SESSION['message_type'] = 'danger';
    header('Location: lichsu.php');
    exit();
}

// Lấy thông tin đặt vé
$datve = fetch_one("SELECT dv.*, cb.ThoiGianBay, cb.SoGheConLai
                    FROM DatVe dv
                    INNER JOIN ChuyenBay cb ON dv.MaChuyenBay = cb.MaChuyenBay
                    WHERE dv.MaDatVe = $madatve AND dv.MaNguoiDung = {$_SESSION['MaNguoiDung']}");

// Kiểm tra tồn tại
if (!$datve) {
    $_SESSION['message'] = 'Đặt vé không tồn tại hoặc không thuộc về bạn!';
    $_SESSION['message_type'] = 'danger';
    header('Location: lichsu.php');
    exit();
}

// Kiểm tra trạng thái
if ($datve['TrangThai'] != 'choxacnhan') {
    $_SESSION['message'] = 'Chỉ có thể hủy vé ở trạng thái "Chờ xác nhận"!';
    $_SESSION['message_type'] = 'warning';
    header('Location: lichsu.php');
    exit();
}

// Kiểm tra thời gian (không cho hủy nếu sắp bay trong 24h)
$thoiGianBay = strtotime($datve['ThoiGianBay']);
$thoiGianHienTai = time();
$soGioConLai = ($thoiGianBay - $thoiGianHienTai) / 3600;

if ($soGioConLai < 24) {
    $_SESSION['message'] = 'Không thể hủy vé khi chuyến bay sắp khởi hành trong 24 giờ!';
    $_SESSION['message_type'] = 'danger';
    header('Location: lichsu.php');
    exit();
}

// Xử lý hủy vé
global $conn;

// Bắt đầu transaction
$conn->begin_transaction();

try {
    // 1. Cập nhật trạng thái đặt vé
    $sqlUpdate = "UPDATE DatVe SET TrangThai = 'dahuy' WHERE MaDatVe = $madatve";
    if (!query($sqlUpdate)) {
        throw new Exception('Không thể cập nhật trạng thái!');
    }
    
    // 2. Hoàn lại số ghế
    $soGheHoanLai = $datve['SoGheConLai'] + $datve['SoLuongVe'];
    $sqlHoanGhe = "UPDATE ChuyenBay SET SoGheConLai = $soGheHoanLai WHERE MaChuyenBay = {$datve['MaChuyenBay']}";
    if (!query($sqlHoanGhe)) {
        throw new Exception('Không thể hoàn lại ghế!');
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['message'] = 'Hủy đặt vé thành công! Đã hoàn lại ' . $datve['SoLuongVe'] . ' ghế.';
    $_SESSION['message_type'] = 'success';
    
} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    $_SESSION['message'] = 'Có lỗi xảy ra: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

// Redirect về trang lịch sử
header('Location: lichsu.php');
exit();
?>

<?php

/**
 * File: khachhang/lichsu.php
 * Mô tả: Lịch sử đặt vé của khách hàng
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

// Lấy tham số lọc
$trangthai = isset($_GET['trangthai']) ? escape($_GET['trangthai']) : '';

// Build query
$where = ["dv.MaNguoiDung = $manguoidung"];
if ($trangthai) {
    $where[] = "dv.TrangThai = '$trangthai'";
}
$whereClause = implode(' AND ', $where);

// Lấy danh sách đặt vé
$sql = "SELECT 
            dv.*,
            cb.MaChuyenBayText,
            cb.HangBay,
            cb.SanBayDi,
            cb.SanBayDen,
            cb.ThoiGianBay,
            cb.ThoiGianDen
        FROM DatVe dv
        INNER JOIN ChuyenBay cb ON dv.MaChuyenBay = cb.MaChuyenBay
        WHERE $whereClause
        ORDER BY dv.NgayDat DESC";

$datveList = fetch_all($sql);

$pageTitle = 'Lịch Sử Đặt Vé - Sky Airline';
$loadKhachHangCSS = true;
require_once '../header.php';
?>

<div class="history-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <h1 class="page-title">
                <i class="bi bi-clock-history me-3"></i>
                Lịch Sử Đặt Vé
            </h1>
            <p class="page-subtitle">
                Quản lý và theo dõi tất cả các chuyến bay bạn đã đặt
            </p>
        </div>

        <!-- Filter -->
        <div class="filter-tabs mb-4">
            <a href="lichsu.php" class="filter-tab <?php echo empty($trangthai) ? 'active' : ''; ?>">
                <i class="bi bi-list-ul me-2"></i>Tất cả
            </a>
            <a href="lichsu.php?trangthai=choxacnhan" class="filter-tab <?php echo $trangthai == 'choxacnhan' ? 'active' : ''; ?>">
                <i class="bi bi-hourglass-split me-2"></i>Chờ xác nhận
            </a>
            <a href="lichsu.php?trangthai=daxacnhan" class="filter-tab <?php echo $trangthai == 'daxacnhan' ? 'active' : ''; ?>">
                <i class="bi bi-check-circle me-2"></i>Đã xác nhận
            </a>
            <a href="lichsu.php?trangthai=dathanhtoan" class="filter-tab <?php echo $trangthai == 'dathanhtoan' ? 'active' : ''; ?>">
                <i class="bi bi-check-all me-2"></i>Đã thanh toán
            </a>
            <a href="lichsu.php?trangthai=dahuy" class="filter-tab <?php echo $trangthai == 'dahuy' ? 'active' : ''; ?>">
                <i class="bi bi-x-circle me-2"></i>Đã hủy
            </a>
        </div>

        <!-- Results -->
        <?php if (empty($datveList)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox empty-icon"></i>
                <h3>Chưa có đặt vé nào</h3>
                <p>Hãy đặt chuyến bay đầu tiên của bạn!</p>
                <a href="chuyenbay.php" class="btn btn-primary">
                    <i class="bi bi-airplane me-2"></i>Đặt vé ngay
                </a>
            </div>
        <?php else: ?>
            <div class="bookings-list">
                <?php foreach ($datveList as $dv): ?>
                    <?php
                    // Lấy danh sách hành khách
                    $hanhkhach = fetch_all("SELECT * FROM ChiTietDatVe WHERE MaDatVe = {$dv['MaDatVe']}");

                    // Tính thời gian bay
                    $start = new DateTime($dv['ThoiGianBay']);
                    $end = new DateTime($dv['ThoiGianDen']);
                    $diff = $start->diff($end);
                    $duration = $diff->h . 'h ' . $diff->i . 'm';

                    // Màu status
                    $statusClass = [
                        'choxacnhan' => 'warning',
                        'daxacnhan' => 'info',
                        'dathanhtoan' => 'primary',
                        'hoanthanh' => 'success',
                        'dahuy' => 'danger'
                    ];
                    $statusColor = $statusClass[$dv['TrangThai']] ?? 'secondary';

                    // Tên trạng thái
                    $statusText = [
                        'choxacnhan' => 'Chờ xác nhận',
                        'daxacnhan' => 'Đã xác nhận',
                        'dathanhtoan' => 'Đã thanh toán',
                        'hoanthanh' => 'Hoàn thành',
                        'dahuy' => 'Đã hủy'
                    ];
                    $statusName = $statusText[$dv['TrangThai']] ?? $dv['TrangThai'];
                    ?>

                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="booking-id">
                                <i class="bi bi-ticket-perforated me-2"></i>
                                <strong>Mã đặt vé: #<?php echo str_pad($dv['MaDatVe'], 6, '0', STR_PAD_LEFT); ?></strong>
                            </div>
                            <span class="badge bg-<?php echo $statusColor; ?>">
                                <?php echo $statusName; ?>
                            </span>
                        </div>

                        <div class="booking-body">
                            <div class="flight-info">
                                <div class="flight-route">
                                    <div class="route-point">
                                        <div class="route-city"><?php echo $dv['SanBayDi']; ?></div>
                                        <div class="route-time"><?php echo date('H:i', strtotime($dv['ThoiGianBay'])); ?></div>
                                        <div class="route-date"><?php echo date('d/m/Y', strtotime($dv['ThoiGianBay'])); ?></div>
                                    </div>

                                    <div class="route-arrow">
                                        <i class="bi bi-arrow-right"></i>
                                        <div class="route-duration"><?php echo $duration; ?></div>
                                    </div>

                                    <div class="route-point">
                                        <div class="route-city"><?php echo $dv['SanBayDen']; ?></div>
                                        <div class="route-time"><?php echo date('H:i', strtotime($dv['ThoiGianDen'])); ?></div>
                                        <div class="route-date"><?php echo date('d/m/Y', strtotime($dv['ThoiGianDen'])); ?></div>
                                    </div>
                                </div>

                                <div class="flight-details">
                                    <div class="detail-item">
                                        <i class="bi bi-airplane"></i>
                                        <span><?php echo $dv['MaChuyenBayText']; ?> - <?php echo $dv['HangBay']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="bi bi-people"></i>
                                        <span><?php echo $dv['SoLuongVe']; ?> hành khách</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="bi bi-calendar"></i>
                                        <span>Đặt ngày: <?php echo date('d/m/Y H:i', strtotime($dv['NgayDat'])); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="booking-actions">
                                <div class="booking-price">
                                    <div class="price-label">Tổng tiền</div>
                                    <div class="price-amount"><?php echo number_format($dv['TongTien'], 0, ',', '.'); ?> đ</div>
                                </div>

                                <div class="action-buttons">
                                    <!-- Xem chi tiết - Luôn hiển thị -->
                                    <button class="btn btn-outline-primary btn-sm"
                                        onclick="toggleDetails(<?php echo $dv['MaDatVe']; ?>)">
                                        <i class="bi bi-eye me-1"></i>Chi tiết
                                    </button>

                                    <?php if ($dv['TrangThai'] == 'choxacnhan'): ?>
                                        <!-- Chờ xác nhận: Có nút Thanh toán và Hủy -->
                                        <a href="thanhtoan.php?madatve=<?php echo $dv['MaDatVe']; ?>"
                                            class="btn btn-success btn-sm">
                                            <i class="bi bi-credit-card me-1"></i>Thanh toán
                                        </a>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="cancelBooking(<?php echo $dv['MaDatVe']; ?>)">
                                            <i class="bi bi-x-circle me-1"></i>Hủy
                                        </button>

                                    <?php elseif ($dv['TrangThai'] == 'daxacnhan'): ?>
                                        <!-- Đã xác nhận: Chỉ có nút Thanh toán -->
                                        <a href="thanhtoan.php?madatve=<?php echo $dv['MaDatVe']; ?>"
                                            class="btn btn-success btn-sm">
                                            <i class="bi bi-credit-card me-1"></i>Thanh toán
                                        </a>

                                    <?php elseif ($dv['TrangThai'] == 'hoanthanh'): ?>
                                        <!-- Hoàn thành: Chỉ hiển thị badge -->
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>Đã thanh toán
                                        </span>

                                    <?php elseif ($dv['TrangThai'] == 'dahuy'): ?>
                                        <!-- Đã hủy: KHÔNG HIỂN THỊ GÌ CẢ, chỉ có badge ở header -->
                                        <!-- Badge "Đã hủy" đã có ở booking-header rồi -->
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>

                        <!-- Chi tiết hành khách (ẩn mặc định) -->
                        <div class="booking-details" id="details-<?php echo $dv['MaDatVe']; ?>" style="display: none;">
                            <h6 class="details-title">
                                <i class="bi bi-people me-2"></i>Danh sách hành khách
                            </h6>
                            <div class="passengers-grid">
                                <?php foreach ($hanhkhach as $index => $hk): ?>
                                    <div class="passenger-card">
                                        <div class="passenger-number"><?php echo $index + 1; ?></div>
                                        <div class="passenger-info">
                                            <div class="passenger-name"><?php echo $hk['HoTenHanhKhach']; ?></div>
                                            <div class="passenger-detail">CMND: <?php echo $hk['CMND']; ?></div>
                                            <div class="passenger-detail">Ghế: <strong><?php echo $hk['SoGhe']; ?></strong></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleDetails(id) {
        const details = document.getElementById('details-' + id);
        if (details.style.display === 'none') {
            details.style.display = 'block';
        } else {
            details.style.display = 'none';
        }
    }

    function cancelBooking(id) {
        if (confirm('Bạn có chắc chắn muốn hủy đặt vé này?\n\nLưu ý:\n- Thao tác này không thể hoàn tác\n- Ghế sẽ được hoàn lại\n- Nếu đã thanh toán, vui lòng liên hệ CSKH để hoàn tiền')) {
            // Redirect đến trang xử lý hủy
            window.location.href = 'huy_datve.php?id=' + id;
        }
    }
</script>


<?php require_once '../footer.php'; ?>
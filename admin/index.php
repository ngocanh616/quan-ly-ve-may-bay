<?php
/*
 * File: admin/index.php
 * Mô tả: Trang Dashboard Admin - Điều chỉnh theo database thực tế
 */

require_once '../config.php';

$page_title = "Dashboard";
$page_subtitle = "Chào mừng trở lại!";
$current_page = 'dashboard';

require_once 'header_admin.php';
// Kiểm tra đăng nhập admin
if (!is_logged_in() || !is_admin()) {
    redirect('../dangnhap.php');
}

// ============================================
// LẤY THỐNG KÊ THỰC TẾ
// ============================================

// Tổng chuyến bay
$stats['total_flights'] = query_single("SELECT COUNT(*) as count FROM ChuyenBay")['count'] ?? 0;

// Tổng đặt vé
$stats['total_bookings'] = query_single("SELECT COUNT(*) as count FROM DatVe")['count'] ?? 0;

// Tổng doanh thu (chỉ tính đã xác nhận và đã thanh toán)
$revenue_result = query_single("SELECT SUM(TongTien) as total FROM DatVe WHERE TrangThai IN ('daxacnhan', 'dathanhtoan')");
$stats['total_revenue'] = $revenue_result['total'] ?? 0;

// Tổng khách hàng
$stats['total_users'] = query_single("SELECT COUNT(*) as count FROM NguoiDung WHERE VaiTro='khachhang'")['count'] ?? 0;

// Số đơn chờ xác nhận
$pending_count = query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='choxacnhan'")['count'] ?? 0;

// ============================================
// THỐNG KÊ THEO TRẠNG THÁI (CHO BIỂU ĐỒ)
// ============================================

$status_stats = [
    'paid' => query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='dathanhtoan'")['count'] ?? 0,
    'confirmed' => query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='daxacnhan'")['count'] ?? 0,
    'pending' => $pending_count,
    'cancelled' => query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='dahuy'")['count'] ?? 0,
];

// ============================================
// DOANH THU 7 NGÀY GẦN NHẤT
// ============================================

$revenue_7days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $revenue = query_single("
        SELECT COALESCE(SUM(TongTien), 0) as total 
        FROM DatVe 
        WHERE DATE(NgayDat) = '$date' 
        AND TrangThai IN ('daxacnhan', 'dathanhtoan')
    ")['total'] ?? 0;

    $revenue_7days[] = [
        'date' => date('d/m', strtotime($date)),
        'amount' => $revenue
    ];
}

// ============================================
// DOANH THU THÁNG TRƯỚC (ĐỂ TÍNH % TĂNG TRƯỞNG)
// ============================================

$current_month_revenue = query_single("
    SELECT COALESCE(SUM(TongTien), 0) as total 
    FROM DatVe 
    WHERE MONTH(NgayDat) = MONTH(CURRENT_DATE())
    AND YEAR(NgayDat) = YEAR(CURRENT_DATE())
    AND TrangThai IN ('daxacnhan', 'dathanhtoan')
")['total'] ?? 0;

$last_month_revenue = query_single("
    SELECT COALESCE(SUM(TongTien), 0) as total 
    FROM DatVe 
    WHERE MONTH(NgayDat) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND YEAR(NgayDat) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND TrangThai IN ('daxacnhan', 'dathanhtoan')
")['total'] ?? 0;

// Tính % tăng trưởng doanh thu
$revenue_growth = 0;
if ($last_month_revenue > 0) {
    $revenue_growth = round((($current_month_revenue - $last_month_revenue) / $last_month_revenue) * 100, 1);
}

// ============================================
// THỐNG KÊ THÁNG TRƯỚC CHO CÁC CHỈ SỐ KHÁC
// ============================================

// Chuyến bay (sử dụng ThoiGianBay thay vì NgayKhoiHanh)
$current_month_flights = query_single("
    SELECT COUNT(*) as count 
    FROM ChuyenBay 
    WHERE MONTH(ThoiGianBay) = MONTH(CURRENT_DATE())
    AND YEAR(ThoiGianBay) = YEAR(CURRENT_DATE())
")['count'] ?? 0;

$last_month_flights = query_single("
    SELECT COUNT(*) as count 
    FROM ChuyenBay 
    WHERE MONTH(ThoiGianBay) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND YEAR(ThoiGianBay) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
")['count'] ?? 0;

$flights_growth = 0;
if ($last_month_flights > 0) {
    $flights_growth = round((($current_month_flights - $last_month_flights) / $last_month_flights) * 100, 1);
}

// Đặt vé
$current_month_bookings = query_single("
    SELECT COUNT(*) as count 
    FROM DatVe 
    WHERE MONTH(NgayDat) = MONTH(CURRENT_DATE())
    AND YEAR(NgayDat) = YEAR(CURRENT_DATE())
")['count'] ?? 0;

$last_month_bookings = query_single("
    SELECT COUNT(*) as count 
    FROM DatVe 
    WHERE MONTH(NgayDat) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND YEAR(NgayDat) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
")['count'] ?? 0;

$bookings_growth = 0;
if ($last_month_bookings > 0) {
    $bookings_growth = round((($current_month_bookings - $last_month_bookings) / $last_month_bookings) * 100, 1);
}

// Khách hàng mới
$current_month_users = query_single("
    SELECT COUNT(*) as count 
    FROM NguoiDung 
    WHERE VaiTro='khachhang'
    AND MONTH(NgayTao) = MONTH(CURRENT_DATE())
    AND YEAR(NgayTao) = YEAR(CURRENT_DATE())
")['count'] ?? 0;

$last_month_users = query_single("
    SELECT COUNT(*) as count 
    FROM NguoiDung 
    WHERE VaiTro='khachhang'
    AND MONTH(NgayTao) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND YEAR(NgayTao) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
")['count'] ?? 0;

$users_growth = 0;
if ($last_month_users > 0) {
    $users_growth = round((($current_month_users - $last_month_users) / $last_month_users) * 100, 1);
}

// ============================================
// ĐẶT VÉ GẦN NHẤT (10 ĐƠN MỚI NHẤT)
// ============================================

$recent_bookings = fetch_all("
    SELECT 
        dv.MaDatVe, 
        dv.TenHanhKhach, 
        cb.SanBayDi, 
        cb.SanBayDen,
        dv.TongTien, 
        dv.TrangThai,
        dv.NgayDat
    FROM DatVe dv
    INNER JOIN ChuyenBay cb ON dv.MaChuyenBay = cb.MaChuyenBay
    ORDER BY dv.NgayDat DESC
    LIMIT 10
");
?>
<!-- Stats Cards -->
<div class="stats-grid">
    <!-- Tổng chuyến bay -->
    <div class="stat-card blue">
        <div class="stat-header">
            <span class="stat-title">Tổng chuyến bay</span>
            <div class="stat-icon">
                <i class="fas fa-plane"></i>
            </div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_flights']) ?></div>
        <div class="stat-footer">
            <?php if ($flights_growth != 0): ?>
                <span class="stat-change <?= $flights_growth >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $flights_growth >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($flights_growth) ?>%
                </span>
                <span class="stat-label">so với tháng trước</span>
            <?php else: ?>
                <span class="stat-label">Không đổi so với tháng trước</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tổng đặt vé -->
    <div class="stat-card green">
        <div class="stat-header">
            <span class="stat-title">Tổng đặt vé</span>
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_bookings']) ?></div>
        <div class="stat-footer">
            <?php if ($bookings_growth != 0): ?>
                <span class="stat-change <?= $bookings_growth >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $bookings_growth >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($bookings_growth) ?>%
                </span>
                <span class="stat-label">so với tháng trước</span>
            <?php else: ?>
                <span class="stat-label">Không đổi so với tháng trước</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Doanh thu -->
    <div class="stat-card orange">
        <div class="stat-header">
            <span class="stat-title">Doanh thu</span>
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_revenue']) ?>đ</div>
        <div class="stat-footer">
            <?php if ($revenue_growth != 0): ?>
                <span class="stat-change <?= $revenue_growth >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $revenue_growth >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($revenue_growth) ?>%
                </span>
                <span class="stat-label">so với tháng trước</span>
            <?php else: ?>
                <span class="stat-label">Không đổi so với tháng trước</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Khách hàng -->
    <div class="stat-card red">
        <div class="stat-header">
            <span class="stat-title">Khách hàng</span>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
        <div class="stat-footer">
            <?php if ($users_growth != 0): ?>
                <span class="stat-change <?= $users_growth >= 0 ? 'up' : 'down' ?>">
                    <i class="fas fa-arrow-<?= $users_growth >= 0 ? 'up' : 'down' ?>"></i>
                    <?= abs($users_growth) ?>%
                </span>
                <span class="stat-label">so với tháng trước</span>
            <?php else: ?>
                <span class="stat-label">Không đổi so với tháng trước</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <!-- Doanh thu 7 ngày -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Doanh thu 7 ngày gần nhất</h3>
        </div>
        <div class="chart-body">
            <canvas id="revenueChart" width="600" height="300"></canvas>
        </div>
    </div>

    <!-- Trạng thái đặt vé -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Trạng thái đặt vé</h3>
        </div>
        <div class="chart-body">
            <canvas id="statusChart" width="300" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">Đặt vé gần đây</h3>
        <button class="btn-view-all" onclick="location.href='datve.php'">
            Xem tất cả <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Mã vé</th>
                <th>Hành khách</th>
                <th>Chuyến bay</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recent_bookings)): ?>
                <?php foreach ($recent_bookings as $booking): ?>
                    <tr>
                        <td><strong>#<?= $booking['MaDatVe'] ?></strong></td>
                        <td><?= htmlspecialchars($booking['TenHanhKhach'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($booking['SanBayDi']) ?> → <?= htmlspecialchars($booking['SanBayDen']) ?></td>
                        <td><strong><?= number_format($booking['TongTien']) ?>đ</strong></td>
                        <td>
                            <?php
                            $status_map = [
                                'choxacnhan' => ['class' => 'pending', 'text' => 'Chờ xác nhận'],
                                'daxacnhan' => ['class' => 'success', 'text' => 'Đã xác nhận'],
                                'dathanhtoan' => ['class' => 'success', 'text' => 'Đã thanh toán'],
                                'dahuy' => ['class' => 'cancelled', 'text' => 'Đã hủy'],
                            ];
                            $status = $status_map[$booking['TrangThai']] ?? ['class' => 'pending', 'text' => 'Không rõ'];
                            ?>
                            <span class="status-badge <?= $status['class'] ?>"><?= $status['text'] ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($booking['NgayDat'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #94A3B8;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        <p>Chưa có đặt vé nào</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <div class="action-btn" onclick="location.href='qly_chuyenbay.php?action=add'">
        <div class="action-icon">
            <i class="fas fa-plus"></i>
        </div>
        <div class="action-text">
            <h4>Thêm chuyến bay mới</h4>
            <p>Tạo lịch bay mới trong hệ thống</p>
        </div>
    </div>

    <div class="action-btn" onclick="location.href='qly_datve.php'">
        <div class="action-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="action-text">
            <h4>Quản lý đặt vé</h4>
            <p>Xem và xử lý đơn đặt vé</p>
        </div>
    </div>

    <div class="action-btn" onclick="location.href='thongke.php'">
        <div class="action-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="action-text">
            <h4>Xem báo cáo</h4>
            <p>Phân tích dữ liệu và thống kê</p>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // Dữ liệu doanh thu 7 ngày THẬT từ PHP
    const revenueData = <?= json_encode(array_column($revenue_7days, 'amount')) ?>;
    const revenueLabels = <?= json_encode(array_column($revenue_7days, 'date')) ?>;

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Doanh thu',
                data: revenueData,
                backgroundColor: 'rgba(0, 102, 204, 0.1)',
                borderColor: '#0066CC',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + 'đ';
                        }
                    }
                }
            }
        }
    });

    // Dữ liệu trạng thái THẬT từ PHP
    const statusData = [
        <?= $status_stats['paid'] ?>,
        <?= $status_stats['confirmed'] ?>,
        <?= $status_stats['pending'] ?>,
        <?= $status_stats['cancelled'] ?>
    ];

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Đã thanh toán', 'Đã xác nhận', 'Chờ xác nhận', 'Đã hủy'],
            datasets: [{
                data: statusData,
                backgroundColor: ['#10B981', '#0066CC', '#F59E0B', '#EF4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<?php require_once 'footer_admin.php'; ?>

</body>

</html>
<?php
require_once '../config.php';

$page_title = "Thống kê & Báo cáo";
$page_subtitle = "Phân tích dữ liệu chi tiết";
$current_page = 'thongke';

// THỐNG KÊ TỔNG QUAN
$total_flights = query_single("SELECT COUNT(*) as count FROM ChuyenBay")['count'] ?? 0;
$total_bookings = query_single("SELECT COUNT(*) as count FROM DatVe")['count'] ?? 0;
$total_revenue = query_single("SELECT COALESCE(SUM(TongTien), 0) as total FROM DatVe WHERE TrangThai IN ('daxacnhan', 'dathanhtoan')")['total'] ?? 0;
$total_users = query_single("SELECT COUNT(*) as count FROM NguoiDung WHERE VaiTro='khachhang'")['count'] ?? 0;

// THỐNG KÊ THEO THÁNG
$monthly_stats = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('m/Y', strtotime("-$i months"));
    
    $revenue = query_single("
        SELECT COALESCE(SUM(TongTien), 0) as total 
        FROM DatVe 
        WHERE DATE_FORMAT(NgayDat, '%Y-%m') = '$month'
        AND TrangThai IN ('daxacnhan', 'dathanhtoan')
    ")['total'] ?? 0;
    
    $bookings = query_single("
        SELECT COUNT(*) as count 
        FROM DatVe 
        WHERE DATE_FORMAT(NgayDat, '%Y-%m') = '$month'
    ")['count'] ?? 0;
    
    $monthly_stats[] = [
        'month' => $month_name,
        'revenue' => (int)$revenue,
        'bookings' => (int)$bookings
    ];
}

// THỐNG KÊ SÂN BAY
$top_airports = fetch_all("
    SELECT 
        cb.SanBayDi as SanBay,
        COUNT(dv.MaDatVe) as SoLuotDat
    FROM DatVe dv
    INNER JOIN ChuyenBay cb ON dv.MaChuyenBay = cb.MaChuyenBay
    GROUP BY cb.SanBayDi
    ORDER BY SoLuotDat DESC
    LIMIT 10
") ?? [];

// DOANH THU THEO TRẠNG THÁI
$dathanhtoan = (int)(query_single("SELECT COALESCE(SUM(TongTien), 0) as total FROM DatVe WHERE TrangThai='dathanhtoan'")['total'] ?? 0);
$daxacnhan = (int)(query_single("SELECT COALESCE(SUM(TongTien), 0) as total FROM DatVe WHERE TrangThai='daxacnhan'")['total'] ?? 0);
$choxacnhan = (int)(query_single("SELECT COALESCE(SUM(TongTien), 0) as total FROM DatVe WHERE TrangThai='choxacnhan'")['total'] ?? 0);
$dahuy = (int)(query_single("SELECT COALESCE(SUM(TongTien), 0) as total FROM DatVe WHERE TrangThai='dahuy'")['total'] ?? 0);

// CHỈ SỐ
$avg_ticket_price = $total_bookings > 0 ? (int)($total_revenue / $total_bookings) : 0;

$occupancy_rate = query_single("
    SELECT ROUND(COUNT(*) * 100.0 / GREATEST(SUM(TongSoGhe), 1), 1) as rate
    FROM DatVe dv
    INNER JOIN ChuyenBay cb ON dv.MaChuyenBay = cb.MaChuyenBay
    WHERE dv.TrangThai IN ('daxacnhan', 'dathanhtoan')
")['rate'] ?? 0;

$booking_completed = query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='dathanhtoan'")['count'] ?? 0;
$completion_rate = $total_bookings > 0 ? round($booking_completed * 100 / $total_bookings, 1) : 0;

require_once 'header_admin.php';
?>

<!-- Key Metrics -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-icon" style="background: #E0F2FE;">
            <i class="fas fa-chart-line" style="color: #0284C7;"></i>
        </div>
        <div class="metric-content">
            <div class="metric-label">Doanh thu trung bình/vé</div>
            <div class="metric-value"><?= number_format($avg_ticket_price) ?>đ</div>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-icon" style="background: #F0FDF4;">
            <i class="fas fa-percent" style="color: #16A34A;"></i>
        </div>
        <div class="metric-content">
            <div class="metric-label">Tỷ lệ lấp đầy ghế</div>
            <div class="metric-value"><?= $occupancy_rate ?>%</div>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-icon" style="background: #FDF2F8;">
            <i class="fas fa-check-double" style="color: #EC4899;"></i>
        </div>
        <div class="metric-content">
            <div class="metric-label">Tỷ lệ hoàn tất</div>
            <div class="metric-value"><?= $completion_rate ?>%</div>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-icon" style="background: #FEF3C7;">
            <i class="fas fa-users" style="color: #D97706;"></i>
        </div>
        <div class="metric-content">
            <div class="metric-label">Tổng khách hàng</div>
            <div class="metric-value"><?= number_format($total_users) ?></div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="chart-row">
    <div class="chart-card">
        <h3 class="chart-title">Doanh thu theo Trạng thái</h3>
        <div class="chart-body">
            <canvas id="revenueStatusChart"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <h3 class="chart-title">Doanh thu 12 tháng</h3>
        <div class="chart-body">
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
    </div>
</div>
<!-- Top Airports Table -->
<div class="table-card">
    <h3 class="chart-title">Top 10 Sân bay (đơn đặt vé)</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Sân bay</th>
                <th>Số lượt đặt vé</th>
                <th>% Tổng</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($top_airports)): ?>
                <?php foreach ($top_airports as $key => $airport): ?>
                <tr>
                    <td><strong><?= $key + 1 ?></strong></td>
                    <td><?= htmlspecialchars($airport['SanBay']) ?></td>
                    <td><strong><?= $airport['SoLuotDat'] ?></strong></td>
                    <td>
                        <div style="background: #E0E7FF; border-radius: 8px; padding: 4px 8px; font-weight: 600; color: #4F46E5; text-align: center;">
                            <?= round($airport['SoLuotDat'] * 100 / max($total_bookings, 1), 1) ?>%
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #94A3B8;">Chưa có dữ liệu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<br>
<!-- Monthly Stats Table -->
<div class="table-card">
    <h3 class="chart-title">Thống kê theo tháng (12 tháng gần nhất)</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tháng</th>
                <th>Số vé đặt</th>
                <th>Doanh thu</th>
                <th>Trung bình/vé</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($monthly_stats as $stat): ?>
            <tr>
                <td><strong><?= $stat['month'] ?></strong></td>
                <td><?= $stat['bookings'] ?></td>
                <td><strong><?= number_format($stat['revenue']) ?>đ</strong></td>
                <td>
                    <?= $stat['bookings'] > 0 ? number_format($stat['revenue'] / $stat['bookings']) . 'đ' : '0đ' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.chart-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--shadow);
}

.chart-body {
    position: relative;
    height: 350px !important;
    width: 100% !important;
    min-height: 350px !important;
}

.chart-body canvas {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    max-height: 100% !important;
    max-width: 100% !important;
}

.chart-title {
    margin: 0 0 16px 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--gray-900);
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.metric-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.metric-content {
    flex: 1;
}

.metric-label {
    font-size: 12px;
    color: var(--gray-600);
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.metric-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--gray-900);
}

.chart-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.chart-body {
    height: 300px;
    position: relative;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra xem Chart CDN đã load chưa
    if (typeof Chart === 'undefined') {
        console.error('Chart.js không được load');
        return;
    }

    // Revenue by Status - BAR CHART
    const statusCtx = document.getElementById('revenueStatusChart');
    if (statusCtx) {
        try {
            new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: ['Đã thanh toán', 'Đã xác nhận', 'Chờ xác nhận', 'Đã hủy'],
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: [<?= $dathanhtoan ?>, <?= $daxacnhan ?>, <?= $choxacnhan ?>, <?= $dahuy ?>],
                        backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Error creating status chart:', e);
        }
    }

    // Monthly Revenue - LINE CHART
    const monthlyCtx = document.getElementById('monthlyRevenueChart');
    if (monthlyCtx) {
        try {
            const monthLabels = <?= json_encode(array_column($monthly_stats, 'month')) ?>;
            const monthRevenues = <?= json_encode(array_column($monthly_stats, 'revenue')) ?>;
            
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: monthRevenues,
                        borderColor: '#0066CC',
                        backgroundColor: 'rgba(0, 102, 204, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#0066CC',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Error creating monthly chart:', e);
        }
    }
});
</script>

<?php require_once 'footer_admin.php'; ?>

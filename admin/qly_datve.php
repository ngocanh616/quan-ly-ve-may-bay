<?php
require_once '../config.php';

$page_title = "Quản lý Đặt vé";
$page_subtitle = "Danh sách và xử lý đơn đặt vé";
$current_page = 'datve';

// ============================================
// XỬ LÝ CẬP NHẬT TRẠNG THÁI
// ============================================

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    $valid_actions = ['xacnhan', 'thanhtoan', 'huy'];
    
    if (in_array($action, $valid_actions)) {
        $status_map = [
            'xacnhan' => 'daxacnhan',
            'thanhtoan' => 'dathanhtoan',
            'huy' => 'dahuy'
        ];
        
        $new_status = $status_map[$action];
        query("UPDATE DatVe SET TrangThai = '$new_status' WHERE MaDatVe = $id");
        
        $message_map = [
            'xacnhan' => 'Xác nhận đặt vé thành công!',
            'thanhtoan' => 'Cập nhật thanh toán thành công!',
            'huy' => 'Hủy đặt vé thành công!'
        ];
        
        set_message('success', $message_map[$action]);
        redirect('datve.php');
    }
}

// ============================================
// PHÂN TRANG VÀ TÌM KIẾM
// ============================================

$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = "1=1";

if (!empty($search)) {
    $search_safe = escape($search);
    $where .= " AND (dv.MaDatVe LIKE '%$search_safe%' 
                OR dv.TenHanhKhach LIKE '%$search_safe%' 
                OR dv.SoDienThoai LIKE '%$search_safe%')";
}

if (!empty($filter_status)) {
    $filter_status_safe = escape($filter_status);
    $where .= " AND dv.TrangThai = '$filter_status_safe'";
}

// Đếm tổng số đặt vé
$total = query_single("SELECT COUNT(*) as count FROM DatVe dv WHERE $where")['count'] ?? 0;
$total_pages = ceil($total / $per_page);

// Lấy danh sách đặt vé
$bookings = fetch_all("
    SELECT 
        dv.MaDatVe,
        dv.TenHanhKhach,
        dv.SoDienThoai,
        dv.Email,
        cb.SanBayDi,
        cb.SanBayDen,
        dv.TongTien,
        dv.TrangThai,
        dv.NgayDat,
        cb.ThoiGianBay
    FROM DatVe dv
    INNER JOIN ChuyenBay cb ON dv.MaChuyenBay = cb.MaChuyenBay
    WHERE $where
    ORDER BY dv.NgayDat DESC
    LIMIT $per_page OFFSET $offset
");

// Thống kê theo trạng thái
$status_count = [
    'choxacnhan' => query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='choxacnhan'")['count'] ?? 0,
    'daxacnhan' => query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='daxacnhan'")['count'] ?? 0,
    'dathanhtoan' => query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='dathanhtoan'")['count'] ?? 0,
    'dahuy' => query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='dahuy'")['count'] ?? 0,
];

$msg = get_message();

require_once 'header_admin.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>">
    <i class="fas fa-<?= $msg['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($msg['message']) ?>
</div>
<?php endif; ?>

<!-- Filter Status -->
<div class="filter-tabs">
    <a href="qly_datve.php" class="filter-tab <?= empty($filter_status) ? 'active' : '' ?>">
        <i class="fas fa-list"></i>
        Tất cả (<?= $total ?>)
    </a>
    <a href="qly_datve.php?status=choxacnhan" class="filter-tab <?= $filter_status === 'choxacnhan' ? 'active' : '' ?>">
        <i class="fas fa-hourglass-half"></i>
        Chờ xác nhận (<?= $status_count['choxacnhan'] ?>)
    </a>
    <a href="qly_datve.php?status=daxacnhan" class="filter-tab <?= $filter_status === 'daxacnhan' ? 'active' : '' ?>">
        <i class="fas fa-check"></i>
        Đã xác nhận (<?= $status_count['daxacnhan'] ?>)
    </a>
    <a href="qly_datve.php?status=dathanhtoan" class="filter-tab <?= $filter_status === 'dathanhtoan' ? 'active' : '' ?>">
        <i class="fas fa-credit-card"></i>
        Đã thanh toán (<?= $status_count['dathanhtoan'] ?>)
    </a>
    <a href="qly_datve.php?status=dahuy" class="filter-tab <?= $filter_status === 'dahuy' ? 'active' : '' ?>">
        <i class="fas fa-ban"></i>
        Đã hủy (<?= $status_count['dahuy'] ?>)
    </a>
</div>

<!-- Table -->
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã vé</th>
                <th>Hành khách</th>
                <th>Liên hệ</th>
                <th>Chuyến bay</th>
                <th>Tổng tiền</th>
                <th>Ngày đặt</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bookings)): ?>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><strong>#<?= $booking['MaDatVe'] ?></strong></td>
                    <td><?= htmlspecialchars($booking['TenHanhKhach']) ?></td>
                    <td>
                        <div style="font-size: 12px; color: #64748B;">
                            <div><i class="fas fa-phone"></i> <?= htmlspecialchars($booking['SoDienThoai']) ?></div>
                            <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($booking['Email']) ?></div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 13px;">
                            <strong><?= htmlspecialchars($booking['SanBayDi']) ?> → <?= htmlspecialchars($booking['SanBayDen']) ?></strong>
                            <div style="color: #64748B; font-size: 12px;">
                                <?= date('d/m/Y H:i', strtotime($booking['ThoiGianBay'])) ?>
                            </div>
                        </div>
                    </td>
                    <td><strong><?= number_format($booking['TongTien']) ?>đ</strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($booking['NgayDat'])) ?></td>
                    <td>
                        <?php
                        $status_map = [
                            'choxacnhan' => ['class' => 'pending', 'text' => 'Chờ xác nhận', 'icon' => 'hourglass-half'],
                            'daxacnhan' => ['class' => 'success', 'text' => 'Đã xác nhận', 'icon' => 'check'],
                            'dathanhtoan' => ['class' => 'success', 'text' => 'Đã thanh toán', 'icon' => 'credit-card'],
                            'dahuy' => ['class' => 'cancelled', 'text' => 'Đã hủy', 'icon' => 'ban'],
                        ];
                        $status = $status_map[$booking['TrangThai']] ?? ['class' => 'pending', 'text' => '?', 'icon' => 'question'];
                        ?>
                        <span class="status-badge <?= $status['class'] ?>">
                            <i class="fas fa-<?= $status['icon'] ?>"></i>
                            <?= $status['text'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="flex-wrap: wrap; gap: 4px;">
                            <?php if ($booking['TrangThai'] === 'choxacnhan'): ?>
                            <button class="btn-icon btn-info" onclick="if(confirm('Xác nhận đặt vé này?')) location.href='?action=xacnhan&id=<?= $booking['MaDatVe'] ?>'" title="Xác nhận">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                            
                            <?php if ($booking['TrangThai'] === 'daxacnhan'): ?>
                            <button class="btn-icon btn-success" onclick="if(confirm('Cập nhật thanh toán?')) location.href='?action=thanhtoan&id=<?= $booking['MaDatVe'] ?>'" title="Thanh toán">
                                <i class="fas fa-credit-card"></i>
                            </button>
                            <?php endif; ?>
                            
                            <?php if ($booking['TrangThai'] !== 'dahuy'): ?>
                            <button class="btn-icon btn-danger" onclick="if(confirm('Hủy đặt vé này?')) location.href='?action=huy&id=<?= $booking['MaDatVe'] ?>'" title="Hủy">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 48px; color: #94A3B8; margin-bottom: 16px; display: block;"></i>
                        <p style="color: #94A3B8;">Không tìm thấy đặt vé nào</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_status) ? '&status=' . urlencode($filter_status) : '' ?>" class="page-link">
        <i class="fas fa-chevron-left"></i>
    </a>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
        <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_status) ? '&status=' . urlencode($filter_status) : '' ?>" 
           class="page-link <?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
        <span class="page-dots">...</span>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($page < $total_pages): ?>
    <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_status) ? '&status=' . urlencode($filter_status) : '' ?>" class="page-link">
        <i class="fas fa-chevron-right"></i>
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- CSS cho filter tabs -->
<style>
.filter-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 16px;
    background: var(--white);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    color: var(--gray-700);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-tab:hover {
    border-color: var(--primary-blue);
    color: var(--primary-blue);
}

.filter-tab.active {
    background: var(--primary-blue);
    color: var(--white);
    border-color: var(--primary-blue);
}

.btn-success {
    background: var(--success-light);
    color: var(--success);
}

.btn-success:hover {
    background: var(--success);
    color: var(--white);
}
</style>

<?php require_once 'footer_admin.php'; ?>

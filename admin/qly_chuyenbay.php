<?php
/*
 * File: admin/chuyenbay.php
 * Mô tả: Quản lý chuyến bay - Danh sách, Thêm, Sửa, Xóa
 */
require_once '../config.php';

$page_title = "Quản lý Chuyến bay";
$page_subtitle = "Danh sách tất cả chuyến bay";
$current_page = 'chuyenbay';

// XỬ LÝ XÓA
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $booking_count = query_single("SELECT COUNT(*) as count FROM DatVe WHERE MaChuyenBay = $id")['count'] ?? 0;
    
    if ($booking_count > 0) {
        set_message('error', 'Không thể xóa chuyến bay này vì đã có ' . $booking_count . ' đặt vé!');
    } else {
        query("DELETE FROM ChuyenBay WHERE MaChuyenBay = $id");
        set_message('success', 'Xóa chuyến bay thành công!');
    }
    redirect('qly_chuyenbay.php');
}

// PHÂN TRANG VÀ TÌM KIẾM
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = "1=1";
if (!empty($search)) {
    $search_safe = escape($search);
    $where .= " AND (MaChuyenBay LIKE '%$search_safe%' OR SanBayDi LIKE '%$search_safe%' OR SanBayDen LIKE '%$search_safe%')";
}

$total = query_single("SELECT COUNT(*) as count FROM ChuyenBay WHERE $where")['count'] ?? 0;
$total_pages = ceil($total / $per_page);

$flights = fetch_all("
    SELECT * FROM ChuyenBay 
    WHERE $where
    ORDER BY ThoiGianBay DESC 
    LIMIT $per_page OFFSET $offset
");

$msg = get_message();

require_once 'header_admin.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>">
    <i class="fas fa-<?= $msg['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($msg['message']) ?>
</div>
<?php endif; ?>

<div class="content-toolbar">
    <button class="btn btn-primary" onclick="location.href='chuyenbay_form.php'">
        <i class="fas fa-plus"></i>
        Thêm chuyến bay
    </button>
    <div class="toolbar-actions">
        <button class="btn btn-secondary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            Làm mới
        </button>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã CB</th>
                <th>Sân bay đi</th>
                <th>Sân bay đến</th>
                <th>Thời gian bay</th>
                <th>Giá vé</th>
                <th>Số ghế</th>
                <th>Còn trống</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($flights)): ?>
                <?php foreach ($flights as $flight): ?>
                <?php
                    $booked = query_single("SELECT COUNT(*) as count FROM DatVe WHERE MaChuyenBay = " . $flight['MaChuyenBay'])['count'] ?? 0;
                    $available = $flight['TongSoGhe'] - $booked;
                    
                    $now = time();
                    $flight_time = strtotime($flight['ThoiGianBay']);
                    $status = $flight_time > $now ? 'active' : 'completed';
                    $status_text = $flight_time > $now ? 'Sắp bay' : 'Đã bay';
                ?>
                <tr>
                    <td><strong>#<?= $flight['MaChuyenBay'] ?></strong></td>
                    <td><?= htmlspecialchars($flight['SanBayDi']) ?></td>
                    <td><?= htmlspecialchars($flight['SanBayDen']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($flight['ThoiGianBay'])) ?></td>
                    <td><strong><?= number_format($flight['GiaVe']) ?>đ</strong></td>
                    <td><?= $flight['TongSoGhe'] ?></td>
                    <td>
                        <span class="badge <?= $available > 0 ? 'badge-success' : 'badge-danger' ?>">
                            <?= $available ?> ghế
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?= $status ?>">
                            <?= $status_text ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon btn-info" onclick="location.href='chuyenbay_form.php?id=<?= $flight['MaChuyenBay'] ?>'" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" onclick="confirmDelete(<?= $flight['MaChuyenBay'] ?>)" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px;">
                        <i class="fas fa-plane-slash" style="font-size: 48px; color: #94A3B8; margin-bottom: 16px; display: block;"></i>
                        <p style="color: #94A3B8;">Không tìm thấy chuyến bay nào</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-link">
        <i class="fas fa-chevron-left"></i>
    </a>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
        <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
           class="page-link <?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
        <span class="page-dots">...</span>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($page < $total_pages): ?>
    <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-link">
        <i class="fas fa-chevron-right"></i>
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function confirmDelete(id) {
    if (confirm('Bạn có chắc muốn xóa chuyến bay này?')) {
        window.location.href = '?action=delete&id=' + id;
    }
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const search = this.value.trim();
        if (search) {
            window.location.href = '?search=' + encodeURIComponent(search);
        } else {
            window.location.href = 'chuyenbay.php';
        }
    }
});
</script>

<?php require_once 'footer_admin.php'; ?>

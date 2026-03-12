<?php
require_once '../config.php';

$page_title = "Quản lý Người dùng";
$page_subtitle = "Danh sách khách hàng và quản lý tài khoản";
$current_page = 'nguoidung';

// ============================================
// XỬ LÝ XÓA/KÍCH HOẠT NGƯỜI DÙNG
// ============================================

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'delete') {
        // Kiểm tra xem người dùng có đặt vé không
        $booking_count = query_single("SELECT COUNT(*) as count FROM DatVe WHERE MaNguoiDung = $id")['count'] ?? 0;
        
        if ($booking_count > 0) {
            set_message('error', 'Không thể xóa người dùng này vì đã có đơn đặt vé!');
        } else {
            query("DELETE FROM NguoiDung WHERE MaNguoiDung = $id");
            set_message('success', 'Xóa người dùng thành công!');
        }
    } elseif ($action === 'lock') {
        query("UPDATE NguoiDung SET TrangThai = 'khoa' WHERE MaNguoiDung = $id");
        set_message('success', 'Khóa tài khoản thành công!');
    } elseif ($action === 'unlock') {
        query("UPDATE NguoiDung SET TrangThai = 'hoatdong' WHERE MaNguoiDung = $id");
        set_message('success', 'Mở khóa tài khoản thành công!');
    }
    
    redirect('qly_nguoidung.php');
}

// ============================================
// PHÂN TRANG VÀ TÌM KIẾM
// ============================================

$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_role = $_GET['role'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = "1=1";

if (!empty($search)) {
    $search_safe = escape($search);
    $where .= " AND (TenDangNhap LIKE '%$search_safe%' OR Email LIKE '%$search_safe%' OR HoTen LIKE '%$search_safe%')";
}

if (!empty($filter_role)) {
    $filter_role_safe = escape($filter_role);
    $where .= " AND VaiTro = '$filter_role_safe'";
} else {
    // Mặc định chỉ hiển thị khách hàng
    $where .= " AND VaiTro = 'khachhang'";
}

if (!empty($filter_status)) {
    $filter_status_safe = escape($filter_status);
    $where .= " AND TrangThai = '$filter_status_safe'";
}

// Đếm tổng số người dùng
$total = query_single("SELECT COUNT(*) as count FROM NguoiDung WHERE $where")['count'] ?? 0;
$total_pages = ceil($total / $per_page);

// Lấy danh sách người dùng
$users = fetch_all("
    SELECT * FROM NguoiDung
    WHERE $where
    ORDER BY NgayTao DESC
    LIMIT $per_page OFFSET $offset
");

// Thống kê
$total_customers = query_single("SELECT COUNT(*) as count FROM NguoiDung WHERE VaiTro='khachhang'")['count'] ?? 0;
$total_active = query_single("SELECT COUNT(*) as count FROM NguoiDung WHERE VaiTro='khachhang' AND TrangThai='hoatdong'")['count'] ?? 0;
$total_locked = query_single("SELECT COUNT(*) as count FROM NguoiDung WHERE VaiTro='khachhang' AND TrangThai='khoa'")['count'] ?? 0;

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
    <a href="qly_nguoidung.php" class="filter-tab <?= empty($filter_status) ? 'active' : '' ?>">
        <i class="fas fa-users"></i>
        Tất cả (<?= $total_customers ?>)
    </a>
    <a href="qly_nguoidung.php?status=hoatdong" class="filter-tab <?= $filter_status === 'hoatdong' ? 'active' : '' ?>">
        <i class="fas fa-check-circle"></i>
        Hoạt động (<?= $total_active ?>)
    </a>
    <a href="qly_nguoidung.php?status=khoa" class="filter-tab <?= $filter_status === 'khoa' ? 'active' : '' ?>">
        <i class="fas fa-lock"></i>
        Bị khóa (<?= $total_locked ?>)
    </a>
</div>

<!-- Table -->
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã người dùng</th>
                <th>Tên đăng nhập</th>
                <th>Tên đầy đủ</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Ngày tạo</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong>#<?= $user['MaNguoiDung'] ?></strong></td>
                    <td><?= htmlspecialchars($user['TenDangNhap']) ?></td>
                    <td><?= htmlspecialchars($user['HoTen'] ?? 'N/A') ?></td>
                    <td>
                        <a href="mailto:<?= htmlspecialchars($user['Email']) ?>" style="color: #0066CC; text-decoration: none;">
                            <?= htmlspecialchars($user['Email']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($user['SoDienThoai'] ?? 'N/A') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($user['NgayTao'])) ?></td>
                    <td>
                        <?php
                        $status = $user['TrangThai'] === 'hoatdong' 
                            ? ['class' => 'success', 'text' => 'Hoạt động', 'icon' => 'check-circle']
                            : ['class' => 'cancelled', 'text' => 'Bị khóa', 'icon' => 'lock'];
                        ?>
                        <span class="status-badge <?= $status['class'] ?>">
                            <i class="fas fa-<?= $status['icon'] ?>"></i>
                            <?= $status['text'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="flex-wrap: wrap; gap: 4px;">
                            <!-- Xem chi tiết -->
                            <button class="btn-icon btn-info" onclick="showUserDetails(<?= $user['MaNguoiDung'] ?>)" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <!-- Khóa/Mở khóa -->
                            <?php if ($user['TrangThai'] === 'hoatdong'): ?>
                            <button class="btn-icon btn-warning" onclick="if(confirm('Khóa tài khoản này?')) location.href='?action=lock&id=<?= $user['MaNguoiDung'] ?>'" title="Khóa" style="background: #FEF3C7; color: #F59E0B;">
                                <i class="fas fa-lock"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn-icon btn-success" onclick="if(confirm('Mở khóa tài khoản này?')) location.href='?action=unlock&id=<?= $user['MaNguoiDung'] ?>'" title="Mở khóa" style="background: #D1FAE5; color: #10B981;">
                                <i class="fas fa-unlock"></i>
                            </button>
                            <?php endif; ?>
                            
                            <!-- Xóa -->
                            <button class="btn-icon btn-danger" onclick="if(confirm('Xóa người dùng này?\nLưu ý: Không thể xóa nếu có đơn đặt vé!')) location.href='?action=delete&id=<?= $user['MaNguoiDung'] ?>'" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 48px; color: #94A3B8; margin-bottom: 16px; display: block;"></i>
                        <p style="color: #94A3B8;">Không tìm thấy người dùng nào</p>
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

<!-- Modal xem chi tiết -->
<div id="userModal" class="modal-overlay" style="display: none;">
    <div class="user-detail-modal">
        <div class="modal-header">
            <h3>Thông tin chi tiết người dùng</h3>
            <span class="modal-close" onclick="closeUserModal()">&times;</span>
        </div>
        
        <div class="modal-body" id="userModalBody">
            <p style="text-align: center; color: #94A3B8;">Đang tải...</p>
        </div>
    </div>
</div>

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

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: var(--white);
    padding: 30px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    width: 90%;
    max-width: 500px;
    position: relative;
}

.modal-close {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 28px;
    font-weight: bold;
    color: #94A3B8;
    cursor: pointer;
}

.modal-close:hover {
    color: var(--gray-800);
}

.user-info-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid var(--gray-200);
}

.user-info-row label {
    font-weight: 600;
    color: var(--gray-700);
    width: 120px;
}

.user-info-row value {
    color: var(--gray-600);
    flex: 1;
    word-break: break-all;
}

.modal-overlay {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.user-detail-modal {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    width: 90%;
    max-width: 600px;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { 
        transform: translateY(20px);
        opacity: 0;
    }
    to { 
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 24px;
    background: linear-gradient(135deg, #0066CC 0%, #004C99 100%);
    color: var(--white);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
}

.modal-close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 32px;
}

.user-info-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: #94A3B8;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 15px;
    color: var(--gray-800);
    font-weight: 500;
    word-break: break-all;
}

.info-value a {
    color: #0066CC;
    text-decoration: none;
    transition: color 0.2s;
}

.info-value a:hover {
    color: #004C99;
    text-decoration: underline;
}

.info-value.status-active {
    color: #10B981;
    font-weight: 700;
}

.info-value.status-locked {
    color: #EF4444;
    font-weight: 700;
}

.info-divider {
    grid-column: 1 / -1;
    height: 1px;
    background: #E2E8F0;
}
</style>

<script>
function showUserDetails(userId) {
    const modal = document.getElementById('userModal');
    const body = document.getElementById('userModalBody');
    
    fetch(`api/get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const u = data.user;
                const ngayTao = new Date(u.NgayTao).toLocaleDateString('vi-VN', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const statusClass = u.TrangThai === 'hoatdong' ? 'status-active' : 'status-locked';
                const statusText = u.TrangThai === 'hoatdong' ? '🟢 Hoạt động' : '🔴 Bị khóa';
                
                body.innerHTML = `
                    <div class="user-info-card">
                        <div class="info-item">
                            <div class="info-label">Mã người dùng</div>
                            <div class="info-value"><strong>#${u.MaNguoiDung}</strong></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Trạng thái</div>
                            <div class="info-value ${statusClass}">${statusText}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Tên đăng nhập</div>
                            <div class="info-value">${u.TenDangNhap}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Tên đầy đủ</div>
                            <div class="info-value">${u.HoTen || '(Chưa cập nhật)'}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><a href="mailto:${u.Email}">${u.Email}</a></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Điện thoại</div>
                            <div class="info-value">${u.SoDienThoai || '(Chưa cập nhật)'}</div>
                        </div>
                        
                        <div class="info-divider"></div>
                        
                        <div class="info-item">
                            <div class="info-label">Địa chỉ</div>
                            <div class="info-value">${u.DiaChi || '(Chưa cập nhật)'}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Ngày tạo tài khoản</div>
                            <div class="info-value">${ngayTao}</div>
                        </div>
                    </div>
                `;
            } else {
                body.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; margin-bottom: 16px;">❌</div>
                        <p style="color: #EF4444; font-weight: 600;">${data.error || 'Lỗi khi tải dữ liệu'}</p>
                    </div>
                `;
            }
            modal.style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            body.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 16px;">⚠️</div>
                    <p style="color: #EF4444; font-weight: 600;">Lỗi kết nối: ${error.message}</p>
                </div>
            `;
            modal.style.display = 'flex';
        });
}

function closeUserModal() {
    const modal = document.getElementById('userModal');
    modal.style.display = 'none';
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>

<?php require_once 'footer_admin.php'; ?>

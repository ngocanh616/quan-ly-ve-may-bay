<?php
require_once '../config.php';

$is_edit = false;
$flight = null;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $flight = fetch_one("SELECT * FROM ChuyenBay WHERE MaChuyenBay = $id");
    
    if (!$flight) {
        set_message('error', 'Không tìm thấy chuyến bay!');
        redirect('chuyenbay.php');
    }
    
    $is_edit = true;
}

$errors = [];

// Lấy danh sách sân bay từ database (DISTINCT từ các chuyến bay hiện có)
$airports_query = "SELECT DISTINCT SanBayDi as airport FROM ChuyenBay 
                   UNION 
                   SELECT DISTINCT SanBayDen as airport FROM ChuyenBay 
                   ORDER BY airport";
$airports_result = fetch_all($airports_query);
$airports = [];
foreach ($airports_result as $row) {
    $airports[] = $row['airport'];
}

// Nếu chưa có dữ liệu, thêm một số sân bay mặc định
if (empty($airports)) {
    $airports = [
        'Hà Nội (HAN)',
        'TP. Hồ Chí Minh (SGN)',
        'Đà Nẵng (DAD)',
        'Phú Quốc (PQC)',
        'Nha Trang (CXR)',
        'Cần Thơ (VCA)',
        'Hải Phòng (HPH)'
    ];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_chuyen_bay_text = strtoupper(escape($_POST['ma_chuyen_bay_text'] ?? ''));
    $hang_bay = escape($_POST['hang_bay'] ?? '');
    $san_bay_di = escape($_POST['san_bay_di'] ?? '');
    $san_bay_den = escape($_POST['san_bay_den'] ?? '');
    $thoi_gian_bay = escape($_POST['thoi_gian_bay'] ?? '');
    $thoi_gian_den = escape($_POST['thoi_gian_den'] ?? '');
    $gia_ve = (float)($_POST['gia_ve'] ?? 0);
    $tong_so_ghe = (int)($_POST['tong_so_ghe'] ?? 0);
    
    // Validation
    if (empty($ma_chuyen_bay_text)) {
        $errors[] = "Mã chuyến bay không được để trống";
    } elseif (!preg_match('/^[A-Z]{2}[0-9]{3,4}$/', $ma_chuyen_bay_text)) {
        $errors[] = "Mã chuyến bay phải có định dạng 2 chữ cái in hoa + 3-4 số (VD: VN123)";
    }
    
    if (empty($hang_bay)) $errors[] = "Hãng bay không được để trống";
    if (empty($san_bay_di)) $errors[] = "Sân bay đi không được để trống";
    if (empty($san_bay_den)) $errors[] = "Sân bay đến không được để trống";
    if ($san_bay_di == $san_bay_den) $errors[] = "Sân bay đi và đến phải khác nhau";
    if (empty($thoi_gian_bay)) $errors[] = "Thời gian bay không được để trống";
    if (empty($thoi_gian_den)) $errors[] = "Thời gian đến không được để trống";
    if ($gia_ve <= 0) $errors[] = "Giá vé phải lớn hơn 0";
    if ($tong_so_ghe <= 0) $errors[] = "Số ghế phải lớn hơn 0";
    
    // Kiểm tra thời gian đến phải sau thời gian bay
    if (!empty($thoi_gian_bay) && !empty($thoi_gian_den)) {
        // Chuyển đổi datetime-local format sang MySQL datetime format
        $thoi_gian_bay_mysql = str_replace('T', ' ', $thoi_gian_bay) . ':00';
        $thoi_gian_den_mysql = str_replace('T', ' ', $thoi_gian_den) . ':00';
        
        $timestamp_bay = strtotime($thoi_gian_bay_mysql);
        $timestamp_den = strtotime($thoi_gian_den_mysql);
        
        if ($timestamp_den <= $timestamp_bay) {
            $errors[] = "Thời gian đến phải sau thời gian bay (Hiện tại: Bay " . date('d/m/Y H:i', $timestamp_bay) . " - Đến " . date('d/m/Y H:i', $timestamp_den) . ")";
        }
    }
    
    // Kiểm tra mã chuyến bay trùng lặp (chỉ khi thêm mới hoặc đổi mã)
    if (!$is_edit || ($is_edit && $ma_chuyen_bay_text != $flight['MaChuyenBayText'])) {
        $check = fetch_one("SELECT MaChuyenBay FROM ChuyenBay WHERE MaChuyenBayText = '$ma_chuyen_bay_text'");
        if ($check) {
            $errors[] = "Mã chuyến bay đã tồn tại";
        }
    }
    
    if (empty($errors)) {
        // Chuyển đổi datetime-local format sang MySQL datetime format
        $thoi_gian_bay_mysql = str_replace('T', ' ', $thoi_gian_bay) . ':00';
        $thoi_gian_den_mysql = str_replace('T', ' ', $thoi_gian_den) . ':00';
        
        if ($is_edit) {
            $sql = "UPDATE ChuyenBay SET 
                    MaChuyenBayText = '$ma_chuyen_bay_text',
                    HangBay = '$hang_bay',
                    SanBayDi = '$san_bay_di',
                    SanBayDen = '$san_bay_den',
                    ThoiGianBay = '$thoi_gian_bay_mysql',
                    ThoiGianDen = '$thoi_gian_den_mysql',
                    GiaVe = $gia_ve,
                    TongSoGhe = $tong_so_ghe,
                    SoGheConLai = $tong_so_ghe - (SELECT COALESCE(SUM(SoLuongVe), 0) FROM DatVe WHERE MaChuyenBay = {$flight['MaChuyenBay']} AND TrangThai != 'dahuy')
                    WHERE MaChuyenBay = {$flight['MaChuyenBay']}";
            
            if (query($sql)) {
                set_message('success', 'Cập nhật chuyến bay thành công!');
                redirect('qly_chuyenbay.php');
            } else {
                $errors[] = "Lỗi khi cập nhật chuyến bay";
            }
        } else {
            // SoGheConLai = TongSoGhe khi tạo mới
            $sql = "INSERT INTO ChuyenBay (MaChuyenBayText, HangBay, SanBayDi, SanBayDen, ThoiGianBay, ThoiGianDen, GiaVe, TongSoGhe, SoGheConLai) 
                    VALUES ('$ma_chuyen_bay_text', '$hang_bay', '$san_bay_di', '$san_bay_den', '$thoi_gian_bay_mysql', '$thoi_gian_den_mysql', $gia_ve, $tong_so_ghe, $tong_so_ghe)";
            
            if (query($sql)) {
                set_message('success', 'Thêm chuyến bay thành công!');
                redirect('qly_chuyenbay.php');
            } else {
                $errors[] = "Lỗi khi thêm chuyến bay";
            }
        }
    }
}

$page_title = $is_edit ? "Sửa Chuyến bay" : "Thêm Chuyến bay";
$page_subtitle = $is_edit ? "Cập nhật thông tin chuyến bay" : "Thêm chuyến bay mới vào hệ thống";
$current_page = 'chuyenbay';

require_once 'header_admin.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <ul style="margin: 0; padding-left: 20px;">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="">
        <div class="form-grid">
            
            <div class="form-group">
                <label for="ma_chuyen_bay_text">
                    <i class="fas fa-barcode"></i>
                    Mã chuyến bay <span class="required">*</span>
                </label>
                <input type="text" 
                       id="ma_chuyen_bay_text" 
                       name="ma_chuyen_bay_text" 
                       class="form-control" 
                       value="<?= htmlspecialchars($flight['MaChuyenBayText'] ?? '') ?>"
                       placeholder="Ví dụ: VN123"
                       pattern="[A-Z]{2}[0-9]{3,4}"
                       title="Mã chuyến bay gồm 2 chữ cái in hoa và 3-4 số (VD: VN123)"
                       style="text-transform: uppercase;"
                       required>
                <small>Định dạng: 2 chữ cái in hoa + 3-4 số (VD: VN123, VJ456)</small>
            </div>

            <div class="form-group">
                <label for="hang_bay">
                    <i class="fas fa-building"></i>
                    Hãng bay <span class="required">*</span>
                </label>
                <select id="hang_bay" 
                        name="hang_bay" 
                        class="form-control" 
                        required>
                    <option value="">-- Chọn hãng bay --</option>
                    <option value="Vietnam Airlines" <?= ($flight['HangBay'] ?? '') == 'Vietnam Airlines' ? 'selected' : '' ?>>Vietnam Airlines</option>
                    <option value="VietJet Air" <?= ($flight['HangBay'] ?? '') == 'VietJet Air' ? 'selected' : '' ?>>VietJet Air</option>
                    <option value="Bamboo Airways" <?= ($flight['HangBay'] ?? '') == 'Bamboo Airways' ? 'selected' : '' ?>>Bamboo Airways</option>
                    <option value="Pacific Airlines" <?= ($flight['HangBay'] ?? '') == 'Pacific Airlines' ? 'selected' : '' ?>>Pacific Airlines</option>
                    <option value="Vietravel Airlines" <?= ($flight['HangBay'] ?? '') == 'Vietravel Airlines' ? 'selected' : '' ?>>Vietravel Airlines</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="san_bay_di">
                    <i class="fas fa-plane-departure"></i>
                    Sân bay đi <span class="required">*</span>
                </label>
                <select id="san_bay_di" 
                        name="san_bay_di" 
                        class="form-control" 
                        required>
                    <option value="">-- Chọn sân bay đi --</option>
                    <?php foreach ($airports as $airport): ?>
                        <option value="<?= htmlspecialchars($airport) ?>" <?= ($flight['SanBayDi'] ?? '') == $airport ? 'selected' : '' ?>>
                            <?= htmlspecialchars($airport) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="san_bay_den">
                    <i class="fas fa-plane-arrival"></i>
                    Sân bay đến <span class="required">*</span>
                </label>
                <select id="san_bay_den" 
                        name="san_bay_den" 
                        class="form-control" 
                        required>
                    <option value="">-- Chọn sân bay đến --</option>
                    <?php foreach ($airports as $airport): ?>
                        <option value="<?= htmlspecialchars($airport) ?>" <?= ($flight['SanBayDen'] ?? '') == $airport ? 'selected' : '' ?>>
                            <?= htmlspecialchars($airport) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="thoi_gian_bay">
                    <i class="fas fa-clock"></i>
                    Thời gian bay <span class="required">*</span>
                </label>
                <input type="datetime-local" 
                       id="thoi_gian_bay" 
                       name="thoi_gian_bay" 
                       class="form-control" 
                       value="<?= $flight ? date('Y-m-d\TH:i', strtotime($flight['ThoiGianBay'])) : '' ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="thoi_gian_den">
                    <i class="fas fa-clock"></i>
                    Thời gian đến <span class="required">*</span>
                </label>
                <input type="datetime-local" 
                       id="thoi_gian_den" 
                       name="thoi_gian_den" 
                       class="form-control" 
                       value="<?= $flight ? date('Y-m-d\TH:i', strtotime($flight['ThoiGianDen'])) : '' ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="gia_ve">
                    <i class="fas fa-tag"></i>
                    Giá vé (VNĐ) <span class="required">*</span>
                </label>
                <input type="number" 
                       id="gia_ve" 
                       name="gia_ve" 
                       class="form-control" 
                       value="<?= $flight['GiaVe'] ?? '' ?>"
                       min="0"
                       step="1000"
                       placeholder="1000000"
                       required>
            </div>
            
            <div class="form-group">
                <label for="tong_so_ghe">
                    <i class="fas fa-chair"></i>
                    Tổng số ghế <span class="required">*</span>
                </label>
                <input type="number" 
                       id="tong_so_ghe" 
                       name="tong_so_ghe" 
                       class="form-control" 
                       value="<?= $flight['TongSoGhe'] ?? '' ?>"
                       min="1"
                       max="500"
                       placeholder="180"
                       required>
            </div>
            
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= $is_edit ? 'Cập nhật' : 'Thêm mới' ?>
            </button>
            <button type="button" class="btn btn-secondary" onclick="location.href='qly_chuyenbay.php'">
                <i class="fas fa-times"></i>
                Hủy bỏ
            </button>
        </div>
    </form>
</div>

<script>
// Tự động tính thời gian đến dựa trên sân bay đi và đến
document.getElementById('thoi_gian_bay').addEventListener('change', calculateArrivalTime);
document.getElementById('san_bay_di').addEventListener('change', calculateArrivalTime);
document.getElementById('san_bay_den').addEventListener('change', calculateArrivalTime);

function calculateArrivalTime() {
    const departure = document.getElementById('thoi_gian_bay').value;
    const from = document.getElementById('san_bay_di').value;
    const to = document.getElementById('san_bay_den').value;
    
    if (!departure || !from || !to) return;
    
    // Thời gian bay ước tính (phút)
    const flightDurations = {
        'Hà Nội (HAN)-TP. Hồ Chí Minh (SGN)': 120,
        'TP. Hồ Chí Minh (SGN)-Hà Nội (HAN)': 120,
        'Hà Nội (HAN)-Đà Nẵng (DAD)': 80,
        'Đà Nẵng (DAD)-Hà Nội (HAN)': 80,
        'TP. Hồ Chí Minh (SGN)-Đà Nẵng (DAD)': 75,
        'Đà Nẵng (DAD)-TP. Hồ Chí Minh (SGN)': 75,
        'Hà Nội (HAN)-Phú Quốc (PQC)': 150,
        'Phú Quốc (PQC)-Hà Nội (HAN)': 150,
        'TP. Hồ Chí Minh (SGN)-Phú Quốc (PQC)': 60,
        'Phú Quốc (PQC)-TP. Hồ Chí Minh (SGN)': 60,
    };
    
    const route = `${from}-${to}`;
    const duration = flightDurations[route] || 90; // Mặc định 90 phút nếu không tìm thấy
    
    const departureDate = new Date(departure);
    const arrivalDate = new Date(departureDate.getTime() + duration * 60000);
    
    // Format datetime-local
    const year = arrivalDate.getFullYear();
    const month = String(arrivalDate.getMonth() + 1).padStart(2, '0');
    const day = String(arrivalDate.getDate()).padStart(2, '0');
    const hours = String(arrivalDate.getHours()).padStart(2, '0');
    const minutes = String(arrivalDate.getMinutes()).padStart(2, '0');
    
    document.getElementById('thoi_gian_den').value = `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Kiểm tra sân bay đi và đến không được trùng nhau
document.getElementById('san_bay_di').addEventListener('change', checkAirports);
document.getElementById('san_bay_den').addEventListener('change', checkAirports);

function checkAirports() {
    const from = document.getElementById('san_bay_di').value;
    const to = document.getElementById('san_bay_den').value;
    
    if (from && to && from === to) {
        alert('Sân bay đi và sân bay đến không được giống nhau!');
        document.getElementById('san_bay_den').value = '';
    }
}
</script>

<?php require_once 'footer_admin.php'; ?>
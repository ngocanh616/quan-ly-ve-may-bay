<?php

/**
 * File: khachhang/datve.php
 * Mô tả: Trang đặt vé - FLOW MỚI: Thông tin -> Chọn ghế -> Xác nhận
 */

require_once '../config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    $_SESSION['message'] = 'Vui lòng đăng nhập để đặt vé!';
    $_SESSION['message_type'] = 'warning';
    header('Location: ../dangnhap.php');
    exit();
}

// Lấy thông tin chuyến bay
$machuyenbay = isset($_GET['machuyenbay']) ? (int)$_GET['machuyenbay'] : 0;

if (!$machuyenbay) {
    $_SESSION['message'] = 'Vui lòng chọn chuyến bay!';
    $_SESSION['message_type'] = 'danger';
    header('Location: chuyenbay.php');
    exit();
}

// Lấy chi tiết chuyến bay
$chuyen = fetch_one("SELECT * FROM ChuyenBay WHERE MaChuyenBay = $machuyenbay");
if (!$chuyen) {
    $_SESSION['message'] = 'Chuyến bay không tồn tại!';
    $_SESSION['message_type'] = 'danger';
    header('Location: chuyenbay.php');
    exit();
}

// Xử lý reset/back
if (isset($_GET['reset']) || isset($_GET['new'])) {
    unset($_SESSION['hanhkhach']);
    unset($_SESSION['ghe_chon']);
    unset($_SESSION['booking_machuyenbay']);
    header('Location: datve.php?machuyenbay=' . $machuyenbay);
    exit();
}

// Lưu lại chuyến bay hiện tại
$_SESSION['booking_machuyenbay'] = $machuyenbay;

$step = 1; // Bước mặc định

// ================================================================
// XỬ LÝ BƯỚC 1: NHẬP THÔNG TIN HÀNH KHÁCH
// ================================================================
if (isset($_POST['step1_thong_tin'])) {
    $soLuongVe = (int)$_POST['soluongve'];

    if ($soLuongVe < 1 || $soLuongVe > $chuyen['SoGheConLai']) {
        $_SESSION['message'] = 'Số lượng vé không hợp lệ! Còn ' . $chuyen['SoGheConLai'] . ' ghế.';
        $_SESSION['message_type'] = 'danger';
    } else {
        $hanhkhach = [];
        $valid = true;

        for ($i = 0; $i < $soLuongVe; $i++) {
            $hoTen = isset($_POST['hoten_' . $i]) ? trim($_POST['hoten_' . $i]) : '';
            $cmnd = isset($_POST['cmnd_' . $i]) ? trim($_POST['cmnd_' . $i]) : '';
            $sdt = isset($_POST['sdt_' . $i]) ? trim($_POST['sdt_' . $i]) : '';
            $email = isset($_POST['email_' . $i]) ? trim($_POST['email_' . $i]) : '';

            if (empty($hoTen) || empty($cmnd)) {
                $_SESSION['message'] = "Vui lòng điền đầy đủ thông tin hành khách " . ($i + 1);
                $_SESSION['message_type'] = 'danger';
                $valid = false;
                break;
            }

            $hanhkhach[] = [
                'hoTen' => escape($hoTen),
                'cmnd' => escape($cmnd),
                'sdt' => escape($sdt),
                'email' => escape($email)
            ];
        }

        if ($valid) {
            $_SESSION['hanhkhach'] = $hanhkhach;
            $step = 2;
        }
    }
}

// ================================================================
// XỬ LÝ BƯỚC 2: CHỌN GHẾ
// ================================================================
if (isset($_POST['step2_chon_ghe'])) {
    $gheChon = isset($_POST['ghe']) ? $_POST['ghe'] : [];
    $soLuongVe = count($_SESSION['hanhkhach']);

    if (empty($gheChon)) {
        $_SESSION['message'] = 'Vui lòng chọn ít nhất 1 ghế!';
        $_SESSION['message_type'] = 'danger';
        $step = 2;
    } elseif (count($gheChon) != $soLuongVe) {
        $_SESSION['message'] = "Vui lòng chọn đúng $soLuongVe ghế!";
        $_SESSION['message_type'] = 'danger';
        $step = 2;
    } else {
        // Gán ghế cho từng hành khách
        foreach ($_SESSION['hanhkhach'] as $index => $hk) {
            $_SESSION['hanhkhach'][$index]['ghe'] = $gheChon[$index];
        }
        $_SESSION['ghe_chon'] = $gheChon;
        $step = 3;
    }
}

// ================================================================
// XỬ LÝ BƯỚC 3: XÁC NHẬN VÀ LƯU VÀO DATABASE
// ================================================================
if (isset($_POST['step3_xac_nhan'])) {
    global $conn;

    $manguoidung = $_SESSION['MaNguoiDung'];
    $soLuongVe = count($_SESSION['hanhkhach']);
    $tongTien = $chuyen['GiaVe'] * $soLuongVe;

    // Lấy thông tin hành khách đầu tiên
    $hanhKhachChinh = $_SESSION['hanhkhach'][0];
    $tenHanhKhach = $hanhKhachChinh['hoTen'];
    $cmnd = $hanhKhachChinh['cmnd'];
    $sdt = $hanhKhachChinh['sdt'];
    $email = $hanhKhachChinh['email'];

    // Insert DatVe
    $sqlDatVe = "INSERT INTO DatVe (MaNguoiDung, MaChuyenBay, TenHanhKhach, CMND, SoDienThoai, Email, SoLuongVe, TongTien, TrangThai) 
                 VALUES ($manguoidung, $machuyenbay, '$tenHanhKhach', '$cmnd', '$sdt', '$email', $soLuongVe, $tongTien, 'choxacnhan')";

    if (query($sqlDatVe)) {
        $maDatVe = get_last_insert_id();

        // Insert ChiTietDatVe
        foreach ($_SESSION['hanhkhach'] as $hk) {
            $sqlChiTiet = "INSERT INTO ChiTietDatVe (MaDatVe, HoTenHanhKhach, CMND, SoDienThoai, Email, SoGhe) 
                          VALUES ($maDatVe, '{$hk['hoTen']}', '{$hk['cmnd']}', '{$hk['sdt']}', '{$hk['email']}', '{$hk['ghe']}')";
            query($sqlChiTiet);
        }

        // Cập nhật số ghế còn lại
        $soGheConLai = $chuyen['SoGheConLai'] - $soLuongVe;
        query("UPDATE ChuyenBay SET SoGheConLai = $soGheConLai WHERE MaChuyenBay = $machuyenbay");

        // Xóa session
        unset($_SESSION['ghe_chon']);
        unset($_SESSION['hanhkhach']);
        unset($_SESSION['booking_machuyenbay']);

        // Redirect
        header('Location: thanhtoan.php?madatve=' . $maDatVe);
        exit();
    } else {
        $_SESSION['message'] = 'Có lỗi xảy ra khi đặt vé!';
        $_SESSION['message_type'] = 'danger';
        $step = 3;
    }
}

// ================================================================
// XÁC ĐỊNH STEP HIỆN TẠI
// ================================================================
if (isset($_SESSION['hanhkhach']) && !empty($_SESSION['hanhkhach'])) {
    if (isset($_SESSION['ghe_chon']) && !empty($_SESSION['ghe_chon'])) {
        $step = 3;
    } else {
        $step = 2;
    }
}

$pageTitle = 'Đặt Vé';
$loadKhachHangCSS = true;
require_once '../header.php';
?>

<div class="booking-page">
    <div class="container py-4">
        <!-- Flight Info -->
        <div class="flight-info-header mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="flight-title">
                        <i class="bi bi-airplane-fill me-2"></i>
                        <?php echo $chuyen['MaChuyenBayText']; ?> - <?php echo $chuyen['HangBay']; ?>
                    </h2>
                    <p class="flight-route">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        <?php echo $chuyen['SanBayDi']; ?> → <?php echo $chuyen['SanBayDen']; ?>
                    </p>
                    <p class="flight-time">
                        <i class="bi bi-calendar-event me-2"></i>
                        <?php echo date('d/m/Y H:i', strtotime($chuyen['ThoiGianBay'])); ?>
                        -
                        <?php echo date('H:i', strtotime($chuyen['ThoiGianDen'])); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="price-box">
                        <div class="price-label">Giá vé</div>
                        <div class="price-value"><?php echo number_format($chuyen['GiaVe'], 0, ',', '.'); ?> đ</div>
                        <div class="available-seats">
                            Còn <strong><?php echo $chuyen['SoGheConLai']; ?></strong> ghế
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Steps Progress -->
        <div class="steps-progress mb-4">
            <div class="step <?php echo ($step >= 1) ? 'active' : ''; ?>">
                <div class="step-number">1</div>
                <div class="step-title">Thông tin</div>
            </div>
            <div class="step-line <?php echo ($step >= 2) ? 'active' : ''; ?>"></div>
            <div class="step <?php echo ($step >= 2) ? 'active' : ''; ?>">
                <div class="step-number">2</div>
                <div class="step-title">Chọn ghế</div>
            </div>
            <div class="step-line <?php echo ($step >= 3) ? 'active' : ''; ?>"></div>
            <div class="step <?php echo ($step >= 3) ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-title">Xác nhận</div>
            </div>
        </div>

        <!-- STEP 1: NHẬP THÔNG TIN -->
        <?php if ($step == 1): ?>
            <div class="booking-card">
                <h3 class="step-title mb-4">
                    <i class="bi bi-person-fill me-2"></i>Bước 1: Nhập thông tin hành khách
                </h3>

                <!-- USER GUIDE -->
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-info-circle me-2"></i>Hướng dẫn:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Nhập số lượng vé cần đặt (tối đa <?php echo $chuyen['SoGheConLai']; ?> ghế)</li>
                        <li>Điền đầy đủ <strong>Họ tên</strong> và <strong>CMND/Passport</strong> cho mỗi hành khách</li>
                        <li>Số điện thoại và Email là tùy chọn (khuyến nghị điền để nhận thông báo)</li>
                        <li>Có thể <strong>Ghi nhớ thông tin</strong> để sử dụng cho lần sau</li>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <form method="POST" action="" id="form-step1">
                    <!-- Số lượng vé -->
                    <div class="form-group mb-4">
                        <label for="soluongve" class="form-label">
                            <i class="bi bi-people me-2"></i>Số lượng vé <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                            class="form-control"
                            id="soluongve"
                            name="soluongve"
                            min="1"
                            max="<?php echo $chuyen['SoGheConLai']; ?>"
                            value="1"
                            onchange="updatePassengerForms()"
                            required>
                        <small class="text-muted">Còn <?php echo $chuyen['SoGheConLai']; ?> ghế</small>
                    </div>

                    <!-- Form hành khách động -->
                    <div id="passengers-container">
                        <!-- Hành khách 1 mặc định -->
                        <div class="passenger-form mb-4" data-index="0">
                            <h5 class="passenger-title">Hành khách 1</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="hoten_0" placeholder="Nguyễn Văn A" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">CMND/Passport <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="cmnd_0" placeholder="001234567890" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="tel" class="form-control" name="sdt_0" placeholder="0901234567">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email_0" placeholder="email@example.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="step1_thong_tin" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-right me-2"></i>Tiếp tục
                        </button>
                    </div>
                </form>
            </div>

            <!-- STEP 2: CHỌN GHẾ -->
        <?php elseif ($step == 2): ?>
            <div class="booking-card">
                <h3 class="step-title mb-4">
                    <i class="bi bi-seat-reclined me-2"></i>Bước 2: Chọn <?php echo count($_SESSION['hanhkhach']); ?> ghế
                </h3>

                <!-- USER GUIDE -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-lightbulb me-2"></i>Mẹo chọn ghế:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Ghế <strong>màu trắng</strong>: Còn trống, có thể chọn</li>
                        <li>Ghế <strong>màu xanh</strong>: Đã chọn (bấm lại để bỏ chọn)</li>
                        <li>Ghế <strong>màu xám</strong>: Đã có người đặt</li>
                        <li>Chọn đúng <strong><?php echo count($_SESSION['hanhkhach']); ?> ghế</strong> rồi bấm "Tiếp tục"</li>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <form method="POST" action="">
                    <div class="seat-map">
                        <div class="seat-legend mb-4">
                            <span class="legend-item">
                                <span class="seat seat-available"></span> Ghế trống
                            </span>
                            <span class="legend-item">
                                <span class="seat seat-selected"></span> Ghế được chọn
                            </span>
                            <span class="legend-item">
                                <span class="seat seat-booked"></span> Ghế đã bán
                            </span>
                        </div>

                        <!-- Sơ đồ ghế -->
                        <div class="seats-container">
                            <?php for ($row = 1; $row <= 15; $row++): ?>
                                <div class="seat-row">
                                    <div class="row-number"><?php echo $row; ?></div>
                                    <?php for ($col = 0; $col < 6; $col++):
                                        $seatCode = $row . chr(65 + $col);
                                        $isBooked = rand(0, 4) > 3; // 20% đã bán
                                    ?>
                                        <label class="seat-wrapper">
                                            <input type="checkbox"
                                                name="ghe[]"
                                                value="<?php echo $seatCode; ?>"
                                                class="seat-checkbox"
                                                <?php echo $isBooked ? 'disabled' : ''; ?>
                                                onchange="updateSeatSelection()">
                                            <span class="seat <?php echo $isBooked ? 'seat-booked' : 'seat-available'; ?>">
                                                <?php echo $seatCode; ?>
                                            </span>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($row % 5 == 0): ?>
                                    <div class="gap"></div>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="selected-seats mt-4">
                        <p class="mb-2">Ghế đã chọn: <span id="selected-count">0</span>/<?php echo count($_SESSION['hanhkhach']); ?></p>
                        <div id="selected-list" class="selected-list"></div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="datve.php?machuyenbay=<?php echo $machuyenbay; ?>&reset=1"
                            class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" name="step2_chon_ghe" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-right me-2"></i>Tiếp tục
                        </button>
                    </div>
                </form>
            </div>

            <!-- STEP 3: XÁC NHẬN -->
        <?php elseif ($step == 3): ?>
            <div class="booking-card">
                <h3 class="step-title mb-4">
                    <i class="bi bi-check-circle me-2"></i>Bước 3: Xác nhận đặt vé
                </h3>

                <!-- USER GUIDE -->
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Lưu ý:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Vui lòng <strong>kiểm tra kỹ</strong> thông tin trước khi xác nhận</li>
                        <li>Sau khi xác nhận, bạn sẽ được chuyển đến trang thanh toán</li>
                        <li>Vé sẽ có trạng thái <strong>"Chờ xác nhận"</strong> cho đến khi thanh toán thành công</li>
                        <li>Bạn có thể hủy vé trong vòng 24h nếu chưa thanh toán</li>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <div class="summary-section mb-4">
                    <h5>Chi tiết chuyến bay</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td>Mã chuyến bay:</td>
                            <td><strong><?php echo $chuyen['MaChuyenBayText']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Hãng bay:</td>
                            <td><?php echo $chuyen['HangBay']; ?></td>
                        </tr>
                        <tr>
                            <td>Tuyến đường:</td>
                            <td><?php echo $chuyen['SanBayDi']; ?> → <?php echo $chuyen['SanBayDen']; ?></td>
                        </tr>
                        <tr>
                            <td>Thời gian:</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($chuyen['ThoiGianBay'])); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="summary-section mb-4">
                    <h5>Hành khách</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hành khách</th>
                                <th>CMND</th>
                                <th>Email</th>
                                <th>Ghế</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['hanhkhach'] as $hk): ?>
                                <tr>
                                    <td><?php echo $hk['hoTen']; ?></td>
                                    <td><?php echo $hk['cmnd']; ?></td>
                                    <td><?php echo $hk['email']; ?></td>
                                    <td><strong><?php echo $hk['ghe']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-section mb-4">
                    <div class="price-summary">
                        <div class="summary-row">
                            <span>Giá vé:</span>
                            <span><?php echo number_format($chuyen['GiaVe'], 0, ',', '.'); ?> đ × <?php echo count($_SESSION['hanhkhach']); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng cộng:</span>
                            <span><?php echo number_format($chuyen['GiaVe'] * count($_SESSION['hanhkhach']), 0, ',', '.'); ?> đ</span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="">
                    <div class="text-center mt-4">
                        <a href="datve.php?machuyenbay=<?php echo $machuyenbay; ?>&reset=1"
                            class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" name="step3_xac_nhan" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Xác nhận đặt vé
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // ================================================================
    // VALIDATION
    // ================================================================

    // Validate số điện thoại Việt Nam
    function validatePhone(phone) {
        // Số điện thoại VN: 10 số, bắt đầu bằng 0
        const phoneRegex = /^0[0-9]{9}$/;
        return phoneRegex.test(phone);
    }

    // Validate email
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Validate CMND/Passport
    function validateCMND(cmnd) {
        // CMND: 9 hoặc 12 số, Passport: 6-12 ký tự chữ số
        const cmndRegex = /^[0-9]{9}$|^[0-9]{12}$|^[A-Z0-9]{6,12}$/;
        return cmndRegex.test(cmnd);
    }

    // Thêm validation real-time cho các input
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('passengers-container');

        // Validate khi người dùng nhập
        container.addEventListener('input', function(e) {
            const input = e.target;

            if (input.type === 'tel') {
                // Validate số điện thoại
                const phone = input.value.trim();
                if (phone && !validatePhone(phone)) {
                    input.setCustomValidity('Số điện thoại không hợp lệ (VD: 0901234567)');
                    input.classList.add('is-invalid');
                } else {
                    input.setCustomValidity('');
                    input.classList.remove('is-invalid');
                    if (phone) input.classList.add('is-valid');
                }
            }

            if (input.type === 'email') {
                // Validate email
                const email = input.value.trim();
                if (email && !validateEmail(email)) {
                    input.setCustomValidity('Email không hợp lệ');
                    input.classList.add('is-invalid');
                } else {
                    input.setCustomValidity('');
                    input.classList.remove('is-invalid');
                    if (email) input.classList.add('is-valid');
                }
            }

            if (input.name && input.name.startsWith('cmnd_')) {
                // Validate CMND
                const cmnd = input.value.trim();
                if (cmnd && !validateCMND(cmnd)) {
                    input.setCustomValidity('CMND/Passport không hợp lệ (9 hoặc 12 số)');
                    input.classList.add('is-invalid');
                } else {
                    input.setCustomValidity('');
                    input.classList.remove('is-invalid');
                    if (cmnd) input.classList.add('is-valid');
                }
            }
        });
    });

    // ================================================================
    // GHI NHỚ THÔNG TIN (LocalStorage)
    // ================================================================

    // Lưu thông tin vào localStorage
    function savePassengerInfo(index) {
        const hoTen = document.querySelector(`input[name="hoten_${index}"]`).value;
        const cmnd = document.querySelector(`input[name="cmnd_${index}"]`).value;
        const sdt = document.querySelector(`input[name="sdt_${index}"]`).value;
        const email = document.querySelector(`input[name="email_${index}"]`).value;

        const data = {
            hoTen,
            cmnd,
            sdt,
            email
        };
        localStorage.setItem(`passenger_${index}`, JSON.stringify(data));
    }

    // Load thông tin từ localStorage
    function loadPassengerInfo(index) {
        const saved = localStorage.getItem(`passenger_${index}`);
        if (saved) {
            const data = JSON.parse(saved);
            document.querySelector(`input[name="hoten_${index}"]`).value = data.hoTen || '';
            document.querySelector(`input[name="cmnd_${index}"]`).value = data.cmnd || '';
            document.querySelector(`input[name="sdt_${index}"]`).value = data.sdt || '';
            document.querySelector(`input[name="email_${index}"]`).value = data.email || '';
            return true;
        }
        return false;
    }

    // Thêm nút "Ghi nhớ" và "Tải lại"
    function addMemoryButtons() {
        const forms = document.querySelectorAll('.passenger-form');
        forms.forEach((form, index) => {
            if (!form.querySelector('.memory-buttons')) {
                const buttonsHTML = `
                <div class="memory-buttons mt-3">
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="savePassengerInfo(${index})">
                        <i class="bi bi-save me-1"></i>Ghi nhớ thông tin
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadPassengerInfo(${index})">
                        <i class="bi bi-arrow-clockwise me-1"></i>Tải lại
                    </button>
                </div>
            `;
                form.querySelector('.row').insertAdjacentHTML('afterend', buttonsHTML);
            }
        });
    }

    // Auto-load thông tin khi trang load
    window.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const forms = document.querySelectorAll('.passenger-form');
            forms.forEach((form, index) => {
                const loaded = loadPassengerInfo(index);
                if (loaded) {
                    // Hiện toast thông báo
                    showToast('Đã tải thông tin hành khách ' + (index + 1), 'info');
                }
            });
            addMemoryButtons();
        }, 500);
    });

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
        <i class="bi bi-check-circle-fill me-2"></i>
        <span>${message}</span>
    `;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ================================================================
    // CẬP NHẬT FORM HÀNH KHÁCH ĐỘNG
    // ================================================================
    function updatePassengerForms() {
        const soLuong = parseInt(document.getElementById('soluongve').value) || 1;
        const container = document.getElementById('passengers-container');
        const currentForms = container.querySelectorAll('.passenger-form').length;

        if (soLuong > currentForms) {
            // Thêm form
            for (let i = currentForms; i < soLuong; i++) {
                const formHTML = `
                <div class="passenger-form mb-4" data-index="${i}">
                    <h5 class="passenger-title">
                        <i class="bi bi-person-circle me-2"></i>Hành khách ${i + 1}
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="hoten_${i}" 
                                       placeholder="Nguyễn Văn A" required>
                                <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">CMND/Passport <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cmnd_${i}" 
                                       placeholder="001234567890" required>
                                <div class="invalid-feedback">CMND phải có 9 hoặc 12 số</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" name="sdt_${i}" 
                                       placeholder="0901234567">
                                <div class="invalid-feedback">Số điện thoại phải có 10 số, bắt đầu bằng 0</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email_${i}" 
                                       placeholder="email@example.com">
                                <div class="invalid-feedback">Email không hợp lệ</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                container.insertAdjacentHTML('beforeend', formHTML);
            }
            addMemoryButtons();
        } else if (soLuong < currentForms) {
            // Xóa form thừa
            const forms = container.querySelectorAll('.passenger-form');
            for (let i = currentForms - 1; i >= soLuong; i--) {
                forms[i].remove();
            }
        }
    }

    // ================================================================
    // CẬP NHẬT GHẾ ĐÃ CHỌN
    // ================================================================
    function updateSeatSelection() {
        const checkboxes = document.querySelectorAll('.seat-checkbox:checked');
        const maxSeats = <?php echo isset($_SESSION['hanhkhach']) ? count($_SESSION['hanhkhach']) : 1; ?>;

        // Giới hạn số ghế
        if (checkboxes.length > maxSeats) {
            // Bỏ chọn ghế mới nhất
            checkboxes[checkboxes.length - 1].checked = false;
            showToast(`Chỉ được chọn tối đa ${maxSeats} ghế!`, 'warning');
            return;
        }

        document.getElementById('selected-count').textContent = checkboxes.length;

        const selectedList = document.getElementById('selected-list');
        const seats = Array.from(checkboxes).map(cb => cb.value).join(', ');
        selectedList.innerHTML = seats ? '<strong>' + seats + '</strong>' : '<em class="text-muted">Chưa chọn ghế</em>';
    }
</script>

<style>
    /* Validation states */
    .is-valid {
        border-color: #28a745 !important;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .invalid-feedback {
        display: none;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .is-invalid~.invalid-feedback {
        display: block;
    }

    /* Memory buttons */
    .memory-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Toast notification */
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        transform: translateX(400px);
        transition: all 0.3s ease;
    }

    .toast-notification.show {
        opacity: 1;
        transform: translateX(0);
    }

    .toast-success {
        border-left: 4px solid #28a745;
        color: #28a745;
    }

    .toast-info {
        border-left: 4px solid #17a2b8;
        color: #17a2b8;
    }

    .toast-warning {
        border-left: 4px solid #ffc107;
        color: #856404;
    }
</style>


<?php require_once '../footer.php'; ?>
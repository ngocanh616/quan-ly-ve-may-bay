# Hệ Thống Quản Lý Bán Vé Máy Bay ✈️
Đây là một ứng dụng web Quản lý bán vé máy bay được xây dựng bằng ngôn ngữ PHP và cơ sở dữ liệu MySQL. Hệ thống cung cấp đầy đủ các tính năng cho cả hai đối tượng người dùng: Ban quản trị (Admin) và Khách hàng (Customer).

# 🚀 Tính năng nổi bật
## 👤 Dành cho Khách hàng (Customer)
- Tài khoản: Đăng ký, Đăng nhập, Đăng xuất, Quên mật khẩu.
- Hồ sơ cá nhân: Cập nhật thông tin cá nhân (khachhang/thongtin.php).
### Tìm kiếm & Đặt vé:
- Tìm kiếm chuyến bay theo điểm đi, điểm đến và thời gian (khachhang/timkiem.php).
- Xem danh sách chuyến bay (khachhang/chuyenbay.php).
- Đặt vé máy bay trực tuyến (khachhang/datve.php).
### Thanh toán & Lịch sử:
- Thực hiện thanh toán vé (khachhang/thanhtoan.php).
- Xem lịch sử các chuyến bay đã đặt (khachhang/lichsu.php).
- Hủy đặt vé nếu cần thiết (khachhang/huy_datve.php).

## 👨‍💻 Dành cho Ban quản trị (Admin)
- Bảng điều khiển (Dashboard): Xem tổng quan hệ thống (admin/index.php).
- Quản lý Chuyến bay: Thêm mới, chỉnh sửa, xóa và theo dõi các chuyến bay (admin/qly_chuyenbay.php, admin/chuyenbay_form.php).
- Quản lý Đặt vé: Duyệt, hủy và theo dõi tình trạng vé khách hàng đã đặt (admin/qly_datve.php).
- Quản lý Người dùng: Xem và quản lý thông tin các tài khoản khách hàng trên hệ thống (admin/qly_nguoidung.php).
- Thống kê: Thống kê doanh thu, số lượng vé bán ra (admin/thongke.php).
- Cài đặt: Thay đổi các cấu hình cơ bản của website (admin/caidat.php).
  
# 🛠️ Công nghệ sử dụng
- Backend: PHP thuần.
- Frontend: HTML5, CSS3 (assets/css/), Vanilla JavaScript (assets/js/).
- Cơ sở dữ liệu: MySQL (database/qlvemaybay.sql).
- 
# 📁 Cấu trúc thư mục

```
quan-ly-ve-may-bay/
│
├── admin/                  # Giao diện và chức năng dành cho Admin
├── assets/                 # Chứa file tĩnh: CSS (admin.css, style.css...), JS (main.js)
├── database/               # Chứa file SQL để khởi tạo database (qlvemaybay.sql)
├── khachhang/              # Chứa các chức năng dành cho khách hàng đã đăng nhập
│
├── config.php              # File cấu hình kết nối Cơ sở dữ liệu
├── index.php               # Trang chủ của hệ thống
├── dangnhap.php            # Trang đăng nhập
├── dangky.php              # Trang đăng ký
└── ...
```

# ⚙️ Hướng dẫn cài đặt
- Để chạy dự án này trên máy cá nhân (Localhost), bạn cần cài đặt các phần mềm tạo máy chủ ảo như XAMPP, WAMP, hoặc MAMP.
* Các bước thực hiện:
- Tải mã nguồn: Clone repository này hoặc tải file ZIP và giải nén vào thư mục htdocs (nếu dùng XAMPP) hoặc www (nếu dùng WAMP).
- Khởi động Server: Mở XAMPP Control Panel và bật Apache và MySQL.
- Tạo Cơ sở dữ liệu:
  + Truy cập vào http://localhost/phpmyadmin/.
  + Tạo một Database mới (ví dụ: qlvemaybay).
  + Chọn tab Import (Nhập) và tải lên file database/qlvemaybay.sql từ thư mục mã nguồn để tạo các bảng và dữ liệu mẫu.
- Cấu hình kết nối Database:
  + Mở file config.php ở thư mục gốc.
  + Kiểm tra và thay đổi thông tin kết nối (Tên database, username, password) sao cho khớp với cấu hình MySQL trên máy của bạn:

```
// Ví dụ:
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qlvemaybay";
```

* Chạy ứng dụng:
- Giao diện người dùng: Truy cập http://localhost/quan-ly-ve-may-bay/ (hoặc đường dẫn tương ứng với tên thư mục bạn đã lưu).
- Giao diện Admin: Truy cập http://localhost/quan-ly-ve-may-bay/admin/

🤝 Đóng góp
Nếu bạn muốn đóng góp cho dự án, vui lòng tạo một Pull Request hoặc mở một Issue để thảo luận về những thay đổi bạn muốn thực hiện.

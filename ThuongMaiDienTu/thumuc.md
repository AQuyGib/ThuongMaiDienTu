CẤU TRÚC THƯ MỤC DỰ ÁN DIENMAY PRO (LARAVEL)Dự án có quy mô Mini-ERP kết hợp E-commerce và có 5 thành viên cùng phát triển. Để tránh tình trạng code lộn xộn (spaghetti code) và hạn chế tối đa xung đột (Merge Conflict) trên Git, dự án bắt buộc phải tuân thủ cấu trúc thư mục mở rộng dưới đây thay vì dùng mặc định của Laravel.1. Cây thư mục Backend (app/)Mở rộng thư mục app để tách biệt logic, giúp code gọn gàng và phân chia rõ ràng trách nhiệm của từng thành viên.app/
├── Enums/                  <-- (TẠO MỚI) Chứa các file liệt kê hằng số, trạng thái (OrderStatus, RoleType...)
│   ├── OrderStatus.php
│   └── RoleType.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          <-- (TẠO MỚI) Controller cho trang quản trị (Hiền, Nguyên, Hòa làm)
│   │   ├── Frontend/       <-- (TẠO MỚI) Controller cho web khách hàng (Quý làm chính)
│   │   └── Api/            <-- (TẠO MỚI) Controller nhận Webhook từ VNPAY, Giao Hàng Nhanh...
│   ├── Middleware/         <-- (CÓ SẴN) Chứa Middleware check quyền (CheckAdmin, CheckRole)
│   └── Requests/           <-- (TẠO MỚI bằng lệnh) Chứa các file FormRequest để Validate form thay vì viết trong Controller
├── Models/                 <-- (CÓ SẴN) Chứa toàn bộ ~20 bảng Database của nhóm
├── Observers/              <-- (TẠO MỚI) Lắng nghe sự kiện ngầm (VD: OrderObserver để tự động cộng điểm khi mua xong)
└── Services/               <-- (TẠO MỚI & QUAN TRỌNG NHẤT) Nơi chứa các logic phức tạp dùng chung
    ├── InventoryService.php
    ├── CartService.php
    └── RewardPointService.php
2. Cây thư mục Giao diện (resources/views/)Tuyệt đối không vứt tất cả file .blade.php ở ngoài cùng. Phải phân chia thư mục views theo từng phân hệ.resources/views/
├── admin/                  <-- (TẠO MỚI) Toàn bộ giao diện trang Quản trị (CMS/ERP)
│   ├── layouts/            <-- Layout gốc (Sidebar, Header, Footer)
│   ├── products/           <-- Các file blade của quản lý sản phẩm
│   ├── orders/             
│   └── ...
├── frontend/               <-- (TẠO MỚI) Giao diện cho Khách hàng (Quý phụ trách chính)
│   ├── layouts/            <-- Layout gốc (Menu khách, Footer khách)
│   ├── home/               
│   ├── products/           <-- Trang chi tiết, danh sách SP
│   └── cart/               
├── pos/                    <-- (TẠO MỚI) Giao diện riêng cho màn hình Thu ngân offline (Vĩnh Em làm)
├── components/             <-- (TẠO MỚI) Các mảnh UI tái sử dụng nhiều lần (VD: Nút bấm, Modal xóa, Card sản phẩm)
└── emails/                 <-- (TẠO MỚI) Giao diện mail gửi cho khách (Mail Quên mật khẩu, Mail Xác nhận đơn)
3. Cây thư mục Tài nguyên Tĩnh (public/)Thư mục public là nơi chứa file CSS, JS, Ảnh. Quy hoạch gọn gàng để dễ quản lý bộ nhớ.public/
├── assets/                 <-- (TẠO MỚI)
│   ├── admin/              <-- Chứa CSS, JS, Template của riêng trang Admin
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── frontend/           <-- Chứa CSS, JS, Template của riêng trang Khách hàng
│       ├── css/
│       ├── js/
│       └── img/
└── uploads/                <-- (TẠO MỚI) Nơi lưu ảnh thực tế do Admin up lên qua giao diện (Ảnh SP, Banner)
    ├── products/
    ├── banners/
    └── avatars/
4. Cây thư mục Định tuyến (routes/)Tránh tình trạng file web.php dài hàng ngàn dòng, gây conflict khi nhiều người cùng thêm Route.routes/
├── web.php                 <-- (CÓ SẴN) Chứa Route cho web khách hàng (Frontend)
├── admin.php               <-- (TẠO MỚI) Chứa toàn bộ Route có tiền tố /admin (CMS/ERP)
└── api.php                 <-- (CÓ SẴN) Dùng cho App POS, Chatbot AI hoặc nhận tín hiệu từ các cổng thanh toán
Lưu ý kỹ thuật: Nếu tạo thêm file routes/admin.php, người khởi tạo dự án phải vào file app/Providers/RouteServiceProvider.php (hoặc bootstrap/app.php đối với Laravel 11) để khai báo file định tuyến mới này.Hướng dẫn thực thi cho Team:Clone dự án gốc từ nhánh main về máy.Ai làm phần nào thì tạo file Controller/View trong đúng thư mục được quy định. Ví dụ, Quý làm giao diện hiển thị sản phẩm thì chỉ tạo file tại app/Http/Controllers/Frontend/ProductController.php và resources/views/frontend/products/index.blade.php.Khi viết xong logic thanh toán / tồn kho, phải đưa vào thư mục Services/ để người khác gọi dùng chung.
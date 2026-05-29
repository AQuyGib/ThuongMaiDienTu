# Project Memory

## Current State & Focus
- **Merge Activities:**

  - Merged `Vinhem/ThanhToan` into `master` successfully (completing the checkout name and address validation features).
  - Merged `master` into branch `Vinhem/QuetMaQR` successfully.
=======
  - Merged `master` into branch `Vinhem/ThanhToan` successfully, implemented checkout page validation, and merged `Vinhem/ThanhToan` back into `master`.
  - Checked and confirmed that branch `master` is already fully merged into branch `AnhQuy/Chatbot` (both local branches point to the same commit `40882a8b`).
  - Merged `Vinhem/CN_tracudonhang` into `master` successfully (fast-forward, 5 commits ahead). Files: `CartController.php`, `ordertracking.blade.php`, `maQR.blade.php`, `routes/web.php`, `ai-memory.md`. Pushed to `origin/master` (`8976d81..6e1a090`).

- **Articles & Lifestyle CRUD (`AnhQuy/Crud-baiviet`):**
  - Added tag-based filtering on the lifestyle listing page.
  - Fixed admin article filters so status buttons and search now work together.
  - Previous fixes remain in place for the admin article form/editor and mobile preview layout.
- **Storefront Upgrades (`master`):**
  - Compare product module upgrade is now aligned with the user-provided roadmap.
  - Advanced product filtering for frontend product listing with partial AJAX rendering.
  - Service-based backend filtering via `ProductFilterService`.

## Files Changed
- **Checkout / Payment Validation:**
  - `ThuongMaiDienTu/resources/views/frontend/cart/pay.blade.php`
  - `ThuongMaiDienTu/resources/views/frontend/cart/maQR.blade.php`
  - `ThuongMaiDienTu/resources/views/partials/chatbot.blade.php`
- **Articles:**
  - `ThuongMaiDienTu/app/Http/Controllers/ArticleFrontendController.php`
  - `ThuongMaiDienTu/app/Http/Controllers/Admin/ArticleController.php`
  - `ThuongMaiDienTu/resources/views/articles/index.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/articles/index.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/articles/form.blade.php`
- **Storefront (Compare & Filter):**
  - `ThuongMaiDienTu/app/Http/Controllers/CompareController.php`
  - `ThuongMaiDienTu/app/Http/Controllers/Frontend/ProductController.php`
  - `ThuongMaiDienTu/app/Http/Controllers/ProductFilterController.php`
  - `ThuongMaiDienTu/app/Services/ProductFilterService.php`
  - `ThuongMaiDienTu/public/assets/frontend/js/compare.js`
  - `ThuongMaiDienTu/public/assets/frontend/js/product-filter.js`
  - `ThuongMaiDienTu/resources/views/frontend/compare/index.blade.php`
  - `ThuongMaiDienTu/resources/views/frontend/products/partials/product_grid.blade.php`
  - `ThuongMaiDienTu/resources/views/frontend/products/show.blade.php`
  - `ThuongMaiDienTu/resources/views/layouts/app.blade.php`
  - `ThuongMaiDienTu/resources/views/partials/compare-bar.blade.php`
  - `ThuongMaiDienTu/routes/web.php`
- **Repair Tickets & Customer Profile:**
  - `ThuongMaiDienTu/app/Http/Controllers/Admin/RepairTicketInvoiceController.php`
  - `ThuongMaiDienTu/app/Http/Controllers/Admin/ServiceInvoiceController.php`
  - `ThuongMaiDienTu/app/Http/Controllers/ProfileController.php`
  - `ThuongMaiDienTu/app/Models/User.php`
  - `ThuongMaiDienTu/routes/admin.php`
  - `ThuongMaiDienTu/routes/web.php`
  - `ThuongMaiDienTu/resources/views/admin/repair-tickets/index.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/repair-tickets/create.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/repair-tickets/edit.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/service-invoices/create.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/service-invoices/edit.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/service-invoices/index.blade.php`
  - `ThuongMaiDienTu/resources/views/frontend/profile.blade.php`

## Important Logic & Behavior Changes
- **Repair Tickets CRUD & Invoicing Link:**
  - Expanded `RepairTicketInvoiceController` to support full ticket lifecycle: creation (`createTicket`, `storeTicket`), updating (`editTicket`, `updateTicket`), and deletion (`destroyTicket`).
  - Created database migration to make `user_id` nullable on `repair_tickets` table and added `customer_address`, `customer_email`, and `customer_source` fields.
  - Removed "Tài khoản khách hàng" (`user_id`) selection dropdown from repair ticket create and edit views, making customer profile creation fully client-contact-driven with required Name, Phone, and optional Address, Email, and Source.
  - Updated validation rules in `RepairTicketInvoiceController` to make user account linking optional, customer name and phone number mandatory, and allow storing the new guest details.
  - Added "Tạo phiếu sửa chữa" button and inline "Sửa"/"Xóa" actions in `admin/repair-tickets/index.blade.php`.
  - Added a search API `/api/customers/search-by-phone` and implemented an AJAX autocomplete script in `create.blade.php` and `edit.blade.php` which automatically fetches and populates customer details (Name, Address, Email, Source) when typing an existing phone number.
  - Displayed the "IMEI / Serial" column in the repair tickets list page (`index.blade.php`).
  - Integrated `datetime-local` input and form select boxes populating customer/technician records.
  - Fixed form action routing in `admin/service-invoices/create.blade.php` to target `admin.repair-tickets.invoice.store` when `$repairTicket` is present (so linking is processed by `RepairTicketInvoiceController@store`).
  - Fixed the hidden input field to submit the correct PK field name `$repairTicket->ticket_id` instead of the non-existent `$repairTicket->id`.
  - Added `edit()`, `update()`, and `destroy()` methods to `ServiceInvoiceController.php` to allow managing and editing service invoices (including publishing drafts), automatically clearing any associated `invoice_no` and `invoiced_at` references in `repair_tickets` table on deletion.
  - Redesigned create and edit forms for service invoices (`create.blade.php`, `edit.blade.php`) to use themed container cards grouping customer, cost, and status information.
  - Dynamically altered title, description, back button, and submit button on `service-invoices/create.blade.php` to read "Xuất hóa đơn" instead of "Lưu hóa đơn / Tạo hóa đơn" when exporting from a repair ticket (`$repairTicket` context), and updated redirect success message to "Đã xuất hóa đơn dịch vụ thành công.".
  - Added compact Edit, Delete, and PDF download buttons to the service invoices actions in `admin/service-invoices/index.blade.php`.
  - Restricted invoice export: nút "Xuất HD" chỉ hiện khi phiếu sửa chữa có status='Done'. Các trạng thái khác hiện badge "Chờ hoàn thành". Backend `create()` cũng chặn nếu status != Done.
  - Thêm trường VAT (%) vào form tạo/sửa hóa đơn dịch vụ (`create.blade.php`, `edit.blade.php`) với tính tổng preview real-time bằng JS. Hiển thị dòng VAT trong `show.blade.php` và `print.blade.php`. Cập nhật `ServiceInvoiceController` và `RepairTicketInvoiceController` để validate `vat_rate` và tính `tax_amount = subtotal * vatRate / 100`.
  - Added migration to add `imei_serial` column to `service_invoices` table. Updated model, controllers, and views (`create`, `edit`, `show`, `print`) to include `imei_serial`.
  - Display pre-generated `invoice_no` on the `service-invoices/create.blade.php` and `edit.blade.php` forms so users can see the invoice code before saving.
  - Updated validation in `RepairTicketInvoiceController`: `imei_serial` remains unique, but `customer_phone` and `customer_email` are no longer enforced to be unique (enabling customers to have multiple tickets). `schedule_date` is enforced to be `after_or_equal:today`.
  - Added a new repair status `Under_Repair` (Đang sửa chữa) to the status list validation, admin creation/editing forms, list badges, customer profile view, and tracking visual stepper.
  - Standardized repair ticket code prefixes to `#RT-` globally (synchronized between customer profile table/modal and admin dashboard).
  - Dynamically updated the date labels in the customer profile: displays "Ngày hẹn mang tới" (Hẹn mang máy) if the ticket status is `Received`, and automatically shifts to "Ngày hẹn trả máy" (Hẹn trả máy) once the ticket moves to `Under_Repair`, `Waiting_Parts`, or `Done` to reflect the updated schedule date updated by the Admin.
  - Fixed a critical bug in `RepairTicketInvoiceController` where updating or storing a repair ticket from the admin panel would reset `user_id` to `null` (since the selection dropdown was removed from admin views). The controller now automatically queries and links the user account by `customer_phone`, fallbacks/preserves existing `user_id` if not matched.
  - Removed the "Tạo hóa đơn mới" button from the service invoices list page and added an "invoice_no" (Mã hóa đơn) search input control to filter results by code.
- **Online Repair Bookings (Customer Portal):**
  - Fully integrated "Lịch sử & Đặt lịch sửa chữa" tab (`repair-tab`) into the customer profile sidebar and tab menu.
  - Reverted profile page layout width, header, and footer back to their exact original visual dimensions (280px sidebar, 20px gap, standard container width) for visual coherence.
  - Removed "Dịch vụ / Hóa đơn", "Kỹ thuật viên", and "Ngày hẹn" from the outer repair list table, retaining them exclusively inside the detail tracking popup.
  - Standardized ticket IDs with `#RT-` prefix globally.
  - Labeled technician in tracking modal as "Kỹ thuật viên phụ trách" and ensured every ticket always has an assigned technician (required validation in admin store/update routes, automatically assigns default technician on customer self-registration).
  - Implemented the multi-section grid layout for the online repair registration form (`customer_name`, `customer_phone`, `customer_email`, `customer_address`, `imei_serial`, `schedule_date`, `issue_desc`).
  - Added visual, state-driven vertical progress Stepper tracking modal populating steps (`Received` -> `Checking` -> `Under_Repair` / `Waiting_Parts` -> `Done`), displaying estimated cost, assigned technicians, real service fees, and invoices dynamically.
  - Implemented automatic redirection / modal re-opening when Laravel validation errors occur during repair ticket submission.
- **Checkout Form Real-Time Validation & Character Counter:**
  - Implemented real-time checking for Họ và tên (Full Name): letters and spaces only, limited to 30 characters (showing "Họ và tên chỉ nhập chữ không nhập số và ký tự đặc biệt" or "Họ và tên tối đa 30 ký tự").
  - Implemented real-time checking for Số điện thoại (Phone): numbers only (showing "Bạn chỉ nhập số" on letters) and standard formatting constraint.
  - Implemented 40-character limit constraint and special character prevention for Địa chỉ giao hàng (Shipping Address) with active real-time character counter (showing "Địa chỉ giao hàng không được chứa ký tự đặc biệt" or "Địa chỉ giao hàng tối đa 40 ký tự").
  - Implemented 250-character limit constraint for Ghi chú (Note) with active real-time character counter.
- **QR Code Payment Page Improvements:**
  - Removed the red expiration timer element and added null-checks to prevent JavaScript crashes when countdown/timer elements are missing.
  - Centered the QR code container utilizing flexbox.
  - Added a "Về trang chủ" (Back to homepage) action button below the main control buttons.
  - Implemented an interactive multi-step "Waiting for admin approval" state when clicking "Xác nhận đã thanh toán", which simulates checking transactions, sending approval requests, and waiting for admin confirmations before displaying the success state.
  - Implemented a custom cancel transaction confirmation modal ("Bạn có muốn hủy đơn hàng không?") with "Có" (cancels order and submits cancel form to backend) and "Không" (closes modal) options.
- **Pending Payment Notification on Storefront:**
  - Stored the pending order ID in local storage when visiting the QR code payment page.
  - Cleared the pending order ID from local storage once payment is successfully confirmed.
  - Implemented a floating cart icon and notification ("Bạn đang có đơn chờ thanh toán") positioned directly above the chatbot FAB on all pages if there is a pending payment, redirecting users back to the respective QR page when clicked.

## TODOs & Follow-up Work
- **Articles:**
  - If desired, refine tag taxonomy so lifestyle tags map to a dedicated database column instead of inferred article attributes.
  - If desired, further reduce duplication by moving article preview markup into a shared partial component.
- **Storefront:**
  - Verify the new detail-page compare button visually against the existing product action buttons.
  - Consider extracting compare button styles into reusable Blade components to reduce duplication.
  - Verify and apply the new migration against the actual database engine.

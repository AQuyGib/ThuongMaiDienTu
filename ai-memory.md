# Project Memory

## Current State & Focus
- **Articles & Lifestyle CRUD (`AnhQuy/Crud-baiviet`):**
  - Added tag-based filtering on the lifestyle listing page.
  - Fixed admin article filters so status buttons and search now work together.
  - Previous fixes remain in place for the admin article form/editor and mobile preview layout.
- **Storefront Upgrades (`master`):**
  - Compare product module upgrade is now aligned with the user-provided roadmap.
  - Advanced product filtering for frontend product listing with partial AJAX rendering.
  - Service-based backend filtering via `ProductFilterService`.

## Files Changed
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
- **Repair Tickets CRUD & Invoicing Link:**
  - `ThuongMaiDienTu/app/Http/Controllers/Admin/RepairTicketInvoiceController.php`
  - `ThuongMaiDienTu/app/Http/Controllers/Admin/ServiceInvoiceController.php`
  - `ThuongMaiDienTu/routes/admin.php`
  - `ThuongMaiDienTu/resources/views/admin/repair-tickets/index.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/repair-tickets/create.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/repair-tickets/edit.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/service-invoices/create.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/service-invoices/edit.blade.php`
  - `ThuongMaiDienTu/resources/views/admin/service-invoices/index.blade.php`

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
  - Added strict validation to `RepairTicketInvoiceController` for creating/updating repair tickets: `imei_serial`, `customer_email`, and `customer_phone` are now strictly unique. `schedule_date` is enforced to be `after_or_equal:today`.
  - Removed the "Tạo hóa đơn mới" button from the service invoices list page and added an "invoice_no" (Mã hóa đơn) search input control to filter results by code.
- **Articles:**
  - `ArticleFrontendController@index()` now accepts `tag` filtering and keeps pagination query strings.
  - Added `applyTagFilter()` for lifestyle tag behavior.
  - Admin article index now applies `status` and `q` filters in a grouped query so both filters work together.
  - Lifestyle page now shows a tag filter row for `standard`, `lifestyle`, and `dienmay-pro`.
- **Storefront (Compare & Filter):**
  - Compare items are stored in `localStorage` under `compare_products` and merged with server data for authenticated users.
  - Frontend filter JS now hydrates from URL, supports quick filters, and syncs active tags with AJAX updates.
  - Product listing cards now show compare loading, animation, and `Đã so sánh` state after AJAX rerenders.
  - `product-filter.js` dispatches `product-grid:updated` after AJAX replacement so compare UI can rebind.
  - `/compare` renders a responsive compare page with diff highlighting and mobile-friendly cards.
  - `/compare/sync` persists the compare list to `WishlistRecentlyViewed` when a user is logged in.
  - `filter_rules` schema is being standardized around `group_key`, `rule_key`, `label`, `conditions`, `sort_order`, `is_active`.

## TODOs & Follow-up Work
- **Articles:**
  - If desired, refine tag taxonomy so lifestyle tags map to a dedicated database column instead of inferred article attributes.
  - If desired, further reduce duplication by moving article preview markup into a shared partial component.
- **Storefront:**
  - Verify the new detail-page compare button visually against the existing product action buttons.
  - Consider extracting compare button styles into reusable Blade components to reduce duplication.
  - Verify and apply the new migration against the actual database engine.

# Project Memory

## Current focus
- Compare product module upgrade is now aligned with the user-provided roadmap.
- Advanced product filtering for frontend product listing with partial AJAX rendering.
- Service-based backend filtering via `ProductFilterService`.

## Recently changed files
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

## Important behavior changes
- Compare items are stored in `localStorage` under `compare_products` and merged with server data for authenticated users.
- Frontend filter JS now hydrates from URL, supports quick filters, and syncs active tags with AJAX updates.
- Product listing cards now show compare loading, animation, and `Đã so sánh` state after AJAX rerenders.
- `product-filter.js` dispatches `product-grid:updated` after AJAX replacement so compare UI can rebind.
- `/compare` renders a responsive compare page with diff highlighting and mobile-friendly cards.
- `/compare/sync` persists the compare list to `WishlistRecentlyViewed` when a user is logged in.
- `filter_rules` schema is being standardized around `group_key`, `rule_key`, `label`, `conditions`, `sort_order`, `is_active`.

## Follow-up work
- Verify the new detail-page compare button visually against the existing product action buttons.
- Consider extracting compare button styles into reusable Blade components to reduce duplication.
- Verify and apply the new migration against the actual database engine.

# Project Memory

## Current focus
- Compare product module upgrade is now aligned with the user-provided roadmap.
- The flow now covers listing cards, detail page, fixed compare bar, compare page, and optional server sync for authenticated users.

## Recently changed files
- `ThuongMaiDienTu/app/Http/Controllers/CompareController.php`
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
- Product listing cards now show compare loading, animation, and `Đã so sánh` state after AJAX rerenders.
- Product detail page now has a dedicated `+ So sánh` button that uses the same compare state as listing cards.
- `product-filter.js` dispatches `product-grid:updated` after AJAX replacement so compare UI can rebind.
- `/compare` renders a responsive compare page with diff highlighting and mobile-friendly cards.
- `/compare/sync` persists the compare list to `WishlistRecentlyViewed` when a user is logged in.

## Follow-up work
- Verify the new detail-page compare button visually against the existing product action buttons.
- Consider extracting compare button styles into reusable Blade components to reduce duplication.
- Review whether `WishlistRecentlyViewed` is the best long-term store for compare sync or if a dedicated table should be introduced later.

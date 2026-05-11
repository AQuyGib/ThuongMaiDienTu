# Project Memory

## Current focus
- Advanced product filtering for frontend product listing.
- Service-based backend filtering via `ProductFilterService` and partial AJAX rendering.

## Recently changed files
- `ThuongMaiDienTu/app/Http/Controllers/Frontend/ProductController.php`
- `ThuongMaiDienTu/app/Http/Controllers/ProductFilterController.php`
- `ThuongMaiDienTu/app/Services/ProductFilterService.php`
- `ThuongMaiDienTu/public/assets/frontend/js/product-filter.js`
- `ThuongMaiDienTu/resources/views/frontend/products/show.blade.php`
- `ThuongMaiDienTu/resources/views/frontend/products/index.blade.php`
- `ThuongMaiDienTu/database/migrations/2026_05_11_000004_standardize_filter_schema_and_optimize_json_filters.php`

## Important behavior changes
- Frontend filter JS now hydrates from URL, supports quick filters, and syncs active tags with AJAX updates.
- `needs` rules are driven by `filter_rules` records; service now falls back to legacy `group` / `key` columns if the migration is absent.
- Product detail page now consumes backend variant data for price display instead of hardcoded client-side price differences.
- Product listing queries eager-load `category`, `variants`, and `productSpecifications` to reduce N+1 issues.
- Product listing count label now uses “Hiển thị” for UI consistency.

## Schema / DB notes
- `filter_rules` schema is being standardized around `group_key`, `rule_key`, `label`, `conditions`, `sort_order`, `is_active`.
- Product filtering optimization migration adds generated/indexed columns for hot pricing/spec lookup paths, but needs validation against the live DB engine.
- `categories.filter_config` remains the main dynamic filter config entry point; a v2 column was introduced for forward migration.

## Follow-up work
- Verify and apply the new migration against the actual database engine; current runtime still uses legacy `filter_rules.group` / `filter_rules.key` if the migration is absent.
- Optionally add seed data or an admin editor contract for `filter_config` / `filter_rules`.
- Consider moving variant price selection to backend API if the UI needs more than simple display pricing.

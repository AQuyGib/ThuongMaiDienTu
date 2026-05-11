# Project Memory

## Current state
- Added tag-based filtering on the lifestyle listing page.
- Fixed admin article filters so status buttons and search now work together.
- Previous fixes remain in place for the admin article form/editor and mobile preview layout.

## Files changed
- `ThuongMaiDienTu/app/Http/Controllers/ArticleFrontendController.php`
- `ThuongMaiDienTu/app/Http/Controllers/Admin/ArticleController.php`
- `ThuongMaiDienTu/resources/views/articles/index.blade.php`
- `ThuongMaiDienTu/resources/views/admin/articles/index.blade.php`
- `ThuongMaiDienTu/resources/views/admin/articles/form.blade.php`

## Important logic changes
- `ArticleFrontendController@index()` now accepts `tag` filtering and keeps pagination query strings.
- Added `applyTagFilter()` for lifestyle tag behavior.
- Admin article index now applies `status` and `q` filters in a grouped query so both filters work together.
- Lifestyle page now shows a tag filter row for `standard`, `lifestyle`, and `dienmay-pro`.

## TODOs
- If desired, refine tag taxonomy so lifestyle tags map to a dedicated database column instead of inferred article attributes.
- If desired, further reduce duplication by moving article preview markup into a shared partial component.

# Step 56: Sub-Category Menu Separation

## Goal
Provide a dedicated, separate Sub-Category navigation menu across the storefront header navbar, mobile navigation drawer, shop page filtering, Filament admin panel, and Product Create/Edit forms.

---

## Changes Implemented

### 1. View Composer & Provider
- **`app/Http/View/Composers/CategoryMenuComposer.php`**: Retrieves active top-level categories with their active child subcategories, as well as all active sub-categories separately.
- **`app/Providers/AppServiceProvider.php`**: Registers `CategoryMenuComposer` for `layouts.partials.navbar`.

### 2. Frontend Layout & Navigation
- **`resources/views/layouts/partials/navbar.blade.php`**:
  - Integrated the **Sub-Categories Mega Menu** directly into the primary header menu (`HOME | SHOP | CATEGORIES | GOLD | SILVER | VENDORS`). Hovering `CATEGORIES` reveals the 3-column glassmorphic sub-categories popover card.
  - Replaced the secondary desktop header navigation strip entirely with a dedicated **Popular Sub-Categories** pill bar (`POPULAR SUB-CATEGORIES: [Necklaces] [Earrings] [Solitaire Rings] ...`).
  - Added left (`<`) and right (`>`) arrow scroll buttons with smooth JS scrolling for uncluttered, scrollbar-free horizontal navigation.

### 3. Shop Page Layout & Filtering
- **`app/Http/Controllers/Frontend/ShopController.php`**: Fetches `$subCategories` and passes them to the shop index view.
- **`resources/views/shop/index.blade.php`**:
  - Added a top sub-category quick pill bar (`Sub-Categories: All Sub-Categories | ...`) with left (`<`) and right (`>`) arrow scroll buttons above product results.
  - Separated sidebar category filtering into distinct "Primary Category" and "Sub Category" filter blocks.

### 4. Admin Panel Separate Sub-Category Resource
- **`app/Filament/Resources/Categories/SubCategoryResource.php`**: Created dedicated Filament resource under Catalog (`navigationSort = 21`) scoped to `whereNotNull('parent_id')`. Uses `Filament\Actions\*` (`Action`, `EditAction`, `BulkAction`, `BulkActionGroup`).
- **`app/Filament/Resources/Categories/Pages/ListSubCategories.php`**, **`CreateSubCategory.php`**, **`EditSubCategory.php`**: Page classes for SubCategoryResource.
- **`app/Filament/Resources/Categories/CategoryResource.php`**: Relabeled to "Main Categories" and scoped to `whereNull('parent_id')` for clear separation in Filament Admin.

### 5. Separate Category & Sub-Category Selection on Product Forms
- **`app/Filament/Resources/Products/Schemas/ProductForm.php`** (Admin Panel): Separated single category select into a dynamic, dependent 2-step selection:
  - **`parent_category_id`** (Main Category): Selects top-level category (`parent_id IS NULL`).
  - **`category_id`** (Sub Category): Reactively filtered to sub-categories belonging to the selected Main Category. Automatically hydrates when editing existing products.
- **`app/Filament/Vendor/Resources/ProductResource.php`** (Vendor Panel): Applied the same reactive 2-step Main Category & Sub Category selection for vendor product creation and editing.

---

## Fixes Applied
- **Fixed Action Imports in `SubCategoryResource.php`**: Corrected namespace from `Filament\Tables\Actions` to `Filament\Actions` to resolve class not found runtime exception in Filament 5.
- **Added Left & Right Arrow Scroll Controls**: Replaced horizontal scrollbars with sleek left (`<`) and right (`>`) chevron buttons with smooth JS scrolling and auto-disabling state on both navbar and shop page.

---

## Verification
- Syntax checked with `php -l app/Filament/Resources/Products/Schemas/ProductForm.php` and `php -l app/Filament/Vendor/Resources/ProductResource.php`.
- Recompiled CSS & JS assets via `npm run build`.

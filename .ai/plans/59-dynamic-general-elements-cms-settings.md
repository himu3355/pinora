# Step 59: Dynamic General Elements, Site Settings & CMS Architecture

**Goal:** Transform all general and static website elements into dynamic, database-backed entities manageable via Filament Admin, including logos, branding, hero sliders, CMS pages (About Us, Policies), FAQs, contact inquiries, and trust badges, complete with a dedicated live-server database seeder.
**Depends On:** Step 42 (Layouts), Step 43 (Homepage)
**Next Step:** N/A (Production deployment & live database synchronization)

---

## Files Created/Modified

- `database/migrations/2026_10_18_000001_create_settings_table.php` [New]
- `database/migrations/2026_10_18_000002_create_home_sliders_table.php` [New]
- `database/migrations/2026_10_18_000003_create_pages_table.php` [New]
- `database/migrations/2026_10_18_000004_create_faqs_table.php` [New]
- `database/migrations/2026_10_18_000005_create_contact_inquiries_table.php` [New]
- `database/migrations/2026_10_18_000006_create_home_value_props_table.php` [New]
- `app/Models/Setting.php` [New]
- `app/Models/HomeSlider.php` [New]
- `app/Models/Page.php` [New]
- `app/Models/Faq.php` [New]
- `app/Models/ContactInquiry.php` [New]
- `app/Models/HomeValueProp.php` [New]
- `app/Helpers/SettingsHelper.php` [New]
- `app/Providers/AppServiceProvider.php` [Modified]
- `app/Providers/Filament/AdminPanelProvider.php` [Modified]
- `app/Filament/Pages/ManageSettings.php` [New]
- `resources/views/filament/pages/manage-settings.blade.php` [New]
- `app/Filament/Resources/HomeSliderResource.php` [New]
- `app/Filament/Resources/HomeSliderResource/Pages/*` [New]
- `app/Filament/Resources/PageResource.php` [New]
- `app/Filament/Resources/PageResource/Pages/*` [New]
- `app/Filament/Resources/FaqResource.php` [New]
- `app/Filament/Resources/FaqResource/Pages/*` [New]
- `app/Filament/Resources/ContactInquiryResource.php` [New]
- `app/Filament/Resources/ContactInquiryResource/Pages/*` [New]
- `app/Filament/Resources/HomeValuePropResource.php` [New]
- `app/Filament/Resources/HomeValuePropResource/Pages/*` [New]
- `app/Http/Controllers/Frontend/PageController.php` [New]
- `resources/views/pages/show.blade.php` [New]
- `resources/views/pages/contact.blade.php` [New]
- `resources/views/pages/faq.blade.php` [New]
- `routes/web.php` [Modified]
- `resources/views/layouts/app.blade.php` [Modified]
- `resources/views/layouts/partials/navbar.blade.php` [Modified]
- `resources/views/layouts/partials/footer.blade.php` [Modified]
- `resources/views/home.blade.php` [Modified]
- `app/Http/Controllers/Frontend/HomeController.php` [Modified]
- `database/seeders/GeneralSettingsSeeder.php` [New]
- `database/seeders/DatabaseSeeder.php` [Modified]

---

## 1. Database Architecture

1. **`settings` Table**:
   - `id`, `key` (unique string), `value` (longText), `group` (string), `type` (string).
   - High-performance cached retrieval using `Setting::get($key, $default)` and helper functions `app_setting()`, `app_setting_image()`, `app_setting_bool()`.
2. **`home_sliders` Table**:
   - Banner slides with `title`, `subtitle`, `badge_text`, `button_text`, `button_url`, `desktop_image`, `mobile_image`, `badge_pill_text`, `sort_order`, `is_active`.
3. **`pages` Table**:
   - CMS pages with `title`, `slug` (unique), `subtitle`, `content` (rich text), `banner_image`, `meta_title`, `meta_description`, `is_published`.
4. **`faqs` Table**:
   - Accordion questions and answers categorized by topic (`category`, `question`, `answer`, `sort_order`, `is_active`).
5. **`contact_inquiries` Table**:
   - Messages sent from the Contact Us form (`name`, `email`, `phone`, `subject`, `message`, `is_read`, `status`, `admin_notes`).
6. **`home_value_props` Table**:
   - The 6 bilingual trust cards below the hero banner (`title`, `subtitle`, `icon`, `description`, `sort_order`, `is_active`).

---

## 2. Filament Admin Management

- **Site Settings Page (`/admin/manage-settings`)**:
  - Organized with tabbed navigation: *General & Branding*, *Announcement Bar*, *Contact & Support*, *Social Links*, *SEO & Sharing*, *Footer & Regulatory*.
- **Content Management Resources**:
  - **Home Sliders**: CRUD for carousel banners with instant active toggles and ordering.
  - **CMS Pages**: Rich editor content editing for About Us, Privacy Policy, Terms, etc.
  - **FAQs**: Categorized Q&A editor.
  - **Customer Inquiries**: Admin message review and reply status tracking.
  - **Trust Badges**: Homepage trust cards management.

---

## 3. Frontend Integration

- **Navbar**:
  - Dynamic top announcement strip with optional link and toggle.
  - Dynamic logo image with automatic fallback to Pinora golden floral SVG emblem.
  - Dynamic tagline and brand name.
- **Footer**:
  - Dynamic logo, about text, Hallmark text, copyright year, real routes to all policy pages, and social media icons.
- **Homepage**:
  - Dynamic Hero Slider driven by `HomeSlider::active()->get()` with auto-advancing carousel indicators.
  - Dynamic 6 Value Proposition Cards driven by `HomeValueProp::active()->get()`.
- **Floating WhatsApp Support Button**:
  - Configurable directly from Settings, pre-filling an inquiry message with one click.
- **Dedicated Public Pages**:
  - `/about`: Brand story & Rajkot artisan legacy.
  - `/contact`: Dynamic showroom address, phone, email, WhatsApp, and interactive submission form.
  - `/faq`: Categorized question tabs with interactive Alpine.js accordions.
  - `/page/{slug}`: Dynamic renderer for all policy pages.

---

## 4. Live Server Seeder

To seed or update a fresh live server with all settings, sliders, trust cards, policy pages, and FAQs:
```bash
php artisan db:seed --class=GeneralSettingsSeeder --force
```

---

- **Filament 5 Unified Actions Namespace**: In Filament v5, all table and record actions (`EditAction`, `DeleteAction`, `BulkActionGroup`, `DeleteBulkAction`) reside under `Filament\Actions\*` instead of `Filament\Tables\Actions\*`. Updated all resources to use the correct namespace.
- **FileUpload Public Disk & APP_URL Alignment**:
  - Public website assets (sliders, logos, CMS banners, category images) must be stored on the `public` disk (`->disk('public')`). If saved to `local` (private) storage, Laravel's `storage.local` route intercepts direct URL requests and denies unsigned access with `403 Forbidden`.
  - For Filament's FilePond previewer to load existing images without being stuck on "Loading...", `APP_URL` in `.env` must match the actual host and port being browsed (e.g., `http://localhost:8000`). Mismatched ports (such as port 80 in `.env` vs port 8000 in browser) trigger CORS cross-origin blocks on FilePond's AJAX fetch requests.
  - An NTFS directory junction (`mklink /J public\storage storage\app\public`) provides permanent direct static file serving on Windows environments.
- **Frontend Rich Content & CMS Typography (Tailwind v4 Preflight Override)**:
  - Tailwind CSS applies a Preflight reset which strips all default HTML styles from headings (no margins, base font-weight, same size), lists (`list-style: none`, 0 padding), and paragraphs.
  - Installed the official `@tailwindcss/typography` plugin with `@plugin "@tailwindcss/typography";` in `resources/css/app.css`.
  - Rather than maintaining fragile manual tag-by-tag CSS rules that break whenever new HTML tags are introduced, all rich text formatting is fully delegated to the official `@tailwindcss/typography` plugin using `prose max-w-none`.
- **Alpine.js Reactive Components (FAQ Filters & Hero Sliders)**:
  - Installed `alpinejs` via npm and imported/started it in `resources/js/app.js` (`import Alpine from 'alpinejs'; window.Alpine = Alpine; Alpine.start();`).
  - Added `[x-cloak] { display: none !important; }` in `resources/css/app.css` to prevent flash of unstyled content.
  - On the FAQ page (`resources/views/pages/faq.blade.php`), category filter buttons dynamically switch active question groups via `activeCategory` state and toggle accordion expansion via `openFaq`, with smooth expand/collapse transitions.


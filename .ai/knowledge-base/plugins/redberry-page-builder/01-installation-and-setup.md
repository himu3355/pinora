# Redberry Page Builder Installation & Integration Guide

This document provides a step-by-step guide for installing, configuring, and integrating the **Redberry Page Builder** plugin into a Laravel/Filament application. It includes troubleshooting steps for common pitfalls like silent database halts, custom styles compilation, and temporary file URL resolution.

---

## 1. Installation

### Prerequisites
Before installing the package, ensure your project meets the following requirements:
* PHP 8.2 or higher
* Laravel 11.28 or higher
* Filament 5.x

### Other Versions
If your project uses an older version of Filament:
* For Filament v3, use [v1.0 (1.x branch)](https://github.com/RedberryProducts/filament-page-builder-plugin/tree/1.x)
* For Filament v4, use [v2.0 (2.x branch)](https://github.com/RedberryProducts/filament-page-builder-plugin/tree/2.x)

### Package Installation
Run Composer to install the package:
```bash
composer require redberry/page-builder-plugin
```

### Database Migrations
Publish the migrations and run them. Make sure your database connection is active and correctly configured:
```bash
php artisan vendor:publish --tag="page-builder-plugin-migrations"
php artisan migrate
```
> [!IMPORTANT]
> This creates three essential tables:
> * `pages`
> * `page_builder_blocks`
> * `global_block_configs`

### Optional Publishing
You can optionally publish the configuration, views, and generator stubs using:
```bash
# Publish config
php artisan vendor:publish --tag="page-builder-plugin-config"

# Publish views
php artisan vendor:publish --tag="page-builder-plugin-views"

# Publish stubs (for customizing generated block files)
php artisan vendor:publish --tag="page-builder-plugin-stubs"
```

---

## 2. Configuration

### Register the Plugin in Filament
Add the `GlobalBlocksPlugin` to your Filament Panel Provider (usually located in [AdminPanelProvider.php](file:///c:/wamp64/www/pinora/app/Providers/Filament/AdminPanelProvider.php)):

```php
use Redberry\PageBuilderPlugin\GlobalBlocksPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configurations
        ->plugins([
            GlobalBlocksPlugin::make(),
        ]);
}
```

### Set Up Frontend Theme Styles
To ensure custom block styles (such as Tailwind utility classes) compile and render inside the Filament admin preview and live preview panels:
1. Register the CSS bundle as a Vite theme in `AdminPanelProvider.php`:
   ```php
   $panel->viteTheme('resources/css/app.css')
   ```
2. Build the production assets:
   ```bash
   npm run build
   ```

### Prepare Your Page Model
Apply the `HasPageBuilder` trait and `$fillable` fields to your `Page` model ([Page.php](file:///c:/wamp64/www/pinora/app/Models/Page.php)):
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Redberry\PageBuilderPlugin\Traits\HasPageBuilder;

class Page extends Model
{
    use HasPageBuilder;

    protected $fillable = [
        'title',
        'slug',
    ];
}
```

---

## 3. Developing Custom Page Blocks

Run the generator command to create new blocks:
```bash
php artisan page-builder-plugin:make-block [BlockName] --type=view
```
This command creates a block class in the `app/Filament/{panel}/Blocks` directory and a corresponding Blade view in `resources/views/blocks/`.

In the block class, define the block fields by returning them inside `getBlockSchema()` (just like standard Filament form fields).

### Golden Rules for Block Development (Troubleshooting & Best Practices)

#### Rule A: Normalize View Data
Always add a normalization check at the top of your block's Blade template (`[block-name].blade.php`). During live previews, Filament wraps the form state in a `data` array, but on the frontend page, variables are passed directly:
```php
@php
    $data = isset($block['data']) && is_array($block['data']) ? $block['data'] : $block;
    $title = $data['title'] ?? 'Default Title';
@endphp
```

#### Rule B: FQCN Reference (Avoid `self::`)
Never call `self::getUrlForFile($image)` inside Blade files. It will throw a `BadMethodCallException` because `self` resolves to the evaluating Livewire page component context. Instead, write out the Full FQCN of the block class:
```php
$imageUrl = \App\Filament\Blocks\[BlockName]::getUrlForFile($image);
```

#### Rule C: Handle File Previews & public disk
By default, Filament saves files to the private `local` disk. This prevents unsigned URLs from accessing them, returning `403 Forbidden` in the console. 
To prevent this, configure your `FileUpload` schema to use the **`public`** disk, and override the URL resolver in your block class (`app/Filament/Blocks/[BlockName].php`):

1. **Form Schema:**
   ```php
   FileUpload::make('image')
       ->image()
       ->disk('public') // Store publicly so it maps to public/storage symlink
       ->directory('block-images')
   ```
2. **URL Resolver method:**
   ```php
   public static function getUrlForFile(array | string | null $path = null): ?string
   {
       if (! $path) {
           return null;
       }

       if (is_array($path)) {
           if (count($path) === 0) {
               return null;
           }
           $path = array_values($path)[0];
       }

       // Generate temporary signed URL for Livewire previews (unsaved uploads)
       if ($path instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
           return $path->temporaryUrl();
       }

       if (is_string($path)) {
           if (str_starts_with($path, 'livewire-tmp/') || str_contains($path, 'livewire-tmp')) {
               try {
                   $filename = basename($path);
                   return \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($filename)->temporaryUrl();
               } catch (\Exception $e) {
                   \Illuminate\Support\Facades\Log::error("Failed to generate temporary URL: " . $e->getMessage());
                   return null;
               }
           }

           // Generate standard URL for saved uploads on public disk
           return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
       }

       return null;
   }
   ```

### Block Categories & Grouping
To visually organize many blocks in the selector:
1. Implement the `getCategory()` method on the block class:
   ```php
   public static function getCategory(): string
   {
       return 'About'; // String category
   }
   ```
2. Alternatively, create a structured category class using:
   ```bash
   php artisan page-builder-plugin:make-block-category
   ```
   This creates a class in `app/Filament/{panel}/BlockCategories/` extending `BaseBlockCategory`. You can return this class FQCN from `getCategory()`:
   ```php
   public static function getCategory(): string
   {
       return \App\Filament\Admin\BlockCategories\Buttons::class;
   }
   ```
3. Customizing Category Classes:
   ```php
   namespace App\Filament\Admin\BlockCategories;
   use Redberry\PageBuilderPlugin\Abstracts\BaseBlockCategory;
   use Illuminate\View\ComponentAttributeBag;

   class Buttons extends BaseBlockCategory
   {
       public static function getCategoryName(): string
       {
           return 'Buttons';
       }

       public static function getCategoryIcon(): string
       {
           return 'heroicon-o-hand-raised';
       }

       public static function getCategoryAttributes(): ComponentAttributeBag
       {
           return new ComponentAttributeBag([
               'class' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
           ]);
       }
   }
   ```
4. Set default category in form schema:
   ```php
   PageBuilder::make('website_content')
       ->selectBlockAction(function (SelectBlockAction $action) {
           return $action->selectField(function (RadioButtonImage $field) {
               return $field->defaultCategory(Navigations::class);
           });
       });
   ```

### Formatting Block Data for Previews
To transform or format the field data (e.g. resolving file/image URLs) before sending it to the frontend preview:
```php
public static function formatForSingleView(array $data): array
{
    $data['text'] = url($data['text']);
    $data['image'] = self::getUrlForFile($data['image']);

    return $data;
}
```
*(Note: `formatForListingView()` automatically calls `formatForSingleView()`, so there's no need to duplicate code).*

### Customizing Block Labels
You can customize the block labels shown in the editor:
* **Attribute-based label:**
  ```php
  public static function getBlockTitleAttribute(): string
  {
      return "logo.name"; // Auto-generates label based on this attribute
  }
  ```
* **Full custom label:**
  ```php
  public static function getBlockLabel(array $state, ?int $index = null)
  {
      return data_get($state, $key) . $index;
  }
  ```

### Disabling Block Selection
If you want to temporarily hide or disable a block from being selected without removing it:
```php
public static function getIsSelectionDisabled(): bool
{
    return true;
}
```

---

## 4. Registering Blocks & Forms

Register block classes inside your form schema using the `PageBuilder` component:
```php
use App\Filament\Blocks\Hero;
use App\Filament\Blocks\VermiCompost;
use Redberry\PageBuilderPlugin\Components\Forms\PageBuilder;

PageBuilder::make('pageBuilderBlocks')
    ->blocks([
        Hero::class,
        VermiCompost::class,
    ])
```

### Reorderable Blocks
Enable drag-and-drop reordering:
```php
PageBuilder::make('website_content')
    ->reorderable()
```

### Thumbnail Selector Previews
Instead of a dropdown/text list, display visual thumbnails for blocks:
1. Define `getThumbnail()` in your block class:
   ```php
   public static function getThumbnail(): string|\Illuminate\Contracts\Support\Htmlable|null
   {
       return 'https://placehold.co/600x400/png';
   }
   ```
2. Enable it on the form field:
   ```php
   PageBuilder::make('website_content')
       ->blocks([Description::class])
       ->renderWithThumbnails()
   ```

### Real-Time Live Previews with Iframe
To render and preview pages inside an iframe (especially useful if the frontend resides in a separate repository/port):
1. Configure `renderPreviewWithIframes()` on the field:
   ```php
   PageBuilder::make('website_content')
       ->renderPreviewWithIframes(
           condition: true,
           createUrl: 'http://localhost:5173',
       )
   ```
2. On your frontend application, send a message to notify Filament once the hydration is complete:
   ```javascript
   window.parent.postMessage({
       type: "readyForPreview",
   }, "*");
   ```
3. To receive data sent from Filament, register a window listener:
   ```javascript
   window.addEventListener("message", (event) => console.log(event.data));
   ```

### Parameter Injection
Methods on your block classes (such as `getBlockSchema`, `formatForSingleView`, etc.) support Filament-style parameter injection, allowing you to access the current model record:
```php
public static function getBlockSchema(?Model $record = null): array
{
    return [
        RichEditor::make('text')
            ->default($record?->text)
            ->required()
    ];
}
```

---

## 5. Global Blocks

Global blocks are special blocks managed in a centralized location. You configure them once and reuse them across multiple pages without per-page setup.

### Creating Global Blocks
Run the block generator with the `--global` flag:
```bash
php artisan page-builder-plugin:make-block ContactForm --type=view --global
```
This creates a block class inside `app/Filament/{panel}/Blocks/Globals/` and sets up the `Globals` category.

### Enabling the Global Blocks Resource
Register `GlobalBlocksPlugin` in your panel provider:
```php
use Redberry\PageBuilderPlugin\GlobalBlocksPlugin;

$panel->plugins([
    GlobalBlocksPlugin::make(),
]);
```
This adds the "Global Blocks" resource under the "Content Management" navigation group.

### Customizing Plugin Options
```php
GlobalBlocksPlugin::make()
    ->enableGlobalBlocks(true) // Set to false to disable
    ->resource(\App\Filament\Resources\CustomGlobalBlocksResource::class) // Custom resource class
    ->navigationGroup('Content Management') // Customize group
    ->navigationSort(10) // Navigation order
    ->navigationIcon('heroicon-o-document-text') // Navigation icon
```

### How Global Blocks Work
Global blocks use the `IsGlobalBlock` trait. You define `getBaseBlockSchema()` for the central configuration, and `getBlockSchema()` applies this globally:
```php
namespace App\Filament\Admin\Blocks\Globals;

use Redberry\PageBuilderPlugin\Traits\IsGlobalBlock;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ContactForm extends BaseBlock
{
    use IsGlobalBlock;

    public static function getBaseBlockSchema(?object $record = null): array
    {
        return [
            TextInput::make('title')->required(),
            Textarea::make('description'),
            TextInput::make('email')->email(),
        ];
    }

    public static function getBlockSchema(?object $record = null): array
    {
        $schema = static::getBaseBlockSchema($record);
        return static::applyGlobalConfiguration($schema);
    }
}
```

---

## 6. Previews & Infolists

### Rendering on Infolist
You can render page builder items on Filament Infolists using `PageBuilderEntry` and `PageBuilderPreviewEntry` (iframe preview):
```php
use Redberry\PageBuilderPlugin\Infolists\Components\PageBuilderEntry;
use Redberry\PageBuilderPlugin\Infolists\Components\PageBuilderPreviewEntry;

$infolist->schema([
    PageBuilderEntry::make('website_content')
        ->blocks([LongDescription::class])
        ->columnSpan(1),
    PageBuilderPreviewEntry::make('website_content_preview')
        ->blocks([LongDescription::class])
        ->iframeUrl('http://localhost:5173')
        ->autoResizeIframe()
        ->columnSpan(2),
]);
```

### Rendering Previews on Forms
To show a real-time, side-by-side preview of a `PageBuilder` field on the form edit/create pages, use `PageBuilderPreview`:
```php
use Redberry\PageBuilderPlugin\Components\Forms\PageBuilderPreview;

PageBuilderPreview::make('website_content_preview')
    ->pageBuilderField('website_content') // Target the key of your PageBuilder field
    ->iframeUrl('http://localhost:5173')
    ->autoResizeIframe()
```

---

## 7. Customizing Actions & Button Rendering

The `PageBuilder` component uses actions for block operations:

| Action name | Action Class | Modifier Function |
|-------------|--------------|-------------------|
| Create | `CreatePageBuilderBlockAction` | `createAction` |
| Edit | `EditPageBuilderBlockAction` | `editAction` |
| Delete | `DeletePageBuilderBlockAction` | `deleteAction` |
| Reorder | `ReorderPageBuilderBlockAction` | `reorderAction` |
| Select block | `SelectBlockAction` | `selectBlockAction` |

### Customizing Buttons (Performance Optimization)
Standard Filament actions carry performance overhead (multiple views, checks, query evaluations). In large page layouts (e.g. 50+ blocks), using standard action components can slow down form rendering by 2-3x (e.g., 500ms vs 150ms).
To optimize performance, this package renders **simple buttons** instead of full actions. You can customize these buttons (or provide custom Blade views) using the closure-based modifiers `deleteActionButton`, `editActionButton`, and `reorderActionButton`:

```php
use Redberry\PageBuilderPlugin\Components\Forms\PageBuilder;

PageBuilder::make('website_content')
    ->deleteActionButton(function ($action, $item, $index, $attributes) {
        return view('filament::components.button.index', [
            ...$attributes,
            'id' => 'delete-custom-button',
            'color' => 'warning',
        ]);
    })
```

---

## 8. Cache Refresh
Whenever you add new blocks, edit block categories, or modify configuration options, run the following cache refresh commands:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

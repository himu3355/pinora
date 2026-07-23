# Filament Page Builder Blocks Development Guide

This guide provides a comprehensive manual on how to design, create, configure, and register custom Page Builder blocks within your Laravel/Filament application.

---

## 1. Creating a New Page Builder Block

### Step 1: Run the Artisan Generator
To generate a new block class and its corresponding view template, run:
```bash
php artisan page-builder-plugin:make-block [BlockName] --type=view
```
*(Replace `[BlockName]` with your block's name in CamelCase, e.g. `FeaturedServices`).*

This command generates two files:
1. **Block Class:** `app/Filament/Blocks/[BlockName].php`
2. **Blade Template:** `resources/views/admin/blocks/[block-name].blade.php`

---

## 2. Organizing Blocks by Categories

Categories group related blocks together under tabs in the block selection modal.

### Step 1: Generate a Category Class
To generate a category group:
```bash
php artisan page-builder-plugin:make-block-category [CategoryName] --panel=admin
```
*(e.g., `Products`, `Headers`, or `Footers`).*

This creates the category class at:
* `app/Filament/BlockCategories/[CategoryName].php`

### Step 2: Assign a Block to a Category
In your block class (`app/Filament/Blocks/[BlockName].php`), import the category and return it from the `getCategory` method:
```php
public static function getCategory(): string
{
    return \App\Filament\BlockCategories\[CategoryName]::class;
}
```

---

## 3. Configuring the Block Form Schema

Define input fields in the `getBlockSchema()` method inside your block class. You can use any standard Filament form components:

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;

public static function getBlockSchema(): array
{
    return [
        TextInput::make('badge_text')
            ->default('New Release')
            ->maxLength(100),
            
        TextInput::make('heading')
            ->required()
            ->maxLength(255),
            
        Textarea::make('subheading')
            ->rows(3)
            ->maxLength(500),
            
        FileUpload::make('image')
            ->image()
            ->disk('public') // MUST use 'public' to allow public url access
            ->directory('block-images'),
    ];
}
```

---

## 4. Designing the Blade View Template

Write your block's markup and style layout inside the generated Blade template (`resources/views/admin/blocks/[block-name].blade.php`).

### Best Practice: Data Normalization
Because Filament passes preview data differently than standard page records, normalize the data array at the top of your Blade file:
```php
@php
    // Normalizes input between real-time form preview and frontend page rendering
    $data = isset($block['data']) && is_array($block['data']) ? $block['data'] : $block;

    $badge = $data['badge_text'] ?? 'Default Badge';
    $heading = $data['heading'] ?? 'Default Heading';
    
    // Resolve file uploads
    $image = $data['image'] ?? null;
    $imageUrl = \App\Filament\Blocks\[BlockName]::getUrlForFile($image) ?: asset('images/fallback.png');
@endphp

<!-- HTML Structure -->
<div class="py-12 bg-white text-slate-900">
    @if($badge)
        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ $badge }}</span>
    @endif
    
    <h1 class="text-4xl font-bold mt-4">{{ $heading }}</h1>
    
    @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $heading }}" class="mt-6 w-full rounded-xl">
    @endif
</div>
```

---

## 5. Resolving File Upload Preview Errors (`403 Forbidden`)

Standard `Storage::url()` calls on the default local disk fail to resolve temporary uploads or private storage paths. Use this custom static helper inside your block class to correctly resolve both **Livewire temporary previews** (using signed URLs) and **public disk locations**:

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

    // Resolve Livewire temporary upload object
    if ($path instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
        return $path->temporaryUrl();
    }

    // Resolve temporary upload path strings (used during unsaved real-time previews)
    if (is_string($path)) {
        if (str_starts_with($path, 'livewire-tmp/') || str_contains($path, 'livewire-tmp')) {
            try {
                $filename = basename($path);
                return \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($filename)->temporaryUrl();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to generate temporary URL for block: " . $e->getMessage());
                return null;
            }
        }

        // Resolve saved public files via symlink
        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }

    return null;
}
```

---

## 6. Complete Integration: Setting up a Page Resource

To use the custom page builder blocks on actual pages, you need a `Page` model and a Filament `PageResource`.

### Step 1: Create the Page Model and Migration
Run the following artisan command:
```bash
php artisan make:model Page -m
```

### Step 2: Configure the Pages Migration
Update the generated migration file in `database/migrations/xxxx_xx_xx_create_pages_table.php` to define the database schema:
```php
public function up(): void
{
    Schema::create('pages', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();
        $table->timestamps();
    });
}
```
*Run the migration:*
```bash
php artisan migrate
```

### Step 3: Configure the Page Model
Apply the `HasPageBuilder` trait and fillable fields inside `app/Models/Page.php`:
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

### Step 4: Generate the Filament Page Resource
Create the resource in your Filament admin panel:
```bash
php artisan make:filament-resource Page
```

---

## 7. Registering the Blocks in Your Resource Schema

Open the generated resource file `app/Filament/Resources/PageResource.php` and import the `PageBuilder` component along with your block classes:

```php
namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\Filament\Blocks\Hero;
use App\Filament\Blocks\VermiCompost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Redberry\PageBuilderPlugin\Components\Forms\PageBuilder;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),

                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(Page::class, 'slug', ignoreRecord: true),

                PageBuilder::make('pageBuilderBlocks')
                    ->blocks([
                        Hero::class,
                        VermiCompost::class,
                    ])
                    ->columnSpanFull()
            ]);
    }
    
    // ... rest of resource methods
}
```

---

## 8. Compiling and Refreshing

Whenever you modify Tailwind utility classes in a Blade block view, rebuild your assets:
```bash
npm run build
```

If the admin panel fails to discover new blocks or categories, refresh Laravel configuration caches:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

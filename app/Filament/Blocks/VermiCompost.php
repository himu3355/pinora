<?php

namespace App\Filament\Blocks;

use Redberry\PageBuilderPlugin\Abstracts\BaseBlock;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;

class VermiCompost extends BaseBlock
{
    public static function getCategory(): string
    {
        return \App\Filament\BlockCategories\Products::class;
    }

    public static function getBlockSchema(): array
    {
        return [
            TextInput::make('heading')
                ->required()
                ->maxLength(255),
                
            Textarea::make('description')
                ->rows(3)
                ->maxLength(500),
                
            FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('block-images'),
        ];
    }

    public static function getView(): ?string
    {
        return 'admin.blocks.vermi-compost';
    }

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

        if ($path instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            return $path->temporaryUrl();
        }

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

            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }

        return null;
    }
}

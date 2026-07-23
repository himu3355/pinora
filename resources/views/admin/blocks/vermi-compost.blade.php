@php
    $data = isset($block['data']) && is_array($block['data']) ? $block['data'] : $block;

    $heading = $data['heading'] ?? 'Default Vermicompost';
    $description = $data['description'] ?? null;
    
    $image = $data['image'] ?? null;
    $imageUrl = \App\Filament\Blocks\VermiCompost::getUrlForFile($image) ?: asset('images/fallback.png');
@endphp

<div class="py-12 bg-slate-50 text-slate-900 border-t border-slate-200">
    <h2 class="text-3xl font-bold">{{ $heading }}</h2>

    @if($description)
        <p class="text-slate-600 mt-2">{{ $description }}</p>
    @endif
    
    @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $heading }}" class="mt-6 w-full max-w-lg rounded-xl shadow-md">
    @endif
</div>
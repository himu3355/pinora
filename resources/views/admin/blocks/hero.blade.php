@php
    // Normalizes input between real-time form preview and frontend page rendering
    $data = isset($block['data']) && is_array($block['data']) ? $block['data'] : $block;

    $badge = $data['badge_text'] ?? 'Default Badge';
    $heading = $data['heading'] ?? 'Default Heading';
    $subheading = $data['subheading'] ?? null;
    
    // Resolve file uploads
    $image = $data['image'] ?? null;
    $imageUrl = \App\Filament\Blocks\Hero::getUrlForFile($image) ?: asset('images/fallback.png');
@endphp

<!-- HTML Structure -->
<div class="py-12 bg-white text-slate-900">
    @if($badge)
        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ $badge }}</span>
    @endif
    
    <h1 class="text-4xl font-bold mt-4">{{ $heading }}</h1>

    @if($subheading)
        <p class="text-slate-600 mt-2">{{ $subheading }}</p>
    @endif
    
    @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $heading }}" class="mt-6 w-full rounded-xl">
    @endif
</div>
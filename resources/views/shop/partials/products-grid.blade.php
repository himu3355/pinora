@if($products->isEmpty())
    <div class="text-center py-20 px-4 text-text-muted">
        <div class="text-5xl mb-4">💎</div>
        <h3 class="font-primary text-2xl mb-2 text-text-light">No products found</h3>
        <p>Try adjusting your filters or <a href="{{ route('shop.index') }}" class="text-gold hover:underline">clear all</a>.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($products as $product)
            @include('partials.product-card', ['product' => $product])
        @endforeach
    </div>
@endif

@if($products->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
        @foreach($products as $product)
            @include('partials.product-card', ['product' => $product])
        @endforeach
    </div>
@else
    <div class="py-16 text-center bg-white border border-[#EAE5DC] rounded-2xl p-8 shadow-xs">
        <div class="text-4xl mb-3">💎</div>
        <h3 class="font-primary text-xl font-bold text-[#142E25] mb-1">No products found</h3>
        <p class="text-xs sm:text-sm text-gray-500 mb-6 max-w-sm mx-auto">We couldn't find any jewellery matching your selected filter criteria. Try adjusting your filters or clear them to view all items.</p>
        <button type="button" class="btn btn-green text-xs px-6 py-2.5 rounded-xl font-semibold cursor-pointer shop-clear-all-btn">
            Clear All Filters
        </button>
    </div>
@endif

@if(request()->hasAny(['category','metal_type','purity','vendor_id','search','in_stock']))
<div class="flex flex-wrap gap-2 mb-6 items-center">
    <span class="text-[0.8rem] text-text-muted">Active filters:</span>
    
    @if(request('category') && isset($selectedCategory) && $selectedCategory)
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Category: {{ $selectedCategory->name }}
            <a href="{{ request()->fullUrlWithoutQuery(['category']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="category">&times;</a>
        </span>
    @endif

    @if(request('metal_type'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Metal: {{ ucfirst(request('metal_type')) }}
            <a href="{{ request()->fullUrlWithoutQuery(['metal_type']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="metal_type">&times;</a>
        </span>
    @endif

    @if(request('purity'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Purity: {{ request('purity') }}
            <a href="{{ request()->fullUrlWithoutQuery(['purity']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="purity">&times;</a>
        </span>
    @endif

    @if(request('vendor_id'))
        @php
            $vName = $vendors->firstWhere('id', request('vendor_id'))?->store_name ?? 'Vendor';
        @endphp
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Vendor: {{ $vName }}
            <a href="{{ request()->fullUrlWithoutQuery(['vendor_id']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="vendor_id">&times;</a>
        </span>
    @endif

    @if(request('in_stock'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            In Stock Only
            <a href="{{ request()->fullUrlWithoutQuery(['in_stock']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="in_stock">&times;</a>
        </span>
    @endif

    @if(request('search'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Search: "{{ request('search') }}"
            <a href="{{ request()->fullUrlWithoutQuery(['search']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="search">&times;</a>
        </span>
    @endif

    <a href="{{ route('shop.index') }}" class="text-[0.78rem] text-text-muted hover:text-gold data-filter-clear">Clear all</a>
</div>
@endif

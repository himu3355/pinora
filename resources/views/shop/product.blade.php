@extends('layouts.app')

@section('title', $product->name . ' — Pinora')
@section('meta_description', $product->short_description ?? 'Shop ' . $product->name . ' at Pinora.')

@section('content')
<div class="max-w-7xl mx-auto px-6 pt-10 pb-20">

    {{-- Breadcrumb --}}
    <nav class="text-[0.8rem] text-text-muted mb-8 flex gap-2 items-center flex-wrap">
        <a href="{{ url('/') }}" class="text-text-muted hover:text-gold">Home</a> /
        @if($product->category)
        <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="text-text-muted hover:text-gold">{{ $product->category->name }}</a> /
        @endif
        <span class="text-gold">{{ $product->name }}</span>
    </nav>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-start">

        {{-- ===== GALLERY ===== --}}
        <div>
            <div id="main-image" class="aspect-square bg-dark-card border border-border-gold rounded-lg overflow-hidden mb-4">
                <img id="main-img-el" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </div>
            @if($product->images->count() > 1)
            <div class="flex gap-3 overflow-x-auto">
                @foreach($product->images as $img)
                <button onclick="document.getElementById('main-img-el').src='{{ $img->url }}'" class="flex-shrink-0 w-18 h-18 rounded-md overflow-hidden border-2 {{ $img->is_primary ? 'border-gold' : 'border-border-gold' }} bg-transparent cursor-pointer p-0 transition-all duration-300 hover:border-gold">
                    <img src="{{ $img->url }}" alt="{{ $img->alt_text }}" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ===== PRODUCT INFO ===== --}}
        <div>
            {{-- Vendor --}}
            <a href="{{ route('vendors.show', $product->vendor->store_slug) }}" class="text-[0.8rem] text-gold tracking-wider uppercase inline-block mb-3 hover:text-gold-light">
                {{ $product->vendor->store_name }}
            </a>

            <h1 class="font-primary text-3xl font-normal leading-[1.25] mb-4 text-text-light">{{ $product->name }}</h1>

            {{-- Rating --}}
            @if($product->reviews->isNotEmpty())
            @php $avgRating = $product->reviews->avg('rating'); @endphp
            <div class="flex items-center gap-3 mb-6">
                <div class="text-gold text-lg">
                    @for($i=1; $i<=5; $i++)
                        {{ $i <= round($avgRating) ? '★' : '☆' }}
                    @endfor
                </div>
                <span class="text-sm text-text-muted">{{ number_format($avgRating,1) }} ({{ $product->reviews->count() }} reviews)</span>
            </div>
            @endif

            {{-- Price Breakdown --}}
            <div class="bg-dark-card border border-border-gold rounded-lg p-6 mb-8">
                <h3 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Price Breakdown</h3>
                <table class="w-full text-sm border-collapse">
                    @if(isset($pricing['metal_rate']))
                    <tr class="border-b border-border-gold">
                        <td class="py-2.5 text-text-muted">Metal Rate ({{ $product->purity }})</td>
                        <td class="py-2.5 text-right text-text-light">₹{{ number_format($pricing['metal_rate'],2) }}/g</td>
                    </tr>
                    @endif
                    @if(isset($pricing['weight']))
                    <tr class="border-b border-border-gold">
                        <td class="py-2.5 text-text-muted">Weight</td>
                        <td class="py-2.5 text-right text-text-light">{{ $pricing['weight'] }}g</td>
                    </tr>
                    @endif
                    @if(isset($pricing['metal_cost']))
                    <tr class="border-b border-border-gold">
                        <td class="py-2.5 text-text-muted">Metal Cost</td>
                        <td class="py-2.5 text-right text-text-light">₹{{ number_format($pricing['metal_cost'],2) }}</td>
                    </tr>
                    @endif
                    @if(isset($pricing['making_charges']))
                    <tr class="border-b border-border-gold">
                        <td class="py-2.5 text-text-muted">Making Charges</td>
                        <td class="py-2.5 text-right text-text-light">₹{{ number_format($pricing['making_charges'],2) }}</td>
                    </tr>
                    @endif
                    @if(isset($pricing['gst_amount']))
                    <tr class="border-b border-border-gold">
                        <td class="py-2.5 text-text-muted">GST (3%)</td>
                        <td class="py-2.5 text-right text-text-light">₹{{ number_format($pricing['gst_amount'],2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="pt-4 font-semibold text-text-light text-base">Final Price</td>
                        <td class="pt-4 text-right font-bold text-2xl text-gold">₹{{ number_format($pricing['final_price'],0) }}</td>
                    </tr>
                </table>
            </div>

            {{-- Variants --}}
            @if($product->variants->isNotEmpty())
            <div class="mb-6">
                <h4 class="text-[0.8rem] tracking-wider uppercase text-text-muted mb-3 font-medium">Select Size / Variant</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($product->variants as $variant)
                    <button onclick="selectVariant(this, {{ $variant->id }})"
                        data-variant-id="{{ $variant->id }}"
                        class="px-4 py-2 border border-border-gold rounded-lg bg-transparent text-text-light cursor-pointer font-secondary text-sm transition-all duration-300 focus:outline-none">
                        {{ $variant->name }}
                    </button>
                    @endforeach
                </div>
                <input type="hidden" name="variant_id" id="selected_variant" value="">
            </div>
            @endif

            {{-- Quantity --}}
            <div class="flex items-center gap-4 mb-6">
                <h4 class="text-[0.8rem] tracking-wider uppercase text-text-muted font-medium">Qty:</h4>
                <div class="flex items-center border border-border-gold rounded-lg overflow-hidden">
                    <button type="button" onclick="changeQty(-1)" class="w-9 h-9 bg-transparent border-0 text-text-light cursor-pointer text-xl flex items-center justify-center hover:bg-gold/10 hover:text-gold transition-colors">−</button>
                    <input type="number" id="qty-input" value="1" min="1" max="{{ $product->stock_quantity }}" class="w-12 text-center bg-transparent border-0 text-text-light font-secondary focus:ring-0 outline-none">
                    <button type="button" onclick="changeQty(1)" class="w-9 h-9 bg-transparent border-0 text-text-light cursor-pointer text-xl flex items-center justify-center hover:bg-gold/10 hover:text-gold transition-colors">+</button>
                </div>
                <span class="text-[0.8rem] text-text-muted">{{ $product->stock_quantity }} in stock</span>
            </div>

            {{-- CTA Buttons --}}
            <form action="{{ route('cart.add') }}" method="POST" class="flex gap-4 flex-wrap mb-6" id="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" id="form_variant_id" value="">
                <input type="hidden" name="quantity" id="form_qty" value="1">
                <button type="submit" class="btn btn-gold flex-1 min-w-[200px] justify-center">Add to Cart</button>
            </form>

            @auth
            <button type="button" class="btn btn-outline-gold w-full justify-center" data-wishlist-toggle="{{ $product->id }}">
                {{ auth()->user()->hasWishlisted($product->id) ? '♥ Remove from Wishlist' : '♡ Add to Wishlist' }}
            </button>
            @else
            <a href="{{ route('login') }}" class="btn btn-outline-gold flex justify-center w-full">♡ Add to Wishlist</a>
            @endauth

            {{-- Certification Badges --}}
            @if(!empty($product->certification_badges))
            <div class="mt-6 pt-4 border-t border-border-gold/30">
                <h4 class="text-xs uppercase tracking-wider text-gold font-semibold mb-3">Guaranteed Certifications</h4>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($product->certification_badges as $key => $badge)
                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-dark-card border border-border-gold/40 text-xs text-text-light transition-all hover:border-gold/80">
                        @if(file_exists(public_path($badge['logo'])))
                            <img src="{{ asset($badge['logo']) }}" alt="{{ $badge['label'] }}" class="w-7 h-7 object-contain flex-shrink-0" />
                        @else
                            <div class="w-7 h-7 rounded-full bg-gold/15 flex items-center justify-center text-gold text-xs flex-shrink-0">
                                🏅
                            </div>
                        @endif
                        <span class="font-medium text-[0.825rem] leading-tight">{{ $badge['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @elseif($product->certification_type && $product->certification_type !== 'none')
            <div class="mt-6 inline-flex items-center gap-2 py-2 px-4 bg-gold/8 border border-border-gold rounded-full text-[0.8rem] text-gold">
                🏅 {{ strtoupper($product->certification_type) }} Certified
                @if($product->certification_number)
                · #{{ $product->certification_number }}
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ===== TABS ===== --}}
    <div class="mt-16 border-t border-border-gold pt-12">
        <div class="flex gap-8 mb-8 border-b border-border-gold" id="tabs">
            @foreach(['description'=>'Description','details'=>'Details','reviews'=>'Reviews (' . $product->reviews->count() . ')'] as $tab => $label)
            <button onclick="showTab('{{ $tab }}')" id="tab-{{ $tab }}" class="py-3 bg-transparent border-0 cursor-pointer font-secondary text-sm text-text-muted border-b-2 border-transparent -mb-[1px] transition-all duration-300">{{ $label }}</button>
            @endforeach
        </div>

        <div id="panel-description">
            <div class="text-text-muted leading-relaxed text-[0.95rem] max-w-[720px]">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>

        <div id="panel-details" style="display:none;">
            <table class="text-sm border-collapse min-w-[320px]">
                @foreach([
                    'Metal Type' => ucfirst($product->metal_type ?? '—'),
                    'Purity' => $product->purity ?? '—',
                    'Weight' => $product->weight_grams ? $product->weight_grams . 'g' : '—',
                    'Loss' => $product->loss ? $product->loss . 'g' : '—',
                    'Stone Type' => $product->stone_type ?? '—',
                    'Stone Weight' => $product->stone_weight_carats ? $product->stone_weight_carats . ' ct' : '—',
                    'Stone Quality' => $product->stone_quality ?? '—',
                    'Certification' => !empty($product->certification_badges) ? implode(', ', array_column($product->certification_badges, 'label')) : '—',
                ] as $label => $value)
                <tr class="border-b border-border-gold">
                    <td class="py-3 pr-4 text-text-muted w-[180px]">{{ $label }}</td>
                    <td class="py-3 text-text-light">{{ $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div id="panel-reviews" style="display:none; max-width:720px;">
            @if($product->reviews->isEmpty())
            <p class="text-text-muted mb-8">No reviews yet. Be the first to review this product!</p>
            @else
            <div class="flex flex-col gap-6 mb-10">
                @foreach($product->reviews as $review)
                <div class="bg-dark-card border border-border-gold rounded-lg p-5">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-[0.9rem] text-text-light">{{ $review->user->name }}</span>
                        <span class="text-[0.8rem] text-text-muted">{{ $review->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="text-gold mb-2">
                        @for($i=1;$i<=5;$i++) {{ $i <= $review->rating ? '★' : '☆' }} @endfor
                    </div>
                    @if($review->title)<div class="font-semibold text-text-light mb-1">{{ $review->title }}</div>@endif
                    <p class="text-text-muted text-sm leading-relaxed">{{ $review->body }}</p>
                    @if($review->is_verified_purchase)<div class="mt-2 text-xs text-gold">✓ Verified Purchase</div>@endif
                </div>
                @endforeach
            </div>
            @endif

            @if($canReview)
            <div class="bg-dark-card border border-border-gold rounded-lg p-6">
                <h3 class="font-primary text-xl mb-5 text-text-light">Write a Review</h3>
                <form action="{{ route('product.review.store', $product->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="text-[0.8rem] text-text-muted block mb-2 font-medium">Rating *</label>
                        <div class="flex gap-2 text-2xl text-gold" id="star-rating-container">
                            @for($i=1;$i<=5;$i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" class="hidden star-radio">
                                <span class="star-icon transition-transform hover:scale-110 inline-block" data-value="{{ $i }}">☆</span>
                            </label>
                            @endfor
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="text-[0.8rem] text-text-muted block mb-2 font-medium">Title *</label>
                        <input type="text" name="title" placeholder="Summarise your review" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary outline-none focus:border-gold transition-colors">
                    </div>
                    <div class="mb-6">
                        <label class="text-[0.8rem] text-text-muted block mb-2 font-medium">Review *</label>
                        <textarea name="body" rows="4" placeholder="Share your experience..." class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary outline-none focus:border-gold transition-colors resize-y"></textarea>
                    </div>
                    <button type="submit" class="btn btn-gold">Submit Review</button>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- ===== RELATED PRODUCTS ===== --}}
    @if($relatedProducts->isNotEmpty())
    <div class="mt-20 border-t border-border-gold pt-12">
        <h2 class="font-primary text-3xl font-normal mb-8 text-text-light">You May Also Like</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function showTab(tab) {
    ['description','details','reviews'].forEach(t => {
        document.getElementById('panel-' + t).style.display = t === tab ? 'block' : 'none';
        const btn = document.getElementById('tab-' + t);
        if (t === tab) {
            btn.classList.remove('text-text-muted', 'border-transparent');
            btn.classList.add('text-gold', 'border-gold');
        } else {
            btn.classList.remove('text-gold', 'border-gold');
            btn.classList.add('text-text-muted', 'border-transparent');
        }
    });
}
showTab('description');

// Star Rating Form Interactivity
document.addEventListener('DOMContentLoaded', () => {
    const starContainer = document.getElementById('star-rating-container');
    if (starContainer) {
        const stars = starContainer.querySelectorAll('.star-icon');
        let selectedRating = 0;

        function updateStars(rating) {
            stars.forEach(star => {
                const val = parseInt(star.dataset.value);
                if (val <= rating) {
                    star.textContent = '★';
                } else {
                    star.textContent = '☆';
                }
            });
        }

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const val = parseInt(star.dataset.value);
                selectedRating = val;
                updateStars(selectedRating);
                
                const radio = starContainer.querySelector(`input[value="${val}"]`);
                if (radio) {
                    radio.checked = true;
                }
            });

            star.addEventListener('mouseenter', () => {
                const val = parseInt(star.dataset.value);
                updateStars(val);
            });
        });

        starContainer.addEventListener('mouseleave', () => {
            updateStars(selectedRating);
        });

        // Enforce minimum 1 star selection on form submit
        const reviewForm = starContainer.closest('form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', (e) => {
                if (selectedRating === 0) {
                    e.preventDefault();
                    alert('Please select at least 1 star for your rating.');
                }
            });
        }
    }
});

function changeQty(delta) {
    const input = document.getElementById('qty-input');
    const newVal = Math.max(1, parseInt(input.value) + delta);
    input.value = newVal;
    document.getElementById('form_qty').value = newVal;
}

function selectVariant(btn, variantId) {
    document.querySelectorAll('[data-variant-id]').forEach(b => {
        b.classList.remove('border-gold', 'text-gold');
        b.classList.add('border-border-gold', 'text-text-light');
    });
    btn.classList.add('border-gold', 'text-gold');
    btn.classList.remove('border-border-gold', 'text-text-light');
    document.getElementById('form_variant_id').value = variantId;
    document.getElementById('selected_variant').value = variantId;
}
</script>
@endpush

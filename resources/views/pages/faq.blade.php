@extends('layouts.app')

@section('title', 'Frequently Asked Questions (FAQ) — ' . app_setting('site_name', 'Pinora'))
@section('meta_description', 'Got questions about Pinora 22K gold nose pins, BIS hallmarking, insured shipping, or returns? Find answers to all frequently asked questions here.')

@section('content')
<div class="bg-[#FAF8F5] py-8 sm:py-12 border-b border-[#EAE5DC]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <nav class="text-xs text-[#8E9E98] mb-4 flex items-center gap-1.5">
            <a href="{{ url('/') }}" class="hover:text-[#083B2B]">Home</a>
            <span>/</span>
            <span class="text-[#083B2B] font-semibold">FAQ</span>
        </nav>
        <h1 class="font-primary text-3xl sm:text-4xl md:text-5xl font-semibold text-[#062E23]">
            Frequently Asked Questions
        </h1>
        <p class="text-sm sm:text-base text-[#60706A] mt-2">
            Everything you need to know about our gold purity, hallmarking, delivery, and exchange guarantee.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12" x-data="{ activeCategory: 'all', openFaq: null }">

    {{-- Category Pills --}}
    @if(isset($faqs) && $faqs->isNotEmpty())
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 scrollbar-none">
            <button type="button" 
                    @click="activeCategory = 'all'" 
                    :class="activeCategory === 'all' ? 'bg-[#083B2B] text-white border-[#083B2B]' : 'bg-white text-[#142E25] border-[#EAE5DC] hover:border-[#083B2B]'" 
                    class="px-4 py-2 rounded-full border text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer">
                All Questions
            </button>
            @foreach($faqs->keys() as $category)
                <button type="button" 
                        @click="activeCategory = '{{ $category }}'" 
                        :class="activeCategory === '{{ $category }}' ? 'bg-[#083B2B] text-white border-[#083B2B]' : 'bg-white text-[#142E25] border-[#EAE5DC] hover:border-[#083B2B]'" 
                        class="px-4 py-2 rounded-full border text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer">
                    {{ $category }}
                </button>
            @endforeach
        </div>

        {{-- Accordion List --}}
        <div class="space-y-4">
            @php $faqIndex = 0; @endphp
            @foreach($faqs as $category => $items)
                <div x-show="activeCategory === 'all' || activeCategory === '{{ $category }}'" class="space-y-3">
                    @if($loop->count > 1)
                        <h2 x-show="activeCategory === 'all'" class="text-xs uppercase tracking-widest font-bold text-[#8E9E98] pt-4 mb-1">
                            {{ $category }}
                        </h2>
                    @endif

                    @foreach($items as $faq)
                        @php $faqIndex++; @endphp
                        <div class="bg-white border border-[#EAE5DC] rounded-xl overflow-hidden shadow-xs transition-colors"
                             :class="openFaq === {{ $faqIndex }} ? 'border-[#083B2B]' : ''">
                            <button type="button" 
                                    @click="openFaq = (openFaq === {{ $faqIndex }} ? null : {{ $faqIndex }})" 
                                    class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 cursor-pointer focus:outline-none">
                                <span class="font-primary text-base sm:text-lg font-medium text-[#142E25]">
                                    {{ $faq->question }}
                                </span>
                                <span class="w-6 h-6 rounded-full bg-[#FAF8F5] flex items-center justify-center flex-shrink-0 text-[#083B2B] font-bold text-sm transition-transform duration-300"
                                      :class="openFaq === {{ $faqIndex }} ? 'rotate-180 bg-[#083B2B] text-white' : ''">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                </span>
                            </button>
                            <div x-show="openFaq === {{ $faqIndex }}" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="px-5 pb-5 pt-1 text-sm text-[#4A5D56] leading-relaxed border-t border-[#F2EFE9] prose prose-sm max-w-none">
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-16 text-[#8E9E98]">
            <p>No questions have been published yet. Please check back soon!</p>
        </div>
    @endif

    {{-- Still have questions footer banner --}}
    <div class="mt-12 p-8 bg-[#FAF8F5] border border-[#EAE5DC] rounded-2xl text-center">
        <h3 class="font-primary text-xl font-semibold text-[#062E23] mb-2">Still have questions?</h3>
        <p class="text-xs sm:text-sm text-[#60706A] mb-5">Can't find the answer you're looking for? Please chat with our friendly jewellery consultants.</p>
        <div class="flex items-center justify-center gap-3 flex-wrap">
            <a href="{{ route('contact') }}" class="px-5 py-2.5 bg-[#083B2B] hover:bg-[#0A4A36] text-[#E0C475] hover:text-white rounded-lg text-xs font-semibold tracking-wider uppercase transition-colors">
                Contact Us
            </a>
            @if(app_setting_bool('whatsapp_enabled', true) && app_setting('whatsapp_number'))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', app_setting('whatsapp_number')) }}" target="_blank" rel="noopener" class="px-5 py-2.5 bg-[#25D366] hover:bg-[#1ebd5b] text-white rounded-lg text-xs font-semibold tracking-wider uppercase transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    WhatsApp Chat
                </a>
            @endif
        </div>
    </div>

</div>
@endsection

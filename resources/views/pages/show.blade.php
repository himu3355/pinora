@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->title) . ' — ' . app_setting('site_name', 'Pinora'))
@section('meta_description', $page->meta_description ?: Str::limit(strip_tags($page->content), 160))

@section('content')
<div class="bg-[#FAF8F5] py-8 sm:py-12 border-b border-[#EAE5DC]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        {{-- Breadcrumb --}}
        <nav class="text-xs text-[#8E9E98] mb-4 flex items-center gap-1.5">
            <a href="{{ url('/') }}" class="hover:text-[#083B2B]">Home</a>
            <span>/</span>
            <span class="text-[#083B2B] font-semibold">{{ $page->title }}</span>
        </nav>

        {{-- Page Heading --}}
        <h1 class="font-primary text-3xl sm:text-4xl md:text-5xl font-semibold text-[#062E23] leading-tight">
            {{ $page->title }}
        </h1>
        @if($page->subtitle)
            <p class="text-sm sm:text-base text-[#60706A] mt-2 font-medium">
                {{ $page->subtitle }}
            </p>
        @endif

    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-16">
    @if($page->banner_image_url)
        <div class="mb-10 rounded-2xl overflow-hidden shadow-sm border border-[#EAE5DC]">
            <img src="{{ $page->banner_image_url }}" alt="{{ $page->title }}" class="w-full max-h-[380px] object-cover">
        </div>
    @endif

    <div class="prose max-w-none text-[#142E25] prose-headings:font-primary prose-headings:text-[#062E23] prose-p:leading-relaxed prose-a:text-[#083B2B] prose-a:underline hover:prose-a:text-[#C59B27] prose-li:text-[#2D453E]">
        {!! $page->content !!}
    </div>
</div>
@endsection

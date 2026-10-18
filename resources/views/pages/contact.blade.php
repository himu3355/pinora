@extends('layouts.app')

@section('title', 'Contact Us — ' . app_setting('site_name', 'Pinora'))
@section('meta_description', 'Get in touch with Pinora customer support. Reach out via WhatsApp, phone, email, or visit our showroom in Rajkot, Gujarat.')

@section('content')
<div class="bg-[#FAF8F5] py-8 sm:py-12 border-b border-[#EAE5DC]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <nav class="text-xs text-[#8E9E98] mb-4 flex items-center gap-1.5">
            <a href="{{ url('/') }}" class="hover:text-[#083B2B]">Home</a>
            <span>/</span>
            <span class="text-[#083B2B] font-semibold">Contact Us</span>
        </nav>
        <h1 class="font-primary text-3xl sm:text-4xl md:text-5xl font-semibold text-[#062E23]">
            Get In Touch
        </h1>
        <p class="text-sm sm:text-base text-[#60706A] mt-2">
            Have a question about nose pin sizing, custom orders, or hallmarking? We are always here to help.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

    @if(session('success'))
        <div class="mb-8 p-4 bg-[#083B2B]/10 border border-[#083B2B] text-[#083B2B] rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        {{-- Left: Contact Cards & Info --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- WhatsApp Direct Card --}}
            @if(app_setting_bool('whatsapp_enabled', true) && app_setting('whatsapp_number'))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', app_setting('whatsapp_number')) }}?text={{ urlencode('Hello Pinora team, I would like to inquire about your jewellery.') }}" 
                   target="_blank" 
                   rel="noopener" 
                   class="block p-5 bg-[#25D366]/10 border-2 border-[#25D366]/40 hover:border-[#25D366] rounded-2xl transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#25D366] text-white flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider font-bold text-[#1e8f47] block">Fastest Response</span>
                            <h3 class="text-base font-semibold text-[#142E25]">Chat with us on WhatsApp</h3>
                            <span class="text-xs text-[#60706A]">Instant assistance for designs & sizing</span>
                        </div>
                    </div>
                </a>
            @endif

            {{-- Phone Card --}}
            <div class="p-5 bg-white border border-[#EAE5DC] rounded-2xl flex items-start gap-4 shadow-xs">
                <div class="w-10 h-10 rounded-full bg-[#083B2B]/10 text-[#083B2B] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs uppercase tracking-wider font-bold text-[#8E9E98] mb-0.5">Customer Support Call</h3>
                    <a href="tel:{{ app_setting('support_phone', '+91 98765 43210') }}" class="text-base font-semibold text-[#062E23] hover:text-[#C59B27] transition-colors">
                        {{ app_setting('support_phone', '+91 98765 43210') }}
                    </a>
                    <p class="text-xs text-[#60706A] mt-1">{{ app_setting('working_hours', 'Mon - Sat: 10:00 AM - 8:00 PM') }}</p>
                </div>
            </div>

            {{-- Email Card --}}
            <div class="p-5 bg-white border border-[#EAE5DC] rounded-2xl flex items-start gap-4 shadow-xs">
                <div class="w-10 h-10 rounded-full bg-[#083B2B]/10 text-[#083B2B] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                </div>
                <div>
                    <h3 class="text-xs uppercase tracking-wider font-bold text-[#8E9E98] mb-0.5">Email Support</h3>
                    <a href="mailto:{{ app_setting('support_email', 'support@pinora.com') }}" class="text-base font-semibold text-[#062E23] hover:text-[#C59B27] transition-colors">
                        {{ app_setting('support_email', 'support@pinora.com') }}
                    </a>
                    <p class="text-xs text-[#60706A] mt-1">We typically reply within 2–4 business hours.</p>
                </div>
            </div>

            {{-- Store Address Card --}}
            <div class="p-5 bg-white border border-[#EAE5DC] rounded-2xl flex items-start gap-4 shadow-xs">
                <div class="w-10 h-10 rounded-full bg-[#083B2B]/10 text-[#083B2B] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs uppercase tracking-wider font-bold text-[#8E9E98] mb-0.5">Rajkot Showroom & Office</h3>
                    <p class="text-sm text-[#142E25] font-medium leading-relaxed">
                        {{ app_setting('store_address', 'Palace Road, Soni Bazar, Rajkot, Gujarat 360001, India') }}
                    </p>
                </div>
            </div>

        </div>

        {{-- Right: Interactive Form --}}
        <div class="lg:col-span-7">
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-[#EAE5DC] shadow-xs">
                <h2 class="font-primary text-2xl font-semibold text-[#062E23] mb-2">Send Us a Message</h2>
                <p class="text-xs text-[#60706A] mb-6">Fill in the form below and our artisan consultants will get back to you.</p>

                <form method="POST" action="{{ route('contact.submit') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-[#142E25] uppercase tracking-wider mb-1.5">Your Name *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', auth()->check() ? auth()->user()->name : '') }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D8D1C5] text-sm focus:border-[#083B2B] focus:ring-1 focus:ring-[#083B2B] outline-none">
                            @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-[#142E25] uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" id="email" name="email" value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D8D1C5] text-sm focus:border-[#083B2B] focus:ring-1 focus:ring-[#083B2B] outline-none">
                            @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-[#142E25] uppercase tracking-wider mb-1.5">Phone / WhatsApp</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D8D1C5] text-sm focus:border-[#083B2B] focus:ring-1 focus:ring-[#083B2B] outline-none" placeholder="+91 ...">
                            @error('phone') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="subject" class="block text-xs font-semibold text-[#142E25] uppercase tracking-wider mb-1.5">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D8D1C5] text-sm focus:border-[#083B2B] focus:ring-1 focus:ring-[#083B2B] outline-none" placeholder="Nose pin inquiry, order status...">
                            @error('subject') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-xs font-semibold text-[#142E25] uppercase tracking-wider mb-1.5">Your Message *</label>
                        <textarea id="message" name="message" rows="5" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D8D1C5] text-sm focus:border-[#083B2B] focus:ring-1 focus:ring-[#083B2B] outline-none" placeholder="How can we help you?">{{ old('message') }}</textarea>
                        @error('message') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-[#083B2B] hover:bg-[#0A4A36] text-[#E0C475] hover:text-white font-medium text-xs tracking-wider uppercase rounded-lg transition-colors shadow-sm">
                        Submit Message
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection

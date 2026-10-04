<footer class="bg-[#062E23] text-[#FAF8F5] pt-12 pb-16 md:pb-8 border-t border-[#0A3D2F]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        {{-- Main Footer Columns --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-10 border-b border-[#0A3D2F]">

            {{-- Brand Info --}}
            <div class="md:col-span-4 flex flex-col items-center md:items-start text-center md:text-left">
                <a href="{{ url('/') }}" class="flex flex-col items-center md:items-start group mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-7 h-7 text-[#C59B27]" viewBox="0 0 100 100" fill="currentColor">
                            <circle cx="50" cy="50" r="10" fill="#C59B27"/>
                            <circle cx="50" cy="20" r="12" fill="#E0C475"/>
                            <circle cx="50" cy="80" r="12" fill="#E0C475"/>
                            <circle cx="20" cy="50" r="12" fill="#E0C475"/>
                            <circle cx="80" cy="50" r="12" fill="#E0C475"/>
                        </svg>
                        <span class="font-primary text-2xl font-bold tracking-[0.2em] uppercase text-[#FAF8F5]">Pinora</span>
                    </div>
                    <span class="text-[0.6rem] tracking-[0.28em] uppercase text-[#E0C475] font-medium mt-0.5">
                        — TINY JEWEL. BIG STYLE —
                    </span>
                </a>
                <p class="text-xs text-[#A8B6B0] max-w-sm leading-relaxed mb-4">
                    Rajkot's premier specialist for handcrafted 22K gold nose pins. Hallmarked, guaranteed, and delivered with love across India.
                </p>
                <div class="flex items-center gap-2 text-xs text-[#E0C475]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#C59B27]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L1 21h22L12 2zm0 4.5l7 12.5H5l7-12.5z"/>
                    </svg>
                    <span>100% Certified 22K BIS Hallmarked Gold</span>
                </div>
            </div>

            {{-- Navigation Links 1 --}}
            <div class="md:col-span-3 text-center md:text-left">
                <h4 class="text-xs font-bold uppercase tracking-widest text-[#E0C475] mb-4">Explore Pinora</h4>
                <ul class="space-y-2.5 text-xs text-[#A8B6B0]">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-[#E0C475] transition-colors">Nose Pins Catalog</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'daily-wear']) }}" class="hover:text-[#E0C475] transition-colors">Daily Wear Collection</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'floral']) }}" class="hover:text-[#E0C475] transition-colors">Floral Motifs</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'premium']) }}" class="hover:text-[#E0C475] transition-colors">Heritage Bridal Pins</a></li>
                    <li><a href="{{ route('shop.index', ['metal_type' => 'gold']) }}" class="hover:text-[#E0C475] transition-colors">22K Gold Jewellery</a></li>
                </ul>
            </div>

            {{-- Navigation Links 2 (Customer Service & Policies) --}}
            <div class="md:col-span-3 text-center md:text-left">
                <h4 class="text-xs font-bold uppercase tracking-widest text-[#E0C475] mb-4">Policies & Support</h4>
                <ul class="space-y-2.5 text-xs text-[#A8B6B0]">
                    <li><a href="#" class="hover:text-[#E0C475] transition-colors">About PINORA</a></li>
                    <li><a href="#" class="hover:text-[#E0C475] transition-colors">Contact Us</a></li>
                    <li><a href="#" class="hover:text-[#E0C475] transition-colors">Delivery Policy</a></li>
                    <li><a href="#" class="hover:text-[#E0C475] transition-colors">Return / Exchange Policy</a></li>
                    <li><a href="#" class="hover:text-[#E0C475] transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-[#E0C475] transition-colors">Terms & Conditions</a></li>
                    <li><a href="#" class="hover:text-[#E0C475] transition-colors">FAQ</a></li>
                </ul>
            </div>

            {{-- Social Media & Follow Us (Image 3) --}}
            <div class="md:col-span-2 text-center md:text-left">
                <h4 class="text-xs font-bold uppercase tracking-widest text-[#E0C475] mb-4">Follow Us</h4>
                <div class="flex items-center justify-center md:justify-start gap-3 text-white/80">
                    {{-- Instagram --}}
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#C59B27] hover:text-[#062E23] flex items-center justify-center transition-all" aria-label="Instagram">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    {{-- Facebook --}}
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#C59B27] hover:text-[#062E23] flex items-center justify-center transition-all" aria-label="Facebook">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.595 0 9 1.582 9 4.615V8z"/></svg>
                    </a>
                    {{-- YouTube --}}
                    <a href="https://youtube.com" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#C59B27] hover:text-[#062E23] flex items-center justify-center transition-all" aria-label="YouTube">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                    {{-- Pinterest --}}
                    <a href="https://pinterest.com" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-white/10 hover:bg-[#C59B27] hover:text-[#062E23] flex items-center justify-center transition-all" aria-label="Pinterest">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345-.09.375-.291 1.199-.334 1.357-.057.24-.19.291-.439.175-1.644-.766-2.67-3.169-2.67-5.1 0-4.155 3.018-7.971 8.709-7.971 4.624 0 8.217 3.296 8.217 7.7 0 4.596-2.898 8.293-6.92 8.293-1.351 0-2.621-.702-3.056-1.533l-.832 3.172c-.301 1.157-1.114 2.607-1.659 3.486 1.258.388 2.593.599 3.978.599 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                    </a>
                </div>
            </div>

        </div>

        {{-- Footer Bottom Bar --}}
        <div class="pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-[#8E9E98] text-center md:text-left">
            <p>&copy; {{ date('Y') }} PINORA. All Rights Reserved. Rajkot, Gujarat, India.</p>
            <p class="text-[0.7rem] text-[#60706A]">Designed with pure elegance for mobile, tablet & desktop.</p>
        </div>

    </div>
</footer>

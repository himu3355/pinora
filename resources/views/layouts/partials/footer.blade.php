<footer class="bg-dark-card border-t border-border-gold py-16 pb-8">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 mb-12">

            {{-- Brand --}}
            <div class="lg:col-span-2">
                <div class="font-primary text-3xl text-gold mb-4">Pinora</div>
                <p class="text-text-muted text-sm leading-relaxed mb-6">Curating the finest jewellery from trusted artisan vendors across India. Every piece is certified, every seller is verified.</p>
                <div class="flex gap-2 mt-4 max-w-md">
                    <input type="email" placeholder="Your email address" class="flex-1 bg-white/5 border border-border-gold rounded-lg px-4 py-2.5 text-text-light font-secondary text-sm outline-none placeholder:text-text-muted focus:border-gold transition-colors">
                    <button class="btn btn-gold px-4 py-2.5 text-sm">Subscribe</button>
                </div>
            </div>

            {{-- Shop --}}
            <div class="lg:col-span-1">
                <div class="text-xs tracking-[0.15em] uppercase text-gold mb-5 font-semibold">Shop</div>
                <ul class="list-none flex flex-col gap-2.5 p-0 m-0">
                    <li><a href="{{ route('shop.index', ['metal_type'=>'gold']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Gold Jewellery</a></li>
                    <li><a href="{{ route('shop.index', ['metal_type'=>'silver']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Silver Jewellery</a></li>
                    <li><a href="{{ route('shop.index', ['metal_type'=>'platinum']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Platinum</a></li>
                    <li><a href="{{ route('shop.index', ['certification'=>'bis_hallmark']) }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">BIS Hallmark</a></li>
                    <li><a href="{{ route('vendors.index') }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">All Vendors</a></li>
                </ul>
            </div>

            {{-- Customer Service --}}
            <div class="lg:col-span-1">
                <div class="text-xs tracking-[0.15em] uppercase text-gold mb-5 font-semibold">Customer Service</div>
                <ul class="list-none flex flex-col gap-2.5 p-0 m-0">
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Track Order</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Return Policy</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Size Guide</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">FAQ</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Contact Us</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div class="lg:col-span-1">
                <div class="text-xs tracking-[0.15em] uppercase text-gold mb-5 font-semibold">Company</div>
                <ul class="list-none flex flex-col gap-2.5 p-0 m-0">
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">About Pinora</a></li>
                    <li><a href="{{ route('vendor.apply') }}" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Sell With Us</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Privacy Policy</a></li>
                    <li><a href="#" class="text-text-muted text-sm hover:text-gold transition-colors duration-300">Terms of Service</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-border-gold pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-text-muted text-[0.8rem] m-0">&copy; {{ date('Y') }} Pinora. All rights reserved. GST registered platform.</p>
            <p class="text-text-muted text-[0.8rem] m-0">Secure payments by Razorpay. All transactions are encrypted.</p>
        </div>
    </div>
</footer>

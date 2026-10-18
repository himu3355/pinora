<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\HomeSlider;
use App\Models\HomeValueProp;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class GeneralSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. GLOBAL SETTINGS
        $settings = [
            // General & Branding
            'site_name' => 'Pinora',
            'site_title' => "Pinora — Rajkot's Nose Pin Specialist | 22K Gold Jewellery",
            'navbar_tagline' => 'TINY JEWEL. BIG STYLE.',
            'specialist_title' => "Rajkot's Nose Pin Specialist",
            'header_logo' => null,
            'footer_logo' => null,
            'favicon' => null,

            // Announcement Bar
            'announcement_enabled' => '1',
            'announcement_text' => "Today's 22K Gold Rate: ₹7,850/g | Free Insured Shipping Across India | 100% Certified & BIS Hallmarked",
            'announcement_link' => '/shop',

            // Contact & WhatsApp
            'support_phone' => '+91 98765 43210',
            'support_email' => 'support@pinora.com',
            'whatsapp_number' => '919876543210',
            'whatsapp_enabled' => '1',
            'store_address' => 'Palace Road, Soni Bazar, Rajkot, Gujarat 360001, India',
            'working_hours' => 'Mon - Sat: 10:00 AM - 8:00 PM',

            // Social links
            'instagram_url' => 'https://instagram.com/pinora_jewels',
            'facebook_url' => 'https://facebook.com/pinorajewels',
            'youtube_url' => 'https://youtube.com/@pinorajewels',
            'pinterest_url' => 'https://pinterest.com/pinorajewels',

            // SEO & Social Share
            'default_meta_title' => "Pinora — Rajkot's Nose Pin Specialist | 22K Gold Jewellery",
            'default_meta_description' => 'Discover certified 22K gold nose pins handcrafted by master artisans in Rajkot. Daily wear, floral designs, studded solitaires, and heritage bridal pins.',
            'default_meta_keywords' => 'gold nose pins, 22K gold jewellery, Rajkot jewellery, certified nose studs, bridal nose pin, hallmarked nose ring',
            'og_image' => null,

            // Footer & Regulatory
            'footer_about_text' => "Rajkot's premier specialist for handcrafted 22K gold nose pins. Hallmarked, guaranteed, and delivered with love across India.",
            'bis_hallmark_text' => '100% Certified 22K BIS Hallmarked Gold',
            'copyright_text' => 'PINORA. All Rights Reserved. Rajkot, Gujarat, India.',
        ];

        foreach ($settings as $key => $val) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $val, 'group' => 'general']
            );
        }

        // 2. HERO BANNER SLIDER
        HomeSlider::updateOrCreate(
            ['title' => 'NOSE PIN SPECIALIST'],
            [
                'badge_text' => "RAJKOT'S",
                'subtitle' => 'Elegant 22K Gold Nose Pins for Every Occasion.',
                'button_text' => 'SHOP NOSE PINS',
                'button_url' => '/shop',
                'desktop_image' => 'images/pinora/hero-banner.jpg',
                'badge_pill_text' => '22K BIS Hallmarked',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        // 3. TRUST & VALUE PROPOSITIONS (6 BILINGUAL CARDS)
        $valueProps = [
            [
                'title' => '22K Gold',
                'subtitle' => 'શુદ્ધ સોનાની ખાતરી',
                'icon' => 'gold-bar',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'BIS Hallmarked',
                'subtitle' => 'હોલમાર્ક પ્રમાણિત',
                'icon' => 'hallmark',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Master Craft',
                'subtitle' => 'રાજકોટના કુશળ કારીગરો',
                'icon' => 'craftsman',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Insured Shipping',
                'subtitle' => 'સુરક્ષિત ડિલિવરી સમગ્ર ભારત',
                'icon' => 'shipping',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Easy 7-Day Exchange',
                'subtitle' => 'સરળ ૭-દિવસ એક્સચેન્જ',
                'icon' => 'exchange',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'WhatsApp Support',
                'subtitle' => 'ઝડપી ગ્રાહક સહાય',
                'icon' => 'sparkles',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($valueProps as $vp) {
            HomeValueProp::updateOrCreate(
                ['title' => $vp['title']],
                $vp
            );
        }

        // 4. CMS PAGES
        $pages = [
            [
                'slug' => 'about-us',
                'title' => 'About PINORA',
                'subtitle' => "The Legacy of Rajkot's Master Goldsmiths",
                'content' => '
                    <p class="lead">Welcome to <strong>PINORA</strong> — Rajkot’s dedicated fine gold jewellery and nose pin atelier. We believe a nose pin is not just an ornament; it is an intimate expression of individuality, grace, and heritage.</p>
                    
                    <h3>Our Artisan Heritage in Rajkot</h3>
                    <p>For centuries, the historic bylanes of Rajkot, Gujarat have resonated with the rhythmic tapping of master goldsmiths crafting the finest filigree and gold jewellery in the subcontinent. Pinora was founded to bring this unmatched heritage directly from the artisan’s bench to your doorstep.</p>
                    
                    <p>Every curve, prong, and motif in our collection is shaped with microscopic precision. Our artisans specialize in ultra-fine jewellery where tolerances are measured in fractions of a millimeter, ensuring that each nose pin sits comfortably, securely, and brilliantly on your skin.</p>

                    <h3>The Pinora Promise</h3>
                    <ul>
                        <li><strong>100% BIS Hallmarked:</strong> Every single piece is laser-inscribed with the mandatory 6-digit HUID code issued by the Bureau of Indian Standards.</li>
                        <li><strong>Pure 22K (916) Gold:</strong> We use certified 22-karat gold with verified purity guaranteed across every piece.</li>
                        <li><strong>Direct Artisan Transparency:</strong> Real-time transparent pricing tied directly to daily certified gold rates with upfront making charges.</li>
                        <li><strong>Insured Doorstep Delivery:</strong> Tamper-evident, fully insured delivery straight from Gujarat across every pin code in India.</li>
                    </ul>
                ',
                'meta_title' => 'About Us — Pinora | Handcrafted 22K Gold Jewellery from Rajkot',
                'meta_description' => 'Discover the story behind Pinora, Rajkot’s premier fine jewellery specialist crafting certified 22K gold nose pins with generations of artisan mastery.',
            ],
            [
                'slug' => 'shipping-policy',
                'title' => 'Shipping & Delivery Policy',
                'subtitle' => '100% Safe, Discreeet & Fully Insured Transit Across India',
                'content' => '
                    <p>At Pinora, we understand the precious nature of fine jewellery. That is why every single package dispatched from our Rajkot studio is 100% insured against loss or damage in transit.</p>

                    <h3>Shipping Rates & Timeline</h3>
                    <ul>
                        <li><strong>Free Shipping:</strong> We provide complimentary insured courier delivery across India on all prepaid orders.</li>
                        <li><strong>Dispatch Time:</strong> Ready-to-ship designs are dispatched within 24 to 48 hours of order confirmation. Custom crafted pieces are dispatched within 4–6 business days.</li>
                        <li><strong>Delivery Turnaround:</strong> Typically 3 to 5 business days for metro cities, and 4 to 7 business days for rest of India.</li>
                    </ul>

                    <h3>Secure & Discreet Packaging</h3>
                    <p>All jewellery items are securely packaged in tamper-evident security envelopes inside premium luxury jewellery gift boxes. For your security and privacy, the outer shipping package carries no jewellery branding or mention of precious gold contents.</p>

                    <h3>Delivery Verification</h3>
                    <p>Deliveries are handled by premium logistics partners (BlueDart, Sequel Logistics, Delhivery). An OTP or physical signature verification is required upon handover to ensure the package reaches your hands safely.</p>
                ',
                'meta_title' => 'Shipping Policy — Pinora Insured Doorstep Delivery',
                'meta_description' => 'Learn about Pinora’s free insured shipping across India, tamper-proof luxury packaging, dispatch timelines, and safe courier verification.',
            ],
            [
                'slug' => 'return-refund-policy',
                'title' => 'Return & Exchange Policy',
                'subtitle' => '7-Day Easy Exchange & 100% Lifetime Exchange Guarantee',
                'content' => '
                    <p>We want you to love your Pinora jewellery with complete peace of mind. We offer a transparent, customer-first exchange and lifetime buyback policy.</p>

                    <h3>7-Day Hassle-Free Exchange</h3>
                    <p>If you have sizing concerns or wish to change your design, you may request an exchange within 7 days of package delivery. The product must be unused, in its original pristine condition, with all certificates and security tags intact.</p>

                    <h3>100% Lifetime Exchange</h3>
                    <p>Jewellery is an investment that lasts lifetimes. Pinora offers a <strong>100% Lifetime Exchange Guarantee</strong>. You may exchange your Pinora gold jewellery at any time in the future against prevailing market gold rates based on the certified net weight of the gold.</p>

                    <h3>How to Initiate an Exchange</h3>
                    <ol>
                        <li>Contact our customer care team via WhatsApp at <strong>' . $settings['support_phone'] . '</strong> or email <strong>' . $settings['support_email'] . '</strong>.</li>
                        <li>Provide your Order Number and photos of the piece with its hallmark tag.</li>
                        <li>Our team will arrange an insured return pickup from your doorstep.</li>
                    </ol>
                ',
                'meta_title' => 'Return & Exchange Policy — Pinora 7-Day & Lifetime Exchange',
                'meta_description' => 'Read Pinora’s 7-day easy exchange and 100% lifetime exchange policy for certified 22K gold jewellery.',
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'subtitle' => 'How We Protect and Respect Your Personal Information',
                'content' => '
                    <p>PINORA ("we", "our", or "us") is dedicated to upholding the highest standards of data privacy and customer trust. This policy describes how we collect, store, and protect your information when you visit or make a purchase from our website.</p>

                    <h3>Information We Collect</h3>
                    <p>When you purchase from Pinora, we collect essential order processing details including your name, shipping address, contact phone number, and email address. Payment credentials (card numbers, UPI IDs) are processed through RBI-approved, PCI-DSS Level 1 compliant gateways (Stripe, Razorpay) and are never stored on our servers.</p>

                    <h3>How We Use Your Information</h3>
                    <ul>
                        <li>To process, pack, insure, and deliver your orders.</li>
                        <li>To provide tracking notifications and order status updates via SMS, email, and WhatsApp.</li>
                        <li>To provide customer support and answer inquiries regarding size, fitting, and hallmarking.</li>
                    </ul>

                    <h3>Data Security</h3>
                    <p>Our website utilizes bank-grade 256-bit SSL encryption. We never sell, rent, or trade your personal information with any third-party marketing companies.</p>
                ',
                'meta_title' => 'Privacy Policy — Pinora',
                'meta_description' => 'Pinora’s commitment to customer privacy, secure checkout, and confidential data protection.',
            ],
            [
                'slug' => 'terms-conditions',
                'title' => 'Terms & Conditions',
                'subtitle' => 'General Terms of Service & Purchase',
                'content' => '
                    <p>By browsing, accessing, or placing an order on PINORA, you agree to comply with the terms and conditions outlined below.</p>

                    <h3>1. Dynamic Gold Pricing</h3>
                    <p>All fine gold jewellery prices on Pinora are calculated dynamically based on real-time market metal rates, certified product gold weight, and artisanal making charges. Once an order is paid and confirmed, the price is locked and will not fluctuate with subsequent market changes.</p>

                    <h3>2. Product Authenticity & Hallmarking</h3>
                    <p>Every piece of gold jewellery supplied by Pinora is certified and hallmarked in accordance with the Bureau of Indian Standards (BIS) guidelines. Each item features the official BIS triangular hallmark, gold purity notation (916 for 22K), and an individual HUID laser inscription.</p>

                    <h3>3. Handcrafted Tolerances</h3>
                    <p>Due to the artisanal nature of handcrafted gold jewellery in Rajkot, actual product weights may vary within a minor industry tolerance of ±0.05 grams. The exact net weight is always documented on your tax invoice.</p>
                ',
                'meta_title' => 'Terms & Conditions — Pinora',
                'meta_description' => 'Terms and conditions governing purchases, pricing, and hallmarking on Pinora jewellery store.',
            ],
            [
                'slug' => 'hallmark-guarantee',
                'title' => 'BIS Hallmark Guarantee',
                'subtitle' => 'Understand the 6-Digit HUID Laser Guarantee on Every Pinora Jewel',
                'content' => '
                    <p class="lead">Under Government of India regulations, hallmarking of gold jewellery is mandatory. At Pinora, every single nose pin and jewellery item is assayed, tested, and laser-marked by an official BIS Assaying & Hallmarking Centre.</p>

                    <h3>The 3 Hallmark Symbols on Your Pinora Jewel</h3>
                    <ol>
                        <li><strong>The BIS Triangular Logo:</strong> Signifies that the jewellery has been tested and certified by a government-approved assaying centre.</li>
                        <li><strong>Purity Mark:</strong> Indicates gold fineness (e.g., <strong>22K916</strong> signifies 91.6% pure gold).</li>
                        <li><strong>6-Digit Alphanumeric HUID:</strong> Hallmark Unique Identification code that gives every single piece of jewellery a distinct, traceable identity.</li>
                    </ol>

                    <h3>Verifying with the BIS Care App</h3>
                    <p>You can verify the authenticity of your Pinora piece by entering its 6-digit HUID code into the official <em>BIS Care Mobile App</em> (available on iOS and Android) to see the assaying centre, date of hallmarking, and certified karat purity.</p>
                ',
                'meta_title' => 'BIS Hallmark Guarantee — Pinora Certified Gold Jewellery',
                'meta_description' => 'Learn how to verify your 22K gold nose pin with the BIS 6-digit HUID code and the official BIS Care app.',
            ],
        ];

        foreach ($pages as $p) {
            Page::updateOrCreate(
                ['slug' => $p['slug']],
                array_merge($p, ['is_published' => true])
            );
        }

        // 5. COMPREHENSIVE FAQs
        $faqs = [
            // Gold & Hallmarking
            [
                'category' => 'Gold & Hallmarking',
                'question' => 'How do I know my gold nose pin is genuinely 22K (916) pure?',
                'answer' => '<p>Every piece crafted by Pinora carries the mandatory <strong>Bureau of Indian Standards (BIS) Hallmark</strong> and a unique 6-digit alphanumeric <strong>HUID</strong> code laser-inscribed directly onto the stem or back of the ornament. You can independently verify your piece using the official government <em>BIS Care App</em> by entering the HUID printed on your product certificate.</p>',
                'sort_order' => 1,
            ],
            [
                'category' => 'Gold & Hallmarking',
                'question' => 'Is 22K gold safe for sensitive skin or freshly pierced noses?',
                'answer' => '<p>Yes! 22K gold (91.6% purity) is naturally hypoallergenic and virtually free of nickel, brass, or reactive base metals. It is the gold standard recommended by Indian master jewelers and dermatologists for daily wear without skin irritation or discoloration.</p>',
                'sort_order' => 2,
            ],

            // Sizing & Fitting
            [
                'category' => 'Sizing & Fitting',
                'question' => 'Which nose pin stem style should I choose (Wire, Screw, or L-Bend)?',
                'answer' => '<p>We offer multiple fitting options suited to your comfort:</p><ul><li><strong>Screw (Marathi / South style):</strong> Best for high security and daily wear. Holds firmly with a threaded ball back.</li><li><strong>Wire / Twist Bend:</strong> Traditional, soft curve that gently hugs the inner nostril. Easy to insert and remove.</li><li><strong>L-Bend / Stud:</strong> A 90-degree right angle post, ideal for quick wear and active lifestyles.</li><li><strong>Clip-on / Pressing:</strong> Designed for non-pierced noses, allowing you to wear fine jewellery without piercing.</li></ul>',
                'sort_order' => 3,
            ],
            [
                'category' => 'Sizing & Fitting',
                'question' => 'Can you customize the wire gauge or length for my nose piercing?',
                'answer' => '<p>Yes! Because our artisans work in our own Rajkot atelier, we can customize stem thickness (standard 20G or delicate 22G) and post length. Simply chat with us on WhatsApp before ordering.</p>',
                'sort_order' => 4,
            ],

            // Orders & Payment
            [
                'category' => 'Orders & Payment',
                'question' => 'What payment methods do you accept?',
                'answer' => '<p>We accept all major secure payment methods across India, including <strong>UPI (Google Pay, PhonePe, Paytm), Credit & Debit Cards (Visa, MasterCard, RuPay), Net Banking, and low-cost EMI options</strong>. All transactions are protected by 256-bit SSL encryption.</p>',
                'sort_order' => 5,
            ],
            [
                'category' => 'Orders & Payment',
                'question' => 'How are gold jewellery prices calculated on Pinora?',
                'answer' => '<p>We practice 100% price transparency. Our formula is: <em>(Net Gold Weight × Today’s 22K Gold Rate) + Transparent Artisan Making Charges + 3% GST</em>. There are no hidden fees or inflated markups.</p>',
                'sort_order' => 6,
            ],

            // Shipping & Delivery
            [
                'category' => 'Shipping & Delivery',
                'question' => 'Is the shipping package insured against transit damage or theft?',
                'answer' => '<p>Absolutely. 100% of all dispatches are fully transit-insured by Pinora until they are safely handed over to you with OTP/signature verification. In the rare event of transit loss, we replace the piece or issue a full refund immediately.</p>',
                'sort_order' => 7,
            ],
            [
                'category' => 'Shipping & Delivery',
                'question' => 'Is the outer courier packaging discreet?',
                'answer' => '<p>Yes. For your privacy and security, the outer parcel is a neutral, tamper-evident security pouch with no mention of "jewellery", "gold", or "precious ornaments".</p>',
                'sort_order' => 8,
            ],

            // Returns & Exchanges
            [
                'category' => 'Returns & Exchanges',
                'question' => 'What is Pinora’s 7-Day Exchange Policy?',
                'answer' => '<p>If you are not completely delighted with your design or size, you can request an exchange within 7 days of delivery. The item must be in its original, unworn condition with its security hallmark tag attached. We arrange a doorstep pickup for your convenience.</p>',
                'sort_order' => 9,
            ],
            [
                'category' => 'Returns & Exchanges',
                'question' => 'Do you offer lifetime exchange or buyback on Pinora jewellery?',
                'answer' => '<p>Yes! We offer a <strong>100% Lifetime Exchange Policy</strong>. You can exchange your Pinora gold piece anytime in the future for its exact certified net gold weight at the prevailing market gold rate on the day of exchange.</p>',
                'sort_order' => 10,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, ['is_active' => true])
            );
        }
    }
}

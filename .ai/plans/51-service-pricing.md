# Step 51: PricingService

**Goal:** Implement the core business logic for jewellery pricing calculation based on dynamic metal rates, weights, making charges, discounts, GST, and 4 distinct product pricing types.
**Depends On:** Step 15 (MetalRate model), Step 16 (Product & ProductVariant models)
**Next Step:** Step 52 (CommissionService)

---

## Goal Explanation

Unlike standard e-commerce products with static prices, fine jewellery pricing is highly dynamic. The price is derived from:

1. **Product Type** — determines which formula to use (`jewellery`, `coin_bar`, `mrp_fixed`, `mrp_linked`).
2. **Net Weight** — `gross_weight (weight_grams) − loss` gives the billable net weight.
3. **Live Metal Cost** — `net_weight × rate_per_gram` (the MetalRate table stores purity-specific alloy rates, so no additional purity multiplier is needed).
4. **Making / Labour Charges** — `fixed` ₹ amount, `per_gram` (₹/g × net_weight), or `percentage` (% of metal cost).
5. **Discount** — applied to raw cost (jewellery and coin_bar only).
6. **GST 3%** — applied after discount for `jewellery` and `coin_bar` (HSN 7113). MRP products exclude GST at this stage as MRP is legally GST-inclusive.
7. **Floor rounding** — Coin & Bar prices are floored to the nearest rupee (industry standard).

---

## Pricing Rules by Product Type

| `product_type` | Formula | GST |
|----------------|---------|-----|
| `jewellery`  | `(net_weight × rate + labour) - discount + 3% GST` | ✅ Added |
| `coin_bar`   | Same as jewellery, then `floor()` to drop decimals | ✅ Added |
| `mrp_fixed`  | `base_price` IS the final MRP — no further computation | ❌ Included in MRP |
| `mrp_linked` | `(net_weight × rate + labour) ÷ mrp_pieces` = per-pc MRP | ❌ Included in MRP |

---

## Important Notes

- **Rate storage**: `MetalRate.rate_per_gram` stores the *alloy rate* per purity (e.g., 22K gold = ₹13,435/g). Do NOT apply purity % in code — it is already baked into the rate.
- **Net weight**: `net_weight = weight_grams − loss`. The `loss` column exists in the `products` table.
- **MRP legal rule**: Under Indian law, MRP is GST-inclusive. So `mrp_fixed` and `mrp_linked` prices must NOT have GST added on top.
- **Coin & Bar rounding**: Use `floor()` not `round()` — decimals are dropped, never rounded up.

---

## Files Modified (2026-10-15)

- `app/Services/PricingService.php` — full rewrite with 4 product type support, GST, floor, net weight
- `app/Filament/Components/PricingCalculatorSection.php` — matching rewrite for live admin calculator
- `resources/views/filament/components/pricing-calculator.blade.php` — UI shows net weight breakdown, GST line, coin floor note
- `app/Filament/Resources/Products/Schemas/ProductForm.php` — added `product_type`, `mrp_pieces` fields; rewired all pricing fields with `->live(onBlur: true)`
- `database/migrations/2026_10_15_000001_add_product_type_to_products_table.php` — adds `product_type` enum and `mrp_pieces` int
- `app/Models/Product.php` — `product_type` and `mrp_pieces` added to `$fillable` and `casts()`

---

## Calculation Examples (with spec-rate ₹1,50,000/g for 24K gold)

### 1. Gold Jewellery
```
Gross Weight: 5.000g  Loss: 0.300g  → Net: 4.700g
Net 4.700g × ₹1,50,000/g (22K alloy rate*) = ₹6,46,250 metal cost
* Note: if 22K rate is entered as alloy rate ₹1,37,500 (91.667% of ₹1,50,000 pure),
  the result is the same: 5g × 91.667% × ₹1,50,000 = 4.7g × ₹1,37,500 = ₹6,46,250
Labour 12%: ₹6,46,250 × 12% = ₹77,550
Raw: ₹6,46,250 + ₹77,550 = ₹7,23,800
GST 3%: ₹7,23,800 × 3% = ₹21,714
FINAL: ₹7,45,514
```

### 2. Silver Jewellery
```
Weight: 20g (no loss)
Metal: 20g × ₹2,000/g = ₹37,000
Labour 10%: ₹3,700
Raw: ₹40,700
GST 3%: ₹1,221
FINAL: ₹41,921
```

### 3A. MRP Fixed
```
base_price = ₹800 (this IS the final price, GST-inclusive)
FINAL: ₹800
```

### 3B. MRP Linked
```
Weight: 3g × 24K rate
Labour 6%
Total batch ÷ 100 pieces = ₹4,770/pc  (at ₹1,50,000/g rate)
Auto-updates when metal rate changes.
```

### 4. Coin/Bar
```
1g × 24K rate = ₹1,50,000
Labour 0.13%: ₹195
Raw: ₹1,50,195
GST 3%: ₹4,505.85
Total: ₹1,54,700.85 → floor() → FINAL: ₹1,54,700
```

---

## `app/Services/PricingService.php`

See the actual file at `app/Services/PricingService.php` for full implementation.
Key methods:
- `calculateForProduct(Product)` → float (total incl. GST)
- `calculateForVariant(ProductVariant)` → float
- `getBreakdownForProduct(Product)` → full array breakdown
- `getBreakdownForVariant(ProductVariant)` → full array breakdown
- `calculate($product, ?int $variantId)` → breakdown + shorthand keys
- `calculateBreakdown(Product, ?ProductVariant)` → protected core engine

---

## Seeded Sample Products (`SampleCalculationProductsSeeder.php`)

Six live sample products are seeded in the database to showcase all 4 calculation modes:

| # | Product Name | Type | Weight / Purity | Labour | Formula & Breakdown | Live Calculated Price |
|---|--------------|------|-----------------|--------|---------------------|-----------------------|
| 1 | `22K Gold Royal Heritage Bangle (Sample)` | `jewellery` | Gross 5.000g, Loss 0.300g → Net 4.700g (22K) | 12.00% | 4.7g × ₹13,435/g = ₹63,144.50 metal + ₹7,577.34 labour + ₹2,121.66 (3% GST) | **₹72,843.50** |
| 2 | `925 Sterling Silver Royal Kada (Sample)` | `jewellery` | Net 20.000g (925 Silver) | 10.00% | 20g × ₹83.25/g = ₹1,665.00 metal + ₹166.50 labour + ₹54.95 (3% GST) | **₹1,886.45** |
| 3 | `Luxury Jewellery Polishing & Care Kit (Sample Direct MRP)` | `mrp_fixed` | Net 0.150g (Direct MRP) | N/A | Fixed statutory MRP (all taxes included by law) | **₹800.00** |
| 4 | `24K Pure Gold Nose Pin - Batch MRP (Sample)` | `mrp_linked` | Batch 3.000g (24K Gold, 100 pcs) | 6.00% | (3g × ₹14,656/g = ₹43,968 metal + ₹2,638.08 labour = ₹46,606.08) ÷ 100 pcs | **₹466.06 / pc** |
| 5 | `1g 24K 999 Fine Gold Minted Bar (Sample)` | `coin_bar` | Net 1.000g (24K Gold) | 0.13% | 1g × ₹14,656/g = ₹14,656 metal + ₹19.05 labour + ₹440.25 (3% GST) = ₹15,115.30 → `floor()` | **₹15,115.00** |
| 6 | `10g 999 Fine Silver Temple Ingot Bar (Sample)` | `coin_bar` | Net 10.000g (Silver 999) | 2.00% | 10g × ₹240/g = ₹2,400 metal + ₹48 labour + ₹73.44 (3% GST) = ₹2,521.44 → `floor()` | **₹2,521.00** |


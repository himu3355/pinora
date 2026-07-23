# Pinora Production Deployment Instructions — Stripe One-Time Payments

This document outlines the steps required to transition the Pinora vendor subscription system from a local development/testing environment to a live production server.

---

## 🔑 1. Configure Environment Variables (`.env`)

In production, you must configure your live Stripe credentials and production settings. Update your `.env` file on the production server:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Live Stripe Credentials (get these from your Stripe Dashboard - Live Mode)
STRIPE_PUBLISHABLE_KEY=pk_live_your_actual_live_publishable_key
STRIPE_SECRET_KEY=sk_live_your_actual_live_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_actual_live_webhook_signing_secret
```

> [!WARNING]
> Never expose your live secret key `sk_live_...` or commit your `.env` file to version control.

---

## 🔒 2. SSL/HTTPS Requirement
Stripe requires all live checkout sessions and webhook endpoints to run securely over **HTTPS**. 
- Ensure your production server has an SSL certificate configured (e.g., via Let's Encrypt).
- Your `APP_URL` in `.env` must begin with `https://`.

---

## 🔌 3. Register the Stripe Webhook in Production

To ensure that vendor subscriptions are automatically activated even if a vendor closes their browser before redirecting back to the success page, you must register a webhook in your live Stripe Dashboard:

1. Go to the **Stripe Dashboard** (make sure Test Mode is toggled **OFF**).
2. Navigate to **Developers** > **Webhooks** > **Add endpoint**.
3. Set the **Endpoint URL** to:
   `https://your-domain.com/webhooks/stripe`
4. Select the following event to listen to (this is the only event required for one-time payments):
   - `checkout.session.completed`
5. Click **Add endpoint**.
6. Reveal the **Signing Secret** (`whsec_...`) and add it to your production `.env` file as `STRIPE_WEBHOOK_SECRET`.

---

## 📦 4. Sync Subscription Plans to Live Stripe Catalog

Once your live keys are configured in your production `.env`, you must sync your local plans to the live Stripe catalog so that checkout can process payments:

1. Log in to the **Pinora Admin Panel** (`/admin/subscription-plans`) on your production server.
2. Edit each plan and click **Save**.
3. The system's saving event listener will automatically connect to live Stripe API, register the product, and generate a **live one-time Price ID** which will be saved to your production database.

---

## ⚡ 5. Production Performance Optimizations

Run the standard Laravel caching commands on the production server to optimize performance and compile assets:

```bash
# Compile CSS/JS frontend assets
npm run build

# Cache configuration, routes, and views
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 🛠️ 6. Manual Subscription Override
If you ever need to manually activate, extend, or expire a vendor's subscription manually without going through Stripe checkout:
1. Go to the **Pinora Admin Panel** > **Vendor Subscriptions** (`/admin/vendor-subscriptions`).
2. Add or Edit a subscription record for the vendor.
3. Assign the status (`active`, `trialing`, `expired`) and expiration dates manually. The portal will immediately reflect these changes.

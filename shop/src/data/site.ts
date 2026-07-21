// Brand/contact info for this deployment. Values come from build-time env
// vars (VITE_*) so a fresh deployment for a different salon only needs a
// different .env — no code changes.
//
// NOTE: this is a stop-gap for a single-tenant deployment. Once the platform
// is multi-tenant (see the SaaS roadmap, Phase 4), this will be replaced by
// a runtime fetch to `GET /api/tenant/config`, keyed off the current
// subdomain, so branding can be edited by the salon owner without a rebuild.

const env = import.meta.env;

export const site = {
  name: env.VITE_SALON_NAME || 'Your Salon',
  shopTagline: env.VITE_SHOP_TAGLINE || 'Salon & Beauty — Shop',
  address: env.VITE_SALON_ADDRESS || '123 Main Street, Your City',
  email: env.VITE_SALON_EMAIL || 'info@yoursalon.com',
  phone: env.VITE_SALON_PHONE || '000 000 0000',
  phoneHref: env.VITE_SALON_PHONE_HREF || 'tel:+10000000000',
  social: {
    facebook: env.VITE_SALON_FACEBOOK_URL || 'https://facebook.com',
    instagram: env.VITE_SALON_INSTAGRAM_URL || 'https://instagram.com',
  },
};

// The main marketing site is a separate app on its own subdomain.
// Set VITE_MAIN_SITE_URL per environment (e.g. http://localhost:5173 locally,
// https://yoursalon.com in production).
export const mainSiteUrl = env.VITE_MAIN_SITE_URL || 'http://localhost:5173';

export const shopSettings = {
  currency: env.VITE_SHOP_CURRENCY || 'USD',
  deliveryFee: Number(env.VITE_SHOP_DELIVERY_FEE) || 0,
  freeDeliveryThreshold: Number(env.VITE_SHOP_FREE_DELIVERY_THRESHOLD) || 0,
  pickupAvailable: (env.VITE_SHOP_PICKUP_AVAILABLE ?? 'true') !== 'false',
};

export const formatCurrency = (n: number) =>
  `${shopSettings.currency} ${n.toLocaleString('en-US')}`;

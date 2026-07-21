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
  owner: env.VITE_SALON_OWNER_NAME || 'Salon Owner',
  ownerFirstName: env.VITE_SALON_OWNER_FIRST_NAME || 'Owner',
  ownerTitle: env.VITE_SALON_OWNER_TITLE || 'Founder & Lead Stylist',
  ownerQuote:
    env.VITE_SALON_OWNER_QUOTE ||
    'Every client should leave feeling like the most confident version of themselves.',
  address: env.VITE_SALON_ADDRESS || '123 Main Street, Your City',
  email: env.VITE_SALON_EMAIL || 'info@yoursalon.com',
  phone: env.VITE_SALON_PHONE || '000 000 0000',
  phoneHref: env.VITE_SALON_PHONE_HREF || 'tel:+10000000000',
  hours: [
    { day: 'Monday – Saturday', time: '9.00 AM – 7.00 PM' },
    { day: 'Sunday', time: '10.00 AM – 4.00 PM' },
  ],
  social: {
    facebook: env.VITE_SALON_FACEBOOK_URL || 'https://facebook.com',
    instagram: env.VITE_SALON_INSTAGRAM_URL || 'https://instagram.com',
  },
};

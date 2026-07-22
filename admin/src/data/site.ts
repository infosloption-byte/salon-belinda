// Same stop-gap pattern as frontend/src/data/site.ts and shop/src/data/site.ts
// — env-driven for now, replaced by a per-tenant API fetch once multi-tenant
// (see SAAS-ROADMAP.md Phase 4).
const env = import.meta.env;

export const site = {
  name: env.VITE_SALON_NAME || 'Your Salon',
  address: env.VITE_SALON_ADDRESS || '123 Main Street, Your City',
};

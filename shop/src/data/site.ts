export const site = {
  name: 'Salon Belinda',
  shopTagline: 'Bridal & Ladies Salon — Shop',
  address: '82 Havelock Rd, Galle 80000, Sri Lanka',
  email: 'info.salonbelinda@gmail.com',
  phone: '070 244 4393',
  phoneHref: 'tel:+94702444393',
  social: {
    facebook: 'https://facebook.com',
    instagram: 'https://instagram.com',
  },
};

// The main marketing site is a separate app on its own subdomain.
// Set VITE_MAIN_SITE_URL per environment (e.g. http://localhost:5173 locally,
// https://salonbelinda.com in production).
export const mainSiteUrl = import.meta.env.VITE_MAIN_SITE_URL || 'http://localhost:5173';

export const shopSettings = {
  currency: 'LKR',
  deliveryFee: 350,
  freeDeliveryThreshold: 8000,
  pickupAvailable: true,
};

export const formatLKR = (n: number) => `LKR ${n.toLocaleString('en-LK')}`;

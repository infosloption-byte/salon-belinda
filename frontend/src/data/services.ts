export interface Service {
  id: string;
  name: string;
  description: string;
  duration: string;
  price: string;
}

export interface ServiceCategory {
  id: string;
  title: string;
  intro: string;
  services: Service[];
}

export const serviceCategories: ServiceCategory[] = [
  {
    id: 'bridal',
    title: 'Bridal Packages',
    intro:
      'Full-day bridal artistry, from the first touch of skincare prep to the last pin in your veil.',
    services: [
      {
        id: 'bridal-full',
        name: 'Complete Bridal Package',
        description:
          'Bridal hair, makeup, saree/gown draping, and touch-up kit for the full ceremony day.',
        duration: '5–6 hrs',
        price: 'LKR 45,000',
      },
      {
        id: 'bridal-trial',
        name: 'Bridal Trial Session',
        description: 'A full rehearsal of your look, styled and photographed ahead of the day.',
        duration: '2 hrs',
        price: 'LKR 8,500',
      },
      {
        id: 'bridal-homecoming',
        name: 'Homecoming / Poruwa Look',
        description: 'A second look styled for the homecoming or poruwa ceremony.',
        duration: '3 hrs',
        price: 'LKR 22,000',
      },
      {
        id: 'bridal-entourage',
        name: 'Bridal Party Styling (per person)',
        description: 'Hair and makeup for bridesmaids, mothers, and family members.',
        duration: '1.5 hrs',
        price: 'LKR 6,500',
      },
    ],
  },
  {
    id: 'hair',
    title: 'Hair',
    intro: 'Cutting, colour, and treatments for everyday shine and special-occasion styling.',
    services: [
      {
        id: 'hair-cut',
        name: 'Cut & Style',
        description: 'Precision cut finished with a blow-dry style of your choice.',
        duration: '45 min',
        price: 'LKR 1,800',
      },
      {
        id: 'hair-colour',
        name: 'Global Colour',
        description: 'Full-head colour using ammonia-friendly professional formulas.',
        duration: '2 hrs',
        price: 'From LKR 6,500',
      },
      {
        id: 'hair-treatment',
        name: 'Keratin / Deep Treatment',
        description: 'Smoothing and strengthening treatment for damaged or frizzy hair.',
        duration: '2.5 hrs',
        price: 'From LKR 9,000',
      },
      {
        id: 'hair-updo',
        name: 'Occasion Updo',
        description: 'Elegant styling for parties, engagements, and photoshoots.',
        duration: '1 hr',
        price: 'LKR 3,500',
      },
    ],
  },
  {
    id: 'skin',
    title: 'Skin & Facial',
    intro: 'Facials and skin treatments tailored to your skin type ahead of any occasion.',
    services: [
      {
        id: 'skin-classic',
        name: 'Classic Facial',
        description: 'Cleansing, exfoliation, and mask suited to your skin type.',
        duration: '1 hr',
        price: 'LKR 3,200',
      },
      {
        id: 'skin-bridal-glow',
        name: 'Bridal Glow Facial Course',
        description: 'A 4-session facial course leading up to the wedding for a natural glow.',
        duration: '1 hr x 4',
        price: 'LKR 14,000',
      },
      {
        id: 'skin-brightening',
        name: 'Brightening Facial',
        description: 'Vitamin-C led treatment to even tone and add radiance.',
        duration: '1 hr 15 min',
        price: 'LKR 4,800',
      },
    ],
  },
  {
    id: 'makeup',
    title: 'Makeup',
    intro: 'Party, photoshoot, and everyday makeup using long-wear, photo-ready products.',
    services: [
      {
        id: 'makeup-party',
        name: 'Party Makeup',
        description: 'Full-face makeup styled for evening events and celebrations.',
        duration: '1 hr',
        price: 'LKR 4,500',
      },
      {
        id: 'makeup-photoshoot',
        name: 'Photoshoot Makeup',
        description: 'HD makeup built for camera, with false lashes included.',
        duration: '1.5 hrs',
        price: 'LKR 6,000',
      },
      {
        id: 'makeup-lesson',
        name: 'Personal Makeup Lesson',
        description: 'One-on-one lesson to learn techniques for your own face shape.',
        duration: '2 hrs',
        price: 'LKR 7,500',
      },
    ],
  },
  {
    id: 'nails',
    title: 'Nails',
    intro: 'Manicures, pedicures, and nail art finished to last through any event.',
    services: [
      {
        id: 'nails-mani',
        name: 'Classic Manicure',
        description: 'Shape, cuticle care, and polish of your choice.',
        duration: '40 min',
        price: 'LKR 1,500',
      },
      {
        id: 'nails-pedi',
        name: 'Spa Pedicure',
        description: 'Soak, scrub, mask, and polish for tired feet.',
        duration: '1 hr',
        price: 'LKR 2,200',
      },
      {
        id: 'nails-gel',
        name: 'Gel Overlay & Art',
        description: 'Long-wear gel colour with optional hand-painted detail.',
        duration: '1 hr 15 min',
        price: 'LKR 3,800',
      },
    ],
  },
  {
    id: 'occasion',
    title: 'Ladies Special Occasion',
    intro: 'Complete look packages for engagements, homecomings, and family celebrations.',
    services: [
      {
        id: 'occasion-full',
        name: 'Full Occasion Look',
        description: 'Hair, makeup, and draping for engagements and family functions.',
        duration: '2.5 hrs',
        price: 'LKR 9,500',
      },
      {
        id: 'occasion-saree',
        name: 'Saree / Osari Draping',
        description: 'Traditional and modern draping styles, pinned to hold all day.',
        duration: '30 min',
        price: 'LKR 2,000',
      },
    ],
  },
];

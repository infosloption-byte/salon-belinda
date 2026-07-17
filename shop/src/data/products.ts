export type Category = 'Hair Care' | 'Skin Care' | 'Makeup' | 'Bridal Accessories';

export interface Product {
  id: string | number;
  slug: string;
  name: string;
  category: Category;
  price: number;
  compareAtPrice?: number;
  description: string;
  details: string[];
  images: string[];
  inStock: boolean;
  stockCount: number;
  rating: number;
  reviewCount: number;
  bestSeller?: boolean;
  isNew?: boolean;
}

export const categories: Category[] = ['Hair Care', 'Skin Care', 'Makeup', 'Bridal Accessories'];

export const products: Product[] = [
  {
    id: 'p1',
    slug: 'argan-repair-shampoo',
    name: 'Argan Repair Shampoo',
    category: 'Hair Care',
    price: 2200,
    description: 'Sulfate-free shampoo for colour-treated and chemically styled hair.',
    details: [
      'Sulfate and paraben free formula',
      'Safe for keratin and colour-treated hair',
      '300ml bottle, lasts 6–8 weeks with daily use',
      'The exact shampoo used in our salon treatments',
    ],
    images: [
      'https://images.unsplash.com/photo-1585232004423-be5da21e0d18?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: true,
    stockCount: 24,
    rating: 4.8,
    reviewCount: 36,
    bestSeller: true,
  },
  {
    id: 'p2',
    slug: 'keratin-leave-in-serum',
    name: 'Keratin Leave-In Serum',
    category: 'Hair Care',
    price: 3100,
    description: 'Lightweight serum that smooths frizz and adds shine without weigh-down.',
    details: [
      'Weightless formula, safe for fine hair',
      'Heat-protectant up to 220°C',
      '50ml dropper bottle',
      'Apply to damp hair before styling',
    ],
    images: [
      'https://images.unsplash.com/photo-1556227834-09f1de7a7d14?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1620756236379-6c1c2c1cbf9a?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: true,
    stockCount: 15,
    rating: 4.6,
    reviewCount: 21,
  },
  {
    id: 'p3',
    slug: 'brightening-vitamin-c-serum',
    name: 'Brightening Vitamin-C Serum',
    category: 'Skin Care',
    price: 3600,
    description: 'Daily serum to even skin tone and add a natural, dewy glow.',
    details: [
      '10% stabilised vitamin C',
      'Fragrance-free, suitable for sensitive skin',
      '30ml, use morning and evening',
      'Follow with SPF during the day',
    ],
    images: [
      'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: true,
    stockCount: 18,
    rating: 4.9,
    reviewCount: 52,
    bestSeller: true,
  },
  {
    id: 'p4',
    slug: 'bridal-glow-face-mask-set',
    name: 'Bridal Glow Face Mask (Set of 4)',
    category: 'Skin Care',
    price: 4200,
    description: 'The same mask course used in-salon for our bridal facial package.',
    details: [
      '4-week course, one mask per week',
      'Brightens and preps skin ahead of the big day',
      'Best started 1 month before the wedding',
      'Includes application guide card',
    ],
    images: [
      'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1519824145371-296894a0daa9?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: true,
    stockCount: 9,
    rating: 4.9,
    reviewCount: 28,
    isNew: true,
  },
  {
    id: 'p5',
    slug: 'long-wear-setting-spray',
    name: 'Long-Wear Setting Spray',
    category: 'Makeup',
    price: 2800,
    description: 'The finishing spray used on every bride to hold looks through long days.',
    details: [
      'Up to 16-hour wear',
      'Non-sticky, dewy finish',
      '100ml spray',
      'Humidity and sweat resistant',
    ],
    images: [
      'https://images.unsplash.com/photo-1631730359585-38a4935cbec4?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: true,
    stockCount: 30,
    rating: 4.7,
    reviewCount: 44,
    bestSeller: true,
  },
  {
    id: 'p6',
    slug: 'hd-foundation-12-shades',
    name: 'HD Foundation — 12 Shades',
    category: 'Makeup',
    price: 3900,
    compareAtPrice: 4500,
    description: 'The photo-ready foundation used in our HD photoshoot makeup service.',
    details: [
      '12 shades to match a wide range of skin tones',
      'Buildable, camera-ready medium-to-full coverage',
      '30ml pump bottle',
      'Message us your shade for a colour match before ordering',
    ],
    images: [
      'https://images.unsplash.com/photo-1631214540242-3cd8c4b0b3b8?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: false,
    stockCount: 0,
    rating: 4.5,
    reviewCount: 19,
  },
  {
    id: 'p7',
    slug: 'bridal-hair-pins-gold-tone',
    name: 'Bridal Hair Pins, Gold-Tone (Set of 12)',
    category: 'Bridal Accessories',
    price: 1800,
    description: 'Delicate pearl-and-gold pins for veils, updos, and floral crowns.',
    details: [
      'Set of 12 mixed pearl and gold-tone pins',
      'Lightweight, secure grip',
      'Pairs well with our floral crown styling service',
      'Gift-boxed',
    ],
    images: [
      'https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1611085583191-a3b181a88401?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: true,
    stockCount: 40,
    rating: 4.8,
    reviewCount: 15,
  },
  {
    id: 'p8',
    slug: 'embroidered-bridal-clutch',
    name: 'Embroidered Bridal Clutch',
    category: 'Bridal Accessories',
    price: 5200,
    description: 'Hand-embroidered clutch to match traditional and modern bridal outfits.',
    details: [
      'Hand-embroidered, made to order',
      'Fits phone, lipstick, and cards',
      'Available in ivory, blush, and gold thread',
      'Allow 3–5 days for embroidery before dispatch',
    ],
    images: [
      'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=900&q=80',
    ],
    inStock: true,
    stockCount: 6,
    rating: 5.0,
    reviewCount: 8,
    isNew: true,
  },
];

export const getProductBySlug = (slug: string) => products.find((p) => p.slug === slug);

export const getRelatedProducts = (product: Product, limit = 4) =>
  products.filter((p) => p.category === product.category && p.id !== product.id).slice(0, limit);

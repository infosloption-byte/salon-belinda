export interface Product {
  id: string;
  name: string;
  category: 'Hair Care' | 'Skin Care' | 'Makeup' | 'Bridal Accessories';
  price: number;
  description: string;
  image: string;
  inStock: boolean;
}

export const products: Product[] = [
  {
    id: 'p1',
    name: 'Argan Repair Shampoo',
    category: 'Hair Care',
    price: 2200,
    description: 'Sulfate-free shampoo for colour-treated and chemically styled hair.',
    image: 'https://images.unsplash.com/photo-1585232004423-be5da21e0d18?auto=format&fit=crop&w=700&q=80',
    inStock: true,
  },
  {
    id: 'p2',
    name: 'Keratin Leave-In Serum',
    category: 'Hair Care',
    price: 3100,
    description: 'Lightweight serum that smooths frizz and adds shine without weigh-down.',
    image: 'https://images.unsplash.com/photo-1556227834-09f1de7a7d14?auto=format&fit=crop&w=700&q=80',
    inStock: true,
  },
  {
    id: 'p3',
    name: 'Brightening Vitamin-C Serum',
    category: 'Skin Care',
    price: 3600,
    description: 'Daily serum to even skin tone and add a natural, dewy glow.',
    image: 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=700&q=80',
    inStock: true,
  },
  {
    id: 'p4',
    name: 'Bridal Glow Face Mask (Set of 4)',
    category: 'Skin Care',
    price: 4200,
    description: 'The same mask course used in-salon for our bridal facial package.',
    image: 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=700&q=80',
    inStock: true,
  },
  {
    id: 'p5',
    name: 'Long-Wear Setting Spray',
    category: 'Makeup',
    price: 2800,
    description: 'The finishing spray used on every bride to hold looks through long days.',
    image: 'https://images.unsplash.com/photo-1631730359585-38a4935cbec4?auto=format&fit=crop&w=700&q=80',
    inStock: true,
  },
  {
    id: 'p6',
    name: 'HD Foundation — 12 Shades',
    category: 'Makeup',
    price: 3900,
    description: 'The photo-ready foundation used in our HD photoshoot makeup service.',
    image: 'https://images.unsplash.com/photo-1631214540242-3cd8c4b0b3b8?auto=format&fit=crop&w=700&q=80',
    inStock: false,
  },
  {
    id: 'p7',
    name: 'Bridal Hair Pins, Gold-Tone (Set of 12)',
    category: 'Bridal Accessories',
    price: 1800,
    description: 'Delicate pearl-and-gold pins for veils, updos, and floral crowns.',
    image: 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=700&q=80',
    inStock: true,
  },
  {
    id: 'p8',
    name: 'Embroidered Bridal Clutch',
    category: 'Bridal Accessories',
    price: 5200,
    description: 'Hand-embroidered clutch to match traditional and modern bridal outfits.',
    image: 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?auto=format&fit=crop&w=700&q=80',
    inStock: true,
  },
];

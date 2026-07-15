export interface GalleryItem {
  id: string;
  category: 'Bridal' | 'Hair' | 'Makeup' | 'Nails' | 'Special Occasion';
  title: string;
  image: string;
}

// Placeholder photography — swap these `image` URLs for Salon Belinda's own
// portfolio shots once available. PortfolioImage will show a soft branded
// placeholder automatically if any link fails to load.
export const galleryItems: GalleryItem[] = [
  {
    id: 'g1',
    category: 'Bridal',
    title: 'Kandyan Bridal Look, Ratgama',
    image: 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g2',
    category: 'Bridal',
    title: 'Beachside Wedding, Galle Road',
    image: 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g3',
    category: 'Bridal',
    title: 'Bridal Trial — Soft Glam',
    image: 'https://images.unsplash.com/photo-1522337660859-02fbefca4702?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g4',
    category: 'Bridal',
    title: 'Homecoming Saree Draping',
    image: 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g5',
    category: 'Hair',
    title: 'Balayage & Blow-dry',
    image: 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g6',
    category: 'Hair',
    title: 'Occasion Updo',
    image: 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g7',
    category: 'Makeup',
    title: 'HD Photoshoot Makeup',
    image: 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g8',
    category: 'Makeup',
    title: 'Evening Party Glam',
    image: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g9',
    category: 'Nails',
    title: 'Bridal French Gel Set',
    image: 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g10',
    category: 'Nails',
    title: 'Hand-painted Nail Art',
    image: 'https://images.unsplash.com/photo-1610992015732-2449b76344bc?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g11',
    category: 'Special Occasion',
    title: 'Engagement Day Styling',
    image: 'https://images.unsplash.com/photo-1583001931096-959e9a1a6223?auto=format&fit=crop&w=900&q=80',
  },
  {
    id: 'g12',
    category: 'Special Occasion',
    title: 'Family Function Look',
    image: 'https://images.unsplash.com/photo-1550614000-4895a10e1bfd?auto=format&fit=crop&w=900&q=80',
  },
];

export const galleryCategories: GalleryItem['category'][] = [
  'Bridal',
  'Hair',
  'Makeup',
  'Nails',
  'Special Occasion',
];

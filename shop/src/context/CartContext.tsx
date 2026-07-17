import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import type { Product } from '../data/products';

export interface CartLine {
  product: Product;
  quantity: number;
}

interface CartContextValue {
  lines: CartLine[];
  add: (product: Product, quantity?: number) => void;
  remove: (productId: Product['id']) => void;
  setQuantity: (productId: Product['id'], quantity: number) => void;
  clear: () => void;
  count: number;
  subtotal: number;
}

const CartContext = createContext<CartContextValue | null>(null);
const STORAGE_KEY = 'salonbelinda_shop_cart_v2';

function loadInitialLines(): CartLine[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return [];
    const saved = JSON.parse(raw) as CartLine[];
    return Array.isArray(saved) ? saved : [];
  } catch {
    return [];
  }
}

export function CartProvider({ children }: { children: ReactNode }) {
  // Cart lines store a snapshot of the product alongside quantity, so the
  // cart survives page reloads regardless of whether products came from the
  // live API or the bundled fallback catalog. Prices/stock are re-validated
  // server-side anyway when the order is actually placed.
  const [lines, setLines] = useState<CartLine[]>(() => loadInitialLines());

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(lines));
  }, [lines]);

  const add = (product: Product, quantity = 1) => {
    setLines((prev) => {
      const existing = prev.find((l) => l.product.id === product.id);
      if (existing) {
        return prev.map((l) =>
          l.product.id === product.id
            ? { ...l, quantity: Math.min(l.quantity + quantity, product.stockCount || l.quantity + quantity) }
            : l
        );
      }
      return [...prev, { product, quantity: Math.min(quantity, product.stockCount || quantity) }];
    });
  };

  const remove = (productId: Product['id']) => {
    setLines((prev) => prev.filter((l) => l.product.id !== productId));
  };

  const setQuantity = (productId: Product['id'], quantity: number) => {
    setLines((prev) =>
      quantity <= 0
        ? prev.filter((l) => l.product.id !== productId)
        : prev.map((l) => (l.product.id === productId ? { ...l, quantity } : l))
    );
  };

  const clear = () => setLines([]);

  const count = lines.reduce((sum, l) => sum + l.quantity, 0);
  const subtotal = lines.reduce((sum, l) => sum + l.quantity * l.product.price, 0);

  return (
    <CartContext.Provider value={{ lines, add, remove, setQuantity, clear, count, subtotal }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used within a CartProvider');
  return ctx;
}

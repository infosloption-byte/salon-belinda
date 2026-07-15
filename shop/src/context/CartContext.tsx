import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import { products, type Product } from '../data/products';

export interface CartLine {
  product: Product;
  quantity: number;
}

interface CartContextValue {
  lines: CartLine[];
  add: (product: Product, quantity?: number) => void;
  remove: (productId: string) => void;
  setQuantity: (productId: string, quantity: number) => void;
  clear: () => void;
  count: number;
  subtotal: number;
}

const CartContext = createContext<CartContextValue | null>(null);
const STORAGE_KEY = 'salonbelinda_shop_cart_v1';

function loadInitialLines(): CartLine[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return [];
    const savedIds: { productId: string; quantity: number }[] = JSON.parse(raw);
    // Re-hydrate against the live product catalog so prices/stock stay current.
    return savedIds
      .map(({ productId, quantity }) => {
        const product = products.find((p) => p.id === productId);
        return product ? { product, quantity } : null;
      })
      .filter((l): l is CartLine => l !== null);
  } catch {
    return [];
  }
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [lines, setLines] = useState<CartLine[]>(() => loadInitialLines());

  useEffect(() => {
    const toSave = lines.map((l) => ({ productId: l.product.id, quantity: l.quantity }));
    localStorage.setItem(STORAGE_KEY, JSON.stringify(toSave));
  }, [lines]);

  const add = (product: Product, quantity = 1) => {
    setLines((prev) => {
      const existing = prev.find((l) => l.product.id === product.id);
      if (existing) {
        return prev.map((l) =>
          l.product.id === product.id
            ? { ...l, quantity: Math.min(l.quantity + quantity, product.stockCount) }
            : l
        );
      }
      return [...prev, { product, quantity: Math.min(quantity, product.stockCount) }];
    });
  };

  const remove = (productId: string) => {
    setLines((prev) => prev.filter((l) => l.product.id !== productId));
  };

  const setQuantity = (productId: string, quantity: number) => {
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

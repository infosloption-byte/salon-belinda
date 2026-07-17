import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import { products as staticProducts, type Product } from '../data/products';
import { fetchProducts } from '../lib/api';

interface ProductsContextValue {
  products: Product[];
  loading: boolean;
  /** True if we're showing the bundled fallback catalog instead of live data. */
  isFallback: boolean;
}

const ProductsContext = createContext<ProductsContextValue | null>(null);

export function ProductsProvider({ children }: { children: ReactNode }) {
  const [products, setProducts] = useState<Product[]>(staticProducts);
  const [loading, setLoading] = useState(true);
  const [isFallback, setIsFallback] = useState(true);

  useEffect(() => {
    let cancelled = false;

    fetchProducts()
      .then((live) => {
        if (!cancelled && live.length > 0) {
          setProducts(live);
          setIsFallback(false);
        }
      })
      .catch(() => {
        // Backend not reachable yet — keep showing the bundled catalog
        // (src/data/products.ts) so the shop still works standalone.
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  return (
    <ProductsContext.Provider value={{ products, loading, isFallback }}>
      {children}
    </ProductsContext.Provider>
  );
}

export function useProducts() {
  const ctx = useContext(ProductsContext);
  if (!ctx) throw new Error('useProducts must be used within a ProductsProvider');
  return ctx;
}

export function useProduct(slug: string | undefined) {
  const { products, loading } = useProducts();
  const product = products.find((p) => p.slug === slug);

  return { product, loading };
}

export function useRelatedProducts(product: Product | undefined, limit = 4) {
  const { products } = useProducts();
  if (!product) return [];

  return products.filter((p) => p.category === product.category && p.id !== product.id).slice(0, limit);
}

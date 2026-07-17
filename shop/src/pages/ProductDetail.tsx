import { useState } from 'react';
import { Link, Navigate, useParams } from 'react-router-dom';
import { ChevronLeft, Minus, Plus, ShoppingBag, Check, Truck } from 'lucide-react';
import { useProduct, useRelatedProducts } from '../context/ProductsContext';
import { formatLKR } from '../data/site';
import { useCart } from '../context/CartContext';
import StarRating from '../components/ui/StarRating';
import Badge from '../components/ui/Badge';
import ProductCard from '../components/product/ProductCard';
import { Button } from '../components/ui/Button';

export default function ProductDetail() {
  const { slug } = useParams();
  const { product, loading } = useProduct(slug);
  const related = useRelatedProducts(product);
  const { add } = useCart();
  const [activeImage, setActiveImage] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [justAdded, setJustAdded] = useState(false);

  if (!product) {
    if (loading) return null;
    return <Navigate to="/products" replace />;
  }

  const handleAdd = () => {
    add(product, quantity);
    setJustAdded(true);
    setTimeout(() => setJustAdded(false), 1600);
  };

  return (
    <div className="max-w-7xl mx-auto px-5 md:px-8 py-10 md:py-16">
      <Link
        to="/products"
        className="inline-flex items-center gap-1.5 text-xs uppercase tracking-wide mb-8"
        style={{ color: 'var(--color-ink)', opacity: 0.6 }}
      >
        <ChevronLeft size={14} /> Back to Shop
      </Link>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">
        <div>
          <div className="relative aspect-square overflow-hidden mb-3" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
            <img src={product.images[activeImage]} alt={product.name} className="w-full h-full object-cover" />
            <div className="absolute top-3 left-3 flex flex-col gap-1.5 items-start">
              {!product.inStock && <Badge tone="muted">Out of Stock</Badge>}
              {product.inStock && product.isNew && <Badge tone="gold">New</Badge>}
              {product.inStock && product.bestSeller && <Badge tone="maroon">Best Seller</Badge>}
            </div>
          </div>
          {product.images.length > 1 && (
            <div className="flex gap-3">
              {product.images.map((img, i) => (
                <button
                  key={img}
                  onClick={() => setActiveImage(i)}
                  className="w-20 h-20 overflow-hidden border-2 transition-colors"
                  style={{ borderColor: i === activeImage ? 'var(--color-gold)' : 'transparent' }}
                  aria-label={`View image ${i + 1}`}
                >
                  <img src={img} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>

        <div>
          <p className="eyebrow mb-2" style={{ color: 'var(--color-gold)' }}>{product.category}</p>
          <h1 className="font-display text-3xl md:text-4xl mb-3" style={{ color: 'var(--color-ink)' }}>
            {product.name}
          </h1>
          <div className="mb-5">
            <StarRating rating={product.rating} reviewCount={product.reviewCount} size={15} />
          </div>
          <div className="flex items-baseline gap-3 mb-6">
            <span className="font-display italic text-3xl" style={{ color: 'var(--color-maroon)' }}>
              {formatLKR(product.price)}
            </span>
            {product.compareAtPrice && (
              <span className="text-base line-through" style={{ color: 'var(--color-ink)', opacity: 0.4 }}>
                {formatLKR(product.compareAtPrice)}
              </span>
            )}
          </div>

          <p className="text-sm leading-relaxed mb-6" style={{ color: 'var(--color-ink)', opacity: 0.75 }}>
            {product.description}
          </p>

          <ul className="space-y-2 mb-8">
            {product.details.map((d) => (
              <li key={d} className="text-sm flex gap-2.5" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
                <span style={{ color: 'var(--color-gold)' }}>—</span> {d}
              </li>
            ))}
          </ul>

          {product.inStock ? (
            <>
              <div className="flex items-center gap-4 mb-6">
                <div className="flex items-center border" style={{ borderColor: 'rgba(38,34,32,0.2)' }}>
                  <button
                    onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                    className="p-3"
                    aria-label="Decrease quantity"
                  >
                    <Minus size={14} />
                  </button>
                  <span className="w-10 text-center text-sm">{quantity}</span>
                  <button
                    onClick={() => setQuantity((q) => Math.min(product.stockCount, q + 1))}
                    className="p-3"
                    aria-label="Increase quantity"
                    disabled={quantity >= product.stockCount}
                  >
                    <Plus size={14} />
                  </button>
                </div>
                <span className="text-xs" style={{ color: 'var(--color-ink)', opacity: 0.5 }}>
                  {product.stockCount <= 10 ? `Only ${product.stockCount} left` : 'In stock'}
                </span>
              </div>

              <Button onClick={handleAdd} className="w-full sm:w-auto">
                {justAdded ? (
                  <>
                    <Check size={16} /> Added to Bag
                  </>
                ) : (
                  <>
                    <ShoppingBag size={16} /> Add to Bag
                  </>
                )}
              </Button>
            </>
          ) : (
            <Button disabled className="w-full sm:w-auto">Out of Stock</Button>
          )}

          <div className="flex items-center gap-2.5 mt-6 pt-6 border-t" style={{ borderColor: 'rgba(38,34,32,0.1)' }}>
            <Truck size={16} style={{ color: 'var(--color-gold)' }} />
            <p className="text-xs" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
              Delivery island-wide, or free pickup at our Ratgama salon.
            </p>
          </div>
        </div>
      </div>

      {related.length > 0 && (
        <section className="mt-20 md:mt-28">
          <h2 className="font-display text-2xl md:text-3xl mb-8" style={{ color: 'var(--color-ink)' }}>
            You May Also Like
          </h2>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            {related.map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        </section>
      )}
    </div>
  );
}

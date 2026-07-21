import { useState } from 'react';
import { Link } from 'react-router-dom';
import { ShoppingBag, Check } from 'lucide-react';
import type { Product } from '../../data/products';
import { formatCurrency } from '../../data/site';
import { useCart } from '../../context/CartContext';
import StarRating from '../ui/StarRating';
import Badge from '../ui/Badge';

export default function ProductCard({ product }: { product: Product }) {
  const { add } = useCart();
  const [justAdded, setJustAdded] = useState(false);

  const handleAdd = () => {
    add(product);
    setJustAdded(true);
    setTimeout(() => setJustAdded(false), 1400);
  };

  return (
    <div className="group">
      <Link to={`/products/${product.slug}`} className="block">
        <div className="relative aspect-square mb-4 overflow-hidden" style={{ backgroundColor: 'var(--color-ivory-dim)' }}>
          <img
            src={product.images[0]}
            alt={product.name}
            className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
            loading="lazy"
          />
          <div className="absolute top-3 left-3 flex flex-col gap-1.5 items-start">
            {!product.inStock && <Badge tone="muted">Out of Stock</Badge>}
            {product.inStock && product.isNew && <Badge tone="gold">New</Badge>}
            {product.inStock && product.bestSeller && <Badge tone="maroon">Best Seller</Badge>}
            {product.inStock && product.compareAtPrice && <Badge tone="ivory">Sale</Badge>}
          </div>
        </div>
        <p className="eyebrow mb-1" style={{ color: 'var(--color-gold)', fontSize: '0.6rem' }}>
          {product.category}
        </p>
        <h3 className="font-display text-lg mb-1 leading-snug" style={{ color: 'var(--color-ink)' }}>
          {product.name}
        </h3>
        <div className="mb-2">
          <StarRating rating={product.rating} reviewCount={product.reviewCount} />
        </div>
      </Link>
      <div className="flex items-center justify-between">
        <span className="flex items-baseline gap-2">
          <span className="font-display italic text-base" style={{ color: 'var(--color-maroon)' }}>
            {formatCurrency(product.price)}
          </span>
          {product.compareAtPrice && (
            <span
              className="text-xs line-through"
              style={{ color: 'var(--color-ink)', opacity: 0.4 }}
            >
              {formatCurrency(product.compareAtPrice)}
            </span>
          )}
        </span>
        <button
          disabled={!product.inStock}
          onClick={handleAdd}
          aria-label={`Add ${product.name} to bag`}
          className="flex items-center gap-1.5 text-xs uppercase tracking-wide px-3 py-2 border disabled:opacity-30 hover:border-[var(--color-gold)] hover:text-[var(--color-maroon)] transition-colors min-w-[84px] justify-center"
          style={
            justAdded
              ? { borderColor: 'var(--color-green)', color: 'var(--color-green)' }
              : { borderColor: 'rgba(38,34,32,0.2)', color: 'var(--color-ink)' }
          }
        >
          {justAdded ? (
            <>
              <Check size={13} /> Added
            </>
          ) : (
            <>
              <ShoppingBag size={13} /> Add
            </>
          )}
        </button>
      </div>
    </div>
  );
}

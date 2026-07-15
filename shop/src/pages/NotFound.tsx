import { LinkButton } from '../components/ui/Button';

export default function NotFound() {
  return (
    <div className="max-w-xl mx-auto px-5 py-24 md:py-32 text-center">
      <p className="eyebrow mb-4" style={{ color: 'var(--color-gold)' }}>404</p>
      <h1 className="font-display text-3xl md:text-4xl mb-4" style={{ color: 'var(--color-ink)' }}>
        Page Not Found
      </h1>
      <p className="text-sm mb-9" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
        The page you're looking for doesn't exist or may have moved.
      </p>
      <LinkButton to="/products">Shop All Products</LinkButton>
    </div>
  );
}

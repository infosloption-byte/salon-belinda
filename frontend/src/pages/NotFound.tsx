import { LinkButton } from '../components/ui/Button';

export default function NotFound() {
  return (
    <div className="max-w-xl mx-auto px-5 py-28 md:py-36 text-center">
      <p className="eyebrow mb-4" style={{ color: 'var(--color-magenta)' }}>
        404
      </p>
      <h1 className="font-display text-4xl md:text-5xl mb-4" style={{ color: 'var(--color-ink)' }}>
        Page Not Found
      </h1>
      <p className="text-sm mb-9" style={{ color: 'var(--color-ink)', opacity: 0.65 }}>
        The page you're looking for doesn't exist or may have moved.
      </p>
      <div className="flex flex-wrap gap-4 justify-center">
        <LinkButton to="/" variant="gradient">
          Back to Home
        </LinkButton>
        <LinkButton to="/booking" variant="ghost">
          Book Appointment
        </LinkButton>
      </div>
    </div>
  );
}

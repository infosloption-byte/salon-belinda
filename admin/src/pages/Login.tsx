import { useState, type FormEvent } from 'react';
import { Navigate } from 'react-router-dom';
import { Lock, Mail } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

export function Login() {
  const { login, isAuthenticated } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (isAuthenticated) {
    return <Navigate to="/" replace />;
  }

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    setIsSubmitting(true);
    try {
      await login(email, password);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unable to log in.');
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-wine px-4">
      <div className="w-full max-w-sm">
        <div className="mb-8 flex flex-col items-center text-paper">
          <div className="arch mb-4 flex h-14 w-14 items-center justify-center border border-gold/40 bg-wine-light">
            <span className="font-display text-2xl text-gold">B</span>
          </div>
          <h1 className="font-display text-2xl">Salon Belinda</h1>
          <p className="mt-1 text-sm text-paper/50">Staff &amp; admin sign in</p>
        </div>

        <form onSubmit={handleSubmit} className="mirror-card p-7">
          <div className="space-y-4">
            <label className="block">
              <span className="mb-1.5 block text-sm text-muted">Email</span>
              <div className="flex items-center gap-2 rounded-lg border border-ink/10 bg-paper px-3 py-2.5 focus-within:border-gold">
                <Mail size={16} className="text-muted" />
                <input
                  type="email"
                  required
                  autoComplete="username"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="you@salonbelinda.com"
                  className="w-full bg-transparent text-sm text-ink outline-none placeholder:text-muted/60"
                />
              </div>
            </label>

            <label className="block">
              <span className="mb-1.5 block text-sm text-muted">Password</span>
              <div className="flex items-center gap-2 rounded-lg border border-ink/10 bg-paper px-3 py-2.5 focus-within:border-gold">
                <Lock size={16} className="text-muted" />
                <input
                  type="password"
                  required
                  autoComplete="current-password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full bg-transparent text-sm text-ink outline-none placeholder:text-muted/60"
                />
              </div>
            </label>
          </div>

          {error && (
            <p className="mt-4 rounded-lg bg-danger-bg px-3 py-2 text-sm text-danger">{error}</p>
          )}

          <button
            type="submit"
            disabled={isSubmitting}
            className="mt-6 w-full rounded-lg bg-wine py-2.5 text-sm font-medium text-paper transition-colors hover:bg-wine-light disabled:opacity-60"
          >
            {isSubmitting ? 'Signing in…' : 'Sign in'}
          </button>
        </form>
      </div>
    </div>
  );
}

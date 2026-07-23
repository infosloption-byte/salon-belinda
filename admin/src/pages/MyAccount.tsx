import { useEffect, useState, type FormEvent } from 'react';
import { fetchAccount, updateAccount, type DashboardUser } from '../lib/api';

export function MyAccount() {
  const [user, setUser] = useState<DashboardUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    fetchAccount()
      .then((res) => setUser(res.user))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load account.'))
      .finally(() => setIsLoading(false));
  }, []);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setMessage(null);
    setError(null);
    const form = new FormData(e.currentTarget);
    const newPassword = String(form.get('password') || '');

    try {
      const res = await updateAccount({
        name: String(form.get('name')),
        email: String(form.get('email')),
        current_password: String(form.get('current_password') || ''),
        password: newPassword || undefined,
        password_confirmation: newPassword ? String(form.get('password_confirmation') || '') : undefined,
      });
      setUser(res.user);
      setMessage(res.message);
      (e.target as HTMLFormElement).reset();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update your account.');
    }
  }

  if (isLoading) return <p className="text-sm text-muted">Loading account…</p>;
  if (!user) return <p className="mirror-card p-6 text-sm text-danger">{error ?? 'Account not found.'}</p>;

  return (
    <div className="max-w-lg space-y-6">
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {message && <p className="mirror-card p-4 text-sm text-emerald-700">{message}</p>}

      <form onSubmit={handleSubmit} className="mirror-card space-y-4 p-5">
        <div>
          <span className="mb-1 block text-xs text-muted">Name</span>
          <input name="name" required defaultValue={user.name} className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
        </div>
        <div>
          <span className="mb-1 block text-xs text-muted">Email</span>
          <input name="email" type="email" required defaultValue={user.email} className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
        </div>
        <div className="border-t border-ink/10 pt-4">
          <p className="mb-3 text-xs text-muted">Change password (leave blank to keep your current password)</p>
          <div className="space-y-3">
            <input name="current_password" type="password" placeholder="Current password" className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <input name="password" type="password" placeholder="New password" className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            <input name="password_confirmation" type="password" placeholder="Confirm new password" className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          </div>
        </div>
        <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
          Save Changes
        </button>
      </form>
    </div>
  );
}

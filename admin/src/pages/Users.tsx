import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, Pencil, X } from 'lucide-react';
import {
  createUser,
  deleteUser,
  fetchUnlinkedStaff,
  fetchUsers,
  updateUser,
  type DashboardUser,
  type Staff,
  type UserFormData,
} from '../lib/api';

function UserForm({
  user,
  onSaved,
  onCancel,
}: {
  user: DashboardUser | null;
  onSaved: () => void;
  onCancel: () => void;
}) {
  const [role, setRole] = useState<'admin' | 'staff'>(user?.role ?? 'staff');
  const [staffMode, setStaffMode] = useState<'link' | 'new'>(user?.staff_id ? 'link' : 'new');
  const [unlinkedStaff, setUnlinkedStaff] = useState<Staff[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (role === 'staff') {
      fetchUnlinkedStaff(user?.id).then((res) => setUnlinkedStaff(res.unlinkedStaff));
    }
  }, [role, user?.id]);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const data: UserFormData = {
      name: String(form.get('name')),
      email: String(form.get('email')),
      role,
    };
    const password = String(form.get('password') || '');
    if (password) {
      data.password = password;
      data.password_confirmation = String(form.get('password_confirmation') || '');
    }
    if (role === 'staff') {
      if (staffMode === 'link') {
        data.staff_id = Number(form.get('staff_id')) || '';
      } else {
        data.new_staff_name = String(form.get('new_staff_name') || '');
        data.new_staff_role_title = String(form.get('new_staff_role_title') || '');
        data.new_staff_phone = String(form.get('new_staff_phone') || '');
        data.new_staff_commission_percent = Number(form.get('new_staff_commission_percent') || 0);
      }
    }

    try {
      if (user) {
        await updateUser(user.id, data);
      } else {
        await createUser(data);
      }
      onSaved();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save account.');
    }
  }

  return (
    <form onSubmit={handleSubmit} className="mirror-card space-y-3 p-4">
      {error && <p className="text-sm text-danger">{error}</p>}
      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <input name="name" required placeholder="Name" defaultValue={user?.name} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
        <input name="email" type="email" required placeholder="Email" defaultValue={user?.email} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
        <input name="password" type="password" placeholder={user ? 'New password (leave blank to keep)' : 'Password'} required={!user} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
        <input name="password_confirmation" type="password" placeholder="Confirm password" required={!user} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
      </div>

      <div className="flex gap-2">
        {(['admin', 'staff'] as const).map((r) => (
          <button
            key={r}
            type="button"
            onClick={() => setRole(r)}
            className={`rounded-lg px-3 py-1.5 text-xs font-medium capitalize ${
              role === r ? 'bg-wine text-paper' : 'border border-ink/10 text-ink hover:bg-paper-dim'
            }`}
          >
            {r}
          </button>
        ))}
      </div>

      {role === 'staff' && (
        <div className="rounded-lg border border-ink/10 p-3">
          <div className="mb-2 flex gap-2">
            <button
              type="button"
              onClick={() => setStaffMode('link')}
              className={`rounded-lg px-3 py-1 text-xs ${staffMode === 'link' ? 'bg-wine text-paper' : 'border border-ink/10 text-ink'}`}
            >
              Link existing staff profile
            </button>
            <button
              type="button"
              onClick={() => setStaffMode('new')}
              className={`rounded-lg px-3 py-1 text-xs ${staffMode === 'new' ? 'bg-wine text-paper' : 'border border-ink/10 text-ink'}`}
            >
              Create new staff profile
            </button>
          </div>

          {staffMode === 'link' ? (
            <select name="staff_id" defaultValue={user?.staff_id ?? ''} className="w-full rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold">
              <option value="">Select staff member…</option>
              {unlinkedStaff.map((s) => (
                <option key={s.id} value={s.id}>{s.name}</option>
              ))}
            </select>
          ) : (
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <input name="new_staff_name" placeholder="Staff name (defaults to account name)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input name="new_staff_role_title" placeholder="Role title" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input name="new_staff_phone" placeholder="Phone" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
              <input name="new_staff_commission_percent" type="number" min={0} max={100} step="0.01" placeholder="Commission %" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
            </div>
          )}
        </div>
      )}

      <div className="flex gap-2">
        <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
          Save
        </button>
        <button type="button" onClick={onCancel} className="rounded-lg border border-ink/10 px-4 py-2 text-sm text-ink hover:bg-paper-dim">
          Cancel
        </button>
      </div>
    </form>
  );
}

export function Users() {
  const [users, setUsers] = useState<DashboardUser[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [adding, setAdding] = useState(false);
  const [editing, setEditing] = useState<DashboardUser | null>(null);

  function load() {
    setIsLoading(true);
    fetchUsers()
      .then((res) => setUsers(res.users))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load accounts.'))
      .finally(() => setIsLoading(false));
  }

  useEffect(load, []);

  async function handleDelete(user: DashboardUser) {
    if (!confirm(`Delete ${user.name}'s account? They'll immediately lose dashboard access.`)) return;
    try {
      await deleteUser(user.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete account.');
    }
  }

  return (
    <div className="space-y-6">
      {error && (
        <p className="mirror-card flex items-center justify-between p-4 text-sm text-danger">
          {error}
          <button onClick={() => setError(null)} className="text-danger/60 hover:text-danger">
            <X size={16} />
          </button>
        </p>
      )}

      <div className="flex justify-end">
        <button
          onClick={() => {
            setEditing(null);
            setAdding((v) => !v);
          }}
          className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light"
        >
          <Plus size={16} /> Add Account
        </button>
      </div>

      {adding && (
        <UserForm
          user={null}
          onSaved={() => {
            setAdding(false);
            load();
          }}
          onCancel={() => setAdding(false)}
        />
      )}

      {isLoading ? (
        <p className="text-sm text-muted">Loading accounts…</p>
      ) : (
        <div className="mirror-card divide-y divide-ink/5">
          {users.map((user) =>
            editing?.id === user.id ? (
              <div key={user.id} className="p-4">
                <UserForm
                  user={user}
                  onSaved={() => {
                    setEditing(null);
                    load();
                  }}
                  onCancel={() => setEditing(null)}
                />
              </div>
            ) : (
              <div key={user.id} className="flex items-center justify-between gap-4 p-4">
                <div>
                  <p className="text-sm text-ink">{user.name}</p>
                  <p className="text-xs text-muted">
                    {user.email} · <span className="capitalize">{user.role}</span>
                    {user.staff?.name ? ` · linked to ${user.staff.name}` : ''}
                  </p>
                </div>
                <div className="flex shrink-0 items-center gap-2">
                  <button
                    onClick={() => {
                      setAdding(false);
                      setEditing(user);
                    }}
                    className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim"
                  >
                    <Pencil size={14} />
                  </button>
                  <button onClick={() => handleDelete(user)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
                    <Trash2 size={14} />
                  </button>
                </div>
              </div>
            )
          )}
        </div>
      )}
    </div>
  );
}

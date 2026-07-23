import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, Pencil, Power, X } from 'lucide-react';
import { createStaff, deleteStaff, fetchStaff, toggleStaffActive, updateStaff, type Staff as StaffMember } from '../lib/api';

export function Staff() {
  const [staff, setStaff] = useState<StaffMember[]>([]);
  const [statusFilter, setStatusFilter] = useState<'active' | 'inactive'>('active');
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [adding, setAdding] = useState(false);
  const [editing, setEditing] = useState<StaffMember | null>(null);

  function load() {
    setIsLoading(true);
    fetchStaff({ status: statusFilter })
      .then((res) => setStaff(res.staff.data))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load staff.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, [statusFilter]);

  async function handleAdd(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await createStaff({
        name: String(form.get('name')),
        role_title: String(form.get('role_title') || ''),
        phone: String(form.get('phone') || ''),
        commission_percent: Number(form.get('commission_percent') || 0),
      });
      setAdding(false);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to add staff member.');
    }
  }

  async function handleUpdate(e: FormEvent<HTMLFormElement>, member: StaffMember) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    try {
      await updateStaff(member.id, {
        name: String(form.get('name')),
        role_title: String(form.get('role_title') || ''),
        phone: String(form.get('phone') || ''),
        commission_percent: Number(form.get('commission_percent') || 0),
      });
      setEditing(null);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update staff member.');
    }
  }

  async function handleDelete(member: StaffMember) {
    if (!confirm(`Delete ${member.name}?`)) return;
    try {
      await deleteStaff(member.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete staff member.');
    }
  }

  async function handleToggle(member: StaffMember) {
    try {
      await toggleStaffActive(member.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update staff member.');
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

      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex gap-2">
          {(['active', 'inactive'] as const).map((s) => (
            <button
              key={s}
              onClick={() => setStatusFilter(s)}
              className={`rounded-lg px-3 py-1.5 text-xs font-medium capitalize ${
                statusFilter === s ? 'bg-wine text-paper' : 'border border-ink/10 text-ink hover:bg-paper-dim'
              }`}
            >
              {s}
            </button>
          ))}
        </div>
        <button
          onClick={() => setAdding((v) => !v)}
          className="flex items-center gap-1.5 rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light"
        >
          <Plus size={16} /> Add Staff
        </button>
      </div>

      {adding && (
        <form onSubmit={handleAdd} className="mirror-card grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
          <input name="name" required placeholder="Name" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold lg:col-span-2" />
          <input name="role_title" placeholder="Role title (e.g. Senior Stylist)" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <input name="phone" placeholder="Phone" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <input name="commission_percent" type="number" min={0} max={100} step="0.01" required placeholder="Commission %" className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
          <button type="submit" className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light lg:col-span-5 lg:w-fit">
            Save
          </button>
        </form>
      )}

      {isLoading ? (
        <p className="text-sm text-muted">Loading staff…</p>
      ) : staff.length === 0 ? (
        <p className="mirror-card p-6 text-center text-sm text-muted">No {statusFilter} staff.</p>
      ) : (
        <div className="mirror-card divide-y divide-ink/5">
          {staff.map((member) =>
            editing?.id === member.id ? (
              <form
                key={member.id}
                onSubmit={(e) => handleUpdate(e, member)}
                className="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5"
              >
                <input name="name" required defaultValue={member.name} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold lg:col-span-2" />
                <input name="role_title" defaultValue={member.role_title ?? ''} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                <input name="phone" defaultValue={member.phone ?? ''} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                <input name="commission_percent" type="number" min={0} max={100} step="0.01" required defaultValue={member.commission_percent} className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold" />
                <div className="flex gap-2 lg:col-span-5">
                  <button type="submit" className="rounded-lg bg-wine px-3 py-2 text-xs font-medium text-paper hover:bg-wine-light">
                    Save
                  </button>
                  <button type="button" onClick={() => setEditing(null)} className="rounded-lg border border-ink/10 px-3 py-2 text-xs text-ink hover:bg-paper-dim">
                    Cancel
                  </button>
                </div>
              </form>
            ) : (
              <div key={member.id} className="flex items-center justify-between gap-4 p-4">
                <div>
                  <p className="text-sm text-ink">{member.name}</p>
                  <p className="text-xs text-muted">
                    {member.role_title || '—'}
                    {member.phone ? ` · ${member.phone}` : ''} · {member.commission_percent}% commission
                  </p>
                </div>
                <div className="flex shrink-0 items-center gap-2">
                  <button
                    onClick={() => handleToggle(member)}
                    title={member.is_active ? 'Deactivate' : 'Reactivate'}
                    className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim"
                  >
                    <Power size={14} />
                  </button>
                  <button onClick={() => setEditing(member)} className="rounded-lg border border-ink/10 p-1.5 text-ink hover:bg-paper-dim">
                    <Pencil size={14} />
                  </button>
                  <button onClick={() => handleDelete(member)} className="rounded-lg border border-ink/10 p-1.5 text-danger hover:bg-danger-bg">
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

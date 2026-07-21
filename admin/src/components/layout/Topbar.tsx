import { useState } from 'react';
import { ChevronDown, LogOut, User as UserIcon } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';

export function Topbar({ title }: { title: string }) {
  const { user, logout } = useAuth();
  const [menuOpen, setMenuOpen] = useState(false);

  return (
    <header className="sticky top-0 z-10 flex items-center justify-between border-b border-ink/5 bg-paper/90 px-6 py-4 backdrop-blur-sm lg:pl-6">
      <h1 className="font-display text-xl text-ink">{title}</h1>

      <div className="relative">
        <button
          onClick={() => setMenuOpen((v) => !v)}
          className="flex items-center gap-2 rounded-full border border-ink/10 bg-paper px-2 py-1.5 pr-3 text-sm text-ink transition-colors hover:border-gold/50"
        >
          <span className="flex h-7 w-7 items-center justify-center rounded-full bg-rose-light text-rose">
            <UserIcon size={15} />
          </span>
          <span className="hidden sm:inline">{user?.name ?? 'Account'}</span>
          <ChevronDown size={14} className="text-muted" />
        </button>

        {menuOpen && (
          <div className="absolute right-0 mt-2 w-48 overflow-hidden rounded-xl border border-ink/10 bg-paper shadow-lg">
            <div className="border-b border-ink/5 px-4 py-3">
              <p className="text-sm font-medium text-ink">{user?.name}</p>
              <p className="truncate text-xs text-muted">{user?.email}</p>
            </div>
            <button
              onClick={() => logout()}
              className="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-danger hover:bg-danger-bg"
            >
              <LogOut size={15} />
              Log out
            </button>
          </div>
        )}
      </div>
    </header>
  );
}

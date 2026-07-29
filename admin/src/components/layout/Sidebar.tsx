import { useEffect, useRef } from 'react';
import { NavLink } from 'react-router-dom';
import {
  LayoutDashboard,
  Sparkles,
  ShoppingBag,
  Image,
  Images,
  CalendarCheck,
  ClipboardList,
  Users,
  UserSquare2,
  Package,
  Quote,
  MessageSquare,
  BarChart3,
  History,
  UserCog,
  UserCircle,
  X,
} from 'lucide-react';
import { site } from '../../data/site';

const navItems = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/services', label: 'Services', icon: Sparkles },
  { to: '/products', label: 'Products', icon: ShoppingBag },
  { to: '/gallery', label: 'Gallery', icon: Image },
  { to: '/albums', label: 'Wedding Albums', icon: Images },
  { to: '/appointments', label: 'Appointments', icon: CalendarCheck },
  { to: '/jobs', label: 'Jobs', icon: ClipboardList },
  { to: '/staff', label: 'Staff', icon: UserSquare2 },
  { to: '/customers', label: 'Customers', icon: Users },
  { to: '/orders', label: 'Orders', icon: Package },
  { to: '/testimonials', label: 'Testimonials', icon: Quote },
  { to: '/messages', label: 'Contact Messages', icon: MessageSquare },
  { to: '/reports', label: 'Reports', icon: BarChart3 },
  { to: '/activity-log', label: 'Activity Log', icon: History },
  { to: '/users', label: 'Users', icon: UserCog },
  { to: '/account', label: 'My Account', icon: UserCircle },
];

type SidebarProps = {
  /** Whether the mobile drawer is open. Ignored on desktop, where the sidebar is always visible. */
  open?: boolean;
  /** Called when the mobile drawer should close (overlay click, nav click, or the X button). */
  onClose?: () => void;
};

function SidebarContent({ onNavigate }: { onNavigate?: () => void }) {
  return (
    <>
      <nav className="mt-2 flex-1 space-y-1 overflow-y-auto px-3">
        {navItems.map(({ to, label, icon: Icon }) => (
          <NavLink
            key={to}
            to={to}
            end={to === '/'}
            onClick={onNavigate}
            className={({ isActive }) =>
              `flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors ${
                isActive
                  ? 'bg-wine-light text-gold-light'
                  : 'text-paper/70 hover:bg-wine-light/60 hover:text-paper'
              }`
            }
          >
            <Icon size={18} strokeWidth={1.75} />
            <span>{label}</span>
          </NavLink>
        ))}
      </nav>

      <div className="px-6 py-5 text-[11px] text-paper/40">{site.name}</div>
    </>
  );
}

export function Sidebar({ open = false, onClose }: SidebarProps) {
  const drawerRef = useRef<HTMLElement>(null);
  const closeBtnRef = useRef<HTMLButtonElement>(null);
  const previouslyFocusedRef = useRef<HTMLElement | null>(null);

  // Escape-to-close, focus trap while open, and focus restoration on close
  useEffect(() => {
    if (!open) return;

    previouslyFocusedRef.current = document.activeElement as HTMLElement | null;
    closeBtnRef.current?.focus();

    function handleKeyDown(e: KeyboardEvent) {
      if (e.key === 'Escape') {
        onClose?.();
        return;
      }

      if (e.key !== 'Tab' || !drawerRef.current) return;

      const focusable = drawerRef.current.querySelectorAll<HTMLElement>(
        'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
      );
      if (focusable.length === 0) return;

      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }

    document.addEventListener('keydown', handleKeyDown);
    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      previouslyFocusedRef.current?.focus();
    };
  }, [open, onClose]);

  return (
    <>
      {/* Desktop sidebar: always visible at lg+ */}
      <aside className="fixed inset-y-0 left-0 z-20 hidden w-64 flex-col bg-wine text-paper lg:flex">
        <div className="flex items-center gap-3 px-6 py-6">
          <div className="arch flex h-10 w-10 items-center justify-center border border-gold/40 bg-wine-light">
            <span className="font-display text-lg text-gold">{site.name.charAt(0)}</span>
          </div>
          <div>
            <p className="font-display text-base leading-tight text-paper">{site.name}</p>
            <p className="text-[11px] uppercase tracking-[0.2em] text-paper/50">Admin</p>
          </div>
        </div>

        <SidebarContent />
      </aside>

      {/* Mobile overlay */}
      <div
        className={`fixed inset-0 z-30 bg-ink/50 transition-opacity lg:hidden ${
          open ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'
        }`}
        onClick={onClose}
        aria-hidden="true"
      />

      {/* Mobile drawer */}
      <aside
        ref={drawerRef}
        className={`fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-wine text-paper transition-transform duration-200 ease-out lg:hidden ${
          open ? 'translate-x-0' : '-translate-x-full'
        }`}
        role="dialog"
        aria-modal="true"
        aria-label="Mobile navigation"
      >
        <div className="flex items-center justify-between gap-3 px-6 py-6">
          <div className="flex items-center gap-3">
            <div className="arch flex h-10 w-10 items-center justify-center border border-gold/40 bg-wine-light">
              <span className="font-display text-lg text-gold">{site.name.charAt(0)}</span>
            </div>
            <div>
              <p className="font-display text-base leading-tight text-paper">{site.name}</p>
              <p className="text-[11px] uppercase tracking-[0.2em] text-paper/50">Admin</p>
            </div>
          </div>
          <button
            ref={closeBtnRef}
            onClick={onClose}
            aria-label="Close menu"
            className="flex h-8 w-8 items-center justify-center rounded-full text-paper/70 transition-colors hover:bg-wine-light hover:text-paper"
          >
            <X size={18} />
          </button>
        </div>

        <SidebarContent onNavigate={onClose} />
      </aside>
    </>
  );
}

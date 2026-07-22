import { NavLink } from 'react-router-dom';
import {
  LayoutDashboard,
  Sparkles,
  ShoppingBag,
  Image,
  CalendarCheck,
  Users,
  UserSquare2,
  Package,
  Quote,
  MessageSquare,
  BarChart3,
  History,
  UserCog,
} from 'lucide-react';
import { site } from '../../data/site';

const navItems = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/services', label: 'Services', icon: Sparkles },
  { to: '/products', label: 'Products', icon: ShoppingBag },
  { to: '/gallery', label: 'Gallery', icon: Image },
  { to: '/appointments', label: 'Appointments', icon: CalendarCheck },
  { to: '/staff', label: 'Staff', icon: UserSquare2 },
  { to: '/customers', label: 'Customers', icon: Users },
  { to: '/orders', label: 'Orders', icon: Package },
  { to: '/testimonials', label: 'Testimonials', icon: Quote },
  { to: '/messages', label: 'Contact Messages', icon: MessageSquare },
  { to: '/reports', label: 'Reports', icon: BarChart3 },
  { to: '/activity-log', label: 'Activity Log', icon: History },
  { to: '/users', label: 'Users', icon: UserCog },
];

export function Sidebar() {
  return (
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

      <nav className="mt-2 flex-1 space-y-1 overflow-y-auto px-3">
        {navItems.map(({ to, label, icon: Icon }) => (
          <NavLink
            key={to}
            to={to}
            end={to === '/'}
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
    </aside>
  );
}

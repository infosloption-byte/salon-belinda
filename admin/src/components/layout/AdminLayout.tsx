import { useEffect, useState } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { Sidebar } from './Sidebar';
import { Topbar } from './Topbar';

const titles: Record<string, string> = {
  '/': 'Dashboard',
  '/services': 'Services',
  '/products': 'Products',
  '/gallery': 'Gallery',
  '/albums': 'Wedding Albums',
  '/appointments': 'Appointments',
  '/jobs': 'Jobs',
  '/staff': 'Staff',
  '/customers': 'Customers',
  '/orders': 'Orders',
  '/testimonials': 'Testimonials',
  '/messages': 'Contact Messages',
  '/reports': 'Reports',
  '/activity-log': 'Activity Log',
  '/users': 'Users',
};

export function AdminLayout() {
  const { pathname } = useLocation();
  const title = titles[pathname] ?? 'Salon Admin';
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  // Close the drawer whenever the route changes (safety net alongside Sidebar's own nav-click close)
  useEffect(() => {
    setMobileMenuOpen(false);
  }, [pathname]);

  // Lock body scroll while the mobile drawer is open, so the page behind it can't scroll
  useEffect(() => {
    document.body.style.overflow = mobileMenuOpen ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [mobileMenuOpen]);

  // Auto-close the drawer if the viewport grows into the desktop (lg) breakpoint
  useEffect(() => {
    const mql = window.matchMedia('(min-width: 1024px)');
    const handleChange = (e: MediaQueryListEvent) => {
      if (e.matches) setMobileMenuOpen(false);
    };
    mql.addEventListener('change', handleChange);
    return () => mql.removeEventListener('change', handleChange);
  }, []);

  return (
    <div className="min-h-screen bg-paper-dim">
      <Sidebar open={mobileMenuOpen} onClose={() => setMobileMenuOpen(false)} />
      <div className="lg:pl-64">
        <Topbar title={title} onMenuClick={() => setMobileMenuOpen(true)} />
        <main className="p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

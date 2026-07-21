import { Outlet, useLocation } from 'react-router-dom';
import { Sidebar } from './Sidebar';
import { Topbar } from './Topbar';

const titles: Record<string, string> = {
  '/': 'Dashboard',
  '/services': 'Services',
  '/products': 'Products',
  '/gallery': 'Gallery',
  '/appointments': 'Appointments',
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

  return (
    <div className="min-h-screen bg-paper-dim">
      <Sidebar />
      <div className="lg:pl-64">
        <Topbar title={title} />
        <main className="p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

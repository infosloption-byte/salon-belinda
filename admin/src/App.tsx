import { Route, Routes } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';
import { AdminLayout } from './components/layout/AdminLayout';
import { Login } from './pages/Login';
import { Dashboard } from './pages/Dashboard';
import { Services } from './pages/Services';
import { Products } from './pages/Products';
import { Gallery } from './pages/Gallery';
import { Appointments } from './pages/Appointments';
import { ComingSoon } from './pages/ComingSoon';

const pendingModules: { path: string; title: string }[] = [
  { path: 'staff', title: 'Staff' },
  { path: 'customers', title: 'Customers' },
  { path: 'orders', title: 'Orders' },
  { path: 'testimonials', title: 'Testimonials' },
  { path: 'messages', title: 'Contact Messages' },
  { path: 'reports', title: 'Reports' },
  { path: 'activity-log', title: 'Activity Log' },
  { path: 'users', title: 'Users' },
  { path: 'jobs', title: 'Jobs' },
  { path: 'albums', title: 'Wedding Albums' },
];

export default function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route
          path="/"
          element={
            <ProtectedRoute>
              <AdminLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<Dashboard />} />
          <Route path="services" element={<Services />} />
          <Route path="products" element={<Products />} />
          <Route path="gallery" element={<Gallery />} />
          <Route path="appointments" element={<Appointments />} />
          {pendingModules.map(({ path, title }) => (
            <Route key={path} path={path} element={<ComingSoon title={title} />} />
          ))}
        </Route>
      </Routes>
    </AuthProvider>
  );
}

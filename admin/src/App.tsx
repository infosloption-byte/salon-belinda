import { Route, Routes } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';
import { AdminLayout } from './components/layout/AdminLayout';
import { Login } from './pages/Login';
import { Dashboard } from './pages/Dashboard';
import { ComingSoon } from './pages/ComingSoon';

const pendingModules: { path: string; title: string }[] = [
  { path: 'services', title: 'Services' },
  { path: 'products', title: 'Products' },
  { path: 'gallery', title: 'Gallery' },
  { path: 'appointments', title: 'Appointments' },
  { path: 'staff', title: 'Staff' },
  { path: 'customers', title: 'Customers' },
  { path: 'orders', title: 'Orders' },
  { path: 'testimonials', title: 'Testimonials' },
  { path: 'messages', title: 'Contact Messages' },
  { path: 'reports', title: 'Reports' },
  { path: 'activity-log', title: 'Activity Log' },
  { path: 'users', title: 'Users' },
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
          {pendingModules.map(({ path, title }) => (
            <Route key={path} path={path} element={<ComingSoon title={title} />} />
          ))}
        </Route>
      </Routes>
    </AuthProvider>
  );
}

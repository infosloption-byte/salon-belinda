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
import { Albums } from './pages/Albums';
import { Jobs } from './pages/Jobs';
import { Staff } from './pages/Staff';
import { Customers } from './pages/Customers';
import { Users } from './pages/Users';
import { MyAccount } from './pages/MyAccount';
import { ComingSoon } from './pages/ComingSoon';

const pendingModules: { path: string; title: string }[] = [
  { path: 'orders', title: 'Orders' },
  { path: 'testimonials', title: 'Testimonials' },
  { path: 'messages', title: 'Contact Messages' },
  { path: 'reports', title: 'Reports' },
  { path: 'activity-log', title: 'Activity Log' },
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
          <Route path="albums" element={<Albums />} />
          <Route path="jobs" element={<Jobs />} />
          <Route path="staff" element={<Staff />} />
          <Route path="customers" element={<Customers />} />
          <Route path="users" element={<Users />} />
          <Route path="account" element={<MyAccount />} />
          {pendingModules.map(({ path, title }) => (
            <Route key={path} path={path} element={<ComingSoon title={title} />} />
          ))}
        </Route>
      </Routes>
    </AuthProvider>
  );
}

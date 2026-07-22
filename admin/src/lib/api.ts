export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const TOKEN_KEY = 'salon_admin_token';

export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken(): void {
  localStorage.removeItem(TOKEN_KEY);
}

/** Fired whenever a request comes back 401 so AuthContext can log the user out. */
export const UNAUTHORIZED_EVENT = 'salon-admin-unauthorized';

async function request<T>(path: string, options?: RequestInit): Promise<T> {
  const token = getToken();

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options?.headers,
    },
  });

  if (response.status === 401) {
    clearToken();
    window.dispatchEvent(new Event(UNAUTHORIZED_EVENT));
    throw new Error('Your session has expired. Please log in again.');
  }

  const body = await response.json().catch(() => null);

  if (!response.ok) {
    const message =
      body?.message ||
      (body?.errors && (Object.values(body.errors)[0] as string[])?.[0]) ||
      'Something went wrong. Please try again.';
    throw new Error(message);
  }

  return body as T;
}

export const api = {
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, data?: unknown) =>
    request<T>(path, { method: 'POST', body: data ? JSON.stringify(data) : undefined }),
  put: <T>(path: string, data?: unknown) =>
    request<T>(path, { method: 'PUT', body: data ? JSON.stringify(data) : undefined }),
  patch: <T>(path: string, data?: unknown) =>
    request<T>(path, { method: 'PATCH', body: data ? JSON.stringify(data) : undefined }),
  del: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
};

export interface AdminUser {
  id: number;
  name: string;
  email: string;
  role: string;
}

export interface LoginResponse {
  token: string;
  user: AdminUser;
}

export function loginRequest(email: string, password: string): Promise<LoginResponse> {
  return api.post<LoginResponse>('/admin/login', { email, password });
}

export function logoutRequest(): Promise<void> {
  return api.post<void>('/admin/logout');
}

export function fetchCurrentUser(): Promise<AdminUser> {
  return api.get<AdminUser>('/admin/me');
}

export interface DashboardStats {
  todayAppointments: number;
  pendingAppointments: number;
  activeCustomers: number;
  pendingOrders: number;
  todayRevenue: number;
  monthRevenue: number;
  pendingTestimonials: number;
  newMessages: number;
  todayJobs: number;
  todaySalonCash: number;
  outstandingBalance: number;
}

export interface DashboardActivity {
  id: number;
  event: string;
  action: string;
  user: string | null;
  time: string;
}

export interface DashboardResponse {
  role: 'admin' | 'staff';
  redirectTo?: string;
  stats?: DashboardStats;
  recentActivity?: DashboardActivity[];
}

export function fetchDashboard(): Promise<DashboardResponse> {
  return api.get<DashboardResponse>('/admin/dashboard');
}

// --- Services ---

export interface Service {
  id: number;
  service_category_id: number;
  name: string;
  description: string | null;
  duration: string | null;
  price: number;
  price_prefix: string | null;
}

export interface ServiceCategory {
  id: number;
  title: string;
  intro: string | null;
  slug: string;
  sort_order: number;
  services: Service[];
}

export function fetchServiceCategories(): Promise<{ categories: ServiceCategory[] }> {
  return api.get('/admin/services');
}

export function createServiceCategory(data: { title: string; intro?: string }) {
  return api.post<{ category: ServiceCategory; message: string }>('/admin/services/categories', data);
}

export function deleteServiceCategory(id: number) {
  return api.del<{ message: string }>(`/admin/services/categories/${id}`);
}

export function createService(data: {
  service_category_id: number;
  name: string;
  description?: string;
  duration?: string;
  price: number;
  price_prefix?: string;
}) {
  return api.post<{ service: Service; message: string }>('/admin/services', data);
}

export function updateService(
  id: number,
  data: { name: string; description?: string; duration?: string; price: number; price_prefix?: string }
) {
  return api.put<{ service: Service; message: string }>(`/admin/services/${id}`, data);
}

export function deleteService(id: number) {
  return api.del<{ message: string }>(`/admin/services/${id}`);
}

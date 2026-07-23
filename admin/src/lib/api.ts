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

/**
 * Like `request`, but for multipart/form-data bodies (file uploads). Skips
 * the JSON Content-Type header so the browser can set its own boundary.
 * PUT is spoofed via a "_method" field since browsers/fetch can't send a
 * real multipart PUT body reliably — Laravel's method-spoofing middleware
 * reads "_method" from POST bodies (form or multipart) and routes it as PUT.
 */
async function requestForm<T>(path: string, formData: FormData, method: 'POST' | 'PUT' = 'POST'): Promise<T> {
  const token = getToken();

  if (method === 'PUT') {
    formData.append('_method', 'PUT');
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: formData,
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
  postForm: <T>(path: string, formData: FormData) => requestForm<T>(path, formData, 'POST'),
  putForm: <T>(path: string, formData: FormData) => requestForm<T>(path, formData, 'PUT'),
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

// --- Products ---

export interface ProductCategoryItem {
  id: number;
  name: string;
  slug: string;
  sort_order: number;
}

export interface Product {
  id: number;
  slug: string;
  name: string;
  category: string;
  price: number;
  compare_at_price: number | null;
  description: string | null;
  details: string[];
  images: string[];
  in_stock: boolean;
  stock_count: number;
  rating: string | null;
  review_count: number;
  best_seller: boolean;
  is_new: boolean;
}

export interface PaginatedProducts {
  data: Product[];
  current_page: number;
  last_page: number;
  total: number;
}

export function fetchProducts(category?: string): Promise<{ products: PaginatedProducts; categories: ProductCategoryItem[] }> {
  return api.get(`/admin/products${category ? `?category=${encodeURIComponent(category)}` : ''}`);
}

export function createProduct(formData: FormData) {
  return api.postForm<{ product: Product; message: string }>('/admin/products', formData);
}

export function updateProduct(id: number, formData: FormData) {
  return api.putForm<{ product: Product; message: string }>(`/admin/products/${id}`, formData);
}

export function deleteProduct(id: number) {
  return api.del<{ message: string }>(`/admin/products/${id}`);
}

export function createProductCategory(name: string) {
  return api.post<{ category: ProductCategoryItem; message: string }>('/admin/products/categories', { name });
}

export function updateProductCategory(id: number, name: string) {
  return api.put<{ category: ProductCategoryItem; message: string }>(`/admin/products/categories/${id}`, { name });
}

export function deleteProductCategory(id: number) {
  return api.del<{ message: string }>(`/admin/products/categories/${id}`);
}

// --- Gallery ---

export interface GalleryCategoryItem {
  id: number;
  name: string;
  slug: string;
  sort_order: number;
}

export interface GalleryItem {
  id: number;
  category: string;
  title: string;
  image: string;
  sort_order: number;
}

export interface PaginatedGalleryItems {
  data: GalleryItem[];
  current_page: number;
  last_page: number;
  total: number;
}

export function fetchGallery(): Promise<{ items: PaginatedGalleryItems; categories: GalleryCategoryItem[] }> {
  return api.get('/admin/gallery');
}

export function createGalleryItem(formData: FormData) {
  return api.postForm<{ item: GalleryItem; message: string }>('/admin/gallery', formData);
}

export function deleteGalleryItem(id: number) {
  return api.del<{ message: string }>(`/admin/gallery/${id}`);
}

export function createGalleryCategory(name: string) {
  return api.post<{ category: GalleryCategoryItem; message: string }>('/admin/gallery/categories', { name });
}

export function updateGalleryCategory(id: number, name: string) {
  return api.put<{ category: GalleryCategoryItem; message: string }>(`/admin/gallery/categories/${id}`, { name });
}

export function deleteGalleryCategory(id: number) {
  return api.del<{ message: string }>(`/admin/gallery/categories/${id}`);
}

// --- Appointments ---

export type AppointmentStatus = 'pending' | 'confirmed' | 'completed' | 'cancelled';

export interface Appointment {
  id: number;
  service_id: number | null;
  service_name: string;
  name: string;
  phone: string;
  email: string | null;
  date: string;
  time: string;
  notes: string | null;
  status: AppointmentStatus;
}

export interface PaginatedAppointments {
  data: Appointment[];
  current_page: number;
  last_page: number;
  total: number;
}

export function fetchAppointments(params?: { status?: string; q?: string; date_from?: string; date_to?: string }): Promise<{ appointments: PaginatedAppointments }> {
  const query = new URLSearchParams(Object.entries(params ?? {}).filter(([, v]) => v) as [string, string][]).toString();
  return api.get(`/admin/appointments${query ? `?${query}` : ''}`);
}

export function updateAppointmentStatus(id: number, status: AppointmentStatus) {
  return api.patch<{ appointment: Appointment; message: string }>(`/admin/appointments/${id}/status`, { status });
}

export function deleteAppointment(id: number) {
  return api.del<{ message: string }>(`/admin/appointments/${id}`);
}

// --- Wedding Albums ---

export interface AlbumPhoto {
  id: number;
  album_id: number;
  image: string;
  caption: string | null;
  sort_order: number;
}

export interface Album {
  id: number;
  title: string;
  slug: string;
  couple_names: string | null;
  event_date: string | null;
  location: string | null;
  category: string | null;
  description: string | null;
  cover_image: string | null;
  is_published: boolean;
  photos_count?: number;
  photos?: AlbumPhoto[];
}

export interface PaginatedAlbums {
  data: Album[];
  current_page: number;
  last_page: number;
  total: number;
}

export function fetchAlbums(q?: string): Promise<{ albums: PaginatedAlbums }> {
  return api.get(`/admin/albums${q ? `?q=${encodeURIComponent(q)}` : ''}`);
}

export function fetchAlbum(id: number): Promise<{ album: Album }> {
  return api.get(`/admin/albums/${id}`);
}

export function createAlbum(formData: FormData) {
  return api.postForm<{ album: Album; message: string }>('/admin/albums', formData);
}

export function updateAlbum(id: number, formData: FormData) {
  return api.putForm<{ album: Album; message: string }>(`/admin/albums/${id}`, formData);
}

export function deleteAlbum(id: number) {
  return api.del<{ message: string }>(`/admin/albums/${id}`);
}

export function deleteAlbumPhoto(albumId: number, photoId: number) {
  return api.del<{ message: string }>(`/admin/albums/${albumId}/photos/${photoId}`);
}

// --- Jobs (daily ops) ---

export type JobStatus = 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
export type DiscountType = 'none' | 'percent' | 'fixed';
export type PaymentMethod = 'cash' | 'card' | 'bank_transfer';

export interface JobCustomer {
  id: number;
  name: string;
  phone: string;
  email: string | null;
  notes: string | null;
}

export interface JobItemRow {
  id: number;
  job_id: number;
  service_id: number | null;
  service_name: string;
  staff_id: number;
  base_price: number;
  discount_type: DiscountType;
  discount_value: string;
  final_price: number;
  commission_percent: string;
  commission_amount: number;
  staff?: { id: number; name: string };
}

export interface JobPaymentRow {
  id: number;
  job_id: number;
  amount: number;
  method: PaymentMethod;
  paid_at: string;
  note: string | null;
  recorded_by: number;
  recordedBy?: { id: number; name: string };
}

export interface SalonJob {
  id: number;
  customer_id: number;
  appointment_id: number | null;
  status: JobStatus;
  job_date: string;
  notes: string | null;
  subtotal: number;
  total_paid: number;
  balance_due: number;
  customer?: JobCustomer;
  items?: JobItemRow[];
  payments?: JobPaymentRow[];
}

export interface PaginatedJobs {
  data: SalonJob[];
  current_page: number;
  last_page: number;
  total: number;
}

export interface ActiveStaffMember {
  id: number;
  name: string;
  role_title: string | null;
  commission_percent: string;
  is_active: boolean;
}

export function fetchJobs(params?: { status?: string; q?: string; from?: string; to?: string }): Promise<{ jobs: PaginatedJobs }> {
  const query = new URLSearchParams(Object.entries(params ?? {}).filter(([, v]) => v) as [string, string][]).toString();
  return api.get(`/admin/jobs${query ? `?${query}` : ''}`);
}

export function fetchJobCreateData(params?: { q?: string; customer_id?: number; appointment_id?: number }): Promise<{
  customers: JobCustomer[];
  selectedCustomer: JobCustomer | null;
  selectedCustomerVisitCount: number;
  appointment: Appointment | null;
}> {
  const query = new URLSearchParams(
    Object.entries(params ?? {}).filter(([, v]) => v).map(([k, v]) => [k, String(v)])
  ).toString();
  return api.get(`/admin/jobs/create${query ? `?${query}` : ''}`);
}

export function quickRegisterCustomer(data: { name: string; phone: string; email?: string }) {
  return api.post<{ customer: JobCustomer; message: string }>('/admin/jobs/quick-register-customer', data);
}

export function createJob(data: { customer_id: number; appointment_id?: number; job_date: string; status: JobStatus; notes?: string }) {
  return api.post<{ job: SalonJob; message: string }>('/admin/jobs', data);
}

export function fetchJob(id: number): Promise<{ job: SalonJob; serviceCategories: ServiceCategory[]; activeStaff: ActiveStaffMember[] }> {
  return api.get(`/admin/jobs/${id}`);
}

export function addJobItem(jobId: number, data: {
  service_id?: number;
  custom_name?: string;
  custom_price?: number;
  staff_id: number;
  discount_type: DiscountType;
  discount_value?: number;
}) {
  return api.post<{ job: SalonJob; message: string }>(`/admin/jobs/${jobId}/items`, data);
}

export function removeJobItem(jobId: number, itemId: number) {
  return api.del<{ job: SalonJob; message: string }>(`/admin/jobs/${jobId}/items/${itemId}`);
}

export function addJobPayment(jobId: number, data: { amount: number; method: PaymentMethod; paid_at?: string; note?: string }) {
  return api.post<{ job: SalonJob; message: string }>(`/admin/jobs/${jobId}/payments`, data);
}

export function removeJobPayment(jobId: number, paymentId: number) {
  return api.del<{ job: SalonJob; message: string }>(`/admin/jobs/${jobId}/payments/${paymentId}`);
}

export function updateJobStatus(jobId: number, status: JobStatus) {
  return api.patch<{ job: SalonJob; message: string }>(`/admin/jobs/${jobId}/status`, { status });
}

/**
 * Receipt PDFs require the Bearer token, so a plain <a href> won't work —
 * fetch as a blob (with auth header) and hand back an object URL the
 * caller can window.open() or trigger a download from.
 */
async function fetchReceiptBlob(jobId: number, download: boolean): Promise<string> {
  const token = getToken();
  const response = await fetch(`${API_BASE_URL}/admin/jobs/${jobId}/receipt${download ? '/download' : ''}`, {
    headers: token ? { Authorization: `Bearer ${token}` } : {},
  });

  if (!response.ok) {
    throw new Error('Failed to load receipt PDF.');
  }

  const blob = await response.blob();

  return URL.createObjectURL(blob);
}

export function openJobReceipt(jobId: number) {
  fetchReceiptBlob(jobId, false).then((url) => window.open(url, '_blank'));
}

export function downloadJobReceipt(jobId: number) {
  fetchReceiptBlob(jobId, true).then((url) => {
    const a = document.createElement('a');
    a.href = url;
    a.download = `receipt-job-${jobId}.pdf`;
    a.click();
  });
}

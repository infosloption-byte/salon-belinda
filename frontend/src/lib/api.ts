export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

async function request<T>(path: string, options?: RequestInit): Promise<T> {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    ...options,
  });

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

export interface ApiService {
  id: number;
  name: string;
  description?: string;
  duration?: string;
  price: string;
}

export interface ApiServiceCategory {
  id: string;
  title: string;
  intro?: string;
  services: ApiService[];
}

export function fetchServiceCategories(): Promise<ApiServiceCategory[]> {
  return request<ApiServiceCategory[]>('/services');
}

export interface AppointmentPayload {
  name: string;
  phone: string;
  email: string;
  service_id: number;
  date: string;
  time: string;
  notes?: string;
}

export interface AppointmentResult {
  message: string;
  appointment: {
    id: number;
    service_id: number | null;
    service_name: string | null;
    name: string;
    phone: string;
    email: string | null;
    date: string;
    time: string;
    notes: string | null;
    status: string;
  };
}

export function createAppointment(payload: AppointmentPayload): Promise<AppointmentResult> {
  return request<AppointmentResult>('/appointments', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export interface AlbumSummary {
  id: number;
  title: string;
  slug: string;
  couple_names: string | null;
  event_date: string | null;
  location: string | null;
  category: string | null;
  cover_image: string | null;
  photos_count: number;
}

export interface AlbumPhoto {
  id: number;
  image: string;
  caption: string | null;
}

export interface AlbumDetail extends AlbumSummary {
  description: string | null;
  photos: AlbumPhoto[];
}

export function fetchAlbums(params?: { q?: string; category?: string }): Promise<AlbumSummary[]> {
  const search = new URLSearchParams();
  if (params?.q) search.set('q', params.q);
  if (params?.category) search.set('category', params.category);
  const qs = search.toString();
  return request<AlbumSummary[]>(`/albums${qs ? `?${qs}` : ''}`);
}

export function fetchAlbumCategories(): Promise<string[]> {
  return request<string[]>('/albums/categories');
}

export function fetchAlbum(slug: string): Promise<AlbumDetail> {
  return request<AlbumDetail>(`/albums/${slug}`);
}

import type { Product } from '../data/products';

export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

function toCamel(key: string): string {
  return key.replace(/_([a-z0-9])/g, (_, c) => c.toUpperCase());
}

/** Laravel returns snake_case JSON; the app's TS types are camelCase. */
function camelizeKeys<T>(input: unknown): T {
  if (Array.isArray(input)) {
    return input.map((item) => camelizeKeys(item)) as T;
  }
  if (input !== null && typeof input === 'object') {
    return Object.fromEntries(
      Object.entries(input as Record<string, unknown>).map(([key, value]) => [
        toCamel(key),
        camelizeKeys(value),
      ])
    ) as T;
  }
  return input as T;
}

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

  return camelizeKeys<T>(body);
}

export interface ProductFilters {
  category?: string;
  q?: string;
  sort?: string;
  inStock?: boolean;
}

export function fetchProducts(filters: ProductFilters = {}): Promise<Product[]> {
  const params = new URLSearchParams();
  if (filters.category) params.set('category', filters.category);
  if (filters.q) params.set('q', filters.q);
  if (filters.sort) params.set('sort', filters.sort);
  if (filters.inStock) params.set('inStock', '1');

  const query = params.toString();

  return request<Product[]>(`/products${query ? `?${query}` : ''}`);
}

export function fetchProductBySlug(slug: string): Promise<Product> {
  return request<Product>(`/products/${slug}`);
}

export interface OrderPayload {
  lines: { productId: number; quantity: number }[];
  fulfilment: 'delivery' | 'pickup';
  payment: 'cod' | 'bank' | 'card';
  customer: {
    fullName: string;
    phone: string;
    email?: string;
    address?: string;
    city?: string;
    notes?: string;
  };
}

export interface OrderResult {
  id: number;
  orderNumber: string;
  status: string;
  paymentMethod: 'cod' | 'bank' | 'card';
  paymentStatus: 'pending' | 'paid' | 'failed';
  transactionId: string | null;
  subtotal: number;
  deliveryFee: number;
  total: number;
}

export function createOrder(payload: OrderPayload): Promise<OrderResult> {
  return request<OrderResult>('/orders', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

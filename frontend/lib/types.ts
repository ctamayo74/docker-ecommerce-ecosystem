export type Product = {
  id: number;
  name: string;
  description: string;
  price: number;
  stock: number;
  imageUrl: string | null;
};

export type OrderItem = {
  productId: number;
  name: string;
  quantity: number;
  unitPrice: number;
};

export type Order = {
  id: number;
  status: string;
  total: number;
  createdAt: string | null;
  items: OrderItem[];
};

export type Page<T> = { items: T[]; currentPage: number; lastPage: number };

export type FormState =
  | { error?: string; fieldErrors?: Record<string, string[]> }
  | undefined;

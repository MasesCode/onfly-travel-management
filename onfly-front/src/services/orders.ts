import api from './api';
import type { Order } from '@/types';

export interface CreateOrderRequest {
  destination: string;
  departure_date: string;
  return_date: string;
}

export interface UpdateOrderRequest {
  destination: string;
  departure_date: string;
  return_date: string;
}

export interface OrderFilters {
  status?: string;
  destination?: string;
  start_date?: string;
  end_date?: string;
}

export class OrderService {
  static async getOrders(filters: OrderFilters = {}): Promise<Order[]> {
    const params = new URLSearchParams();

    Object.entries(filters).forEach(([key, value]) => {
      if (value) {
        params.append(key, value);
      }
    });

    const queryString = params.toString();
    const url = queryString ? `/orders?${queryString}` : '/orders';

    const response = await api.get(url);
    return response.data.data || response.data;
  }

  static async getOrder(id: number): Promise<Order> {
    const response = await api.get(`/orders/${id}`);
    return response.data.data || response.data;
  }

  static async createOrder(data: CreateOrderRequest): Promise<Order> {
    const response = await api.post('/orders', data);
    return response.data.data || response.data;
  }

  static async updateOrder(id: number, data: UpdateOrderRequest): Promise<Order> {
    const response = await api.put(`/orders/${id}`, data);
    return response.data.data || response.data;
  }

  static async updateOrderStatus(id: number, status: string): Promise<Order> {
    const response = await api.patch(`/orders/${id}/status`, { status });
    return response.data.data || response.data;
  }

  static async deleteOrder(id: number): Promise<void> {
    await api.delete(`/orders/${id}`);
  }
}

export default OrderService;

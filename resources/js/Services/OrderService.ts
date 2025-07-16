export interface Order {
  id: number;
  user_id: number;
  requester: string;
  destination: string;
  start_date: string;
  end_date: string;
  status: string;
  passenger_name?: string;
  travel_date?: string;
  notes?: string;
  created_at?: string;
  updated_at?: string;
}

export interface OrderFilters {
  status?: string;
  destination?: string;
  start_date?: string;
  end_date?: string;
}

export default class OrderService {
  static async getOrders(filters: OrderFilters = {}): Promise<Order[]> {
    const response = await window.axios.get('/api/orders', { params: filters });
    return response.data;
  }

  static async createOrder(orderData: any): Promise<Order> {
    const response = await window.axios.post('/api/orders', orderData);
    return response.data;
  }

  static async updateOrder(orderId: number, orderData: any): Promise<Order> {
    const response = await window.axios.put(`/api/orders/${orderId}`, orderData);
    return response.data;
  }

  static async updateOrderStatus(orderId: number, status: string): Promise<Order> {
    const response = await window.axios.patch(`/api/orders/${orderId}/status`, { status });
    return response.data;
  }
}

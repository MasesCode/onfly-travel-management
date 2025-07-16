export interface User {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface UserFilters {
  search?: string;
  is_admin?: boolean;
}

export default class UserService {
  static async getUsers(filters: UserFilters = {}): Promise<User[]> {
    const response = await window.axios.get('/api/users', { params: filters });
    return response.data;
  }

  static async createUser(userData: any): Promise<User> {
    const response = await window.axios.post('/api/users', userData);
    return response.data;
  }

  static async updateUser(userId: number, userData: any): Promise<User> {
    const response = await window.axios.put(`/api/users/${userId}`, userData);
    return response.data;
  }

  static async deleteUser(userId: number): Promise<void> {
    await window.axios.delete(`/api/users/${userId}`);
  }
}

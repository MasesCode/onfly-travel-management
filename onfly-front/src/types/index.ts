export interface User {
  id: number
  name: string
  email: string
  is_admin: boolean
  created_at: string
  updated_at: string
}

export interface OrderStatus {
  id: number
  name: string
  is_custom: boolean
  created_at: string
  updated_at: string
}

export interface Order {
  id: number
  user_id: number
  requester: string
  destination: string
  start_date: string
  end_date: string
  status: string
  notes?: string
  created_at: string
  updated_at: string
}

export interface Travel {
  id: number
  order_id: number
  pickup_address: string
  delivery_address: string
  recipient_name: string
  recipient_email: string
  recipient_cpf: string
  weight?: number
  height?: number
  width?: number
  length?: number
  is_private_send: boolean
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface CreateOrderData {
  user_id?: number
  destination: string
  departure_date: string
  return_date: string
}

export interface CreateUserData {
  name: string
  email: string
  password: string
  is_admin?: boolean
}

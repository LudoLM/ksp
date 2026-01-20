/**
 * API response-related TypeScript types and interfaces
 */

export interface ApiResponse<T> {
  data: T
  message?: string
  status: number
  timestamp: string
}

export interface ApiListResponse<T> {
  data: T[]
  pagination?: {
    page: number
    limit: number
    total: number
    pages: number
  }
  message?: string
  status: number
}

export interface ApiErrorResponse {
  message: string
  status: number
  errors?: Record<string, string[]>
  timestamp: string
}

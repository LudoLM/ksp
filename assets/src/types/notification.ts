/**
 * Notification/Alert-related TypeScript types and interfaces
 */

export interface Notification {
  id: string
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration?: number
  timestamp: number
}

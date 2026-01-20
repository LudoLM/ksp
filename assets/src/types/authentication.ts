/**
 * Authentication-related TypeScript types and interfaces
 */

import { User } from './user'

export interface AuthLoginRequest {
  email: string
  password: string
}

export interface AuthLoginResponse {
  token: string
  refreshToken: string
  user: User
}

export interface AuthTokens {
  accessToken: string
  refreshToken: string
}

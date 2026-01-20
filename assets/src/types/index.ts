/**
 * Frontend TypeScript Types & Interfaces
 * Central export point for all types - synchronized with backend entities (Symfony)
 * 
 * Organization:
 * - user.ts: User, UserProfile, UsersCours, etc.
 * - cours.ts: Cours, TypeCours, StatusCours, CoursWeekType, etc.
 * - calendar.ts: Calendar-specific types and component props
 * - authentication.ts: Auth login/response types
 * - notification.ts: Notification/Alert types
 * - api.ts: API response wrapper types
 */

// User types
export type {
  User,
  UserProfile,
  CreateUserDTO,
  UpdateUserDTO,
  UsersCours,
  UserFilterParams,
} from './user'
export { UserCoursStatusEnum, UserRoleEnum } from './user'

// Cours types
export type {
  TypeCours,
  StatusCours,
  Cours,
  CoursDetail,
  CalendarCours,
  CoursWeekType,
  WeekType,
  CreateCoursDTO,
  UpdateCoursDTO,
  CoursFilterParams,
} from './cours'
export { StatusCoursEnum } from './cours'

// Calendar types
export type {
  CalendarNextCoursInfo,
  CalendarState,
  CalendarHeaderProps,
  CalendarGridProps,
  CalendarMobileProps,
  CalendarLogicParams,
} from './calendar'

// Authentication types
export type {
  AuthLoginRequest,
  AuthLoginResponse,
  AuthTokens,
} from './authentication'

// Notification types
export type { Notification } from './notification'

// API types
export type {
  ApiResponse,
  ApiListResponse,
  ApiErrorResponse,
} from './api'

// ============================================================================
// CALENDAR TYPES (for backward compatibility during migration)
// ============================================================================
export interface CalendarEvent {
  id: number
  title: string
  start: Date
  end: Date
  coursId?: number
  color?: string
}

export interface CalendarDay {
  date: Date
  isCurrentMonth: boolean
  isToday: boolean
  events?: CalendarEvent[]
}

/**
 * Frontend TypeScript Types & Interfaces
 * Synchronised with backend entities (Symfony)
 */

// ============================================================================
// USER TYPES
// ============================================================================

export interface User {
  id: number
  email: string
  prenom: string
  nom?: string
  roles: string[]
  telephone: string
  adresse?: string
  codePostal?: string
  commune?: string
  nombreCours: number
  isPrioritized: boolean
  createdAt?: string
  updatedAt?: string
}

export interface UserProfile extends User {
  usersCours?: UsersCours[]
}

export interface CreateUserDTO {
  email: string
  password: string
  prenom: string
  nom?: string
  telephone: string
  adresse?: string
  codePostal?: string
  commune?: string
}

export interface UpdateUserDTO {
  prenom?: string
  nom?: string
  telephone?: string
  adresse?: string
  codePostal?: string
  commune?: string
}

// ============================================================================
// TYPE COURS
// ============================================================================

export interface TypeCours {
  id: number
  libelle: string
  descriptif: string
  image?: string
}

// ============================================================================
// COURSE TYPES
// ============================================================================

export interface Course {
  id: number
  titre: string
  description?: string
  prix: number
  status: StatusCoursEnum
  typeCours?: TypeCours
  dateDebut?: string
  dateFin?: string
  professeur?: User
  placesDisponibles?: number
  placesMax?: number
  createdAt?: string
  updatedAt?: string
}

export interface CourseDetail extends Course {
  usersCours?: UsersCours[]
  coursWeekTypes?: CoursWeekType[]
}

export interface CreateCoursDTO {
  titre: string
  description?: string
  prix: number
  status: StatusCoursEnum
  typeCours?: number
  dateDebut?: string
  dateFin?: string
  placesMax?: number
}

export interface UpdateCoursDTO {
  titre?: string
  description?: string
  prix?: number
  status?: StatusCoursEnum
  typeCours?: number
  dateDebut?: string
  dateFin?: string
  placesMax?: number
}

// ============================================================================
// COURSE WEEK TYPE
// ============================================================================

export interface CoursWeekType {
  id: number
  cours: Course
  weekType: WeekType
  jour: string
  heureDebut: string
  heureFin: string
  createdAt?: string
  updatedAt?: string
}

export interface CreateCoursWeekTypeDTO {
  coursId: number
  weekTypeId: number
  jour: string
  heureDebut: string
  heureFin: string
}

// ============================================================================
// WEEK TYPE
// ============================================================================

export interface WeekType {
  id: number
  label: string
  description?: string
}

// ============================================================================
// USER COURS (SUBSCRIPTION)
// ============================================================================

export interface UsersCours {
  id: number
  user: User
  cours: Course
  dateInscription: string
  dateDesistement?: string
  isArchived: boolean
  status: UserCoursStatusEnum
}

// ============================================================================
// ENUMS
// ============================================================================

export enum StatusCoursEnum {
  ACTIVE = 'active',
  ARCHIVED = 'archived',
  DRAFT = 'draft',
  CANCELLED = 'cancelled'
}

export enum UserCoursStatusEnum {
  ACTIVE = 'active',
  DESISTED = 'desisted',
  COMPLETED = 'completed',
  ARCHIVED = 'archived'
}

export enum UserRoleEnum {
  ADMIN = 'ROLE_ADMIN',
  USER = 'ROLE_USER',
  TEACHER = 'ROLE_TEACHER'
}

// ============================================================================
// API RESPONSE TYPES
// ============================================================================

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

// ============================================================================
// AUTHENTICATION
// ============================================================================

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

// ============================================================================
// FORM DATA TYPES
// ============================================================================

export interface CourseFilterParams {
  status?: StatusCoursEnum
  typeCours?: number
  dateDebut?: string
  dateFin?: string
  search?: string
  page?: number
  limit?: number
}

export interface UserFilterParams {
  search?: string
  role?: UserRoleEnum
  isPrioritized?: boolean
  page?: number
  limit?: number
}

// ============================================================================
// NOTIFICATION/ALERT
// ============================================================================

export interface Notification {
  id: string
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  duration?: number
  timestamp: number
}

// ============================================================================
// CALENDAR TYPES
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

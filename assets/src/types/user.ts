/**
 * User-related TypeScript types and interfaces
 * Synchronized with Symfony backend entities
 */

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

export interface UsersCours {
  id: number
  user: User
  cours: any // Forward reference to Cours
  dateInscription: string
  dateDesistement?: string
  isArchived: boolean
  status: UserCoursStatusEnum
  isOnWaitingList?: boolean
}

export enum UserCoursStatusEnum {
  ACTIVE = 'active',
  DESISTED = 'desisted',
  COMPLETED = 'completed',
  ARCHIVED = 'archived',
}

export enum UserRoleEnum {
  ADMIN = 'ROLE_ADMIN',
  USER = 'ROLE_USER',
  TEACHER = 'ROLE_TEACHER',
}

export interface UserFilterParams {
  search?: string
  role?: UserRoleEnum
  isPrioritized?: boolean
  page?: number
  limit?: number
}

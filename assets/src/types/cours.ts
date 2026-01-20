/**
 * Course/Cours-related TypeScript types and interfaces
 * Synchronized with Symfony backend entities
 */

import { User, UsersCours } from './user'

export interface TypeCours {
  id: number
  libelle: string
  descriptif: string
  image?: string
  thumbnail?: string
}

export interface StatusCours {
  id: number
  libelle: string
}

export interface CoursWeekType {
  id: number
  cours: Cours
  weekType: WeekType
  jour: string
  heureDebut: string
  heureFin: string
  createdAt?: string
  updatedAt?: string
}

export interface WeekType {
  id: number
  label: string
  description?: string
}

export interface Cours {
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
  dateCours?: string
  usersCours?: UsersCours[]
  coursWeekTypes?: CoursWeekType[]
  createdAt?: string
  updatedAt?: string
}

export interface CoursDetail extends Cours {
  usersCours?: UsersCours[]
  coursWeekTypes?: CoursWeekType[]
}

export interface CalendarCours extends Cours {
  dateCours: string
  typeCours: TypeCours
  statusCours: StatusCours
  usersCours: UsersCours[]
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

export interface CoursFilterParams {
  status?: StatusCoursEnum
  typeCours?: number
  dateDebut?: string
  dateFin?: string
  search?: string
  page?: number
  limit?: number
}

export enum StatusCoursEnum {
  ACTIVE = 'active',
  ARCHIVED = 'archived',
  DRAFT = 'draft',
  CANCELLED = 'cancelled',
}

/**
 * User-related TypeScript types and interfaces
 * Synchronized with Symfony backend entities
 */
import {Cours} from "@/types/cours.ts";

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
  cours: Cours
  createdAt: string
  unsubscribedAt: string
  isArchived: boolean
  isOnWaitingList?: boolean
}


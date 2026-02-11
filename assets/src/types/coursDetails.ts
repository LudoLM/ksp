export interface StatusCoursDTO {
  id: number
  libelle: string
}

export interface TypeCoursDTO {
  id: number
  libelle: string
  descriptif: string
  thumbnail: string
}

export interface CoursPublicDetailDTO {
  id: number
  dateCours: string
  launchedAt: string | null
  statusCours: StatusCoursDTO
  typeCours: TypeCoursDTO
  duree: number
  nbInscriptionMax: number
  hasPriority: boolean
  hasLimitOfOneCoursPerWeek: boolean
  specialNote: string | null
  activeSubscribedCount: number
  remainingSlots: number
  isSubscribed: boolean
  isUserOnWaitingList: boolean
}

export interface CoursAdminDetailDTO {
  cours: CoursPublicDetailDTO
  usersSubscribed: UsersCoursDTO[]
  usersOnStandby: UsersCoursDTO[]
}

export interface LightUserDTO {
  id: number
  nom: string
  prenom: string
}

export interface UsersCoursDTO {
  id: number
  isOnWaitingList: boolean
  user: LightUserDTO
}

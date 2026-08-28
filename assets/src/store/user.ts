import { defineStore } from 'pinia'
import {reactive, computed, toRef} from 'vue'
import { useCalendarStore } from './calendar'

// Interface pour typer l'utilisateur
interface User {
  email: string
  id: number
  nom: string
  prenom: string
  telephone: string
  commune: string
  adresse: string
  codePostal: string
  nombreCours: number
  roles: string[]
  certificatMedical: UserCertificatMedical | null
}


export interface UserCertificatMedical {
  id: number
  status: string | null
  uploadedAt: Date | null
  validUntil: Date | null
  rejectionReason: string | null

}

interface UserState {
  user: User | null
}

export const useUserStore = defineStore(
  'userStore',
  () => {
    // State - objet unique au lieu de 10 refs
    const state = reactive<UserState>({
      user: null,
    })

    // Actions
    const setUser = (userData: User): void => {
      state.user = { ...userData }
    }

    const clearUser = (): void => {
      state.user = null
    }

    const logout = async (): Promise<void> => {
      try {
        const result = await fetch('/logout', {
          method: 'POST',
        })

        if (result.ok) {
          clearUser()
          useCalendarStore().$reset()

          const { default: router } = await import('../router')
          await router.push({ name: 'Accueil', query: { alert: 'logout' } })
        } else {
          console.error("Erreur lors de l'invalidation du token.")
        }
      } catch (error) {
        console.error('Erreur réseau ou CORS:', error)
      }
    }

    const updateUserNombreCours = (newCount: number): void => {
      if (state.user) {
        state.user.nombreCours = newCount
      }
    }
    // Getters - computed simplifié
    const userId = computed((): number | null => state.user?.id ?? null)
    const userEmail = computed((): string | null => state.user?.email ?? null)
    const userNom = computed((): string | null => state.user?.nom ?? null)
    const userPrenom = computed((): string | null => state.user?.prenom ?? null)
    const userTelephone = computed((): string | null => state.user?.telephone ?? null)
    const userVille = computed((): string | null => state.user?.commune ?? null)
    const userAdresse = computed((): string | null => state.user?.adresse ?? null)
    const userCodePostal = computed((): string | null => state.user?.codePostal ?? null)
    const userNombreCours = computed((): number => state.user?.nombreCours ?? 0)
    const userRoles = computed((): string[] => state.user?.roles ?? [])
    const userCertificatMedical = computed((): UserCertificatMedical | null => state.user?.certificatMedical ?? null)

    // Getters avec ancien nommage (pour compatibilité)
    const getUserId = computed((): number | null => userId.value)
    const getUserEmail = computed((): string | null => userEmail.value)
    const getUserNom = computed((): string | null => userNom.value)
    const getUserPrenom = computed((): string | null => userPrenom.value)
    const getUserTelephone = computed((): string | null => userTelephone.value)
    const getUserVille = computed((): string | null => userVille.value)
    const getUserAdresse = computed((): string | null => userAdresse.value)
    const getUserCodePostal = computed((): string | null => userCodePostal.value)
    const getUserNombreCours = computed((): number => userNombreCours.value)
    const getUserCertificatMedical = computed((): UserCertificatMedical | null => userCertificatMedical.value)

    // Computed pour les états dérivés
    const isAuthenticated = computed((): boolean => !!state.user?.id)
    const isAdmin = computed((): boolean => state.user?.roles.includes('ROLE_ADMIN') ?? false)

    return {
      // State - accès direct à l'objet user
      user: toRef(state, 'user'),
      // Actions
      setUser,
      clearUser,
      logout,
      updateUserNombreCours,
      // Getters (nouveau nommage - recommandé)
      userId,
      userEmail,
      userNom,
      userPrenom,
      userTelephone,
      userVille,
      userAdresse,
      userCodePostal,
      userNombreCours,
      userRoles,
      userCertificatMedical,
      // Getters (ancien nommage - compatibilité)
      getUserId,
      getUserEmail,
      getUserNom,
      getUserPrenom,
      getUserTelephone,
      getUserVille,
      getUserAdresse,
      getUserCodePostal,
      getUserNombreCours,
      getUserCertificatMedical,
      // États dérivés
      isAuthenticated,
      isAdmin,
    }
  },
  {
    persist: {
      enabled: true,
      strategies: [
        {
          key: 'userStore',
          storage: localStorage,
          paths: ['user'],
        },
      ],
    } as any,
  }
)

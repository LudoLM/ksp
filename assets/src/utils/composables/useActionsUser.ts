/**
 * Composable pour gérer les actions utilisateur
 * Gère le profil, historique, paiements
 */

import { useUserStore } from '@/store/user'
import { apiFetch } from '@/utils/useFetchInterceptor'
import { computed, ref } from 'vue'
import { alertStore } from '@/store/alert'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'

interface UserProfileData {
  userId: number | null
  prenom: string | null
  nom: string | null
  email: string | null
  nombreCours: number | null
  telephone: string | null
  codePostal: string | null
  adresse: string | null
  commune: string | null
}

interface DeleteUserResponse {
  success: boolean
  isOwnProfile?: boolean
}

export function useActionsUser() {
  const userCoursHistory = ref<any[]>([])
  const currentUserNewCount = ref<number | null>(null)
  const userPaymentsHistory = ref<{ historiquePaiements: any[] }>({ historiquePaiements: [] })
  const route = useRoute()
  const userStore = useUserStore()

  const isViewingOtherUser = computed((): boolean => {
    return route.path.startsWith('/admin')
  })

  // Données du profil affiché
  const currentUser = ref<UserProfileData>({
    userId: null,
    prenom: null,
    nom: null,
    email: null,
    nombreCours: null,
    telephone: null,
    codePostal: null,
    adresse: null,
    commune: null,
  })

  /**
   * Récupère les données de l'utilisateur connecté
   */
  const getUser = async (): Promise<void> => {
    try {
      const response = await apiFetch('/user', {
        method: 'GET',
        credentials: 'include',
      })

      if (response.ok) {
        const data = await response.json()
        userStore.setUser(data)
      } else {
        userStore.clearUser()
      }
    } catch (error) {
      userStore.clearUser()
    }
  }

  /**
   * Supprime un utilisateur
   * @param userId - ID de l'utilisateur à supprimer (optionnel)
   */
  const deleteUser = async (userId?: number): Promise<DeleteUserResponse> => {
    try {
      const targetUserId = userId || (isViewingOtherUser.value ? (route.params.id as string | number) : userStore.userId)
      const response = await apiFetch(`/delete-user/${targetUserId}`, {
        method: 'DELETE',
      })

      if (response.ok) {
        alertStore.setAlert('Profil supprimé avec succès', 'success')
        return {
          success: true,
          isOwnProfile: !isViewingOtherUser.value,
        }
      } else {
        const errorData = await response.json()
        alertStore.setAlert(errorData.message || 'Erreur lors de la suppression du profil', 'error')
        return { success: false }
      }
    } catch (error) {
      alertStore.setAlert('Erreur lors de la suppression du profil', 'error')
      return { success: false }
    }
  }

  /**
   * Charge l'historique des cours de l'utilisateur
   * @param userId - ID de l'utilisateur (optionnel)
   * @param newPage - Numéro de la page (optionnel)
   */
   const loadUserCoursHistory = async (userId?: number, newPage?: number): Promise<void> => {
     try {
       const targetUserId = userId || userStore.userId
       const response = await apiFetch(`/user/${targetUserId}/cours-history?page=${newPage || 1}`)
       userCoursHistory.value = await response.json()
     } catch (error) {
       // Silent error for now
     }
   }

  /**
   * Charge l'historique des paiements de l'utilisateur
   * @param userId - ID de l'utilisateur (optionnel)
   */
  const loadUserPaymentsHistory = async (userId?: number): Promise<void> => {
    try {
      const targetUserId = userId || userStore.userId
      const response = await apiFetch(`/user/${targetUserId}/payments-history`)
      userPaymentsHistory.value = await response.json()
    } catch (error) {
      // Silent error for now
    }
  }

  /**
   * Charge les données du profil utilisateur
   * @param userId - ID de l'utilisateur à charger
   */
  const loadProfileData = async (userId?: number | string): Promise<void> => {
    try {
      if (userId && userStore.isAdmin) {
        // Admin consulte le profil d'un autre utilisateur
        const result = await apiFetch(`/user/${userId}`)
        currentUser.value = await result.json()
        currentUserNewCount.value = currentUser.value.nombreCours
      } else {
        // Utilisateur consulte son propre profil
        const { getUserId: userId, getUserPrenom: userPrenom, getUserNom: userNom, getUserEmail: userEmail, getUserNombreCours: userNombreCours, getUserTelephone: userTelephone, getUserCodePostal: userCodePostal, getUserAdresse: userAdresse, getUserVille: userVille } = storeToRefs(userStore)

        currentUser.value = {
          userId: userId.value,
          prenom: userPrenom.value,
          nom: userNom.value,
          email: userEmail.value,
          nombreCours: userNombreCours.value,
          telephone: userTelephone.value,
          codePostal: userCodePostal.value,
          adresse: userAdresse.value,
          commune: userVille.value,
        }
      }
    } catch (error) {
      alertStore.setAlert('Erreur lors du chargement du profil', 'error')
    }
  }

  /**
   * Met à jour le nombre de cours d'un utilisateur
   * @param newCount - Le nouveau nombre de cours
   */
  const handleUpdateCounterCours = async (newCount: number): Promise<void> => {
    if (newCount === null || isNaN(newCount) || newCount < 0) {
      alertStore.setAlert('Veuillez entrer un nombre valide', 'error')
      return
    }

    try {
      const userId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
      const response = await apiFetch(`/admin/user/${userId}/cours-count`, {
        method: 'PUT',
        body: JSON.stringify({ nombreCours: parseInt(String(newCount)) }),
      })

      if (response.ok) {
        const data = await response.json()
        currentUser.value.nombreCours = data.nombreCours
        currentUserNewCount.value = data.nombreCours

        // Mettre à jour le store si c'est le profil de l'utilisateur connecté
        if (!isViewingOtherUser.value) {
          userStore.updateUserNombreCours(data.nombreCours)
        }

        alertStore.setAlert('Le nombre de cours a été mis à jour', 'success')
        await loadProfileData(userId)
      } else {
        const errorData = await response.json()
        alertStore.setAlert(errorData.message || 'Erreur lors de la mise à jour', 'error')
      }
    } catch (error) {
      alertStore.setAlert('Erreur lors de la mise à jour', 'error')
    }
  }

  return {
    currentUser,
    userCoursHistory,
    currentUserNewCount,
    userPaymentsHistory,
    isViewingOtherUser,
    getUser,
    deleteUser,
    loadUserCoursHistory,
    loadUserPaymentsHistory,
    loadProfileData,
    handleUpdateCounterCours,
  }
}

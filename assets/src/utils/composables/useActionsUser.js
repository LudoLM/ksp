// composables/useActionsUser.js
import { useUserStore } from '../../store/user';
import { apiFetch } from '../useFetchInterceptor';
import {computed, ref} from "vue";
import { alertStore } from '../../store/alert';
import {storeToRefs} from "pinia";
import {useRoute} from "vue-router";



export function useActionsUser() {
  const userCoursHistory = ref([]);
  const currentUserNewCount = ref(null);
  const userPaymentsHistory = ref({ historiquePaiements: []});
  const route = useRoute();
  const userStore = useUserStore();

  const isViewingOtherUser = computed(() => {
    return route.path.startsWith('/admin');
  });

  // Données du profil affiché
  const currentUser = ref({
    userId: null,
    prenom: null,
    nom: null,
    email: null,
    nombreCours: null,
    telephone: null,
    codePostal: null,
    adresse: null,
    commune: null
  });

  const getUser = async () => {
    try {
      const response = await apiFetch('/api/user', {
        method: 'GET',
        credentials: 'include',
      });
      if (response.ok) {
        const data = await response.json();
        userStore.setUser(data);
      } else {
        userStore.clearUser();
      }
    } catch (error) {
      userStore.clearUser();
    }
  };

  const deleteUser = async (userId) => {
    try {
      const targetUserId = userId ? userId : isViewingOtherUser.value ? route.params.id : userStore.userId;
      const response = await apiFetch(`/api/deleteUser/${targetUserId}`, {
        method: 'DELETE'
      });

      if (response.ok) {
        alertStore.setAlert("Profil supprimé avec succès", "success");
        return {
          success: true,
          isOwnProfile: !isViewingOtherUser.value
        }
      } else {
        const errorData = await response.json();
        alertStore.setAlert(errorData.message || "Erreur lors de la suppression du profil", "error");
        return {success: false}
      }
    } catch (error) {
      console.error("Erreur lors de la suppression du profil:", error);
      alertStore.setAlert("Erreur lors de la suppression du profil", "error");
      return {success: false}
    }
  };


  const loadUserCoursHistory = async (userId, newPage) => {
    try {
      const targetUserId = userId || userStore.userId;
      const response = await apiFetch(`/api/userCoursHistory/${targetUserId}?page=${newPage || 1}`);
      userCoursHistory.value = await response.json();
    } catch (error) {
      console.error("Erreur de chargement de l'historique des cours:", error);
    }
  };

// Charger l'historique de l'utilisateur
  const loadUserPaymentsHistory = async (userId) => {
    try {
      const targetUserId = userId || userStore.userId;
      const response = await apiFetch(`/api/userPaymentsHistory/${targetUserId}`);
      userPaymentsHistory.value = await response.json();
    } catch (error) {
      console.error("Erreur de chargement de l'historique des paiements:", error);
    }
  };

  // Charger les données du profil
  const loadProfileData = async (userId) => {
    try {
      if (userId && userStore.isAdmin) {
        // Admin consulte le profil d'un autre utilisateur
        const result = await apiFetch(`/api/user/${userId}`);
        currentUser.value = await result.json();
        currentUserNewCount.value = currentUser.value.nombreCours;
      } else {
        // Utilisateur consulte son propre profil
        const { userId, userPrenom, userNom, userEmail, userNombreCours, userTelephone, userCodePostal, userAdresse, userVille } = storeToRefs(userStore);

        currentUser.value = {
          userId: userId.value,
          prenom: userPrenom.value,
          nom: userNom.value,
          email: userEmail.value,
          nombreCours: userNombreCours.value,
          telephone: userTelephone.value,
          codePostal: userCodePostal.value,
          adresse: userAdresse.value,
          commune: userVille.value
        };
      }
    } catch (error) {
      console.error("Erreur de chargement des données utilisateur:", error);
      alertStore.setAlert("Erreur lors du chargement du profil", "error");
    }
  };

  const handleUpdateCounterCours = async (newCount) => {
    if (newCount === null || isNaN(newCount) || newCount < 0) {
      alertStore.setAlert("Veuillez entrer un nombre valide", "error");
      return;
    }

    try {
      const response = await apiFetch(`/api/updateUserCoursCount/${route.params.id}`, {
        method: 'PUT',
        body: JSON.stringify({ nombreCours: parseInt(newCount) })
      });

      if (response.ok) {
        const data = await response.json();
        currentUser.value.nombreCours = data.nombreCours;
        currentUserNewCount.value = data.nombreCours;

        // Mettre à jour le store si c'est le profil de l'utilisateur connecté
        if (!isViewingOtherUser.value) {
          userStore.userNombreCours = data.nombreCours;
        }

        alertStore.setAlert("Le nombre de cours a été mis à jour", "success");
        await loadProfileData(route.params.id);
      } else {
        const errorData = await response.json();
        alertStore.setAlert(errorData.message || "Erreur lors de la mise à jour", "error");
      }
    } catch (error) {
      console.error("Erreur lors de la mise à jour du nombre de cours:", error);
      alertStore.setAlert("Erreur lors de la mise à jour", "error");
    }
  };

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
    handleUpdateCounterCours
  };
}

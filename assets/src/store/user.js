import { defineStore } from 'pinia';
import { useCalendarStore } from "./calendar";
import router from '../router';


export const useUserStore = defineStore('userStore', {
  state: () => ({
    userEmail: null,
    userId: null,
    userNom: null,
    userPrenom: null,
    userTelephone: null,
    userVille: null,
    userAdresse: null,
    userCodePostal: null,
    userNombreCours: 0,
    userRoles: null,
  }),
  actions: {
    // Action pour mettre à jour toutes les infos utilisateur
    setUser(user) {
      this.userEmail = user.email;
      this.userId = user.id;
      this.userNom = user.nom;
      this.userPrenom = user.prenom;
      this.userTelephone = user.telephone;
      this.userVille = user.commune;
      this.userAdresse = user.adresse;
      this.userCodePostal = user.codePostal;
      this.userNombreCours = user.nombreCours;
      this.userRoles = user.roles;
    },

    // Action pour réinitialiser l'utilisateur
    clearUser() {
      this.userEmail = null;
      this.userId = null;
      this.userNom = null;
      this.userPrenom = null;
      this.userTelephone = null;
      this.userVille = null;
      this.userAdresse = null;
      this.userCodePostal = null;
      this.userNombreCours = 0;
      this.userRoles = null;
    },
    async logout(){
      try {

        const result = await fetch("/logout", {
          method: "POST",
        });
        if (result.ok) {
          console.log("Déconnexion réussie.");
          this.clearUser();
          useCalendarStore().$reset();
          await router.push({name: 'Accueil', query: {alert: 'logout'}});

        } else {
          console.log("Erreur lors de l'invalidation du token. Le serveur n'a pas pu traiter la demande.");
        }
      } catch (error) {
        console.error("Erreur réseau ou CORS:", error);
      }
    },
  },
  getters: {
    getUserId: (state) => state.userId,
    getUserNom: (state) => state.userNom,
    getUserPrenom: (state) => state.userPrenom,
    getUserTelephone: (state) => state.userTelephone,
    getUserVille: (state) => state.userVille,
    getUserAdresse: (state) => state.userAdresse,
    getUserCodePostal: (state) => state.userCodePostal,
    getUserNombreCours: (state) => state.userNombreCours,
    getUserEmail: (state) => state.userEmail,
    getAccessToken: (state) => state.accessToken,
    isAuthenticated: (state) => !!state.userId,
    isAdmin: (state) => state.userRoles?.includes('ROLE_ADMIN') || false,
  },

  persist: {
    enabled: true,
    strategies: [
      {
        key: 'userStore',
        storage: localStorage,
        paths: ['userId'],
      },
    ],
  }
});

import { defineStore } from 'pinia';
import {useCalendarStore} from "./calendar";

export const useUserStore = defineStore('userStore', {


    state: () => ({
        userEmail: null,
        userId: null,
        userNom: null,
        userPrenom: null,
        userNombreCours: 0,
    }),
    actions: {
        setUserEmail(email) {
            this.userEmail = email;
        },

        setUserId(id) {
            this.userId = id;
        },

        setUserNom(nom) {
            this.userNom = nom;
        },

        setUserPrenom(prenom) {
            this.userPrenom = prenom;
        },

        setUserNombreCours(nombre) {
            this.userNombreCours = nombre;
        },

        logout() {
            this.userEmail = null;
            this.userId = null;
            this.userNom = null;
            this.userPrenom = null;
            this.userNombreCours = null;
            localStorage.removeItem('token');
            useCalendarStore().$reset();
        },
    },
    getters: {
        getUserId: (state) => state.userId,
        getUserNom: (state) => state.userNom,
        getUserPrenom: (state) => state.userPrenom,
        getUserNombreCours: (state) => state.userNombreCours,
        getUserEmail: (state) => state.userEmail,
    },
    persist: {
      enabled: true, // Active la persistance pour ce store
      strategies: [
        {
          key: 'userStore', // Une clé unique pour le localStorage
          storage: localStorage, // Utilise le localStorage
          paths: ['userId', 'userToken'], // Les propriétés du store à persister
        },
      ],
    },
});

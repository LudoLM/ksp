import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', {


    state: () => ({
        userEmail: null,
        userId: null,
        userPrenom: null,
        userJWTexp: null,
    }),
    actions: {
        setUserEmail(email) {
            this.userEmail = email;
        },

        setUserId(id) {
            this.userId = id;
        },

        setUserPrenom(prenom) {
            this.userPrenom = prenom;
        },

        setUserJWTExp(exp) {
            this.userJWTexp = exp;
        },

        logout() {
            this.userEmail = null;
            this.userId = null;
            this.userPrenom = null;
            this.userJWTexp = null;
            localStorage.removeItem('token');
        },
    },
    getters: {
        getUserId: (state) => state.userId,
        getUserPrenom: (state) => state.userPrenom,
        getUserEmail: (state) => state.userEmail,
        getUserJWTexp: (state) => state.userJWTexp,
    }
});

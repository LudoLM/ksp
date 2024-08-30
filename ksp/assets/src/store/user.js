import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', {
    state: () => ({
        user: null,
        isAutenticated: false,
    }),
    actions: {
        setUser(user) {
            this.user = user;
        },
        setIsAutenticated() {
            this.isAutenticated = !this.isAutenticated;
        },

    },
    getters: {
        getUserId: (state) => state.user?.id,
        getUser: (state) => state.user,
        getIsAutenticated: (state) => state.isAutenticated
    }
});

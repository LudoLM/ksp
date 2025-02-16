import { defineStore } from "pinia";

export const useCoursStore = defineStore("cours", {
  state: () => ({
    // Un utilisateur peut s'inscrire à un cours jusqu'à 30 minutes avant le début
    timeLimiteToSubscribe: 30 * 60 * 1000,
    timerBeforeBeginning: 6 * 60 * 60 * 1000
  }),
});

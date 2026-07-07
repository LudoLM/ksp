/**
 * Cours Store
 * Manages cours-related constants and timing
 */

import { defineStore } from 'pinia'

interface CoursState {
  /**
   * Délai avant le début d'un cours avant lequel on ne peut plus s'inscrire (en ms)
   * Par défaut: 30 minutes
   */
  timeLimiteToSubscribe: number

  /**
   * Délai avant le début d'un cours (en ms)
   * Par défaut: 6 heures
   */
  timerBeforeBeginning: number
}

export const useCoursStore = defineStore('cours', {
  state: (): CoursState => ({
    // Un utilisateur peut s'inscrire à un cours jusqu'à 30 minutes avant le début
    timeLimiteToSubscribe: 30 * 60 * 1000,

    // Timer de 6 heures avant le début
    timerBeforeBeginning: 6 * 60 * 60 * 1000,
  }),
})



/**
 * Store for calendar management
 * Handles cours calendar state, week navigation, filtering
 */

import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useDateFormat } from '@vueuse/core'
import { useGetCours, useGetOnlyNextCours, useGetTypesCours, useGetStatusCours } from '@/utils/useActionCours'

interface CalendarState {
  date: Date
  daySelected: number
  selectedTypeCours: number
  selectedStatusCours: number
  infos: any[]
  weekInfos: any[][]
  firstNextCoursInNextWeeks: any | null
  uniqueTypeCoursList: any[]
  uniqueStatusCoursList: any[]
  weekString: string
  days: string[]
}

/**
 * Calcule le lundi d'une date donnée
 * @param inputDate - Date d'entrée
 * @returns Le lundi de cette semaine
 */
export const getMondayOfSpecificDate = (inputDate: Date): Date => {
  const date = new Date(inputDate)
  const dayOfWeek = date.getDay()
  date.setDate(date.getDate() - ((dayOfWeek + 6) % 7))
  date.setHours(0, 0, 0, 0)
  return date
}

/**
 * Calcule le dimanche d'une date donnée
 * @param date - Date d'entrée
 * @returns Le dimanche de cette semaine
 */
export const getSundayOfSpecificDate = (date: Date): Date => {
  const dayOfWeek = date.getDay()
  const sunday = new Date(date)
  sunday.setDate(date.getDate() + (7 - dayOfWeek))
  sunday.setHours(23, 59, 59, 999)
  return sunday
}

export const useCalendarStore = defineStore('calendar', {
  state: (): CalendarState => ({
    date: new Date().getDay() === 0 ? new Date(new Date().setDate(new Date().getDate() + 7)) : new Date(),
    daySelected: new Date().getDay() === 0 ? 0 : new Date().getDay() - 1,
    selectedTypeCours: 0,
    selectedStatusCours: 0,
    infos: [],
    weekInfos: [[], [], [], [], [], [], []],
    firstNextCoursInNextWeeks: null,
    uniqueTypeCoursList: [],
    uniqueStatusCoursList: [],
    weekString: '',
    days: [],
  }),

  getters: {
    /**
     * Récupère le lundi de la semaine affichée
     */
    getMondayOfDisplayedWeek: (state): Date => {
      return getMondayOfSpecificDate(state.date)
    },

    /**
     * Récupère le lundi de la semaine courante
     */
    getMondayOfCurrentWeek: (): Date => {
      return getMondayOfSpecificDate(new Date())
    },

    /**
     * Récupère la plage de dates sous forme texte (ex: "01 January 2025 au 06 January 2025")
     */
    getWeekString(): string {
      const monday = this.getMondayOfDisplayedWeek
      const saturday = new Date(monday)
      saturday.setDate(monday.getDate() + 5)

      return useDateFormat(monday, 'DD MMMM YYYY').value + ' au ' + useDateFormat(saturday, 'DD MMMM YYYY').value
    },

    /**
     * Vérifie si le bouton "semaine précédente" doit être désactivé
     */
    shouldPreviousWeekDisabled(): boolean {
      return this.getMondayOfDisplayedWeek.getTime() <= this.getMondayOfCurrentWeek.getTime()
    },
  },

  actions: {
    /**
     * Récupère les cours pour la semaine affichée
     * @param isOpenRequired - Si vrai, récupère uniquement les cours ouverts
     */
    async fetchCoursPerWeek(isOpenRequired: boolean = false): Promise<void> {
      try {
        const dateFormatted = ref(this.date.toISOString().split('T')[0])
        const tempInfos = ref<any[]>([])
        const route = ref('get-cours-calendar')

        await useGetCours(route, tempInfos, this.selectedTypeCours, dateFormatted, this.selectedStatusCours, isOpenRequired)
        this.infos = tempInfos.value

        if (isOpenRequired && this.infos && this.infos.length > 0) {
          this.date = new Date(this.infos[0].dateCours)
          this.setDaySelected(this.date.getDay() - 1)
        }

        if (Array.isArray(this.infos)) {
          this.infos.sort((a, b) => new Date(a.dateCours).getTime() - new Date(b.dateCours).getTime())
          this.weekInfos = [[], [], [], [], [], [], []]
          this.infos.forEach((info) => {
            const rawDay = new Date(info.dateCours).getDay()
            const day = (rawDay + 6) % 7
            if (this.weekInfos[day]) {
              this.weekInfos[day].push(info)
            }
          })
        } else {
          this.weekInfos = [[], [], [], [], [], [], []]
        }

        this.updateDaysOfWeek()

        if (this.weekInfos[6].length === 0 && !this.weekInfos.every((info) => info.length === 0)) {
          this.firstNextCoursInNextWeeks = await useGetOnlyNextCours(this.selectedTypeCours, this.days[5], this.selectedStatusCours)
        }
      } catch (error) {
        this.infos = [{ message: 'Une erreur est survenue lors du chargement des cours.' } as any]
        this.weekInfos = [[], [], [], [], [], [], []]
      }
    },

    /**
     * Récupère la liste des types de cours
     */
    async fetchTypesCours(): Promise<void> {
      this.uniqueTypeCoursList = await useGetTypesCours()
    },

    /**
     * Récupère la liste des statuts de cours
     */
    async fetchStatusCours(): Promise<void> {
      this.uniqueStatusCoursList = await useGetStatusCours()
    },

    /**
     * Met à jour les jours de la semaine affichée
     */
    updateDaysOfWeek(): void {
      const monday = this.getMondayOfDisplayedWeek
      this.days = []

      for (let i = 0; i < 6; i++) {
        const day = new Date(monday)
        day.setDate(monday.getDate() + i)
        this.days.push(useDateFormat(day, 'YYYY-MM-DD').value)
      }

      this.weekString = this.getWeekString
    },

    /**
     * Définit la date courante
     * @param newDate - Nouvelle date
     */
    setDate(newDate: Date): void {
      this.date = new Date(newDate)
    },

    /**
     * Définit le jour sélectionné
     * @param index - Index du jour (0-6)
     */
    setDaySelected(index: number): void {
      this.daySelected = index
    },

    /**
     * Définit le type de cours sélectionné pour le filtrage
     * @param typeId - ID du type de cours
     */
    setSelectedTypeCours(typeId: number): void {
      this.selectedTypeCours = typeId
    },

    /**
     * Définit le statut de cours sélectionné pour le filtrage
     * @param statusId - ID du statut de cours
     */
    setSelectedStatusCours(statusId: number): void {
      this.selectedStatusCours = statusId
    },

    /**
     * Navigue à la semaine suivante
     */
    nextWeek(): void {
      const current = new Date(this.date)
      this.setDaySelected(0)
      current.setDate(current.getDate() + 7)
      this.date = current
    },

    /**
     * Navigue à la semaine précédente
     */
    prevWeek(): void {
      const current = new Date(this.date)
      this.setDaySelected(5)
      current.setDate(current.getDate() - 7)
      this.date = current
    },

    /**
     * Force une mise à jour de la date courante
     */
    nextCours(): void {
      this.date = new Date(this.date)
    },

    /**
     * Réinitialise le calendrier à la date du jour
     */
    resetCalendar(): void {
      this.date = new Date().getDay() === 0 ? new Date(new Date().setDate(new Date().getDate() + 7)) : new Date()
      this.daySelected = new Date().getDay() === 0 ? 0 : new Date().getDay() - 1
      this.setSelectedTypeCours(0)
      this.setSelectedStatusCours(0)
    },
  },
})

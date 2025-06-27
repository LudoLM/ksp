// stores/calendar.js
import {defineStore} from 'pinia';
import {ref} from 'vue';
import {useDateFormat} from "@vueuse/core";
import {useGetCours, useGetOnlyNextCours, useGetTypesCours} from "../utils/useActionCours";

// --- Fonction utilitaire pour calculer le lundi d'une date ---
const getMondayOfSpecificDate = (inputDate) => {
  const date = new Date(inputDate);
  const dayOfWeek = date.getDay();
  date.setDate(date.getDate() - ((dayOfWeek + 6) % 7));
  // Réinitialiser l'heure pour éviter les problèmes de fuseau horaire
  date.setHours(0, 0, 0, 0);
  return date;
};


export const useCalendarStore = defineStore('calendar', {
  state: () => ({
    date: new Date(),
    daySelected: new Date().getDay() - 1,
    selectedTypeCours: 0,
    infos: [],
    weekInfos: [[], [], [], [], [], [], []],
    firstNextCoursInNextWeeks: null,
    uniqueTypeCoursList: [],
    weekString: '',
    days: [],
  }),
  getters: {
    getMondayOfDisplayedWeek: (state) => {
      return getMondayOfSpecificDate(state.date);
    },

    getMondayOfCurrentWeek: () => {
      return getMondayOfSpecificDate(new Date());
    },

    getWeekString() {
      const monday = this.getMondayOfDisplayedWeek;
      const saturday = new Date(monday);
      saturday.setDate(monday.getDate() + 5);

      return useDateFormat(monday, 'DD MMMM YYYY').value + ' au ' + useDateFormat(saturday, 'DD MMMM YYYY').value;
    },

    shouldPreviousWeekDisabled() {
      return this.getMondayOfDisplayedWeek.getTime() <= this.getMondayOfCurrentWeek.getTime();
    }
  },
  actions: {
    async fetchCoursPerWeek(isOpenRequired = false) {
      try {
        const dateFormatted = ref(this.date.toISOString().split('T')[0]);
        const statusCours = ref(null)
        const tempInfos = ref([]);
        const route = ref("getCoursCalendar");
        await useGetCours(route, tempInfos, this.selectedTypeCours, dateFormatted, statusCours, isOpenRequired);
        this.infos = tempInfos.value;
        // Si isOpenRequired est vrai, on récupère uniquement le prochain cours, on change la date et on affiche le jour du cours
        if (isOpenRequired && this.infos && this.infos.length > 0) {
          this.date = new Date(this.infos[0].dateCours);
          this.setDaySelected(this.date.getDay() - 1);
        }

        // On trie les cours par date et on les répartit dans les jours de la semaine
        if (Array.isArray(this.infos)) {
          this.infos.sort((a, b) => new Date(a.dateCours) - new Date(b.dateCours));
          this.weekInfos = [[], [], [], [], [], [], []];
          this.infos.forEach(info => {
            const rawDay = new Date(info.dateCours).getDay();
            const day = (rawDay + 6) % 7;
            if (this.weekInfos[day]) {
              this.weekInfos[day].push(info);
            } else {
              console.error(`Indice de jour invalide: ${day}`);
            }
          });
        } else {
          this.weekInfos = [[], [], [], [], [], [], []];
        }
        this.updateDaysOfWeek();

        // Si le dernier jour de la semaine n'a pas de cours et qu'il y a des cours dans la semaine, on récupère le prochain cours
        if (this.weekInfos[6].length === 0 && !this.weekInfos.every((info) => info.length === 0)) {
          this.firstNextCoursInNextWeeks = await useGetOnlyNextCours(this.selectedTypeCours, this.days[5], "0");
        }

      } catch (error) {
        console.error("Erreur lors de la récupération des cours :", error);
        this.infos = {message: "Une erreur est survenue lors du chargement des cours."};
        this.weekInfos = [[], [], [], [], [], [], []];
      }

    },
    async fetchTypesCours() {
      this.uniqueTypeCoursList = await useGetTypesCours();
    },
    updateDaysOfWeek() {
      const monday = this.getMondayOfDisplayedWeek;

      this.days = [];
      for (let i = 0; i < 6; i++) {
        const day = new Date(monday);
        day.setDate(monday.getDate() + i);
        this.days.push(useDateFormat(day, 'YYYY-MM-DD').value);
      }
      this.weekString = this.getWeekString;
    },
    setDate(newDate) {
      this.date = new Date(newDate);
    },
    setDaySelected(index) {
      this.daySelected = index;
    },
    setSelectedTypeCours(typeId) {
      this.selectedTypeCours = typeId;
    },
    nextWeek() {
      const current = new Date(this.date);
      this.setDaySelected(0);
      current.setDate(current.getDate() + 7);
      this.date = current;
    },
    prevWeek() {
      const current = new Date(this.date);
      this.setDaySelected(5);
      current.setDate(current.getDate() - 7);
      this.date = current;
    },
    nextCours() {
      this.date = new Date(this.date);
    },
    resetCalendar() {
      this.date = new Date();
      this.daySelected = this.date.getDay() - 1;
      this.selectedTypeCours = 0;
    },
  },
});


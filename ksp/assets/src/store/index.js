import { defineStore } from 'pinia'

// Créez votre store
export const infos = defineStore('coordonnees',{
  state: () => ({
    coordonnees: {
      name: "Servane COSQUERIC",
      adresse: "3 rue de Rennes",
      codePostal: "35310",
      ville: "Mordelles",
      mail: "contact@kine-sport-sante.fr",
      phone: "06 12 34 55 67"
    },
  }),
  getters: {
    fullAddress: (state) => {
      return state.coordonnees.codePostal + " " + state.coordonnees.ville;
    },
    fullPhone: (state) => {
      return state.coordonnees.phone;
    },
    fullMail: (state) => {
      return state.coordonnees.mail;
    },
    fullName: (state) => {
      return state.coordonnees.name;
    },
    address: (state) => {
      return state.coordonnees.adresse;
    }
  },
  mutations: {},
  actions: {},
  modules: {}
})



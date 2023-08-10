import { createStore } from 'vuex'

// Créez votre store
const store = createStore({
  state: {
    coordonnees: {
      name: "Servane COSQUERIC",
      adresse: "3 rue de Rennes",
      codePostal: "35310",
      ville: "Mordelles",
      mail: "contact@kine-sport-sante.fr",
      phone: "06 12 34 55 67"
    },
  },
  getters: {
    fullAddress: (state) => {
      return state.coordonnees.codePostal + " " + state.coordonnees.ville;
    }
  },
  mutations: {},
  actions: {},
  modules: {}
})

export default store;


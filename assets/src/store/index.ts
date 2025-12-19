/**
 * Contact Information Store
 * Stores company/organization contact details
 */

import { defineStore } from 'pinia'

interface ContactInfo {
  name: string
  adresse: string
  codePostal: string
  ville: string
  mail: string
  phone: string
}

interface InfosState {
  coordonnees: ContactInfo
}

export const infos = defineStore('coordonnees', {
  state: (): InfosState => ({
    coordonnees: {
      name: 'Servane COSQUERIC',
      adresse: '3 rue de Rennes',
      codePostal: '35310',
      ville: 'Mordelles',
      mail: 'contact@kine-sport-sante.fr',
      phone: '06 12 34 55 67',
    },
  }),

  getters: {
    /**
     * Récupère l'adresse complète (code postal + ville)
     */
    fullAddress: (state): string => {
      return `${state.coordonnees.codePostal} ${state.coordonnees.ville}`
    },

    /**
     * Récupère le numéro de téléphone
     */
    fullPhone: (state): string => {
      return state.coordonnees.phone
    },

    /**
     * Récupère l'adresse email
     */
    fullMail: (state): string => {
      return state.coordonnees.mail
    },

    /**
     * Récupère le nom complet
     */
    fullName: (state): string => {
      return state.coordonnees.name
    },

    /**
     * Récupère l'adresse postale
     */
    address: (state): string => {
      return state.coordonnees.adresse
    },
  },
})

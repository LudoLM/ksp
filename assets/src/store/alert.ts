/**
 * Alert/Notification Store
 * Manages global alert/notification state
 */

import { reactive } from 'vue'

interface AlertState {
  message: string
  type: 'success' | 'error' | 'warning' | 'info'
  visible: boolean
}

let timeoutId: ReturnType<typeof setTimeout> | null = null

const state = reactive<AlertState>({
  message: '',
  type: 'info',
  visible: false,
})

/**
 * Affiche une alerte
 * @param message - Le message à afficher
 * @param type - Le type d'alerte (success, error, warning, info)
 * @param duration - Durée d'affichage en ms (0 = permanent)
 */
const setAlert = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success', duration: number = 5000): void => {
  if (timeoutId) {
    clearTimeout(timeoutId)
  }

  state.message = message
  state.type = type
  state.visible = true

  if (duration > 0) {
    timeoutId = setTimeout(() => {
      clearAlert()
    }, duration)
  }
}

/**
 * Efface l'alerte
 */
const clearAlert = (): void => {
  if (timeoutId) {
    clearTimeout(timeoutId)
    timeoutId = null
  }

  state.message = ''
  state.type = 'info'
  state.visible = false
}

/**
 * Affiche une alerte de succès
 */
const success = (message: string, duration?: number): void => setAlert(message, 'success', duration)

/**
 * Affiche une alerte d'erreur
 */
const error = (message: string, duration?: number): void => setAlert(message, 'error', duration)

/**
 * Affiche une alerte d'avertissement
 */
const warning = (message: string, duration?: number): void => setAlert(message, 'warning', duration)

/**
 * Affiche une alerte d'information
 */
const info = (message: string, duration?: number): void => setAlert(message, 'info', duration)

export const alertStore = {
  state,
  setAlert,
  clearAlert,
  success,
  error,
  warning,
  info,
}

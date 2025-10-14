// store/alert.js
import { reactive } from 'vue';

// ✅ Crée l'instance UNE SEULE FOIS (singleton)
const state = reactive({
  message: '',
  type: '',
  visible: false,
});

let timeoutId = null;

const setAlert = (message, type = 'success', duration = 5000) => {
  if (timeoutId) {
    clearTimeout(timeoutId);
  }

  state.message = message;
  state.type = type;
  state.visible = true;

  if (duration > 0) {
    timeoutId = setTimeout(() => {
      clearAlert();
    }, duration);
  }
};

const clearAlert = () => {
  if (timeoutId) {
    clearTimeout(timeoutId);
    timeoutId = null;
  }

  state.message = '';
  state.type = '';
  state.visible = false;
};

const success = (message, duration) => setAlert(message, 'success', duration);
const error = (message, duration) => setAlert(message, 'error', duration);
const warning = (message, duration) => setAlert(message, 'warning', duration);
const info = (message, duration) => setAlert(message, 'info', duration);

// ✅ Exporte directement l'objet (pas une fonction)
export const alertStore = {
  state,
  setAlert,
  clearAlert,
  success,
  error,
  warning,
  info,
};

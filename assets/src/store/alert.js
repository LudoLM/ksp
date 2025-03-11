import { reactive } from 'vue';

export function createAlertStore() {
    const state = reactive({
        message: '',
        type: '', // "success", "error", etc.
        visible: false,
    });

    const setAlert = (message, type = 'success') => {
        state.message = message;
        state.type = type;
        state.visible = true;

        // Optionnel : cacher automatiquement l'alerte après un délai
        setTimeout(() => {
            state.visible = false;
        }, 5000);
    };

    const clearAlert = () => {
        state.message = '';
        state.type = '';
        state.visible = false;
    };

    return {
        state,
        setAlert,
        clearAlert,
    };
}

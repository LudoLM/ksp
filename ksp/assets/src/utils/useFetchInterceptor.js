import {useUserStore} from "../store/user";


export async function apiFetch(url, options = {}) {
  // Ajoutez le préfixe `/api` si nécessaire
  const fullUrl = url.startsWith('/api') ? url : `/api${url}`;
  const userStore = useUserStore();

  // Ajoutez automatiquement l'en-tête Authorization
  const defaultHeaders = {
    'Content-Type': 'application/json',
    Authorization: `Bearer ${localStorage.getItem('token')}`,
  };

  const mergedOptions = {
    ...options,
    headers: { ...defaultHeaders, ...options.headers },
  };

  try {
    const response = await fetch(fullUrl, mergedOptions);

    if (response.status === 401) {
        userStore.logout();
        const error = new Error('La session a expiré. Veuillez vous reconnecter.');
        error.type = 'info'; // Ajoute un type personnalisé
        throw error;
    }

    // Vérifiez si la réponse est ok
    if (response.ok) {
      return response; // Retourne les données JSON si tout va bien
    }

    // Pour d'autres erreurs, lancez une erreur explicite
    throw new Error(result?.message || `Erreur HTTP ${response.status}`);
  } catch (error) {
    throw error;
  }
}

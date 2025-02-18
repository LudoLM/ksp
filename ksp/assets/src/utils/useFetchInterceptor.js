import {useUserStore} from "../store/user";


export async function apiFetch(url, options = {}) {
  // Ajoutez le préfixe `/api` si nécessaire
  const fullUrl = url.startsWith('/api') ? url : `/api${url}`;
  const userStore = useUserStore();

  // Ajoutez automatiquement l'en-tête Authorization
  const defaultHeaders = {
    Authorization: `Bearer ${localStorage.getItem('token')}`,
  };

  const mergedOptions = {
    ...options,
    headers: { ...defaultHeaders, ...options.headers },
  };

    const response = await fetch(fullUrl, mergedOptions);

    if (response.status === 401) {
      userStore.logout();
      const error = new Error('La session a expiré. Veuillez vous reconnecter.');
      error.type = 'info';
      throw error;
    }

    return response;
}

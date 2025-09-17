import { useUserStore } from "../store/user";

// Variable de verrouillage
let isRefreshing = false;

// Attendre que le refresh soit terminé
async function waitForRefresh() {
  return new Promise(resolve => {
    const check = setInterval(() => {
      if (!isRefreshing) {
        clearInterval(check);
        resolve();
      }
    }, 50); // Vérifie toutes les 50ms
  });
}

export async function apiFetch(url, options = {}) {
  const fullUrl = url.startsWith('/api') ? url : `/api${url}`;
  const defaultHeaders = {};
  if (!(options.body instanceof FormData)) {
    defaultHeaders['Content-Type'] = 'application/json';
  }
  const userStore = useUserStore();
  const mergedOptions = {
    ...options,
    headers: { ...defaultHeaders, ...options.headers },
    credentials: 'include'
  };

  let response = await fetch(fullUrl, mergedOptions);

  if (response.status === 401) {
    if (!isRefreshing) {
      isRefreshing = true;
      try {
        const refreshRes = await fetch('/api/token/refresh', {
          method: 'POST',
          credentials: 'include',
        });
        if (!refreshRes.ok) {
          throw new Error('Impossible de rafraîchir le token.');
        }
        // Le refresh a réussi, éteindre le drapeau et rejouer la requête initiale
        isRefreshing = false;
        return await fetch(fullUrl, mergedOptions);
      } catch (err) {
        // Refresh échoué → logout et propager l'erreur
        isRefreshing = false;
        await userStore.logout();
        throw new Error('La session a expiré. Veuillez vous reconnecter.');
      }
    } else {
      // Un refresh est déjà en cours, attendre
      await waitForRefresh();
      // Une fois le refresh terminé, rejouer la requête
      return await fetch(fullUrl, mergedOptions);
    }
  }

  return response;
}

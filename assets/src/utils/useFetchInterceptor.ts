/**
 * Fetch Interceptor for API calls
 * Handles JWT token refresh and auth errors
 */

import { useUserStore } from '@/store/user'

// Variable de verrouillage
let isRefreshing = false

/**
 * Attendre que le refresh de token soit terminé
 * @returns Promise qui se résout quand le refresh est fait
 */
async function waitForRefresh(): Promise<void> {
  return new Promise((resolve) => {
    const check = setInterval(() => {
      if (!isRefreshing) {
        clearInterval(check)
        resolve()
      }
    }, 50) // Vérifie toutes les 50ms
  })
}

/**
 * Fait une requête API avec gestion automatique du refresh token
 * @param url - L'URL (avec ou sans /api)
 * @param options - Les options Fetch
 * @returns La réponse fetch
 * @throws Error si l'authentification échoue
 */
export async function apiFetch(url: string, options: RequestInit = {}): Promise<Response> {
  const fullUrl = url.startsWith('/api') ? url : `/api${url}`

  const defaultHeaders: Record<string, string> = {}
  if (!(options.body instanceof FormData)) {
    defaultHeaders['Content-Type'] = 'application/json'
  }

  const userStore = useUserStore()
  const mergedOptions: RequestInit = {
    ...options,
    headers: { ...defaultHeaders, ...(options.headers as Record<string, string>) },
    credentials: 'include',
  }

  let response = await fetch(fullUrl, mergedOptions)

  if (response.status === 401) {
    if (!isRefreshing) {
      isRefreshing = true
      try {
        const refreshRes = await fetch('/api/token/refresh', {
          method: 'POST',
          credentials: 'include',
        })

        if (!refreshRes.ok) {
          throw new Error('Impossible de rafraîchir le token.')
        }

        // Le refresh a réussi, éteindre le drapeau et rejouer la requête initiale
        isRefreshing = false
        return await fetch(fullUrl, mergedOptions)
      } catch (err) {
        // Refresh échoué → logout et propager l'erreur
        isRefreshing = false
        await userStore.logout()
        throw new Error('La session a expiré. Veuillez vous reconnecter.')
      }
    } else {
      // Un refresh est déjà en cours, attendre
      await waitForRefresh()
      // Une fois le refresh terminé, rejouer la requête
      return await fetch(fullUrl, mergedOptions)
    }
  }

  return response
}

import { describe, expect, it, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

// user -> calendar -> useActionCours -> @/router, qui initialise Vuetify
// (createVuetify + import CSS) au chargement du module.
vi.mock('@/router', () => ({ default: { push: vi.fn() } }))

import { useUserStore } from './user'

describe('userStore - hasValidWishesForm', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('is false when there is no user', () => {
    const store = useUserStore()
    expect(store.hasValidWishesForm).toBe(false)
  })

  it('is false when the current wishes form is not Valide', () => {
    const store = useUserStore()
    store.setUser({
      id: 1, email: 'jean@example.com', nom: 'Dupont', prenom: 'Jean',
      telephone: '', commune: '', adresse: '', codePostal: '',
      nombreCours: 0, roles: ['ROLE_USER'], certificatMedical: null,
      coursWishesForm: { id: 1, status: 'EnAttente', saison: '2026-2027' },
    })
    expect(store.hasValidWishesForm).toBe(false)
  })

  it('is true when the current wishes form is Valide', () => {
    const store = useUserStore()
    store.setUser({
      id: 1, email: 'jean@example.com', nom: 'Dupont', prenom: 'Jean',
      telephone: '', commune: '', adresse: '', codePostal: '',
      nombreCours: 0, roles: ['ROLE_USER'], certificatMedical: null,
      coursWishesForm: { id: 1, status: 'Valide', saison: '2026-2027' },
    })
    expect(store.hasValidWishesForm).toBe(true)
  })
})

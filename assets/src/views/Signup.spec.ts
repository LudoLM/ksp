import { describe, expect, it, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory, type Router } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'

// user -> calendar -> useActionCours -> @/router, qui initialise Vuetify
// (createVuetify + import CSS) au chargement du module.
vi.mock('@/router', () => ({ default: { push: vi.fn() } }))

import Signup from './Signup.vue'

vi.stubGlobal('fetch', vi.fn())

const jsonResponse = (data: unknown, ok = true) => ({
  ok,
  json: async () => data,
})

const mountSignup = async (path: string): Promise<{ wrapper: ReturnType<typeof mount>; router: Router }> => {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/register', name: 'Register', component: Signup },
      { path: '/', name: 'Accueil', component: { template: '<div />' } },
    ],
  })
  await router.push(path)
  await router.isReady()

  const wrapper = mount(Signup, {
    global: { plugins: [router] },
  })
  await flushPromises()

  return { wrapper, router }
}

describe('Signup.vue', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.mocked(fetch).mockReset()
  })

  it('prefills and disables email/nom/prenom/telephone when the token resolves', async () => {
    vi.mocked(fetch).mockResolvedValue(jsonResponse({
      email: 'jean@example.com',
      nom: 'Dupont',
      prenom: 'Jean',
      telephone: '0612345678',
    }) as unknown as Response)

    const { wrapper } = await mountSignup('/register?token=raw-token')

    expect(fetch).toHaveBeenCalledWith(expect.stringContaining('/api/register/prefill?token=raw-token'))

    const emailInput = wrapper.find('#email')
    const nomInput = wrapper.find('#nom')
    const prenomInput = wrapper.find('#prenom')
    const phoneInput = wrapper.find('#phone')

    expect((emailInput.element as HTMLInputElement).value).toBe('jean@example.com')
    expect((nomInput.element as HTMLInputElement).value).toBe('Dupont')
    expect((prenomInput.element as HTMLInputElement).value).toBe('Jean')
    expect((phoneInput.element as HTMLInputElement).value).toBe('0612345678')

    expect((emailInput.element as HTMLInputElement).disabled).toBe(true)
    expect((nomInput.element as HTMLInputElement).disabled).toBe(true)
    expect((prenomInput.element as HTMLInputElement).disabled).toBe(true)
    expect((phoneInput.element as HTMLInputElement).disabled).toBe(true)
  })

  it('shows a message and a link to request a wishes form when the token does not resolve', async () => {
    vi.mocked(fetch).mockResolvedValue(jsonResponse({ error: 'Lien invalide ou expiré.' }, false) as unknown as Response)

    const { wrapper } = await mountSignup('/register?token=invalid-token')

    expect(wrapper.find('form').exists()).toBe(false)
    expect(wrapper.find('#email').exists()).toBe(false)
    expect(wrapper.text()).toContain("n'est possible qu'après validation d'un dossier d'inscription")
    expect(wrapper.text()).toContain('Faire une demande d\'inscription')
  })

  it('shows a message and a link to request a wishes form when no token is present', async () => {
    const { wrapper } = await mountSignup('/register')

    expect(fetch).not.toHaveBeenCalled()
    expect(wrapper.find('form').exists()).toBe(false)
    expect(wrapper.find('#email').exists()).toBe(false)
    expect(wrapper.text()).toContain('Faire une demande d\'inscription')
  })

  it('submits only token/password/adresse/cp/commune when prefilled', async () => {
    vi.mocked(fetch)
      .mockResolvedValueOnce(jsonResponse({
        email: 'jean@example.com',
        nom: 'Dupont',
        prenom: 'Jean',
        telephone: '0612345678',
      }) as unknown as Response)
      .mockResolvedValueOnce(jsonResponse({ message: 'Utilisateur créé' }) as unknown as Response)
      .mockResolvedValueOnce(jsonResponse({ id: 1, email: 'jean@example.com' }) as unknown as Response)

    const { wrapper } = await mountSignup('/register?token=raw-token')

    await wrapper.find('#password').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    const submitCall = vi.mocked(fetch).mock.calls[1]
    expect(submitCall[0]).toContain('/api/register')
    const body = JSON.parse(submitCall[1]?.body as string)
    expect(body).toEqual({
      token: 'raw-token',
      password: 'password123',
      adresse: '',
      cp: '',
      commune: '',
    })
  })
})

import { describe, expect, it, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'

// store/user -> store/calendar -> useActionCours -> @/router, qui initialise Vuetify
// (createVuetify + import CSS) au chargement du module. Un mock évite de charger
// tout ce bootstrap juste pour monter le formulaire dans ce test.
vi.mock('@/router', () => ({ default: { push: vi.fn() } }))

import CoursWishesForm from './CoursWishesForm.vue'
import { apiFetch } from '@/utils/useFetchInterceptor.ts'
import { alertStore } from '@/store/alert.ts'
import { useUserStore } from '@/store/user.ts'

vi.mock('@/utils/useFetchInterceptor.ts', () => ({
  apiFetch: vi.fn(),
}))

vi.mock('@/store/alert.ts', () => ({
  alertStore: { setAlert: vi.fn() },
}))

const jsonResponse = (data: unknown) => ({ ok: true, json: async () => data })

describe('CoursWishesForm.vue', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.mocked(apiFetch).mockReset()
    vi.mocked(alertStore.setAlert).mockReset()
    vi.mocked(apiFetch).mockImplementation((url: string) => {
      if (url.includes('/season-planning/current')) {
        return Promise.resolve(jsonResponse([
          {
            id: 1,
            daySelected: 4,
            timeSelected: '18:00:00',
            typeCoursOptions: [{ libelle: 'Pilates Début' }, { libelle: 'Tous les cours' }],
          },
        ])) as any
      }
      if (url.includes('/packs')) {
        return Promise.resolve(jsonResponse([{ id: 1, nom: 'Carte de 5 séances', tarif: 80 }])) as any
      }
      if (url.includes('/cours-wishes-form')) {
        return Promise.resolve(jsonResponse(null)) as any
      }
      return Promise.resolve(jsonResponse({})) as any
    })
  })

  it('loads the available créneaux and packs on mount, listing alternating cours on one card', async () => {
    const wrapper = mount(CoursWishesForm)
    await new Promise((resolve) => setTimeout(resolve, 0))

    expect(apiFetch).toHaveBeenCalledWith(expect.stringContaining('/season-planning/current'))
    expect(apiFetch).toHaveBeenCalledWith(expect.stringContaining('/packs'))
    expect(wrapper.text()).toContain('Pilates Début/Tous les cours')
    expect(wrapper.findAll('.wishes-form-creneau')).toHaveLength(1)
  })

  it('disables the submit button until a créneau prioritaire is selected', async () => {
    const wrapper = mount(CoursWishesForm)
    await new Promise((resolve) => setTimeout(resolve, 0))

    await wrapper.find('input[name="nom"]').setValue('Dupont')
    await wrapper.find('input[name="prenom"]').setValue('Jean')
    await wrapper.find('input[name="telephone"]').setValue('0612345678')
    await wrapper.find('select[name="packSouhaiteId"]').setValue('1')

    const submitButton = wrapper.find('.wishes-form-submit')
    expect(submitButton.attributes('disabled')).toBeDefined()

    await wrapper.find('.wishes-form-toggle--primaire').trigger('click')
    expect(submitButton.attributes('disabled')).toBeUndefined()
  })

  it('toggling prioritaire then secondaire on the same créneau is mutually exclusive', async () => {
    const wrapper = mount(CoursWishesForm)
    await new Promise((resolve) => setTimeout(resolve, 0))

    const primaireButton = wrapper.find('.wishes-form-toggle--primaire')
    const secondaireButton = wrapper.find('.wishes-form-toggle--secondaire')

    await primaireButton.trigger('click')
    expect(primaireButton.attributes('aria-pressed')).toBe('true')
    expect(secondaireButton.attributes('aria-pressed')).toBe('false')

    await secondaireButton.trigger('click')
    expect(primaireButton.attributes('aria-pressed')).toBe('false')
    expect(secondaireButton.attributes('aria-pressed')).toBe('true')
  })

  it('submits the form with the selected créneau and values', async () => {
    const wrapper = mount(CoursWishesForm)
    await new Promise((resolve) => setTimeout(resolve, 0))

    await wrapper.find('input[name="nom"]').setValue('Dupont')
    await wrapper.find('input[name="prenom"]').setValue('Jean')
    await wrapper.find('input[name="telephone"]').setValue('0612345678')
    await wrapper.find('.wishes-form-toggle--primaire').trigger('click')
    await wrapper.find('select[name="packSouhaiteId"]').setValue('1')
    await wrapper.find('input[name="modeReglement"][value="CbComptant"]').setValue(true)
    await wrapper.find('form').trigger('submit.prevent')
    await new Promise((resolve) => setTimeout(resolve, 0))

    expect(apiFetch).toHaveBeenCalledWith(
      expect.stringContaining('/cours-wishes-form'),
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({
          email: '',
          nom: 'Dupont',
          prenom: 'Jean',
          telephone: '0612345678',
          creneauPrimaireId: 1,
          creneauSecondaireId: null,
          packSouhaiteId: 1,
          modeReglement: 'CbComptant',
        }),
      }),
    )
    expect(alertStore.setAlert).toHaveBeenCalledWith(expect.stringContaining('envoyé'), 'success')
  })

  it('hides nom/prenom/telephone fields for an authenticated submitter and omits them from the payload', async () => {
    useUserStore().setUser({
      id: 1,
      email: 'jean@example.com',
      nom: 'Dupont',
      prenom: 'Jean',
      telephone: '0612345678',
      commune: '',
      adresse: '',
      codePostal: '',
      nombreCours: 0,
      roles: ['ROLE_USER'],
      certificatMedical: null,
    })

    const wrapper = mount(CoursWishesForm)
    await new Promise((resolve) => setTimeout(resolve, 0))

    expect(wrapper.find('input[name="nom"]').exists()).toBe(false)
    expect(wrapper.find('input[name="prenom"]').exists()).toBe(false)
    expect(wrapper.find('input[name="telephone"]').exists()).toBe(false)

    await wrapper.find('.wishes-form-toggle--primaire').trigger('click')
    await wrapper.find('select[name="packSouhaiteId"]').setValue('1')
    await wrapper.find('form').trigger('submit.prevent')
    await new Promise((resolve) => setTimeout(resolve, 0))

    expect(apiFetch).toHaveBeenCalledWith(
      expect.stringContaining('/cours-wishes-form'),
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({
          email: 'jean@example.com',
          nom: null,
          prenom: null,
          telephone: null,
          creneauPrimaireId: 1,
          creneauSecondaireId: null,
          packSouhaiteId: 1,
          modeReglement: 'CbComptant',
        }),
      }),
    )
  })
})

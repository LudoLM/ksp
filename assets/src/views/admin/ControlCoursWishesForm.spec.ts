import { describe, expect, it, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import { createRouter, createMemoryHistory, type Router } from 'vue-router';
import ControlCoursWishesForm from './ControlCoursWishesForm.vue';
import SmartPagination from '@/components/admin/SmartPagination.vue';
import { apiFetch } from '@/utils/useFetchInterceptor.ts';
import { alertStore } from '@/store/alert.ts';

vi.mock('@/utils/useFetchInterceptor.ts', () => ({
    apiFetch: vi.fn(),
}));

vi.mock('@/store/alert.ts', () => ({
    alertStore: {
        setAlert: vi.fn(),
    },
}));

const daysAgo = (days: number): string => {
    const date = new Date();
    date.setDate(date.getDate() - days);
    return date.toISOString();
};

const sampleForm = (overrides: Record<string, unknown> = {}) => ({
    id: 12,
    email: 'test@test.fr',
    contactNom: 'Dupont',
    contactPrenom: 'Jean',
    contactTelephone: '0612345678',
    saison: '2026-2027',
    status: 'EnAttente',
    submittedAt: daysAgo(1),
    creneauPrimaire: { id: 9, daySelected: 1, timeSelected: '18:04', cours: 'Pilates Début' },
    creneauSecondaire: { id: 10, daySelected: 2, timeSelected: '19:05', cours: 'Stretching/Postural Ball' },
    packSouhaite: 'Pack 3 cours',
    modeReglement: 'Cheque3x',
    user: null,
    ...overrides,
});

const jsonResponse = (data: unknown) => ({
    ok: true,
    json: async () => data,
});

const mountControlWishesForm = async (): Promise<{ wrapper: ReturnType<typeof mount>; router: Router }> => {
    const router = createRouter({
        history: createMemoryHistory(),
        routes: [{ path: '/', component: ControlCoursWishesForm }],
    });
    await router.push('/');
    await router.isReady();

    const wrapper = mount(ControlCoursWishesForm, {
        global: {
            plugins: [router],
            stubs: { Banner: true },
        },
    });
    await flushPromises();

    return { wrapper, router };
};

describe('ControlCoursWishesForm.vue', () => {
    beforeEach(() => {
        vi.mocked(apiFetch).mockReset();
        vi.mocked(alertStore.setAlert).mockReset();
    });

    it('loads pending dossiers on mount and displays the resolved créneaux', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm()],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        expect(apiFetch).toHaveBeenCalledWith('/admin/cours-wishes-form/pending?page=1');
        expect(wrapper.text()).toContain('test@test.fr');
        expect(wrapper.text()).toContain('Lundi 18:04 — Pilates Début');
        expect(wrapper.text()).toContain('Mardi 19:05 — Stretching/Postural Ball');
        expect(wrapper.text()).toContain('1 dossier à traiter');
    });

    it('displays the contact info for calling the requester', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm()],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        expect(wrapper.text()).toContain('Jean Dupont');
        expect(wrapper.text()).toContain('0612345678');
    });

    it('shows "Non renseigné" when a créneau is missing', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm({ creneauSecondaire: null })],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        expect(wrapper.text()).toContain('Non renseigné');
    });

    it('disables Valider and shows a hint when the dossier has no créneau prioritaire', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm({ creneauPrimaire: null })],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        expect(wrapper.text()).toContain('Créneau prioritaire manquant');
        expect(wrapper.find('.wf-btn--success').attributes('disabled')).toBeDefined();
        expect(wrapper.find('.wf-btn--danger-outline').attributes('disabled')).toBeUndefined();
    });

    it('shows the default "En attente" badge for a recently submitted dossier', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm({ submittedAt: daysAgo(1) })],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        expect(wrapper.find('.wf-badge--pending').text()).toBe('En attente');
        expect(wrapper.find('.wf-badge--stale').exists()).toBe(false);
    });

    it('shows a stale badge with the day count once a dossier has been pending 7+ days', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm({ submittedAt: daysAgo(9) })],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        expect(wrapper.find('.wf-badge--stale').text()).toContain('9 j');
        expect(wrapper.find('.wf-badge--pending').exists()).toBe(false);
    });

    it('shows the empty state when there is nothing pending', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [],
            metadata: { total_items: 0, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        expect(wrapper.text()).toContain('Aucun dossier en attente');
    });

    it('shows an error alert and clears the list when loading fails', async () => {
        vi.mocked(apiFetch).mockRejectedValue(new Error('network down'));

        const { wrapper } = await mountControlWishesForm();

        expect(alertStore.setAlert).toHaveBeenCalledWith('Erreur lors du chargement des dossiers', 'error');
        expect(wrapper.text()).toContain('Aucun dossier en attente');
    });

    it('approves a dossier and removes it from the list', async () => {
        vi.mocked(apiFetch)
            .mockResolvedValueOnce(jsonResponse({
                data: [sampleForm()],
                metadata: { total_items: 1, current_page: 1, total_pages: 1 },
            }) as unknown as Response)
            .mockResolvedValueOnce(jsonResponse({ status: 'Valide' }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        await wrapper.find('.wf-btn--success').trigger('click');
        await flushPromises();

        expect(apiFetch).toHaveBeenLastCalledWith('/admin/cours-wishes-form/12/validate', expect.objectContaining({
            method: 'POST',
            body: expect.any(FormData),
        }));

        const body = vi.mocked(apiFetch).mock.calls[1][1]?.body as FormData;
        expect(body.get('action')).toBe('approve');
        expect(body.has('reason')).toBe(false);

        expect(alertStore.setAlert).toHaveBeenCalledWith('Dossier de test@test.fr validé', 'success');
        expect(wrapper.text()).toContain('Aucun dossier en attente');
    });

    it('disables the confirm button until a correction reason is entered', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm()],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        await wrapper.find('.wf-btn--danger-outline').trigger('click');
        const confirmButton = wrapper.find('.wf-btn--danger');
        expect(confirmButton.attributes('disabled')).toBeDefined();

        await wrapper.find('.wf-actions__reason-input').setValue('Créneau complet');
        expect(confirmButton.attributes('disabled')).toBeUndefined();
    });

    it('sends a dossier back for correction with the entered reason', async () => {
        vi.mocked(apiFetch)
            .mockResolvedValueOnce(jsonResponse({
                data: [sampleForm()],
                metadata: { total_items: 1, current_page: 1, total_pages: 1 },
            }) as unknown as Response)
            .mockResolvedValueOnce(jsonResponse({ status: 'Rejete' }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        await wrapper.find('.wf-btn--danger-outline').trigger('click');
        await wrapper.find('.wf-actions__reason-input').setValue('Créneau complet');
        await wrapper.find('.wf-btn--danger').trigger('click');
        await flushPromises();

        const body = vi.mocked(apiFetch).mock.calls[1][1]?.body as FormData;
        expect(body.get('action')).toBe('correction');
        expect(body.get('reason')).toBe('Créneau complet');
        expect(wrapper.text()).toContain('Aucun dossier en attente');
    });

    it('cancels the correction confirmation and restores the default actions', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm()],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        await wrapper.find('.wf-btn--danger-outline').trigger('click');
        expect(wrapper.find('.wf-actions__reason-input').exists()).toBe(true);

        await wrapper.find('.wf-btn--ghost').trigger('click');

        expect(wrapper.find('.wf-actions__reason-input').exists()).toBe(false);
        expect(wrapper.find('.wf-btn--danger-outline').exists()).toBe(true);
    });

    it('refetches the corresponding page when pagination emits a page change', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleForm()],
            metadata: { total_items: 20, current_page: 1, total_pages: 2 },
        }) as unknown as Response);

        const { wrapper } = await mountControlWishesForm();

        await wrapper.findComponent(SmartPagination).vm.$emit('page-changed', 2);
        await flushPromises();

        expect(apiFetch).toHaveBeenLastCalledWith('/admin/cours-wishes-form/pending?page=2');
    });
});

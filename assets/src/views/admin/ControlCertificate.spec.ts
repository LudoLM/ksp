import { describe, expect, it, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import { createRouter, createMemoryHistory, type Router } from 'vue-router';
import ControlCertificate from './ControlCertificate.vue';
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

const sampleCertificate = (overrides: Record<string, unknown> = {}) => ({
    id: 1,
    status: 'Pending',
    uploadedAt: '2026-01-10 09:30:00',
    validUntil: null,
    certificateFilename: 'certificate.pdf',
    user: { id: 10, nom: 'Dupont', prenom: 'Jean', email: 'jean@example.com' },
    ...overrides,
});

const jsonResponse = (data: unknown) => ({
    ok: true,
    json: async () => data,
});

const mountControlCertificate = async (): Promise<{ wrapper: ReturnType<typeof mount>; router: Router }> => {
    const router = createRouter({
        history: createMemoryHistory(),
        routes: [{ path: '/', component: ControlCertificate }],
    });
    await router.push('/');
    await router.isReady();

    const wrapper = mount(ControlCertificate, {
        global: {
            plugins: [router],
            stubs: { Banner: true },
        },
    });
    await flushPromises();

    return { wrapper, router };
};

describe('ControlCertificate.vue', () => {
    beforeEach(() => {
        vi.mocked(apiFetch).mockReset();
        vi.mocked(alertStore.setAlert).mockReset();
        vi.spyOn(window, 'open').mockImplementation(() => null);
    });

    it('loads and displays pending certificates on mount', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleCertificate()],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();

        expect(apiFetch).toHaveBeenCalledWith('/admin/certificates/pending?page=1');
        expect(wrapper.text()).toContain('Jean Dupont');
        expect(wrapper.text()).toContain('jean@example.com');
        expect(wrapper.text()).toContain('En attente');
        expect(wrapper.text()).toContain('1 certificat à traiter');
    });

    it('shows the empty state when there is nothing pending', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [],
            metadata: { total_items: 0, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();

        expect(wrapper.text()).toContain('Aucun certificat en attente');
    });

    it('shows an error alert and clears the list when loading fails', async () => {
        vi.mocked(apiFetch).mockRejectedValue(new Error('network down'));

        const { wrapper } = await mountControlCertificate();

        expect(alertStore.setAlert).toHaveBeenCalledWith('Erreur lors du chargement des certificats', 'error');
        expect(wrapper.text()).toContain('Aucun certificat en attente');
    });

    it('opens the PDF in a new tab', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleCertificate({ id: 7 })],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();
        await wrapper.find('.cert-btn--ghost').trigger('click');

        expect(window.open).toHaveBeenCalledWith('/api/admin/certificate/7', '_blank');
    });

    it('approves a certificate and removes it from the list', async () => {
        vi.mocked(apiFetch)
            .mockResolvedValueOnce(jsonResponse({
                data: [sampleCertificate({ id: 5 })],
                metadata: { total_items: 1, current_page: 1, total_pages: 1 },
            }) as unknown as Response)
            .mockResolvedValueOnce(jsonResponse({ status: 'Approved' }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();

        await wrapper.find('.cert-btn--success').trigger('click');
        await flushPromises();

        expect(apiFetch).toHaveBeenLastCalledWith('/admin/certificate/5/validate', expect.objectContaining({
            method: 'POST',
            body: expect.any(FormData),
        }));

        const body = vi.mocked(apiFetch).mock.calls[1][1]?.body as FormData;
        expect(body.get('action')).toBe('approve');
        expect(body.has('reason')).toBe(false);

        expect(alertStore.setAlert).toHaveBeenCalledWith('Certificat de Jean Dupont approuvé avec succès', 'success');
        expect(wrapper.text()).toContain('Aucun certificat en attente');
    });

    it('disables the confirm button until a rejection reason is entered', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleCertificate({ id: 3 })],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();

        await wrapper.find('.cert-btn--danger-outline').trigger('click');
        const confirmButton = wrapper.find('.cert-btn--danger');
        expect(confirmButton.attributes('disabled')).toBeDefined();

        await wrapper.find('.cert-actions__reason-input').setValue('Document illisible');
        expect(confirmButton.attributes('disabled')).toBeUndefined();
    });

    it('rejects a certificate with the entered reason', async () => {
        vi.mocked(apiFetch)
            .mockResolvedValueOnce(jsonResponse({
                data: [sampleCertificate({ id: 9 })],
                metadata: { total_items: 1, current_page: 1, total_pages: 1 },
            }) as unknown as Response)
            .mockResolvedValueOnce(jsonResponse({ status: 'Rejected' }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();

        await wrapper.find('.cert-btn--danger-outline').trigger('click');
        await wrapper.find('.cert-actions__reason-input').setValue('Document illisible');
        await wrapper.find('.cert-btn--danger').trigger('click');
        await flushPromises();

        const body = vi.mocked(apiFetch).mock.calls[1][1]?.body as FormData;
        expect(body.get('action')).toBe('reject');
        expect(body.get('reason')).toBe('Document illisible');
        expect(wrapper.text()).toContain('Aucun certificat en attente');
    });

    it('cancels the rejection confirmation and restores the default actions', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleCertificate({ id: 4 })],
            metadata: { total_items: 1, current_page: 1, total_pages: 1 },
        }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();

        await wrapper.find('.cert-btn--danger-outline').trigger('click');
        expect(wrapper.find('.cert-actions__reason-input').exists()).toBe(true);

        await wrapper.find('.cert-btn--ghost').trigger('click');

        expect(wrapper.find('.cert-actions__reason-input').exists()).toBe(false);
        expect(wrapper.find('.cert-btn--danger-outline').exists()).toBe(true);
    });

    it('refetches the corresponding page when pagination emits a page change', async () => {
        vi.mocked(apiFetch).mockResolvedValue(jsonResponse({
            data: [sampleCertificate()],
            metadata: { total_items: 20, current_page: 1, total_pages: 2 },
        }) as unknown as Response);

        const { wrapper } = await mountControlCertificate();

        await wrapper.findComponent(SmartPagination).vm.$emit('page-changed', 2);
        await flushPromises();

        expect(apiFetch).toHaveBeenLastCalledWith('/admin/certificates/pending?page=2');
    });
});

import { describe, expect, it, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import CertificateStatus from './CertificateStatus.vue';
import CertificateUpload from './CertificateUpload.vue';
import { apiFetch } from '@/utils/useFetchInterceptor.ts';
import { alertStore } from '@/store/alert.ts';
import type { UserCertificatMedical } from '@/store/user';

vi.mock('@/utils/useFetchInterceptor.ts', () => ({
    apiFetch: vi.fn(),
}));

vi.mock('@/store/alert.ts', () => ({
    alertStore: {
        setAlert: vi.fn(),
    },
}));

let fakeTab: { location: { href: string }; close: ReturnType<typeof vi.fn> };

beforeEach(() => {
    vi.mocked(apiFetch).mockReset();
    vi.mocked(alertStore.setAlert).mockReset();

    fakeTab = { location: { href: '' }, close: vi.fn() };
    vi.spyOn(window, 'open').mockReset().mockReturnValue(fakeTab as unknown as Window);
    URL.createObjectURL = vi.fn(() => 'blob:mock-url');
    URL.revokeObjectURL = vi.fn();
});

const certificate = (overrides: Partial<UserCertificatMedical> = {}): UserCertificatMedical => ({
    id: 1,
    status: null,
    uploadedAt: null,
    validUntil: null,
    rejectionReason: null,
    ...overrides,
});

describe('CertificateStatus.vue', () => {
    it('shows the empty state and default upload label when there is no certificate', () => {
        const wrapper = mount(CertificateStatus, {
            props: { certificatMedical: null },
        });

        expect(wrapper.text()).toContain('Aucun certificat transmis. Il est requis pour réserver certains cours.');
        expect(wrapper.text()).toContain('Transmettre mon certificat');
        expect(wrapper.find('span.inline-flex').exists()).toBe(false);
    });

    it('shows the pending message without a status badge (no validUntil yet)', () => {
        const wrapper = mount(CertificateStatus, {
            props: {
                certificatMedical: certificate({
                    status: 'Pending',
                    uploadedAt: new Date('2026-01-10T09:30:00'),
                }),
            },
        });

        expect(wrapper.text()).toContain('en attente de validation');
        expect(wrapper.find('span.inline-flex').exists()).toBe(false);
        expect(wrapper.text()).toContain('Transmettre un nouveau certificat');
    });

    it('shows the rejection reason when present', () => {
        const wrapper = mount(CertificateStatus, {
            props: {
                certificatMedical: certificate({
                    status: 'Rejected',
                    rejectionReason: 'Document illisible',
                }),
            },
        });

        expect(wrapper.text()).toContain('Certificat refusé : Document illisible.');
    });

    it('does not show a reason clause when rejected without a reason', () => {
        const wrapper = mount(CertificateStatus, {
            props: {
                certificatMedical: certificate({ status: 'Rejected', rejectionReason: null }),
            },
        });

        expect(wrapper.text()).toContain('Certificat refusé.');
        expect(wrapper.text()).not.toContain('Certificat refusé :');
    });


    it('shows the badge even for a Pending status, as long as validUntil is set', () => {
        const wrapper = mount(CertificateStatus, {
            props: {
                certificatMedical: certificate({
                    status: 'Pending',
                    uploadedAt: new Date('2026-01-10T09:30:00'),
                    validUntil: new Date('2027-01-10'),
                }),
            },
        });

        const badge = wrapper.find('span.inline-flex');
        expect(badge.exists()).toBe(true);
        expect(badge.text()).toContain('En attente');
    });

    it('hides the badge for an Approved status without validUntil, leaving the date blank', () => {
        const wrapper = mount(CertificateStatus, {
            props: {
                certificatMedical: certificate({ status: 'Approved', validUntil: null }),
            },
        });

        expect(wrapper.find('span.inline-flex').exists()).toBe(false);
        expect(wrapper.text()).toContain("Valide jusqu'au");
        expect(wrapper.find('span.font-medium').text()).toBe('');
    });

    it.each([
        { status: 'Approved', label: 'Valide', badgeClass: 'bg-green-100' },
        { status: 'Expired', label: 'Expiré', badgeClass: 'bg-red-100' },
    ])('shows the $label badge for status $status once validUntil is set', ({ status, label, badgeClass }) => {
        const wrapper = mount(CertificateStatus, {
            props: {
                certificatMedical: certificate({
                    status,
                    uploadedAt: new Date('2026-01-10T09:30:00'),
                    validUntil: new Date('2027-01-10'),
                }),
            },
        });

        const badge = wrapper.find('span.inline-flex');
        expect(badge.exists()).toBe(true);
        expect(badge.text()).toContain(label);
        expect(badge.classes().join(' ')).toContain(badgeClass);
    });

    it('emits "uploaded" when the child upload component emits it', async () => {
        const wrapper = mount(CertificateStatus, {
            props: { certificatMedical: null },
        });

        await wrapper.findComponent(CertificateUpload).vm.$emit('uploaded');

        expect(wrapper.emitted('uploaded')).toHaveLength(1);
    });

    it('forwards the userId prop to the upload component', () => {
        const wrapper = mount(CertificateStatus, {
            props: { certificatMedical: null, userId: 42 },
        });

        expect(wrapper.findComponent(CertificateUpload).props('userId')).toBe(42);
    });

    it('hides the "view PDF" button for the user\'s own profile (no userId)', () => {
        const wrapper = mount(CertificateStatus, {
            props: { certificatMedical: certificate({ status: 'Approved' }) },
        });

        expect(wrapper.find('.cert-view-pdf').exists()).toBe(false);
    });

    it('hides the "view PDF" button when there is no certificate, even for an admin', () => {
        const wrapper = mount(CertificateStatus, {
            props: { certificatMedical: null, userId: 42 },
        });

        expect(wrapper.find('.cert-view-pdf').exists()).toBe(false);
    });

    it('opens the PDF in a new tab via apiFetch when the admin clicks "Voir le PDF"', async () => {
        const pdfBlob = new Blob(['%PDF-1.4'], { type: 'application/pdf' });
        vi.mocked(apiFetch).mockResolvedValue({ ok: true, blob: async () => pdfBlob } as unknown as Response);

        const wrapper = mount(CertificateStatus, {
            props: { certificatMedical: certificate({ id: 99, status: 'Approved' }), userId: 42 },
        });

        const button = wrapper.find('.cert-view-pdf');
        expect(button.exists()).toBe(true);
        expect(button.text()).toBe('Voir le PDF');

        await button.trigger('click');
        expect(window.open).toHaveBeenCalledWith('', '_blank');

        await flushPromises();

        expect(apiFetch).toHaveBeenCalledWith('/admin/certificate/99');
        expect(fakeTab.location.href).toBe('blob:mock-url');
    });
});

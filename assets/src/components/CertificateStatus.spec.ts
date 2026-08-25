import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CertificateStatus from './CertificateStatus.vue';
import CertificateUpload from './CertificateUpload.vue';
import type { UserCertificatMedical } from '@/store/user';

vi.mock('@/utils/useFetchInterceptor.ts', () => ({
    apiFetch: vi.fn(),
}));

vi.mock('@/store/alert.ts', () => ({
    alertStore: {
        setAlert: vi.fn(),
    },
}));

const certificate = (overrides: Partial<UserCertificatMedical> = {}): UserCertificatMedical => ({
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
});

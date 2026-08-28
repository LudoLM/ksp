import { describe, expect, it, vi, beforeEach } from 'vitest';
import { viewCertificatePdf } from './viewCertificatePdf.ts';
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

describe('viewCertificatePdf', () => {
    let fakeTab: { location: { href: string }; close: ReturnType<typeof vi.fn> };

    beforeEach(() => {
        vi.mocked(apiFetch).mockReset();
        vi.mocked(alertStore.setAlert).mockReset();

        fakeTab = { location: { href: '' }, close: vi.fn() };
        vi.spyOn(window, 'open').mockReset().mockReturnValue(fakeTab as unknown as Window);
        URL.createObjectURL = vi.fn(() => 'blob:mock-url');
        URL.revokeObjectURL = vi.fn();
    });

    it('opens a blank tab synchronously, then navigates it to the fetched PDF blob', async () => {
        const pdfBlob = new Blob(['%PDF-1.4'], { type: 'application/pdf' });
        vi.mocked(apiFetch).mockResolvedValue({ ok: true, blob: async () => pdfBlob } as unknown as Response);

        await viewCertificatePdf(99);

        expect(window.open).toHaveBeenCalledWith('', '_blank');
        expect(apiFetch).toHaveBeenCalledWith('/admin/certificate/99');
        expect(fakeTab.location.href).toBe('blob:mock-url');
        expect(fakeTab.close).not.toHaveBeenCalled();
    });

    it('closes the tab and shows an alert when the response is not ok', async () => {
        vi.mocked(apiFetch).mockResolvedValue({ ok: false } as unknown as Response);

        await viewCertificatePdf(99);

        expect(fakeTab.close).toHaveBeenCalled();
        expect(alertStore.setAlert).toHaveBeenCalledWith('Impossible de récupérer le certificat.', 'error');
    });

    it('closes the tab and shows an alert when the request throws', async () => {
        vi.mocked(apiFetch).mockRejectedValue(new Error('network down'));

        await viewCertificatePdf(99);

        expect(fakeTab.close).toHaveBeenCalled();
        expect(alertStore.setAlert).toHaveBeenCalledWith('Erreur lors de la récupération du certificat.', 'error');
    });

    it('shows an alert and never calls apiFetch when the browser blocks the popup', async () => {
        vi.spyOn(window, 'open').mockReset().mockReturnValue(null);

        await viewCertificatePdf(99);

        expect(apiFetch).not.toHaveBeenCalled();
        expect(alertStore.setAlert).toHaveBeenCalledWith(
            'Le navigateur a bloqué l\'ouverture du PDF. Autorisez les popups pour ce site.',
            'error',
        );
    });
});

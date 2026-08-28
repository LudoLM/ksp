import { describe, expect, it, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import CertificateUpload from './CertificateUpload.vue';
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

const selectFile = async (wrapper: ReturnType<typeof mount>, file: File | undefined) => {
    const input = wrapper.find('input[type="file"]');
    Object.defineProperty(input.element, 'files', {
        value: file ? [file] : [],
        configurable: true,
    });
    await input.trigger('change');
};

const pdfFile = (name = 'certificate.pdf', size = 1024, type = 'application/pdf'): File => {
    const file = new File(['%PDF-1.4'], name, { type });
    Object.defineProperty(file, 'size', { value: size, configurable: true });

    return file;
};

describe('CertificateUpload.vue', () => {
    beforeEach(() => {
        vi.mocked(apiFetch).mockReset();
        vi.mocked(alertStore.setAlert).mockReset();
    });

    it('displays the default label and formats hint', () => {
        const wrapper = mount(CertificateUpload);

        expect(wrapper.text()).toContain('Transmettre mon certificat');
        expect(wrapper.text()).toContain('Format PDF uniquement, 5 Mo maximum.');
    });

    it('accepts a custom button label via props', () => {
        const wrapper = mount(CertificateUpload, {
            props: { buttonLabel: 'Transmettre un nouveau certificat' },
        });

        expect(wrapper.text()).toContain('Transmettre un nouveau certificat');
    });

    it('rejects a non-PDF file without calling the API', async () => {
        const wrapper = mount(CertificateUpload);

        await selectFile(wrapper, pdfFile('photo.png', 1024, 'image/png'));

        expect(wrapper.text()).toContain('Seuls les fichiers PDF sont acceptés.');
        expect(apiFetch).not.toHaveBeenCalled();
    });

    it('rejects a PDF larger than 5 Mo without calling the API', async () => {
        const wrapper = mount(CertificateUpload);

        await selectFile(wrapper, pdfFile('certificate.pdf', 6 * 1024 * 1024));

        expect(wrapper.text()).toContain('Le fichier ne doit pas dépasser 5 Mo.');
        expect(apiFetch).not.toHaveBeenCalled();
    });

    it('uploads a valid PDF and emits "uploaded" on success', async () => {
        vi.mocked(apiFetch).mockResolvedValue({ ok: true, json: async () => ({}) } as Response);

        const wrapper = mount(CertificateUpload);

        await selectFile(wrapper, pdfFile());
        await flushPromises();

        expect(apiFetch).toHaveBeenCalledWith('/certificate/upload', expect.objectContaining({
            method: 'POST',
            body: expect.any(FormData),
        }));
        expect(wrapper.emitted('uploaded')).toHaveLength(1);
        expect(alertStore.setAlert).toHaveBeenCalledWith('Le certificat a bien été enregistré', 'success');
        expect(wrapper.find('p.text-red-600').exists()).toBe(false);
    });

    it('targets the admin endpoint when a userId is provided', async () => {
        vi.mocked(apiFetch).mockResolvedValue({ ok: true, json: async () => ({}) } as Response);

        const wrapper = mount(CertificateUpload, { props: { userId: 42 } });

        await selectFile(wrapper, pdfFile());
        await flushPromises();

        expect(apiFetch).toHaveBeenCalledWith('/admin/users/42/certificate/upload', expect.objectContaining({
            method: 'POST',
            body: expect.any(FormData),
        }));
    });

    it('shows a disabled loading state while the upload is in progress', async () => {
        let resolveFetch: (value: Response) => void = () => {};
        vi.mocked(apiFetch).mockReturnValue(new Promise((resolve) => {
            resolveFetch = resolve;
        }));

        const wrapper = mount(CertificateUpload);
        await selectFile(wrapper, pdfFile());

        expect(wrapper.text()).toContain('Envoi en cours...');
        expect(wrapper.find('button').attributes('disabled')).toBeDefined();

        resolveFetch({ ok: true, json: async () => ({}) } as Response);
        await flushPromises();

        expect(wrapper.text()).not.toContain('Envoi en cours...');
    });

    it('displays the server error message when the upload fails', async () => {
        vi.mocked(apiFetch).mockResolvedValue({
            ok: false,
            json: async () => ({ error: 'Erreur serveur personnalisée' }),
        } as Response);

        const wrapper = mount(CertificateUpload);
        await selectFile(wrapper, pdfFile());
        await flushPromises();

        expect(wrapper.text()).toContain('Erreur serveur personnalisée');
        expect(wrapper.emitted('uploaded')).toBeUndefined();
    });

    it('displays a generic error message when the request throws', async () => {
        vi.mocked(apiFetch).mockRejectedValue(new Error('Network error'));

        const wrapper = mount(CertificateUpload);
        await selectFile(wrapper, pdfFile());
        await flushPromises();

        expect(wrapper.text()).toContain('Une erreur est survenue lors de l\'envoi du certificat.');
        expect(wrapper.emitted('uploaded')).toBeUndefined();
    });
});

function flushPromises(): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, 0));
}

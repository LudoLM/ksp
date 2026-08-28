import { apiFetch } from '@/utils/useFetchInterceptor.ts';
import { alertStore } from '@/store/alert.ts';

const BLOB_URL_REVOKE_DELAY = 60_000;

/**
 * Ouvre le PDF d'un certificat dans un nouvel onglet en passant par apiFetch
 * (bénéficie du refresh automatique du token JWT), plutôt qu'un window.open
 * direct vers l'URL de l'API qui échouerait silencieusement si le token a expiré.
 *
 * L'onglet est ouvert de façon synchrone dès l'appel pour éviter le blocage
 * de popup des navigateurs (qui se déclenche si window.open est appelé après
 * un await). Si ce premier appel est quand même bloqué, on prévient l'admin
 * plutôt que de retenter un window.open après coup, qui échouerait très
 * probablement pour la même raison.
 */
export async function viewCertificatePdf(certificateId: number): Promise<void> {
    const newTab = window.open('', '_blank');

    if (!newTab) {
        alertStore.setAlert('Le navigateur a bloqué l\'ouverture du PDF. Autorisez les popups pour ce site.', 'error');
        return;
    }

    try {
        const response = await apiFetch(`/admin/certificate/${certificateId}`);

        if (!response.ok) {
            newTab.close();
            alertStore.setAlert('Impossible de récupérer le certificat.', 'error');
            return;
        }

        const blob = await response.blob();
        const blobUrl = URL.createObjectURL(blob);
        newTab.location.href = blobUrl;

        setTimeout(() => URL.revokeObjectURL(blobUrl), BLOB_URL_REVOKE_DELAY);
    } catch (error) {
        newTab.close();
        alertStore.setAlert('Erreur lors de la récupération du certificat.', 'error');
    }
}

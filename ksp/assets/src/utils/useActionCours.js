
export async function useDeleteCours(coursId) {
    try {
        const response = await fetch(`/api/cours/delete/${coursId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });

        if (response.ok) {
            console.log('Réponse du serveur:', "Cours supprimé");
            return true;
        } else {
            console.log('Échec de la suppression du cours');
            return false;
        }
    }
    catch (error) {
        console.error('Erreur:', error);
        return false;
    }
}
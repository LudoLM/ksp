
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

export async function useGetPacks() {
    try {
        const response = await fetch('/api/packs', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });

        if (response.ok) {
            const data = await response.json();
            console.log('Réponse du serveur:', data);
            return data;
        } else {
            console.log('Échec de la récupération des packs');
            return [];
        }
    }
    catch (error) {
        console.error('Erreur:', error);
        return [];
    }
}
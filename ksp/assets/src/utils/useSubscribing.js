
export async function useSubscription(coursId) {
    try {
        const response = await fetch(`/api/addUser/${coursId}`, {
            method: 'POST',
        });

        if (response.ok) {
            const result = await response.json();
            console.log('Réponse du serveur:', "Utilisateur ajouté au cours");
            return result;
        } else {
            const errorData = await response.json();
            console.log('Erreur du serveur:', errorData);
            return false;
        }
    } catch (error) {
        console.error('Erreur de réseau:', error);
        return false;
    }
}



export async function useUnSubscription(coursId) {
    try {
        const response = await fetch(`/api/removeUser/${coursId}`, {
            method: 'DELETE',
        });

        if (response.ok) {
            const result = await response.json();
            console.log('Réponse du serveur:', "Utilisateur retiré du cours");
            return result;
        } else {
            console.log('Échec du retrait de l\'utilisateur du cours');
            return false;
        }
    } catch (error) {
        console.error('Erreur:', error);
        return false;
    }
}

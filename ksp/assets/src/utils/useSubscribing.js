
export async function useSubscription(coursId) {

        try {
            const response = await fetch(`/api/addUser/${coursId}`, {
                method: 'POST',
            });

            if (response.ok) {
                console.log('Utilisateur ajouté au cours avec succès');
                return true;
            } else {
                console.log('Échec de l\'ajout de l\'utilisateur au cours');
                return false
            }
        } catch (error) {
            console.error('Erreur:', error);
            return false
        }
}


export async function useUnSubscription(coursId) {
    try {
        const response = await fetch(`/api/removeUser/${coursId}`, {
            method: 'DELETE',
        });

        if (response.ok) {
            console.log('Utilisateur retiré du cours avec succès');
            return true;
        } else {
            console.log('Échec du retrait de l\'utilisateur du cours');
            return false;
        }
    } catch (error) {
        console.error('Erreur:', error);
        return false;
    }
}

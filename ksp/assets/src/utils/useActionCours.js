export async function useGetCours(role, infos) {
    try {
        const response = await fetch("/api/getCours", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-ACCESS-TOKEN": role
            },
        });
        infos.value = await response.json();
    } catch (error) {
        console.error("Erreur lors de la récupération des cours:", error);
    }
}


export async function useGetCoursById(coursId) {
        try {
            const response = await fetch(`/api/getCours/${coursId}`);
            return await response.json();
        } catch (error) {
            console.error('Error fetching cours details:', error);
        }
}


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
            return await response.json();
        } else {
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

export async function useOpenCours(coursId) {
    try {
        const response = await fetch(`/api/cours/open/${coursId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });
        if (response.ok) {
            return await response.json();
        } else {
            return false;
        }
    }
    catch (error) {
        return false;
    }
}

export async function useCancelCours(coursId) {
    try {
        const response = await fetch(`/api/cours/cancel/${coursId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });
        if (response.ok) {
            return await response.json();
        } else {
            return false;
        }
    }
    catch (error) {
        return false;
    }
}
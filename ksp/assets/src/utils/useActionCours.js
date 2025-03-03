import {apiFetch} from "./useFetchInterceptor";

export async function useGetCours(route, infos, currentPage, maxPerPage, totalItems, selectedTypeCours, selectedDate, selectedStatusId, totalPages) {
    try {
      const params = new URLSearchParams({
        currentPage: currentPage.value || 1,
        maxPerPage: maxPerPage.value || 10,
        typeCoursId: selectedTypeCours.value === null ? "0" : selectedTypeCours.value,
        dateCoursStr: selectedDate.value,
        statusCoursId: selectedStatusId.value === null ? "0" : selectedStatusId.value,
      });

      const response = await fetch(`/api/${route.value}?${params.toString()}`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });
      const result = JSON.parse(await response.json());
      infos.value = result.data;
      currentPage.value = result.pagination.currentPage;
      totalItems.value = result.pagination.totalItems;
      totalPages.value = result.pagination.totalPages;
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


export async function useGetTypesCours() {
    try {
        const response = await fetch('/api/getTypesCours', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        return await response.json();


    } catch (error) {
        console.error('Erreur:', error);
        return false;
    }
}

export async function useGetStatusCours() {

    try {
        const response = await fetch('/api/getStatusCours', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',

            },
        });

        return await response.json();

    } catch (error) {
        console.error('Erreur:', error);
        return false;
    }
}


export async function useDeleteCours(coursId) {
    try {
        const response = await apiFetch(`/api/cours/delete/${coursId}`, {
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
        return error;
    }
}

/**
 * Récupère la liste des packs disponibles.
 * @returns {Promise<Object[]>} - Une promesse résolue avec la liste des packs.
 */
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
        const response = await apiFetch(`/api/cours/open/${coursId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
        });

        return await response.json();
    }
    catch (error) {
        return error;
    }
}

export async function useCancelCours(coursId) {
    try {
        const response = await apiFetch(`/api/cours/cancel/${coursId}`, {
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
        return error;
    }
}

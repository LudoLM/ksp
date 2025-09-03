import {apiFetch} from "./useFetchInterceptor";
import {isRef} from "vue";
import {useCalendarStore} from "../store/calendar";


export async function useGetCours(route, infos, selectedTypeCours, selectedDate, selectedStatusCours, isOpenRequired = false) {
    try {
      const typeCoursValue = isRef(selectedTypeCours) ? selectedTypeCours.value : selectedTypeCours;
      const dateCoursValue = isRef(selectedDate) ? selectedDate.value : selectedDate;
      const statusCoursValue = isRef(selectedStatusCours) ? selectedStatusCours.value : selectedStatusCours;
      const isOpenRequiredValue = isRef(isOpenRequired) ? isOpenRequired.value : isOpenRequired;


      let params = new URLSearchParams({
        typeCoursId: typeCoursValue === null ? "0" : typeCoursValue,
        dateCoursStr: dateCoursValue,
        statusCoursId: statusCoursValue === null ? "0" : statusCoursValue,
        isOpenRequired: isOpenRequiredValue,

      });
      const response = await fetch(`/api/${route.value}?${params.toString()}`, {
        method: "GET",
        headers : makeRequestHeaders()
      });
      if (response.ok) {
        infos.value = await response.json();
      }
      else {
        infos.value = await useGetOnlyNextCours(typeCoursValue, dateCoursValue, statusCoursValue);
      }
    } catch (error) {
        console.error(error.message);
    }
}


export async function useGetOnlyNextCours(selectedTypeCours, selectedDate, selectedStatusId) {
  try{
    const typeCoursValue = isRef(selectedTypeCours) ? selectedTypeCours.value : selectedTypeCours;
    const dateCoursValue = isRef(selectedDate) ? selectedDate.value : selectedDate;
    const statusCoursValue = isRef(selectedStatusId) ? selectedStatusId.value : selectedStatusId;

    let params = new URLSearchParams({
      typeCoursId: typeCoursValue === null ? "0" : typeCoursValue,
      dateCoursStr: dateCoursValue,
      statusCoursId: statusCoursValue === null ? "0" : statusCoursValue,
      isOpenRequired: true,
    });

    const response = await fetch(`/api/getOnlyNextCours?${params.toString()}`, {
      method: "GET",
      headers : makeRequestHeaders()
    });
    return await response.json();
  } catch (error) {
    console.error(error.message);
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

export async function handleLaunchAllCours(days) {
  const calendarStore = useCalendarStore();
  const firstAndLastDays = {
    "startDate" : days.value[0],
    "endDate" : days.value[5]
  }
  const response = await apiFetch("/api/week/open", {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(firstAndLastDays)
  });

  const result = await response.json();

  if(response.ok){
    alertStore.setAlert(result.message, "success");
  }
  else{
    alertStore.setAlert(result.message, "error");
  }

  calendarStore.setSelectedStatusCours(0);
  await calendarStore.fetchCoursPerWeek();
}

function makeRequestHeaders() {
    const token = localStorage.getItem('token');
    const headers = {
        'Content-Type': 'application/json',
    };
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    return headers;
}

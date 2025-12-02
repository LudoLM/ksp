import {apiFetch} from "./useFetchInterceptor";
import {isRef} from "vue";
import {useCalendarStore} from "../store/calendar";
import {useUserStore} from "../store/user";
import {alertStore} from "../store/alert.js";


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

    const response = await fetch(`/api/get-only-next-cours?${params.toString()}`, {
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
            const response = await fetch(`/api/get-cours/${coursId}`);
            return await response.json();
        } catch (error) {
            console.error('Error fetching cours details:', error);
        }
}


export async function useGetTypesCours() {
    try {
        const response = await fetch('/api/get-types-cours', {
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
        const response = await fetch('/api/get-status-cours', {
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
        const response = await apiFetch(`/api/admin/cours/delete/${coursId}`, {
            method: 'DELETE',
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

export async function useOpenCours(coursId) {
    try {
        const response = await apiFetch(`/api/admin/cours/open/${coursId}`, {
            method: 'PUT',
        });

        return await response.json();
    }
    catch (error) {
        return error;
    }
}

export async function useCancelCours(coursId) {
    try {
        const response = await apiFetch(`/api/admin/cours/cancel/${coursId}`, {
            method: 'PUT',
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
  const response = await apiFetch("/api/admin/week/open", {
    method: "PUT",
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
    const token = useUserStore().accessToken;
    const headers = {
        'Content-Type': 'application/json',
    };
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    return headers;
}

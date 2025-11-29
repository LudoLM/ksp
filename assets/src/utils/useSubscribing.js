import {apiFetch} from "./useFetchInterceptor";
import {useUserStore} from "../store/user";

export async function useSubscription(coursId, isOnWaitingList, userId = null) {
    try {
        const response = await apiFetch(`/api/addUser`, {
            method: 'POST',
            body: JSON.stringify({coursId, isOnWaitingList, userId})
        });

        const data = await response.json();

        if (data.success) {
            return data;
        } else {
            throw data;
        }
    } catch (error) {
        return error;
    }
}




export async function useUnSubscription(coursId, isOnWaitingList) {
    try {
        const response = await apiFetch(`/api/removeUser`, {
            method: 'PUT',
            body: JSON.stringify({coursId, isOnWaitingList})
        });

        const data = await response.json();
        if (data.success) {
            return data;
        } else {
            throw data;
        }
    } catch (error) {
        return error;
    }
}

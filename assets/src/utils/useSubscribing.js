import {apiFetch} from "./useFetchInterceptor";

export async function useSubscription(coursId, isOnWaitingList, userId = null) {
    try {
        const response = await apiFetch(`/api/addUser`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `bearer ${localStorage.getItem('token')}`
            },
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
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `bearer ${localStorage.getItem('token')}`
            },
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

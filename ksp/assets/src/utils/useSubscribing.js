import {apiFetch} from "./useFetchInterceptor";

export async function useSubscription(coursId, isAttente, userId = null) {
    try {
        const response = await apiFetch(`/api/addUser`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({coursId, isAttente, userId})
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




export async function useUnSubscription(coursId, isAttente) {
    try {
        const response = await apiFetch(`/api/removeUser/${coursId}/${isAttente}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `bearer ${localStorage.getItem('token')}`
            }

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

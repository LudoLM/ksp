
export async function useSubscription(coursId) {
    try {
        const response = await fetch(`/api/addUser/${coursId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `bearer ${localStorage.getItem('token')}`
            }
        });

        if (response.ok) {
            return await response.json();
        } else {
            const errorData = await response.json();
            return false;
        }
    } catch (error) {
        return false;
    }
}



export async function useUnSubscription(coursId) {
    try {
        const response = await fetch(`/api/removeUser/${coursId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `bearer ${localStorage.getItem('token')}`
            }

        });

        if (response.ok) {
            return await response.json();
        } else {
            return false;
        }
    } catch (error) {
        return false;
    }
}

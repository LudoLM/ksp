import {useUserStore} from "../store/user";
import {apiFetch} from "./useFetchInterceptor";

export async function getUser() {
  const userStore = useUserStore();
  try {
    const response = await apiFetch('/api/user', {
      method: 'GET',
      credentials: 'include',
    });
    if (response.ok) {
      const data = await response.json();
      userStore.setUser(data);
    } else {
      userStore.clearUser();
    }
  } catch (error) {
    userStore.clearUser();
  }
}

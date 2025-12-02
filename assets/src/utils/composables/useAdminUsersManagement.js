import {apiFetch} from "../useFetchInterceptor";
import {alertStore} from "../../store/alert";
import {ref} from "vue";


export function useAdminUsersManagement() {

  const users = ref([]);
  const metadata = ref({ total_pages: 1 });
  const currentPage = ref(1);
  const searchUser = ref('');

  const getUsers = async (page) => {
    const targetPage = page !== undefined ? page : currentPage.value;
    try {
      const res = await apiFetch(`/api/admin/users?page=${targetPage}&searchUser=${searchUser.value}`).then((res) => res.json());

      users.value = res.data;
      metadata.value = res.metadata;
      currentPage.value = res.metadata.current_page;

    } catch (error) {
      console.error("Erreur lors de la récupération des utilisateurs:", error);
      alertStore.setAlert("Erreur lors de la récupération des utilisateurs", "error");

      users.value = [];
      metadata.value = { total_pages: 1 };
      currentPage.value = page;
    }
  };

  const resetAllUserCounterCours = async () => {
    try {
      const res = await apiFetch(`/api/admin/users/reset-counters`, {
        method: 'PUT',
      })
      if (res.ok) {
        const data = await res.json();
        alertStore.setAlert(data.message, 'success');
        await getUsers(currentPage.value);
      }
    } catch (error) {
      alertStore.setAlert(error.message, 'error');
    }
  };


return {
  users,
  metadata,
  currentPage,
  searchUser,
  getUsers,
  resetAllUserCounterCours

}


}


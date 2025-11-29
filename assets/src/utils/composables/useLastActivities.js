import {apiFetch} from "../useFetchInterceptor.js";
import {ref, watchEffect} from "vue";

export function useLastActivitiesPerMonth(selectedMonth, selectedYear, userName = ref('') ) {

  const lastActivitiesPerMonth = ref([]);

  const fetchLastActivitiesPerMonth = async (month, year, userName) => {
    const response = await apiFetch(`/api/getUsersActionsPerMonth?month=${month}&year=${year}&userName=${userName}`, {
      method: 'GET',
    });
    lastActivitiesPerMonth.value = await response.json();
  };


  let eventSource = null;

  watchEffect(async () => {
    await fetchLastActivitiesPerMonth(selectedMonth.value, selectedYear.value, userName.value);
    eventSource = new EventSource('https://localhost/.well-known/mercure?topic=admin/notifications')
    if (selectedMonth.value === new Date().getMonth() && selectedYear.value === new Date().getFullYear()) {
      eventSource.onmessage = (e) => lastActivitiesPerMonth.value = [JSON.parse(e.data).content, ...lastActivitiesPerMonth.value];
    }
  });

  return {
    lastActivitiesPerMonth,
  }

}

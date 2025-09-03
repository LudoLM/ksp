import { inject } from 'vue';
import {apiFetch} from "./useFetchInterceptor";
export function useWeekActions() {
  const alertStore = inject('alertStore');

  const handleLaunchAllCours = async (days) => {
    try {
      if (!days || days.length < 2) throw new Error("Dates manquantes");

      const firstAndLastDays = {
        startDate: days[0],
        endDate: days[days.length - 1]
      };

      const response = await apiFetch("/api/week/open", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(firstAndLastDays)
      });

      const result = await response.json();

      if (response.ok) {
        alertStore.setAlert(result.message, "success");
        return { success: true, cours: result.cours };
      } else {
        alertStore.setAlert(result.message || "Erreur", "error");
        return { success: false, cours: [] };
      }
    } catch (error) {
      console.error(error);
      alertStore.setAlert("Erreur inattendue", "error");
      return { success: false };
    }
  };

  return { handleLaunchAllCours };
}

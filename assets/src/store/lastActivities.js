// stores/activities.js
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { apiFetch } from '../utils/useFetchInterceptor.js'

export const useLastActivitiesStore = defineStore('activities', () => {
  const lastActivities = ref([])
  let eventSource = null

  const countActivities = computed(() => lastActivities.value.length)

  const fetchLastActivities = async () => {
    const response = await apiFetch('/api/getLastUsersActions', {
      method: 'GET',
    })
    lastActivities.value = await response.json();
  }

  const clearLastActivities = () => {
    lastActivities.value = [];
  }

  const connectToMercure = () => {
    if (eventSource){
      return;
    }
    eventSource = new EventSource('https://localhost/.well-known/mercure?topic=admin/notifications')

    eventSource.onmessage = (e) => {
      lastActivities.value = [
        JSON.parse(e.data).content,
        ...lastActivities.value.slice(0, 9)
      ]
    }

    eventSource.onerror = (error) => {
      console.error('Erreur Mercure:', error)
    }
  }

  const disconnectFromMercure = () => {
    if (eventSource) {
      eventSource.close()
      eventSource = null
    }
  }

  return {
    lastActivities,
    countActivities,
    fetchLastActivities,
    connectToMercure,
    disconnectFromMercure,
    clearLastActivities
  }
})

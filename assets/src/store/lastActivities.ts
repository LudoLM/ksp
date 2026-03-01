import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { apiFetch } from '../utils/useFetchInterceptor.js'

interface ActivityItem {
  type: string
  dateAction: string
  userName: string
  subject: string
  dateSubject: string
}

export const useLastActivitiesStore = defineStore('activities', () => {
  const lastActivities = ref<ActivityItem[]>([])
  let eventSource: EventSource | null = null

  const countActivities = computed(() => lastActivities.value.length)

  const fetchLastActivities = async (): Promise<void> => {
    const response = await apiFetch('/api/getLastUsersActions', {
      method: 'GET',
    })
    lastActivities.value = await response.json()
  }

  const clearLastActivities = (): void => {
    lastActivities.value = []
  }

  const connectToMercure = (): void => {
    if (eventSource) {
      return
    }

    eventSource = new EventSource(`${window.location.origin}/.well-known/mercure?topic=admin/notifications`)

    eventSource.onmessage = (event: MessageEvent<string>) => {
      const data = JSON.parse(event.data) as { content: ActivityItem }
      lastActivities.value = [
        data.content,
        ...lastActivities.value.slice(0, 9),
      ]
    }

    eventSource.onerror = (error: Event) => {
      if (eventSource?.readyState === EventSource.CLOSED) {
        return
      }
      console.error('Erreur Mercure:', error)
    }
  }

  const disconnectFromMercure = (): void => {
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
    clearLastActivities,
  }
})

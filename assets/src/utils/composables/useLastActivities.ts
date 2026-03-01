import { apiFetch } from '../useFetchInterceptor.js'
import { ref, watchEffect, type Ref } from 'vue'

interface ActivityItem {
  type: string
  dateAction: string
  userName: string
  subject: string
  dateSubject: string
}

export function useLastActivitiesPerMonth(
  selectedMonth: Ref<number>,
  selectedYear: Ref<number>,
  userName: Ref<string> = ref('')
) {
  const lastActivitiesPerMonth = ref<ActivityItem[]>([])
  let eventSource: EventSource | null = null

  const fetchLastActivitiesPerMonth = async (
    month: number,
    year: number,
    userNameValue: string
  ): Promise<void> => {
    const response = await apiFetch(`/api/getUsersActionsPerMonth?month=${month}&year=${year}&userName=${userNameValue}`, {
      method: 'GET',
    })
    lastActivitiesPerMonth.value = await response.json()
  }

  watchEffect(async (onCleanup) => {
    await fetchLastActivitiesPerMonth(selectedMonth.value, selectedYear.value, userName.value)

    if (eventSource) {
      eventSource.close()
      eventSource = null
    }

    const isCurrentMonth = selectedMonth.value === new Date().getMonth()
    const isCurrentYear = selectedYear.value === new Date().getFullYear()

    if (isCurrentMonth && isCurrentYear) {
      eventSource = new EventSource(`${window.location.origin}/.well-known/mercure?topic=admin/notifications`)
      eventSource.onmessage = (event: MessageEvent<string>) => {
        const data = JSON.parse(event.data) as { content: ActivityItem }
        lastActivitiesPerMonth.value = [data.content, ...lastActivitiesPerMonth.value]
      }
    }

    onCleanup(() => {
      if (eventSource) {
        eventSource.close()
        eventSource = null
      }
    })
  })

  return {
    lastActivitiesPerMonth,
  }
}

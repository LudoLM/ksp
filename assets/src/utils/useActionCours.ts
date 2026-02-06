import { apiFetch } from './useFetchInterceptor'
import { isRef, type Ref } from 'vue'
import { useCalendarStore } from '../store/calendar'
import { useUserStore } from '../store/user'
import { alertStore } from '../store/alert'

export async function useGetCours(
  route: Ref<string>,
  infos: Ref<any>,
  selectedTypeCours: Ref<number | null> | number | null,
  selectedDate: Ref<string> | string,
  selectedStatusCours: Ref<number | null> | number | null,
  isOpenRequired: Ref<boolean> | boolean = false
): Promise<void> {
  try {
    const typeCoursValue = isRef(selectedTypeCours) ? selectedTypeCours.value : selectedTypeCours
    const dateCoursValue = isRef(selectedDate) ? selectedDate.value : selectedDate
    const statusCoursValue = isRef(selectedStatusCours) ? selectedStatusCours.value : selectedStatusCours
    const isOpenRequiredValue = isRef(isOpenRequired) ? isOpenRequired.value : isOpenRequired

    const params = new URLSearchParams({
      typeCoursId: typeCoursValue === null ? '0' : String(typeCoursValue),
      dateCoursStr: String(dateCoursValue),
      statusCoursId: statusCoursValue === null ? '0' : String(statusCoursValue),
      isOpenRequired: String(isOpenRequiredValue),
    })

    const response = await fetch(`/api/${route.value}?${params.toString()}`, {
      method: 'GET',
      headers: makeRequestHeaders(),
    })

    if (response.ok) {
      infos.value = await response.json()
    } else {
      infos.value = await useGetOnlyNextCours(typeCoursValue, dateCoursValue, statusCoursValue)
    }
  } catch (error) {
    const err = error as Error
    console.error(err.message)
  }
}

export async function useGetOnlyNextCours(
  selectedTypeCours: Ref<number | null> | number | null,
  selectedDate: Ref<string> | string,
  selectedStatusId: Ref<number | null> | number | null
): Promise<any> {
  try {
    const typeCoursValue = isRef(selectedTypeCours) ? selectedTypeCours.value : selectedTypeCours
    const dateCoursValue = isRef(selectedDate) ? selectedDate.value : selectedDate
    const statusCoursValue = isRef(selectedStatusId) ? selectedStatusId.value : selectedStatusId

    const params = new URLSearchParams({
      typeCoursId: typeCoursValue === null ? '0' : String(typeCoursValue),
      dateCoursStr: String(dateCoursValue),
      statusCoursId: statusCoursValue === null ? '0' : String(statusCoursValue),
      isOpenRequired: 'true',
    })

    const response = await fetch(`/api/get-only-next-cours?${params.toString()}`, {
      method: 'GET',
      headers: makeRequestHeaders(),
    })
    return await response.json()
  } catch (error) {
    const err = error as Error
    console.error(err.message)
  }
}

export async function useGetCoursById(coursId: number): Promise<any> {
  try {
    const response = await fetch(`/api/public/get-cours/${coursId}`)
    return await response.json()
  } catch (error) {
    const err = error as Error
    console.error('Error fetching cours details:', err.message)
  }
}

export async function useGetTypesCours(): Promise<any> {
  try {
    const response = await fetch('/api/public/get-types-cours', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    return await response.json()
  } catch (error) {
    const err = error as Error
    console.error('Erreur:', err.message)
    return false
  }
}

export async function useGetStatusCours(): Promise<any> {
  try {
    const response = await fetch('/api/public/get-status-cours', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    return await response.json()
  } catch (error) {
    const err = error as Error
    console.error('Erreur:', err.message)
    return false
  }
}

export async function useDeleteCours(coursId: number): Promise<any> {
  try {
    const response = await apiFetch(`/api/admin/cours/delete/${coursId}`, {
      method: 'DELETE',
    })

    if (response.ok) {
      return await response.json()
    } else {
      return false
    }
  } catch (error) {
    return error
  }
}

export async function useOpenCours(coursId: number): Promise<any> {
  try {
    const response = await apiFetch(`/api/admin/cours/open/${coursId}`, {
      method: 'PUT',
    })

    return await response.json()
  } catch (error) {
    return error
  }
}

export async function useCancelCours(coursId: number): Promise<any> {
  try {
    const response = await apiFetch(`/api/admin/cours/cancel/${coursId}`, {
      method: 'PUT',
    })
    if (response.ok) {
      return await response.json()
    } else {
      return false
    }
  } catch (error) {
    return error
  }
}

export async function handleLaunchAllCours(days: Ref<any>): Promise<void> {
  const calendarStore = useCalendarStore()
  const firstAndLastDays = {
    startDate: days.value[0],
    endDate: days.value[5],
  }
  const response = await apiFetch('/api/admin/week/open', {
    method: 'PUT',
    body: JSON.stringify(firstAndLastDays),
  })

  const result = await response.json()

  if (response.ok) {
    alertStore.setAlert(result.message, 'success')
  } else {
    alertStore.setAlert(result.message, 'error')
  }

  calendarStore.setSelectedStatusCours(0)
  await calendarStore.fetchCoursPerWeek()
}

function makeRequestHeaders(): Record<string, string> {
  const token = (useUserStore() as any).accessToken as string | undefined
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  }
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }
  return headers
}


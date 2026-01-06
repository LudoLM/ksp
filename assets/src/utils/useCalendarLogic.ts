/**
 * Composable for calendar logic operations
 * Handles date formatting, URL updates, course navigation, and admin actions
 */

import { Ref, ComputedRef } from 'vue'
import { useCalendarStore } from '../store/calendar'
import { alertStore } from '../store/alert'
import { apiFetch } from './useFetchInterceptor'

interface UseCalendarLogicParams {
  calendarStore: ReturnType<typeof useCalendarStore>
  selectedTypeCours: Ref<number> | ComputedRef<number>
  selectedStatusCours: Ref<number> | ComputedRef<number>
  days: ComputedRef<string[]>
  isOpenRequiredFromUrl: Ref<boolean>
}

/**
 * Formats a date string into HTML with day name and number
 * @param day - Date string in YYYY-MM-DD format
 * @returns HTML string with formatted day
 */
export const formatDay = (day: string): string => {
  const date = new Date(day)
  const daysOfWeek = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
  const dayOfWeek = daysOfWeek[date.getDay()]
  const dayPart = day.split('-')[2]
  return `<p>${dayOfWeek.substring(0, 3)} </p><p> ${dayPart}</p>`
}

/**
 * Updates the browser URL with current filter parameters
 * @param keepIsOpenRequired - Whether to preserve the isOpenRequired parameter
 * @param selectedTypeCours - Currently selected course type
 * @param selectedStatusCours - Currently selected course status
 */
export const updateUrl = (
  keepIsOpenRequired: boolean,
  selectedTypeCours: number,
  selectedStatusCours: number
): void => {
  const newParams = new URLSearchParams(window.location.search)
  
  if (newParams.has('isOpenRequired') && !keepIsOpenRequired) {
    newParams.delete('isOpenRequired')
  }
  
  if (selectedTypeCours !== 0) {
    newParams.set('typeCoursId', selectedTypeCours.toString())
  } else {
    newParams.delete('typeCoursId')
  }
  
  if (selectedStatusCours !== 0) {
    newParams.set('statusCoursId', selectedStatusCours.toString())
  } else {
    newParams.delete('statusCoursId')
  }
  
  window.history.replaceState({}, '', `${window.location.pathname}?${newParams}`)
}

/**
 * Displays formatted next course date string
 * @param date - Date of next course
 * @param typeCours - Course type name
 * @param statusCours - Course status name
 * @param selectedTypeCours - Currently selected type filter
 * @param selectedStatusCours - Currently selected status filter
 * @returns Formatted date string
 */
export const displayDateNextCoursString = (
  date: string | Date,
  typeCours: string,
  statusCours: string,
  selectedTypeCours: number,
  selectedStatusCours: number
): string => {
  return `Prochain cours ${selectedTypeCours !== 0 ? " de " + typeCours : ""}  ${selectedStatusCours !== 0 ? " " + statusCours : ""} disponible le ${new Date(date).toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })}`
}

/**
 * Main composable function combining all calendar logic operations
 */
export const useCalendarLogic = (params: UseCalendarLogicParams) => {
  const { calendarStore, selectedTypeCours, selectedStatusCours, days, isOpenRequiredFromUrl } = params

  /**
   * Handles week navigation (prev, next, or jump to next course)
   */
  const handleGetCoursPerWeek = async (direction: 'prev' | 'next' | 'nextCours'): Promise<void> => {
    if (isOpenRequiredFromUrl.value) {
      isOpenRequiredFromUrl.value = false
      updateUrl(false, selectedTypeCours.value, selectedStatusCours.value)
    }

    if (direction === 'next') {
      calendarStore.nextWeek()
    } else if (direction === 'prev') {
      calendarStore.prevWeek()
      calendarStore.setDaySelected(5)
    } else {
      calendarStore.nextCours()
    }
  }

  /**
   * Handles opening/launching all courses for the week (admin action)
   */
  const handleLaunchAllCours = async (): Promise<void> => {
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

  return {
    handleGetCoursPerWeek,
    handleLaunchAllCours,
  }
}

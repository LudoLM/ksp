<script setup lang="ts">
import { computed, onBeforeMount, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'
import { useCalendarStore } from '../store/calendar'
import { useUserStore } from '../store/user'
import CalendarHeader from '../components/calendar/CalendarHeader.vue'
import CalendarGrid from '../components/calendar/CalendarGrid.vue'
import CalendarMobile from '../components/calendar/CalendarMobile.vue'
import { useCalendarLogic, updateUrl, displayDateNextCoursString } from '../utils/useCalendarLogic'

// ===== STORE & ROUTER SETUP =====
const calendarStore = useCalendarStore()
const userStore = useUserStore()
const route = useRoute()

// ===== REFS & COMPUTED =====
const dateToday = ref(new Date())

const typeCoursIdFromUrl = Number(route.query.typeCoursId ?? 0)
const statusCoursIdFromUrl = Number(route.query.statusCoursId ?? 0)
const isInitializing = ref(true)
const nextDateIndex = ref<number | null>(null)

// Computed values from store
const date = computed(() => calendarStore.date)
const daySelected = computed(() => calendarStore.daySelected)
const weekString = computed(() => calendarStore.weekString)
const days = computed(() => calendarStore.days)
const infos = computed(() => calendarStore.infos)
const { weekInfos, selectedTypeCours, selectedStatusCours } = storeToRefs(calendarStore)
const uniqueTypeCoursList = computed(() => calendarStore.uniqueTypeCoursList)

// Filter status list based on user role
const uniqueStatusCoursList = computed(() =>
  userStore.isAdmin
    ? calendarStore.uniqueStatusCoursList.filter((s) => ![6, 7].includes(s.id))
    : calendarStore.uniqueStatusCoursList.filter((s) => ![4, 6, 7].includes(s.id))
)

// ===== CALENDAR LOGIC COMPOSABLE =====
const { handleGetCoursPerWeek, handleLaunchAllCours } = useCalendarLogic({
  calendarStore,
  selectedTypeCours,
  selectedStatusCours,
  days,
})

// ===== COMPUTED: NEXT COURSE DATES =====
const nextDateInTheWeek = computed(() => {
  const getCoursInRestOfWeek = calendarStore.weekInfos.slice(calendarStore.daySelected).find((info) => info.length > 0)

  // If there are courses remaining in the week after selected day
  if (getCoursInRestOfWeek) {
    nextDateIndex.value = getCoursInRestOfWeek ? new Date(getCoursInRestOfWeek[0].dateCours).getDay() - 1 : null
    return displayDateNextCoursString(
      getCoursInRestOfWeek[0].dateCours,
      getCoursInRestOfWeek[0].typeCours.libelle,
      getCoursInRestOfWeek[0].statusCours.libelle,
      selectedTypeCours.value,
      selectedStatusCours.value
    )
  }
  // If no courses in remaining week, get first course from upcoming weeks
  else if (
    !getCoursInRestOfWeek &&
    calendarStore.firstNextCoursInNextWeeks &&
    calendarStore.firstNextCoursInNextWeeks.nextCoursDate
  ) {
    nextDateIndex.value = null
    return displayDateNextCoursString(
      calendarStore.firstNextCoursInNextWeeks.nextCoursDate.date,
      calendarStore.firstNextCoursInNextWeeks.typeCours ?? '',
      calendarStore.firstNextCoursInNextWeeks.statusCours ?? '',
      selectedTypeCours.value,
      selectedStatusCours.value
    )
  }
})

const nextDateInNextWeek = computed(() => {
  const infosValue = infos.value as any
  if (infosValue?.type === 'info_next_cours') {
    return displayDateNextCoursString(
      infosValue.nextCoursDate?.date ?? '',
      infosValue.typeCours ?? '',
      infosValue.statusCours ?? '',
      selectedTypeCours.value,
      selectedStatusCours.value
    )
  }
})

// ===== LIFECYCLE HOOKS =====
onBeforeMount(async () => {
  // Initialize selectedTypeCours from URL if exists
    if ((!isNaN(typeCoursIdFromUrl) && typeCoursIdFromUrl !== 0) || (!isNaN(statusCoursIdFromUrl) && statusCoursIdFromUrl)) {
    calendarStore.setSelectedTypeCours(typeCoursIdFromUrl)
    calendarStore.setSelectedStatusCours(statusCoursIdFromUrl)
    await calendarStore.fetchCoursPerWeek()
  } else {
    await calendarStore.fetchCoursPerWeek()
  }

  // Fetch
    // ourse types and statuses
  await calendarStore.fetchTypesCours()
  await calendarStore.fetchStatusCours()
  isInitializing.value = false
})

// ===== WATCHERS =====
// Update store and URL when filters change
watch([selectedTypeCours, selectedStatusCours], () => {
  calendarStore.setSelectedTypeCours(selectedTypeCours.value)
  calendarStore.setSelectedStatusCours(selectedStatusCours.value)
  updateUrl(selectedTypeCours.value, selectedStatusCours.value)
})

// Fetch data when date or filters change
watch([date, selectedTypeCours, selectedStatusCours], async () => {
  if (isInitializing.value) {
    return
  }
  calendarStore.firstNextCoursInNextWeeks = null
  await calendarStore.fetchCoursPerWeek()
})

// ===== EVENT HANDLERS =====
const handleNavigate = async (direction: 'prev' | 'next'): Promise<void> => {
  await handleGetCoursPerWeek(direction)
}

const handleReset = (): void => {
  calendarStore.resetCalendar()
  updateUrl(0, 0)
}

const handleLaunchWeek = async (): Promise<void> => {
  await handleLaunchAllCours()
}

const handleNavigateDay = (direction: 'prev' | 'next'): void => {
  if (direction === 'prev') {
    if (daySelected.value !== 0) {
      calendarStore.setDaySelected(daySelected.value - 1)
    } else {
      handleGetCoursPerWeek('prev')
    }
  } else {
    if (daySelected.value !== 5) {
      calendarStore.setDaySelected(daySelected.value + 1)
    } else {
      handleGetCoursPerWeek('next')
    }
  }
}

const handleNavigateWeek = async (direction: 'prev' | 'next'): Promise<void> => {
  await handleGetCoursPerWeek(direction)
}

const handleGoToNextCourse = (dateValue: Date, dayIndex?: number): void => {
  calendarStore.setDate(dateValue)
  if (dayIndex !== undefined) {
    calendarStore.setDaySelected(dayIndex)
  } else {
    calendarStore.setDaySelected(new Date(dateValue).getDay() - 1)
  }
  handleGetCoursPerWeek('nextCours')
}
</script>

<template>
  <CalendarHeader
    :weekString="weekString"
    :uniqueTypeCoursList="uniqueTypeCoursList"
    :uniqueStatusCoursList="uniqueStatusCoursList"
    :selectedTypeCours="selectedTypeCours"
    :selectedStatusCours="selectedStatusCours"
    :shouldPreviousWeekDisabled="calendarStore.shouldPreviousWeekDisabled"
    :isAdmin="userStore.isAdmin"
    :canLaunchWeek="weekInfos.some((day) => day.some((cours) => cours.statusCours.id === 4))"
    @navigate="handleNavigate"
    @update:selectedTypeCours="selectedTypeCours = $event"
    @update:selectedStatusCours="selectedStatusCours = $event"
    @reset="handleReset"
    @launch-week="handleLaunchWeek"
  />

  <CalendarGrid
    :days="days"
    :weekInfos="weekInfos"
    :daySelected="daySelected"
    :infos="infos"
    :nextDateInNextWeek="nextDateInNextWeek"
    :isAdminRoute="false"
    @select-day="calendarStore.setDaySelected"
    @go-to-next-course="handleGoToNextCourse"

  />

  <CalendarMobile
    :days="days"
    :daySelected="daySelected"
    :weekInfos="weekInfos"
    :date="date"
    :dateToday="dateToday"
    :infos="infos"
    :nextDateInNextWeek="nextDateInNextWeek"
    :nextDateInTheWeek="nextDateInTheWeek"
    :nextDateIndex="nextDateIndex"
    :firstNextCoursInNextWeeks="calendarStore.firstNextCoursInNextWeeks"
    :isAdminRoute="false"
    @navigate-day="handleNavigateDay"
    @navigate-week="handleNavigateWeek"
    @go-to-next-course="handleGoToNextCourse"
    @select-day="calendarStore.setDaySelected"
  />
</template>

<style scoped>
/* Styles are now in child components */
</style>



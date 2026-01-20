<script setup lang="ts">
import CoursCardCalendar from '../CoursCardCalendar.vue'
import type { CalendarGridProps } from '../../types'

defineProps<CalendarGridProps>()

interface CalendarGridEmits {
  'select-day': [index: number]
  'go-to-next-course': [date: Date]
}

defineEmits<CalendarGridEmits>()

/**
 * Formats a date string into HTML with day name and number
 */
const formatDay = (day: string): string => {
  const date = new Date(day)
  const daysOfWeek = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
  const dayOfWeek = daysOfWeek[date.getDay()]
  const dayPart = day.split('-')[2]
  return `<p>${dayOfWeek.substring(0, 3)} </p><p> ${dayPart}</p>`
}
</script>

<template>
  <div class="p-10 bg-white grid-container">
    <div class="grid grid-cols-6 gap-4">
      <div
        v-for="(day, index) in days"
        :key="day"
        class="flex justify-center day-cell"
        @click="$emit('select-day', index)"
      >
        <div
          v-html="formatDay(day)"
          :class="[daySelected === index ? 'days dayActif' : 'days', weekInfos[index]?.length > 0 ? 'has-cours' : '']"
        />
      </div>
      <div v-if="weekInfos.every((info) => info.length === 0)" class="col-span-6 mx-auto text-center p-4 m-20">
        <a v-if="(infos as any)?.type === 'info_next_cours'" @click="$emit('go-to-next-course', new Date((infos as any).nextCoursDate?.date ?? ''))">
          {{ nextDateInNextWeek }}
        </a>
        <p v-else>
          {{ (infos as any)?.message }}
        </p>
      </div>
      <div v-for="(weekInfo, index) in weekInfos" :key="index">
        <div v-for="info in weekInfo" :key="info.id" class="flex flex-col items-center">
          <CoursCardCalendar :info="info" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.grid-container {
  display: block;
}

a {
  color: #472371;
  text-decoration: underline;
  cursor: pointer;
}

.days {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  padding-bottom: 20px;
  width: 100%;
  min-height: 5vh;
}

.has-cours {
  color: #5e2ca5;
  font-weight: bold;
}

.dayActif {
  border-bottom: 1px solid #5e2ca5;
}

@media (min-width: 980px) {
  .days {
    font-weight: normal;
    cursor: default;
    pointer-events: none;
  }

  .has-cours {
    width: 100%;
    border: none;
    color: #000;
  }

  .dayActif {
    border-bottom: none;
  }

  .day-cell {
    cursor: pointer;
  }

  .day-cell:hover .days {
    pointer-events: auto;
  }
}

@media (max-width: 980px) {
  .grid-container {
    display: none;
  }

  .days {
    opacity: 0.4;
  }

  .dayActif {
    opacity: 1;
  }
}
</style>

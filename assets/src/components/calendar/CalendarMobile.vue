<script setup lang="ts">
import CoursCardCalendar from '../CoursCardCalendar.vue'
import type { CalendarMobileProps } from '@/types'

defineProps<CalendarMobileProps>()

interface CalendarMobileEmits {
  'navigate-day': [direction: 'prev' | 'next']
  'navigate-week': [direction: 'prev' | 'next']
  'go-to-next-course': [date: Date, dayIndex?: number]
  'select-day': [index: number]
}

defineEmits<CalendarMobileEmits>()

/**
 * Formats a date string into HTML with day name and number (same as CalendarGrid)
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
  <div class="p-10 bg-white mobile-container">
    <!-- Mobile day selector -->
    <div class="mobile-days-selector">
      <div
        v-for="(day, index) in days"
        :key="day"
        class="mobile-day"
        :class="[
          daySelected === index ? 'mobile-day-active' : '',
          weekInfos[index]?.length > 0 ? 'has-cours' : ''
        ]"
        @click="$emit('select-day', index)"
        v-html="formatDay(day)"
      />
    </div>

    <!-- Mobile carousel -->
    <div class="mobile">
      <div
        :class="daySelected !== 0 || new Date(date) > new Date(dateToday) ? 'dayBefore' : 'dayBefore invisible'"
        @click="
          daySelected !== 0
            ? $emit('navigate-day', 'prev')
            : $emit('navigate-week', 'prev')
        "
      />
      <div class="active">
        <div v-if="weekInfos[daySelected]?.length > 0">
          <div v-for="info in weekInfos[daySelected]" :key="info.id" class="flex flex-col items-center">
            <CoursCardCalendar :info="info" />
          </div>
        </div>
        <div v-else class="flex justify-center items-center h-70 text-center nextDateDisplayed">
          <a
            v-if="(infos as any)?.type === 'info_next_cours'"
            @click="$emit('go-to-next-course', new Date((infos as any).nextCoursDate?.date ?? ''), new Date((infos as any).nextCoursDate?.date ?? '').getDay() - 1)"
          >
            {{ nextDateInNextWeek }}
          </a>
          <a
            v-else-if="nextDateInTheWeek"
            @click="
              $emit(
                'go-to-next-course',
                !nextDateIndex ? (firstNextCoursInNextWeeks as any)?.nextCoursDate?.date ?? date : date,
                nextDateIndex ? nextDateIndex : new Date((firstNextCoursInNextWeeks as any)?.nextCoursDate?.date ?? '').getDay() - 1
              )
            "
          >
            {{ nextDateInTheWeek }}
          </a>
          <p v-else>
            {{ (firstNextCoursInNextWeeks as any)?.message ?? (infos as any)?.message }}
          </p>
        </div>
      </div>
      <div
        :class="daySelected !== 5 ? 'dayAfter' : 'dayAfter'"
        @click="
          daySelected !== 5
            ? $emit('navigate-day', 'next')
            : $emit('navigate-week', 'next')
        "
      />
    </div>
  </div>
</template>

<style scoped>

a {
  color: #472371;
  text-decoration: underline;
  cursor: pointer;
}

.mobile {
  display: none;
}

.mobile-days-selector {
  display: none;
}

.nextDateDisplayed {
  font-size: clamp(0.9rem, 1.4vw, 1.4rem);
}

@media (max-width: 980px) {
  .mobile-days-selector {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1rem;
  }

  .mobile-day {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    padding-bottom: 20px;
    width: 100%;
    opacity: 0.4;
  }

  .mobile-day.has-cours {
    color: #5e2ca5;
    font-weight: bold;
  }

  .mobile-day-active {
    opacity: 1;
    border-bottom: 1px solid #5e2ca5;
  }

  .mobile {
    display: flex;
    justify-content: space-around;
    gap: 10vw;
    position: relative;
    height: auto;
    margin-top: 50px;
  }

  .active {
    width: 40vw;
    position: relative;
    transition: all 0.5s ease;
  }

  .dayBefore,
  .dayAfter {
    width: clamp(40px, 9vw, 150px);
    height: clamp(40px, 9vw, 150px);
    background: url('../../../../assets/icons/arrow.svg') no-repeat center;
    background-size: 50%;
    margin-top: 100px;
    cursor: pointer;
    position: relative;
  }

  .dayBefore::after,
  .dayAfter::after {
    position: absolute;
    content: '';
    width: clamp(40px, 9vw, 150px);
    height: clamp(40px, 9vw, 150px);
    background: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    top: 0;
    left: 0;
  }

  .dayBefore {
    transform: rotate(180deg);
  }

  .invisible {
    visibility: hidden;
  }
}
</style>

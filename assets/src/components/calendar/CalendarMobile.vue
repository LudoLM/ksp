<script setup lang="ts">
import CoursCardCalendar from '../CoursCardCalendar.vue'

interface CalendarMobileProps {
  daySelected: number
  weekInfos: any[][]
  date: Date
  dateToday: Date
  infos: any
  nextDateInNextWeek: string | undefined
  nextDateInTheWeek: string | undefined
  nextDateIndex: number | null
  firstNextCoursInNextWeeks: any
}

defineProps<CalendarMobileProps>()

interface CalendarMobileEmits {
  'navigate-day': [direction: 'prev' | 'next']
  'navigate-week': [direction: 'prev' | 'next']
  'go-to-next-course': [date: Date, dayIndex?: number]
}

defineEmits<CalendarMobileEmits>()
</script>

<template>
  <div class="p-10 bg-white mobile-container">
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
            v-if="infos.type === 'info_next_cours'"
            @click="$emit('go-to-next-course', new Date(infos.nextCoursDate.date), new Date(infos.nextCoursDate.date).getDay() - 1)"
          >
            {{ nextDateInNextWeek }}
          </a>
          <a
            v-else-if="nextDateInTheWeek"
            @click="
              $emit(
                'go-to-next-course',
                !nextDateIndex ? firstNextCoursInNextWeeks.nextCoursDate.date : date,
                nextDateIndex ? nextDateIndex : new Date(firstNextCoursInNextWeeks.nextCoursDate.date).getDay() - 1
              )
            "
          >
            {{ nextDateInTheWeek }}
          </a>
          <p v-else>
            {{ firstNextCoursInNextWeeks && firstNextCoursInNextWeeks.message ? firstNextCoursInNextWeeks.message : infos.message }}
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
.mobile-container {
  padding: 0;
}

a {
  color: #472371;
  text-decoration: underline;
  cursor: pointer;
}

.mobile {
  display: none;
}

.nextDateDisplayed {
  font-size: clamp(0.9rem, 1.4vw, 1.4rem);
}

@media (max-width: 980px) {
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
    background: url('../../../assets/icons/arrow.svg') no-repeat center;
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

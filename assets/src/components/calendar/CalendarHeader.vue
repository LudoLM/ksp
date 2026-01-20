<script setup lang="ts">
import CustomButton from '../forms/CustomButton.vue'
import DeleteItem from '../../../icons/adminActions/DeleteItem.vue'
import Tooltip from '../Tooltip.vue'
import TypeCoursFilter from '../filtersCours/TypeCoursFilter.vue'
import StatusCoursFilter from '../filtersCours/StatusCoursFilter.vue'
import bannerImage from '../../../images/banners/imageBanner9.jpg'
import Banner from '../Banner.vue'
import type { CalendarHeaderProps } from '../../types'

defineProps<CalendarHeaderProps>()

interface CalendarHeaderEmits {
  navigate: [direction: 'prev' | 'next']
  'update:selectedTypeCours': [value: number]
  'update:selectedStatusCours': [value: number]
  reset: []
  'launch-week': []
}

defineEmits<CalendarHeaderEmits>()
</script>

<template>
  <Banner
    title="Planning des cours"
    :backgroundColor="'rgba(30, 27, 75, .9)'"
    :image="bannerImage"
    :hasButton="false"
  />
  <div class="flex flex-col justify-center items-center gap-4 my-8 relative">
    <div>
      <p>{{ weekString }}</p>
    </div>
    <div class="buttons flex justify-center gap-1">
      <CustomButton
        :disabled="shouldPreviousWeekDisabled"
        :color="shouldPreviousWeekDisabled ? 'gray' : 'purple'"
        @click="$emit('navigate', 'prev')"
      >
        Semaine Précédente
      </CustomButton>
      <CustomButton @click="$emit('navigate', 'next')">
        Semaine Suivante
      </CustomButton>
    </div>
    <div class="flex flex-col justify-center items-center gap-2">
      <div class="flex justify-center items-center gap-2 mx-2 mb-2">
        <TypeCoursFilter
          :uniqueTypeCoursList="uniqueTypeCoursList"
          :typeCoursId="selectedTypeCours"
          @update:typeCoursId="$emit('update:selectedTypeCours', $event)"
        />
        <StatusCoursFilter
          :uniqueStatusCoursList="uniqueStatusCoursList"
          :selectedStatusId="selectedStatusCours"
          @update:selectedStatusId="$emit('update:selectedStatusCours', $event)"
        />
        <Tooltip title="Réinitialiser les filtres et revenir à la semaine actuelle" tooltip-pos="right">
          <button class="hover:text deleteIcon" @click="$emit('reset')">
            <DeleteItem size="18" />
          </button>
        </Tooltip>
      </div>

      <div class="flex justify-center items-center gap-2">
        <CustomButton v-if="isAdmin && canLaunchWeek" @click="$emit('launch-week')" class="mb-4">
          Ouvrir la semaine
        </CustomButton>
      </div>
    </div>
  </div>
</template>

<style scoped>
.deleteIcon {
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  transition: border 0.3s ease-in-out;
  font-size: clamp(0.8rem, 1.5vw, 1rem);
}

.deleteIcon > p {
  font-size: clamp(0.8rem, 1.5vw, 1rem);
}

.deleteIcon:hover {
  border-color: darkred;
}
</style>

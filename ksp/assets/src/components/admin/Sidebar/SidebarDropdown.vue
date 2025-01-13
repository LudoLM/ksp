<script setup>
import {useSidebarStore} from '../../../store/sidebar.js'

const sidebarStore = useSidebarStore()

const props = defineProps({
    items: { type: Array },
    index: { type: Number }
});

const handleItemClick = (index) => {
    sidebarStore.selected = sidebarStore.selected === props.items[index].label ? '' : props.items[index].label
}
</script>



<template>
  <ul class="mt-4 mb-5.5 flex gap-2.5 pl-6">
    <template v-for="(childItem, index) in items" :key="index">
      <li>
        <router-link
          :to="{ name: childItem.route }"
          @click="handleItemClick(index)"
          class="group relative flex items-center gap-2.5 rounded-md px-4 font-medium text-bodydark2 duration-300 ease-in-out hover:text-white"
          :class="{
            '!text-white': childItem.label === sidebarStore.selected
          }"
        >
          {{ childItem.label }}
        </router-link>
      </li>
    </template>
  </ul>
</template>

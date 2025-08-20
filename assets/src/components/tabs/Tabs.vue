<script setup>
import { ref, computed, useSlots } from 'vue'

const emit = defineEmits(['changeTab'])

const active = ref(0)
const slots = useSlots().default?.()
const transform = computed(() =>
    `translate3d(-${active.value * 100}%, 0px, 0px)`
)

function selectTab(index) {
    active.value = index
    emit('changeTab', index)
}
</script>

<template>
    <div class="overflow-hidden flex flex-col">
        <div class="flex items-center justify-around my-10">
            <span
                v-for="(slot, index) in slots" :key="slot.props?.title"
                @click="selectTab(index)"
                class="w-full flex items-center justify-center cursor-pointer mx-10 pb-2 border-b-2"
                :class="active === index  ? 'border-templateMainColor' : 'border-transparent'"
            >
              {{ slot.props?.title }}
            </span>
        </div>
        <div
            class="flex transition-transform [&>*]:w-full [&>*]:shrink-0"
            :style="{ transform }"
        >
            <slot />
        </div>
    </div>
</template>

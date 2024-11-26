<script setup>

defineProps({
  color: {
    type: String,
    default: "violet"
  }
});

</script>

<template>
  <button class="color-button mt-3 mx-2 block bg-{{ color }}-800 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-{{ color }}-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600"
          :class="[
            `bg-${color}-800`,
            `hover:bg-${color}-600`,
            `focus-visible:outline-${color}-600`
    ]">
    <slot></slot>
  </button>
</template>

<style scoped lang="scss">

</style>
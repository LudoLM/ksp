<script setup>

defineProps({
  color: {
    type: String,
    default: "indigo"
  }
});

</script>

<template>
  <button class="mt-3 mx-2 block rounded-md bg-{{ color }}-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-{{ color }}-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          :class="[
            `bg-${color}-600`,
            `hover:bg-${color}-500`,
            `focus-visible:outline-${color}-600`
    ]">
    <slot></slot>
  </button>
</template>

<style scoped lang="scss">

</style>
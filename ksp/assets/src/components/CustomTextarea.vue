<script setup>

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  item: {
    type: String,
    default: "",
  },
  id: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div class="form-group mb-4">
    <label :for="id" class="block text-gray-700 font-semibold mb-2">{{ item }}:</label>
    <textarea
        :id="id"
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
  </div>
</template>

<style scoped lang="scss">

</style>
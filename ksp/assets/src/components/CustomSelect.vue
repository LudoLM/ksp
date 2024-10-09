<script setup>
defineProps({
  options: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: [String, Number],
    default: "",
  },
  item: String,
  id: String,
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div class="form-group mb-4">
    <label :for="id" class="block text-gray-700 font-semibold mb-2">{{ item }}:</label>
    <select
        :id="id"
        :value="modelValue"
        @change="emit('update:modelValue', $event.target.value)"
        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
      <option v-for="cours in options" :key="cours.id" :value="cours.id">{{ cours.libelle }}</option>
    </select>
  </div>
</template>


<style scoped lang="scss">

</style>
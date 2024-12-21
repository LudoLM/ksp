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
  error: {
    type: String,
    default: null,
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
    <div class="error-message text-red-500 text-sm h-4">
      {{ error }}
    </div>
  </div>
  <!-- Affiche le message d'erreur s'il est présent -->
</template>

<style scoped lang="scss">
  textarea {
    border-radius: 0;
    border: 1px solid #000;
    background: #fff;
  }
</style>
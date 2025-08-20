<script setup>

defineProps({
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
  <div>
    <label :for="id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ item }}:</label>
    <textarea
        :id="id"
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        class="dark:bg-dark-900 w-full h-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
    />
    <div class="error-message text-red-500 text-sm h-4">
      {{ error }}
    </div>
  </div>
  <!-- Affiche le message d'erreur s'il est présent -->
</template>

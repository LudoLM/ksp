<template>
  <div class="form-group mb-4">
    <label :for="id" class="block text-gray-700 font-semibold mb-2">{{ item }}:</label>
    <input
        :type="type"
        :id="id"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
    <!-- Affiche le message d'erreur s'il est présent -->
    <div class="error-message text-red-500 text-sm h-4">
      {{ error }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    item: String,
    type: String,
    id: String,
    modelValue: [String, Number, Date],
    error: {
      type: String,
      default: null,
    },
  },
};

</script>


<style scoped lang="scss">

input {
  border-radius: 0;
  border: 2px solid #000;
  background: #fff;
}

</style>

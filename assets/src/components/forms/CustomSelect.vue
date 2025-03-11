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

<!--<template>
  <div class="form-group mb-4">
    <label :for="id" class="block text-gray-700 font-semibold mb-2" v-if="item !== ''">{{ item }}:</label>
    <select
        :id="id"
        :value="modelValue"
        @change="emit('update:modelValue', $event.target.value)"
        class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
    >

      <option value="0" v-if="id"> {{ id }}</option>
      <option v-for="cours in options" :key="cours.id" :value="cours.id">{{ cours.libelle }}</option>
    </select>
  </div>
</template>-->

<template>
    <div class="space-y-6">
        <!-- Single Select Input -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" v-if="item !== ''">
                {{ item }}
            </label>
            <div class="relative z-20 bg-transparent">
                <select
                    :id="id"
                    :value="modelValue"
                    @change="emit('update:modelValue', $event.target.value)"
                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                >
                    <option value="0" v-if="id">Sélectionner</option>
                    <option v-for="cours in options" :key="cours.id" :value="cours.id" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ cours.libelle }}</option>
                </select>
                <span
                    class="absolute z-30 text-gray-700 -translate-y-1/2 pointer-events-none right-4 top-1/2 dark:text-gray-400"
                >
          <svg
              class="stroke-current"
              width="20"
              height="20"
              viewBox="0 0 20 20"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
          >
            <path
                d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396"
                stroke=""
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
          </svg>
        </span>
            </div>
        </div>
    </div>
</template>

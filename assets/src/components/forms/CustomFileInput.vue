<template>
    <div class="space-y-6">
        <!-- Elements -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                {{ item }}
            </label>
            <input
                type="file"
                class="focus:border-ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pl-3.5 file:pr-3 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden focus:file:ring-brand-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400"
            />
            <div class="error-message text-red-500 text-xs h-4">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script setup>

defineProps({
    item: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: null,
    },
    id: String,
    placeholder: String,
    modelValue: [String, Number, Date],
    isRequired: {
        type: Boolean,
        default: false,
    },
});


</script>

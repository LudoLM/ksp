<template>
    <div :class="isHorizontal ? 'flex justify-between items-center gap-2' : ''">
        <label
            :for="id"
            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400 text-xs sm:text-sm"
        >
            {{ item }}<span v-if="isRequired" class="text-red-600">*</span>
        </label>
        <input
            :type="type"
            :id="id"
            :value="modelValue"
            :placeholder="placeholder"
            @input="$emit('update:modelValue', $event.target.value)"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-xs md:text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
        />
        <div class="error-message text-red-500 text-xs h-4">
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
        placeholder: String,
        modelValue: [String, Number, Date],
        isHorizontal: {
            type: Boolean,
            default: false,
        },
        error: {
            type: String,
            default: null,
        },
        isRequired: {
            type: Boolean,
            default: false,
        },
    },
};

</script>






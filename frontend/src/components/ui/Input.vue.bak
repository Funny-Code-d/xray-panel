<script setup>
defineProps({
  modelValue: String,
  label: String,
  error: String,
  type: {
    type: String,
    default: 'text',
  },
  placeholder: String,
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="input-wrap">
    <label v-if="label" class="block text-sm font-bold uppercase tracking-wide mb-2 transition-opacity duration-200">
      {{ label }}
    </label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-full px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 focus:outline-none transition-all duration-200"
      :class="error
        ? 'border-red-500 shadow-[3px_3px_0_#dc2626] dark:shadow-[3px_3px_0_#f87171]'
        : 'border-black dark:border-white focus:shadow-brutal-sm'"
    />
    <Transition name="fade-slide">
      <p v-if="error" class="mt-1 text-sm font-medium text-red-600 dark:text-red-400">
        {{ error }}
      </p>
    </Transition>
  </div>
</template>

<style scoped>
.input-wrap {
  animation: fieldAppear 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
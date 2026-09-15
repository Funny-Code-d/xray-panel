<script setup>
defineProps({
  variant: {
    type: String,
    default: 'default', // default | danger
  },
  title: String,
})

const variants = {
  default: 'border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-[#1a0b2e]',
  danger: 'border-black dark:border-white hover:border-red-600 hover:bg-red-600 hover:text-white dark:hover:border-red-400 dark:hover:bg-red-400 dark:hover:text-[#1a0b2e]',
}
</script>

<template>
  <button
    :title="title"
    :class="[
      'inline-flex items-center justify-center w-9 h-9 border-2 transition-all duration-100 hover:shadow-brutal-sm',
      variants[variant],
    ]"
  >
    <slot />
  </button>
</template>
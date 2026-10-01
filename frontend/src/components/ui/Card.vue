<script setup>
defineProps({
  // Плотный режим — меньше отступы
  dense: { type: Boolean, default: false },
  // Цветная полоса сверху: 'none' | 'yellow' | 'black'
  accent: { type: String, default: 'none' },
  // Убрать внутренние отступы (если содержимое само управляет)
  flush: { type: Boolean, default: false },
})
</script>

<template>
  <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal relative overflow-hidden">
    <!-- Акцентная полоса сверху -->
    <div
      v-if="accent === 'yellow'"
      class="h-2 bg-[#FFD700] border-b-[3px] border-black dark:border-white"
    ></div>
    <div
      v-else-if="accent === 'black'"
      class="h-2 bg-black dark:bg-white border-b-[3px] border-black dark:border-white"
    ></div>

    <!-- Header -->
    <div
      v-if="$slots.header"
      class="border-b-[3px] border-black dark:border-white"
      :class="dense ? 'px-4 py-2' : 'px-6 py-4'"
    >
      <slot name="header" />
    </div>

    <!-- Body -->
    <div :class="flush ? '' : (dense ? 'p-4' : 'p-6')">
      <slot />
    </div>

    <!-- Footer -->
    <div
      v-if="$slots.footer"
      class="border-t-[3px] border-black dark:border-white"
      :class="dense ? 'px-4 py-2' : 'px-6 py-4'"
    >
      <slot name="footer" />
    </div>
  </div>
</template>
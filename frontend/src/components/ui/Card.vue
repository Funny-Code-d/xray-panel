<template>
  <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal">
    <div v-if="$slots.header" class="px-6 py-4 border-b-2 border-black dark:border-white">
      <slot name="header" />
    </div>
    <div class="p-6">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-6 py-4 border-t-2 border-black dark:border-white">
      <slot name="footer" />
    </div>
  </div>
</template>
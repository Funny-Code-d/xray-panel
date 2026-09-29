<script setup>
import { ref } from 'vue'

const props = defineProps({
  question: { type: String, required: true },
  answer: { type: String, required: true },
  defaultOpen: { type: Boolean, default: false },
})

const open = ref(props.defaultOpen)
</script>

<template>
  <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal">
    <button
      type="button"
      class="w-full flex items-center justify-between gap-4 p-5 text-left hover:bg-[#FFD700]/10 transition-colors"
      @click="open = !open"
    >
      <span class="font-black uppercase tracking-wider text-sm sm:text-base">
        {{ question }}
      </span>
      <span
        class="shrink-0 w-8 h-8 flex items-center justify-center border-[3px] border-black dark:border-white font-black text-lg transition-transform"
        :class="open ? 'rotate-45 bg-[#FF4911] text-white' : 'bg-[#FFD700]'"
      >
        +
      </span>
    </button>

    <div
      v-show="open"
      class="px-5 pb-5 pt-0 text-sm opacity-70 leading-relaxed border-t-[3px] border-black dark:border-white mt-0"
    >
      <div class="pt-4">{{ answer }}</div>
    </div>
  </div>
</template>
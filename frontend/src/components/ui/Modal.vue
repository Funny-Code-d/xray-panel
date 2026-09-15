<script setup>
import { watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  title: String,
})
const emit = defineEmits(['update:modelValue'])

function close() {
  emit('update:modelValue', false)
}

// Закрытие по Escape
watch(() => props.modelValue, (open) => {
  if (open) {
    document.addEventListener('keydown', onKey)
    document.body.style.overflow = 'hidden'
  } else {
    document.removeEventListener('keydown', onKey)
    document.body.style.overflow = ''
  }
})

function onKey(e) {
  if (e.key === 'Escape') close()
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="close"
    >
      <div class="w-full max-w-md bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal-lg">
        <!-- Заголовок -->
        <div class="flex justify-between items-center px-6 py-4 border-b-2 border-black dark:border-white">
          <h2 class="text-lg font-black uppercase tracking-wider">{{ title }}</h2>
          <button
            @click="close"
            class="w-8 h-8 inline-flex items-center justify-center border-2 border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-[#1a0b2e] transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Контент -->
        <div class="p-6">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>
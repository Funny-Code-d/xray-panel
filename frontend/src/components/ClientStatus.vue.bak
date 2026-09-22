<script setup>
import { computed } from 'vue'

const props = defineProps({
  client: { type: Object, required: true },
})

const status = computed(() => {
  if (!props.client.is_active) {
    return {
      label: 'Отключён',
      class: 'bg-slate-200 text-slate-700 border-slate-700 dark:bg-slate-700 dark:text-slate-200 dark:border-slate-300',
    }
  }
  if (props.client.expires_at && new Date(props.client.expires_at) < new Date()) {
    return {
      label: 'Истёк',
      class: 'bg-red-100 text-red-700 border-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-400',
    }
  }
  return {
    label: 'Активен',
    class: 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400',
  }
})
</script>

<template>
  <Transition name="fade-slide" mode="out-in">
    <span
      :key="status.label"
      :class="[
        'inline-block px-2 py-0.5 text-xs font-bold uppercase tracking-wider border-2 whitespace-nowrap',
        status.class,
      ]"
    >
      {{ status.label }}
    </span>
  </Transition>
</template>
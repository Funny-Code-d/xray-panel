<script setup>
import { computed } from 'vue'
import { formatBytes, formatDate } from '@/utils/format'
import IconButton from '@/components/ui/IconButton.vue'

const props = defineProps({
  client: { type: Object, required: true },
})

defineEmits(['show-config', 'edit', 'delete'])

const isExpired = computed(() => {
  if (!props.client.expires_at) return false
  return new Date(props.client.expires_at) < new Date()
})

const status = computed(() => {
  if (!props.client.is_active) return { label: 'Disabled', color: 'slate' }
  if (isExpired.value) return { label: 'Expired', color: 'red' }
  return { label: 'Active', color: 'green' }
})

const statusClass = computed(() => {
  const map = {
    green: 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400',
    red: 'bg-red-100 text-red-700 border-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-400',
    slate: 'bg-slate-200 text-slate-700 border-slate-700 dark:bg-slate-700 dark:text-slate-200 dark:border-slate-300',
  }
  return map[status.value.color]
})
</script>

<template>
  <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal flex flex-col sm:flex-row">
    <!-- Акцентная полоса слева -->
    <div class="h-2 sm:h-auto sm:w-2 bg-black dark:bg-white border-b-[3px] sm:border-b-0 sm:border-r-[3px] border-black dark:border-white shrink-0"></div>

    <!-- Контент -->
    <div class="flex-1 p-4 min-w-0">
      <div class="flex items-center gap-2 mb-2 flex-wrap">
        <h3 class="font-black uppercase tracking-wider text-lg truncate">
          {{ client.name }}
        </h3>
        <span
          class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider border-2"
          :class="statusClass"
        >
          {{ status.label }}
        </span>
      </div>

      <p class="text-xs font-mono opacity-60 truncate mb-2">
        {{ client.email }}
      </p>

      <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs opacity-70">
        <span v-if="client.xray_server">
          {{ client.xray_server.country_flag }} {{ client.xray_server.name }}
        </span>
        <span>Traffic: {{ formatBytes(client.traffic_used ?? 0) }}</span>
        <span v-if="client.expires_at">
          Until: {{ formatDate(client.expires_at) }}
        </span>
      </div>
    </div>

    <!-- Действия -->
    <div class="flex items-center gap-2 p-4 border-t-[3px] sm:border-t-0 sm:border-l-[3px] border-black dark:border-white">
      <IconButton title="QR и ссылка" @click="$emit('show-config', client)">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
          <rect x="3" y="3" width="7" height="7"/>
          <rect x="14" y="3" width="7" height="7"/>
          <rect x="3" y="14" width="7" height="7"/>
          <line x1="14" y1="14" x2="14" y2="14.01"/>
          <line x1="21" y1="14" x2="21" y2="21"/>
          <line x1="14" y1="21" x2="21" y2="21"/>
        </svg>
      </IconButton>

      <IconButton title="Редактировать" @click="$emit('edit', client)">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
      </IconButton>

      <IconButton variant="danger" title="Удалить" @click="$emit('delete', client)">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
          <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
      </IconButton>
    </div>
  </div>
</template>
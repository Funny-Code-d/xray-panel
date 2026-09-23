<script setup>
import { computed } from 'vue'

const props = defineProps({
  server: { type: Object, required: true },
})

const isOnline = computed(() => props.server.status === 'online')
</script>

<template>
  <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-5 hover:shadow-brutal-hover transition-all">
    <!-- Верх: флаг + имя + LED -->
    <div class="flex items-center justify-between gap-3 mb-4">
      <div class="flex items-center gap-3 min-w-0">
        <span class="text-3xl shrink-0">{{ server.country_flag }}</span>
        <div class="min-w-0">
          <h3 class="font-bold text-base truncate">{{ server.name }}</h3>
          <p class="text-xs opacity-60 truncate">
            {{ server.city || server.country_name || '—' }}
          </p>
        </div>
      </div>

      <span
        :class="['led', isOnline ? 'led-online' : 'led-offline']"
        :title="isOnline ? 'Online' : 'Offline'"
      ></span>
    </div>

    <!-- IP -->
    <div class="font-mono text-xs opacity-70 mb-4">
      {{ server.host }}:{{ server.port }}
    </div>

    <!-- Клиенты + статус -->
    <div class="flex justify-between items-baseline pt-4 border-t-2 border-black/10 dark:border-white/10">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider opacity-60">Клиентов</p>
        <p class="text-2xl font-black">{{ server.vpn_clients_count }}</p>
      </div>

      <span
        :class="[
          'px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border-2',
          isOnline
            ? 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400'
            : 'bg-red-100 text-red-700 border-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-400',
        ]"
      >
        {{ isOnline ? 'ONLINE' : 'OFFLINE' }}
      </span>
    </div>
  </div>
</template>
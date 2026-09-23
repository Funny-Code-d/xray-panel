<script setup>
import { computed } from 'vue'

const props = defineProps({
  server: { type: Object, required: true },
})

const isOnline = computed(() => props.server.status === 'online')
</script>

<template>
  <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-4 hover:shadow-brutal-hover transition-all">
    <!-- Флаг + LED -->
    <div class="flex items-center justify-between mb-3">
      <span class="text-2xl">{{ server.country_flag }}</span>
      <span
        :class="['led', isOnline ? 'led-online' : 'led-offline']"
        :title="isOnline ? 'Online' : 'Offline'"
      ></span>
    </div>

    <!-- Название -->
    <h3 class="font-bold text-sm truncate mb-1">{{ server.name }}</h3>

    <!-- Город -->
    <p class="text-xs opacity-60 truncate mb-3">
      {{ server.city || server.country_name || '—' }}
    </p>

    <!-- IP -->
    <p class="text-[10px] font-mono opacity-50 truncate mb-3">
      {{ server.host }}:{{ server.port }}
    </p>

    <!-- Клиенты + статус -->
    <div class="flex justify-between items-baseline pt-2 border-t-2 border-black/10 dark:border-white/10">
      <span class="text-xs opacity-60">Клиентов</span>
      <span class="font-black text-lg">{{ server.vpn_clients_count }}</span>
    </div>
  </div>
</template>
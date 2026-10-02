<script setup>
import { computed } from 'vue'

const props = defineProps({
  server: { type: Object, required: true },
})

const isOnline = computed(() => props.server.status === 'online')

const enabledProtocols = computed(() =>
  (props.server.protocols || []).filter(p => p.is_enabled)
)
</script>

<template>
  <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal hover:shadow-brutal-hover transition-all overflow-hidden">
    <!-- Шапка: флаг + название + LED -->
    <div class="flex items-center justify-between px-3 py-2 bg-[#FFD700] border-b-[3px] border-black dark:border-white">
      <div class="flex items-center gap-2 min-w-0">
        <span class="text-2xl leading-none shrink-0">{{ server.country_flag }}</span>
        <span class="font-black uppercase tracking-wider text-base truncate">
          {{ server.name }}
        </span>
      </div>
      <span
        :class="['led shrink-0', isOnline ? 'led-online' : 'led-offline']"
        :title="isOnline ? 'Online' : 'Offline'"
      ></span>
    </div>

    <!-- Тело -->
    <div class="p-3">
      <!-- Локация -->
      <p class="text-sm font-bold uppercase tracking-wider opacity-70 truncate mb-3">
        {{ server.city || server.country_name || '—' }}
      </p>

      <!-- Протоколы -->
      <div class="flex flex-wrap gap-1 mb-3">
        <span
          v-for="p in enabledProtocols"
          :key="p.protocol"
          class="px-1.5 py-0.5 text-[10px] font-black uppercase tracking-wider border-2 border-black dark:border-white bg-[#FFD700] text-black"
        >
          {{ p.protocol }}
        </span>
        <span
          v-if="enabledProtocols.length === 0"
          class="text-[10px] font-black uppercase tracking-wider opacity-50"
        >
          —
        </span>
      </div>

      <!-- Клиенты -->
      <div class="flex justify-between items-baseline pt-2 border-t-[3px] border-black dark:border-white">
        <span class="text-xs font-black uppercase tracking-wider opacity-60">Clients</span>
        <span class="text-2xl font-black leading-none">{{ server.vpn_clients_count ?? 0 }}</span>
      </div>
    </div>
  </div>
</template>
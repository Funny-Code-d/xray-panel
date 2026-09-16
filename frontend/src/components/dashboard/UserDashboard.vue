<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { formatBytes } from '@/utils/format'

const auth = useAuthStore()

const trafficUsed = computed(() => formatBytes(auth.user?.traffic_used ?? 0))

const trafficLimit = computed(() => {
  const limit = auth.user?.traffic_limit
  if (limit === null || limit === undefined) return 'Безлимит'
  return formatBytes(limit)
})

const trafficPercent = computed(() => {
  const limit = auth.user?.traffic_limit
  const used = auth.user?.traffic_used ?? 0
  if (!limit || used === 0) return 0
  return Math.min(100, Math.round((used / limit) * 100))
})

const clientsCount = computed(() => auth.user?.vpn_clients_count ?? 0)
</script>

<template>
  <div class="mb-8">
    <h1 class="text-3xl font-black uppercase tracking-wider">Dashboard</h1>
    <p class="mt-2 text-sm opacity-70">
      {{ auth.user?.full_name || auth.user?.email }}
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Трафик -->
    <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
      <h2 class="text-xs font-bold uppercase tracking-wider opacity-60 mb-2">
        Использовано трафика
      </h2>
      <p class="text-3xl font-black">{{ trafficUsed }}</p>
      <p class="text-xs mt-1 opacity-60">из {{ trafficLimit }}</p>

      <div v-if="auth.user?.traffic_limit" class="mt-4">
        <div class="w-full border-2 border-black dark:border-white h-4">
          <div
            class="h-full transition-all"
            :class="trafficPercent > 90 ? 'bg-red-500' : trafficPercent > 70 ? 'bg-amber-500' : 'bg-blue-600 dark:bg-orange-500'"
            :style="{ width: `${trafficPercent}%` }"
          ></div>
        </div>
        <p class="text-xs font-bold mt-1">{{ trafficPercent }}%</p>
      </div>
    </div>

    <!-- Ключи -->
    <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
      <h2 class="text-xs font-bold uppercase tracking-wider opacity-60 mb-2">
        VPN-ключи
      </h2>
      <p class="text-3xl font-black">{{ clientsCount }}</p>
      <p class="text-xs mt-1 opacity-60">
        {{ clientsCount === 1 ? 'активный ключ' : 'активных ключей' }}
      </p>
      <RouterLink
        :to="{ name: 'clients' }"
        class="text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-orange-500 hover:underline mt-3 inline-block"
      >
        Перейти к ключам →
      </RouterLink>
    </div>

    <!-- Статус -->
    <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
      <h2 class="text-xs font-bold uppercase tracking-wider opacity-60 mb-2">
        Статус аккаунта
      </h2>
      <p class="text-3xl font-black text-green-600 dark:text-green-400">Активен</p>
      <p class="text-xs mt-1 opacity-60">
        Регистрация: {{ auth.user?.registration_date?.split('T')[0] || '—' }}
      </p>
    </div>
  </div>
</template>
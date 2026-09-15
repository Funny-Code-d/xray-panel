<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()

const tabs = [
  { name: 'admin-applications', label: 'Заявки' },
  { name: 'admin-users', label: 'Пользователи' },
]
</script>

<template>
  <div>
    <!-- Заголовок раздела -->
    <div class="mb-6">
      <h1 class="text-3xl font-black uppercase tracking-wider">Админка</h1>
    </div>

    <!-- Табы -->
    <div class="flex gap-2 mb-6 border-b-2 border-black dark:border-white">
      <RouterLink
        v-for="tab in tabs"
        :key="tab.name"
        :to="{ name: tab.name }"
        class="px-4 py-2 font-bold uppercase tracking-wide text-sm border-2 border-b-0 transition-all"
        :class="route.name === tab.name
          ? 'bg-blue-600 dark:bg-orange-500 text-white border-black dark:border-white -mb-0.5'
          : 'bg-transparent border-transparent hover:border-black dark:hover:border-white'"
      >
        {{ tab.label }}
      </RouterLink>
    </div>

    <!-- Контент таба -->
    <slot />
  </div>
</template>
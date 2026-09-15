<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'

const router = useRouter()
const auth = useAuthStore()
const theme = useThemeStore()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-[#1a0b2e]">
    <nav class="bg-white dark:bg-[#2a1548] border-b-2 border-black dark:border-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-8">
            <RouterLink :to="{ name: 'dashboard' }" class="text-xl font-black uppercase tracking-wider">
              VPN Panel
            </RouterLink>
            <div class="hidden sm:flex items-center gap-6">
              <RouterLink
                :to="{ name: 'dashboard' }"
                class="text-sm font-bold uppercase tracking-wide hover:text-blue-600 dark:hover:text-orange-500 transition"
                active-class="text-blue-600 dark:text-orange-500"
              >
                Dashboard
              </RouterLink>
              <RouterLink
                :to="{ name: 'clients' }"
                class="text-sm font-bold uppercase tracking-wide hover:text-blue-600 dark:hover:text-orange-500 transition"
                active-class="text-blue-600 dark:text-orange-500"
              >
                Мои ключи
              </RouterLink>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button
              @click="theme.toggle"
              class="px-3 py-2 border-2 border-black dark:border-white font-bold text-sm hover:shadow-brutal-sm transition-all duration-100"
              :title="theme.isDark ? 'Светлая тема' : 'Тёмная тема'"
            >
              {{ theme.isDark ? '☀' : '☾' }}
            </button>

            <div class="text-right hidden sm:block">
              <p class="text-sm font-bold">{{ auth.user?.full_name || auth.user?.email }}</p>
              <p class="text-xs opacity-70">{{ auth.isAdmin ? 'Администратор' : 'Пользователь' }}</p>
            </div>

            <button
              @click="handleLogout"
              class="px-3 py-2 border-2 border-black dark:border-white font-bold text-sm uppercase tracking-wide hover:shadow-brutal-sm transition-all duration-100"
            >
              Выйти
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>
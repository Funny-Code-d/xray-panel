<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'

const router = useRouter()
const auth = useAuthStore()
const theme = useThemeStore()

const mobileMenuOpen = ref(false)

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-[#1a0b2e]">
    <nav class="sticky top-0 z-40 bg-white dark:bg-[#2a1548] border-b-2 border-black dark:border-white animate-nav">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Логотип + навигация -->
          <div class="flex items-center gap-8">
            <RouterLink
              :to="{ name: 'dashboard' }"
              class="text-xl font-black uppercase tracking-wider"
              @click="closeMobileMenu"
            >
              FunnyNodes
            </RouterLink>

            <!-- Desktop-навигация -->
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
              <RouterLink
                :to="{ name: 'news' }"
                class="text-sm font-bold uppercase tracking-wide hover:text-blue-600 dark:hover:text-orange-500 transition"
                active-class="text-blue-600 dark:text-orange-500"
              >
                Новости
              </RouterLink>
              <RouterLink
                :to="{ name: 'subscriptions' }"
                class="text-sm font-bold uppercase tracking-wide hover:text-blue-600 dark:hover:text-orange-500 transition"
                active-class="text-blue-600 dark:text-orange-500"
              >
                Подписки
              </RouterLink>
              <RouterLink
                v-if="auth.isAdmin"
                :to="{ name: 'admin-applications' }"
                class="text-sm font-bold uppercase tracking-wide hover:text-blue-600 dark:hover:text-orange-500 transition"
                active-class="text-blue-600 dark:text-orange-500"
              >
                Управление
              </RouterLink>
              <RouterLink
                :to="{ name: 'about' }"
                class="text-sm font-bold uppercase tracking-wide hover:text-blue-600 dark:hover:text-orange-500 transition"
                active-class="text-blue-600 dark:text-orange-500"
              >
                О проекте
              </RouterLink>
            </div>
          </div>

          <!-- Действия справа -->
          <div class="flex items-center gap-2 sm:gap-3">
            <!-- Тема -->
            <button
              @click="theme.toggle"
              class="px-3 py-2 border-2 border-black dark:border-white font-bold text-sm hover:shadow-brutal-sm transition-all duration-100"
              :title="theme.isDark ? 'Светлая тема' : 'Тёмная тема'"
            >
              {{ theme.isDark ? '☀' : '☾' }}
            </button>

            <!-- Пользователь (desktop) -->
            <div class="text-right hidden sm:block">
              <p class="text-sm font-bold">{{ auth.user?.full_name || auth.user?.email }}</p>
              <p class="text-xs opacity-70">{{ auth.isAdmin ? 'Администратор' : 'Пользователь' }}</p>
            </div>

            <!-- Выйти (desktop) -->
            <button
              @click="handleLogout"
              class="hidden sm:block px-3 py-2 border-2 border-black dark:border-white font-bold text-sm uppercase tracking-wide hover:shadow-brutal-sm transition-all duration-100"
            >
              Выйти
            </button>

            <!-- Гамбургер (mobile) -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="sm:hidden w-10 h-10 flex items-center justify-center border-2 border-black dark:border-white hover:shadow-brutal-sm transition-all"
              :title="mobileMenuOpen ? 'Закрыть меню' : 'Меню'"
            >
              <svg
                v-if="!mobileMenuOpen"
                xmlns="http://www.w3.org/2000/svg"
                width="20" height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="square"
              >
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                width="20" height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="square"
              >
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Мобильное меню -->
      <Transition name="fade-slide">
        <div
          v-if="mobileMenuOpen"
          class="sm:hidden border-t-2 border-black dark:border-white bg-white dark:bg-[#2a1548]"
        >
          <div class="px-4 py-4 space-y-3">
            <!-- Пользователь -->
            <div class="pb-3 border-b-2 border-black/10 dark:border-white/10">
              <p class="text-sm font-bold truncate">
                {{ auth.user?.full_name || auth.user?.email }}
              </p>
              <p class="text-xs opacity-70">
                {{ auth.isAdmin ? 'Администратор' : 'Пользователь' }}
              </p>
            </div>

            <!-- Ссылки -->
            <RouterLink
              :to="{ name: 'dashboard' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-transparent hover:border-black dark:hover:border-white transition-all"
              active-class="border-black dark:border-white bg-blue-50 dark:bg-[#1a0b2e]"
              @click="closeMobileMenu"
            >
              Dashboard
            </RouterLink>

            <RouterLink
              :to="{ name: 'clients' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-transparent hover:border-black dark:hover:border-white transition-all"
              active-class="border-black dark:border-white bg-blue-50 dark:bg-[#1a0b2e]"
              @click="closeMobileMenu"
            >
              Мои ключи
            </RouterLink>

            <RouterLink
              :to="{ name: 'news' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-transparent hover:border-black dark:hover:border-white transition-all"
              active-class="border-black dark:border-white bg-blue-50 dark:bg-[#1a0b2e]"
              @click="closeMobileMenu"
            >
              Новости
            </RouterLink>

            <RouterLink
              :to="{ name: 'subscriptions' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-transparent hover:border-black dark:hover:border-white transition-all"
              active-class="border-black dark:border-white bg-blue-50 dark:bg-[#1a0b2e]"
              @click="closeMobileMenu"
            >
              Подписки
            </RouterLink>

            <RouterLink
              v-if="auth.isAdmin"
              :to="{ name: 'admin-applications' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-transparent hover:border-black dark:hover:border-white transition-all"
              active-class="border-black dark:border-white bg-blue-50 dark:bg-[#1a0b2e]"
              @click="closeMobileMenu"
            >
              Управление
            </RouterLink>

            <RouterLink
              :to="{ name: 'about' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-transparent hover:border-black dark:hover:border-white transition-all"
              active-class="border-black dark:border-white bg-blue-50 dark:bg-[#1a0b2e]"
              @click="closeMobileMenu"
            >
              О проекте
            </RouterLink>

            <!-- Выйти -->
            <button
              @click="handleLogout"
              class="w-full px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-red-600 text-red-600 dark:border-red-400 dark:text-red-400 hover:shadow-brutal-sm transition-all"
            >
              Выйти
            </button>
          </div>
        </div>
      </Transition>
    </nav>

    <!-- Контент -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>
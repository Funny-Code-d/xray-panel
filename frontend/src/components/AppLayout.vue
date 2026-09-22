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
  <div class="min-h-screen bg-white dark:bg-[#121212]">
    <nav class="sticky top-0 z-40 bg-white dark:bg-[#1A1A1A] border-b-[3px] border-black dark:border-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Логотип + бургер -->
          <div class="flex items-center gap-4">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="w-10 h-10 flex items-center justify-center border-2 border-black dark:border-white bg-[#FFD700] text-black hover:shadow-brutal-sm transition-all"
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

            <RouterLink
              :to="{ name: 'dashboard' }"
              class="text-xl font-black uppercase tracking-wider"
              @click="closeMobileMenu"
            >
              FunnyNodes
            </RouterLink>
          </div>

          <!-- Действия справа -->
          <div class="flex items-center gap-2 sm:gap-3">
            <button
              @click="theme.toggle"
              class="w-10 h-10 flex items-center justify-center border-2 border-black dark:border-white font-bold hover:shadow-brutal-sm transition-all"
              :title="theme.isDark ? 'Светлая тема' : 'Тёмная тема'"
            >
              {{ theme.isDark ? '☀' : '☾' }}
            </button>

            <div class="text-right hidden md:block">
              <p class="text-sm font-bold">{{ auth.user?.full_name || auth.user?.email }}</p>
              <p class="text-xs opacity-70">{{ auth.isAdmin ? 'Администратор' : 'Пользователь' }}</p>
            </div>

            <button
              @click="handleLogout"
              class="hidden md:flex w-10 h-10 items-center justify-center border-2 border-black dark:border-white hover:shadow-brutal-sm transition-all"
              title="Выйти"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Выпадающее меню -->
      <Transition name="fade-slide">
        <div
          v-if="mobileMenuOpen"
          class="border-t-[3px] border-black dark:border-white bg-white dark:bg-[#1A1A1A]"
        >
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-2">
            <div class="md:hidden pb-3 mb-3 border-b-2 border-black/10 dark:border-white/10">
              <p class="text-sm font-bold truncate">
                {{ auth.user?.full_name || auth.user?.email }}
              </p>
              <p class="text-xs opacity-70">
                {{ auth.isAdmin ? 'Администратор' : 'Пользователь' }}
              </p>
            </div>

            <RouterLink
              :to="{ name: 'dashboard' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-l-4 border-[#FFD700] hover:bg-[#FFD700] hover:text-black transition-all"
              active-class="bg-[#FFD700] text-black"
              @click="closeMobileMenu"
            >
              Dashboard
            </RouterLink>

            <RouterLink
              :to="{ name: 'clients' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-l-4 border-[#FF4911] hover:bg-[#FF4911] hover:text-white transition-all"
              active-class="bg-[#FF4911] text-white"
              @click="closeMobileMenu"
            >
              Мои ключи
            </RouterLink>

            <RouterLink
              :to="{ name: 'news' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-l-4 border-[#00FF00] hover:bg-[#00FF00] hover:text-black transition-all"
              active-class="bg-[#00FF00] text-black"
              @click="closeMobileMenu"
            >
              Новости
            </RouterLink>

            <RouterLink
              :to="{ name: 'subscriptions' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-l-4 border-[#FF00FF] hover:bg-[#FF00FF] hover:text-white transition-all"
              active-class="bg-[#FF00FF] text-white"
              @click="closeMobileMenu"
            >
              Подписки
            </RouterLink>

            <RouterLink
              v-if="auth.isAdmin"
              :to="{ name: 'admin-applications' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-l-4 border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-all"
              active-class="bg-black text-white dark:bg-white dark:text-black"
              @click="closeMobileMenu"
            >
              Управление
            </RouterLink>

            <RouterLink
              :to="{ name: 'about' }"
              class="block px-3 py-2 text-sm font-bold uppercase tracking-wide border-l-4 border-slate-400 hover:bg-slate-400 hover:text-white transition-all"
              active-class="bg-slate-400 text-white"
              @click="closeMobileMenu"
            >
              О проекте
            </RouterLink>

            <button
              @click="handleLogout"
              class="w-full md:hidden px-3 py-2 text-sm font-bold uppercase tracking-wide border-2 border-red-600 text-red-600 dark:border-red-400 dark:text-red-400 hover:shadow-brutal-sm transition-all"
            >
              Выйти
            </button>
          </div>
        </div>
      </Transition>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>
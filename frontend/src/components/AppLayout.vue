<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppSidebar from '@/components/AppSidebar.vue'
import Button from '@/components/ui/Button.vue'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const sidebarOpen = ref(false)

const menuLinks = computed(() => {
  const base = [
    { name: 'home', label: 'HOME' },
    { name: 'clients', label: 'KEYS' },
    { name: 'servers', label: 'NODES' },
    { name: 'subscriptions', label: 'Subscriptions' },
    { name: 'news', label: 'NEWS' },
    { name: 'about', label: 'ABOUT' },
  ]

  if (auth.isAdmin) {
    base.push(
      { name: 'admin-applications', label: 'Applications' },
      { name: 'admin-users', label: 'Users' },
      { name: 'admin-servers', label: 'Manage nodes' },
      { name: 'admin-posts', label: 'Posts' },
      { name: 'admin-tags', label: 'Tags' },
    )
  }

  return base
})

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'home' })
}
</script>

<template>
  <div class="min-h-screen bg-bg dark:bg-[#0A0A0A]">
    <!-- Шапка -->
    <header class="border-b-[3px] border-black dark:border-white sticky top-0 z-30 bg-white dark:bg-[#121212]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <!-- Кнопка-бургер -->
          <button
            type="button"
            class="w-10 h-10 flex items-center justify-center border-[3px] border-black dark:border-white bg-[#FFD700] hover:bg-black hover:text-white transition-colors"
            aria-label="Открыть меню"
            @click="sidebarOpen = true"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square">
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
          </button>

          <RouterLink to="/" class="flex items-center gap-3">
            <img src="/logo-icon.svg" alt="FunnyNodes" class="h-9 w-9" />
            <span class="text-xl font-black uppercase tracking-wider hidden sm:inline">FunnyNodes</span>
          </RouterLink>
        </div>

        <div class="flex gap-2 items-center">
          <span class="text-sm opacity-70 hidden sm:inline">
            {{ auth.user?.email }}
          </span>
          <Button variant="secondary" size="sm" @click="handleLogout">
            Выйти
          </Button>
        </div>
      </div>
    </header>

    <!-- Основной контент со сдвигом -->
    <main
      class="transition-transform duration-300 ease-out"
      :class="sidebarOpen ? 'md:translate-x-[280px]' : 'translate-x-0'"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <slot />
      </div>
    </main>

    <!-- Сайдбар -->
    <AppSidebar
      :open="sidebarOpen"
      :links="menuLinks"
      @close="sidebarOpen = false"
    />
  </div>
</template>
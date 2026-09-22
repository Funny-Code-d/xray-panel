<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'

const auth = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-white dark:bg-[#1a0b2e] px-4">
    <div class="w-full max-w-md">
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-8 text-center">
        <!-- Иконка замка -->
        <div class="mx-auto w-16 h-16 flex items-center justify-center border-2 border-red-600 dark:border-red-400 text-red-600 dark:text-red-400 mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
            <rect x="3" y="11" width="18" height="11" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
        </div>

        <h1 class="text-2xl font-black uppercase tracking-wider mb-3">
          Аккаунт заблокирован
        </h1>

        <p class="text-sm opacity-70 mb-6">
          Ваш доступ к панели приостановлен администратором.
          <template v-if="auth.user?.block_reason">
            <br><br>
            <span class="font-bold">Причина:</span> {{ auth.user.block_reason }}
          </template>
        </p>

        <Button variant="secondary" class="w-full" @click="handleLogout">
          Выйти
        </Button>
      </div>

      <p class="mt-6 text-xs text-center opacity-60">
        Если вы считаете, что произошла ошибка, свяжитесь с администратором.
      </p>
    </div>
  </div>
</template>
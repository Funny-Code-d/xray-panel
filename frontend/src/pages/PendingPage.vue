<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'

const auth = useAuthStore()
const router = useRouter()

const isRejected = computed(() => auth.isRejected)

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

async function refreshStatus() {
  try {
    await auth.fetchMe()
    if (auth.isApproved) {
      router.push({ name: 'dashboard' })
    }
  } catch (e) {
    // ignore
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-white dark:bg-[#1a0b2e] px-4">
    <div class="w-full max-w-md">
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-8 text-center">
        <!-- Иконка: часы для pending, крест для rejected -->
        <div
          class="mx-auto w-16 h-16 flex items-center justify-center border-2 mb-6"
          :class="isRejected
            ? 'border-red-600 dark:border-red-400 text-red-600 dark:text-red-400'
            : 'border-black dark:border-white'"
        >
          <!-- Часы -->
          <svg
            v-if="!isRejected"
            xmlns="http://www.w3.org/2000/svg"
            width="32" height="32"
            viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="square"
          >
            <circle cx="12" cy="12" r="10" />
            <path d="M12 6v6l4 2" />
          </svg>

          <!-- Крест -->
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            width="32" height="32"
            viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="square"
          >
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
        </div>

        <h1 class="text-2xl font-black uppercase tracking-wider mb-3">
          {{ isRejected ? 'Заявка отклонена' : 'Заявка на рассмотрении' }}
        </h1>

        <p class="text-sm opacity-70 mb-6">
          <template v-if="isRejected">
            Ваша заявка была отклонена администратором.
            <template v-if="auth.user?.rejection_reason">
              <br><br>
              <span class="font-bold">Причина:</span> {{ auth.user.rejection_reason }}
            </template>
          </template>
          <template v-else>
            Ваш аккаунт создан, но требует подтверждения администратором.
            После одобрения вы получите полный доступ к панели.
          </template>
        </p>

        <div class="space-y-3">
          <!-- Проверка статуса — только для pending -->
          <Button
            v-if="!isRejected"
            variant="primary"
            class="w-full"
            @click="refreshStatus"
          >
            Проверить статус
          </Button>

          <!-- Выйти — всегда -->
          <Button variant="secondary" class="w-full" @click="handleLogout">
            Выйти
          </Button>
        </div>
      </div>

      <p class="mt-6 text-xs text-center opacity-60">
        <template v-if="isRejected">
          Если вы считаете, что произошла ошибка, свяжитесь с администратором.
        </template>
        <template v-else>
          Если заявка долго не рассматривается — свяжитесь с администратором.
        </template>
      </p>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

// Сообщение об успешном сбросе пароля
const resetSuccess = ref(route.query.reset === 'success')

// Форма
const email = ref('')
const password = ref('')
const loading = ref(false)
const errors = ref({})

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  try {
    await auth.login(email.value, password.value)
    router.push({ name: 'dashboard' })
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      errors.value = { email: ['Что-то пошло не так. Попробуйте ещё раз.'] }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-white dark:bg-[#1a0b2e] px-4 py-12">
    <div class="w-full max-w-md">
      <!-- Логотип / заголовок -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-black uppercase tracking-wider">VPN Panel</h1>
        <p class="mt-2 text-sm opacity-70">Войдите в свой аккаунт</p>
      </div>

      <!-- Карточка формы -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-8">
        <!-- Сообщение об успешном сбросе -->
        <div
          v-if="resetSuccess"
          class="mb-5 p-3 bg-green-100 dark:bg-green-900 border-2 border-green-700 dark:border-green-400 text-sm font-bold"
        >
          Пароль сброшен. Войдите с новым паролем.
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Email -->
          <Input
            v-model="email"
            type="email"
            label="Email"
            placeholder="admin@vpn.local"
            :error="errors.email?.[0]"
          />

          <!-- Password -->
          <Input
            v-model="password"
            type="password"
            label="Пароль"
            placeholder="••••••••"
            :error="errors.password?.[0]"
          />

          <!-- Кнопка -->
          <Button type="submit" :loading="loading" class="w-full">
            Войти
          </Button>
        </form>

        <!-- Забыли пароль -->
        <p class="mt-4 text-center text-sm opacity-70">
          <RouterLink
            :to="{ name: 'forgot-password' }"
            class="hover:underline"
          >
            Забыли пароль?
          </RouterLink>
        </p>

        <!-- Регистрация -->
        <p class="mt-3 text-center text-sm opacity-70">
          Нет аккаунта?
          <RouterLink
            :to="{ name: 'register' }"
            class="font-bold underline hover:opacity-80"
          >
            Зарегистрироваться
          </RouterLink>
        </p>
      </div>

      <!-- Подсказка для разработки -->
      <p class="mt-6 text-center text-xs opacity-50">
        Для теста:
        <code class="bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5">admin@vpn.local</code>
        /
        <code class="bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5">password</code>
      </p>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const token = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const errors = ref({})
const invalidLink = ref(false)

onMounted(() => {
  token.value = route.query.token || ''
  email.value = route.query.email || ''

  if (!token.value || !email.value) {
    invalidLink.value = true
  }
})

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  try {
    await api.post('/reset-password', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    // Успех — на логин с флагом
    router.push({ name: 'login', query: { reset: 'success' } })
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      errors.value = { email: ['Что-то пошло не так'] }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-white dark:bg-[#1a0b2e] px-4 py-12">
    <div class="w-full max-w-md">
      <!-- Заголовок -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-black uppercase tracking-wider">AEGIS</h1>
        <p class="mt-2 text-sm opacity-70">Новый пароль</p>
      </div>

      <!-- Карточка -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-8">
        <!-- Невалидная ссылка -->
        <div v-if="invalidLink" class="text-center">
          <div class="mx-auto w-16 h-16 flex items-center justify-center border-2 border-red-600 dark:border-red-400 text-red-600 dark:text-red-400 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </div>

          <p class="font-bold text-lg mb-2">Неверная ссылка</p>
          <p class="text-sm opacity-70 mb-6">
            Ссылка для сброса пароля повреждена или устарела.
            Запросите новую.
          </p>

          <Button variant="primary" class="w-full" @click="router.push({ name: 'forgot-password' })">
            Запросить новую ссылку
          </Button>
        </div>

        <!-- Форма -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label class="block text-sm font-bold uppercase tracking-wide mb-2">
              Email
            </label>
            <input
              :value="email"
              readonly
              class="w-full px-3 py-2 bg-slate-100 dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white opacity-60 cursor-not-allowed"
            />
          </div>

          <Input
            v-model="password"
            type="password"
            label="Новый пароль"
            placeholder="Минимум 8 символов"
            :error="errors.password?.[0]"
          />

          <Input
            v-model="passwordConfirmation"
            type="password"
            label="Повторите пароль"
            placeholder="••••••••"
          />

          <!-- Общая ошибка (невалидный токен) -->
          <p v-if="errors.email?.[0]" class="text-sm font-bold text-red-600 dark:text-red-400">
            {{ errors.email[0] }}
          </p>

          <Button type="submit" :loading="loading" class="w-full">
            Сбросить пароль
          </Button>
        </form>
      </div>

      <!-- Подсказка -->
      <p class="mt-6 text-center text-xs opacity-50">
        <RouterLink :to="{ name: 'login' }" class="hover:underline">
          Вернуться к входу
        </RouterLink>
      </p>
    </div>
  </div>
</template>
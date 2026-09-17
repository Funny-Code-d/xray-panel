<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import api from '@/api/axios'
import AuthFooter from '@/components/AuthFooter.vue'

const router = useRouter()

const email = ref('')
const loading = ref(false)
const errors = ref({})
const sent = ref(false)

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  try {
    await api.post('/forgot-password', { email: email.value })
    sent.value = true
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
        <h1 class="text-4xl font-black uppercase tracking-wider">VPN Panel</h1>
        <p class="mt-2 text-sm opacity-70">Восстановление пароля</p>
      </div>

      <!-- Карточка -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-8">
        <!-- Отправлено -->
        <div v-if="sent" class="text-center">
          <div class="mx-auto w-16 h-16 flex items-center justify-center border-2 border-green-600 dark:border-green-400 text-green-600 dark:text-green-400 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square">
              <path d="M20 6L9 17l-5-5" />
            </svg>
          </div>

          <p class="font-bold text-lg mb-2">Проверьте почту</p>
          <p class="text-sm opacity-70 mb-6">
            Если <b>{{ email }}</b> зарегистрирован,
            мы отправили ссылку для сброса пароля.
            Ссылка действительна 60 минут.
          </p>

          <Button variant="secondary" class="w-full" @click="router.push({ name: 'login' })">
            Вернуться к входу
          </Button>
        </div>

        <!-- Форма -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-5">
          <p class="text-sm opacity-70">
            Введите email, указанный при регистрации.
            Мы отправим ссылку для сброса пароля.
          </p>

          <Input
            v-model="email"
            type="email"
            label="Email"
            placeholder="you@example.com"
            :error="errors.email?.[0]"
          />

          <Button type="submit" :loading="loading" class="w-full">
            Отправить ссылку
          </Button>

          <p class="text-center text-sm opacity-70 pt-2">
            <RouterLink :to="{ name: 'login' }" class="font-bold underline hover:opacity-80">
              Вернуться к входу
            </RouterLink>
          </p>
        </form>
      </div>
    </div>
    <AuthFooter />
  </div>
</template>
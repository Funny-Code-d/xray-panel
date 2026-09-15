<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

const router = useRouter()
const auth = useAuthStore()

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
      errors.value = { email: ['Что-то пошло не так.'] }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-white dark:bg-[#1a0b2e] px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-4xl font-black uppercase tracking-wider">VPN Panel</h1>
        <p class="mt-2 text-sm opacity-70">Войдите в свой аккаунт</p>
      </div>

      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-8">
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <Input
            v-model="email"
            type="email"
            label="Email"
            placeholder="admin@vpn.local"
            :error="errors.email?.[0]"
          />

          <Input
            v-model="password"
            type="password"
            label="Пароль"
            placeholder="••••••••"
            :error="errors.password?.[0]"
          />

          <Button type="submit" :loading="loading" class="w-full">
            Войти
          </Button>
        </form>
        <p class="mt-6 text-center text-sm opacity-70">
            Нет аккаунта?
            <RouterLink :to="{ name: 'register' }" class="font-bold underline hover:opacity-80">
                Зарегистрироваться
            </RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
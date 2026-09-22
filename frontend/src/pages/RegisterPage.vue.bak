<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import AuthFooter from '@/components/AuthFooter.vue'

const router = useRouter()
const auth = useAuthStore()

const form = ref({
  first_name: '',
  last_name: '',
  middle_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})
const loading = ref(false)
const errors = ref({})

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  try {
    await auth.register(form.value)
    // После регистрации — на страницу ожидания одобрения
    router.push({ name: 'pending' })
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
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
      <!-- Заголовок -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-black uppercase tracking-wider">FunnyNodes</h1>
        <p class="mt-2 text-sm opacity-70">Создайте аккаунт</p>
      </div>

      <!-- Карточка формы -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-8">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Имя и Фамилия -->
          <div class="grid grid-cols-2 gap-3">
            <Input
              v-model="form.last_name"
              label="Фамилия"
              :error="errors.last_name?.[0]"
            />
            <Input
              v-model="form.first_name"
              label="Имя"
              :error="errors.first_name?.[0]"
            />
          </div>

          <!-- Отчество -->
          <Input
            v-model="form.middle_name"
            label="Отчество"
            placeholder="Опционально"
            :error="errors.middle_name?.[0]"
          />

          <!-- Email -->
          <Input
            v-model="form.email"
            type="email"
            label="Email"
            placeholder="you@example.com"
            :error="errors.email?.[0]"
          />

          <!-- Телефон -->
          <Input
            v-model="form.phone"
            type="tel"
            label="Телефон"
            placeholder="+7 (999) 123-45-67"
            :error="errors.phone?.[0]"
          />

          <!-- Пароль -->
          <Input
            v-model="form.password"
            type="password"
            label="Пароль"
            placeholder="Минимум 8 символов"
            :error="errors.password?.[0]"
          />

          <!-- Подтверждение пароля -->
          <Input
            v-model="form.password_confirmation"
            type="password"
            label="Повторите пароль"
            placeholder="••••••••"
          />

          <!-- Кнопка -->
          <Button type="submit" :loading="loading" class="w-full !mt-6">
            Зарегистрироваться
          </Button>
        </form>

        <!-- Ссылка на логин -->
        <p class="mt-6 text-center text-sm opacity-70">
          Уже есть аккаунт?
          <RouterLink
            :to="{ name: 'login' }"
            class="font-bold underline hover:opacity-80"
          >
            Войти
          </RouterLink>
        </p>
      </div>
      <AuthFooter />

      <!-- Подсказка про модерацию -->
      <p class="mt-6 text-xs text-center opacity-50">
        После регистрации аккаунт требует подтверждения администратором.
      </p>
    </div>
  </div>
</template>
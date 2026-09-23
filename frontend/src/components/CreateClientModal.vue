<script setup>
import { ref, watch, onMounted } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  modelValue: Boolean,
})
const emit = defineEmits(['update:modelValue', 'created'])

const auth = useAuthStore()

const name = ref('')
const expiresAt = ref('')
const userId = ref('')
const serverId = ref('')
const users = ref([])
const servers = ref([])
const loading = ref(false)
const errors = ref({})

// Сброс формы при открытии
watch(() => props.modelValue, (open) => {
  if (open) {
    name.value = ''
    expiresAt.value = ''
    userId.value = ''
    serverId.value = ''
    errors.value = {}

    if (auth.isAdmin) {
      fetchUsers()
    }

    fetchServers()
  }
})

async function fetchUsers() {
  try {
    const { data } = await api.get('/admin/users')
    users.value = data.data || data
  } catch (e) {
    users.value = []
  }
}

async function fetchServers() {
  try {
    const { data } = await api.get('/servers')
    servers.value = data

    // Если сервер один — выбираем автоматически
    if (data.length === 1) {
      serverId.value = data[0].id
    }
  } catch (e) {
    servers.value = []
  }
}

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  const payload = {
    name: name.value,
    expires_at: expiresAt.value || null,
  }

  if (auth.isAdmin && userId.value) {
    payload.user_id = Number(userId.value)
  }

  if (serverId.value) {
    payload.xray_server_id = Number(serverId.value)
  }

  try {
    const { data } = await api.post('/clients', payload)
    emit('created', data)
    emit('update:modelValue', false)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      errors.value = { name: ['Что-то пошло не так'] }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    title="Новый ключ"
  >
    <form @submit.prevent="handleSubmit" class="space-y-5">
      <Input
        v-model="name"
        label="Название"
        placeholder="Например: Мой iPhone"
        :error="errors.name?.[0]"
      />

      <Input
        v-model="expiresAt"
        type="date"
        label="Срок действия (опционально)"
        :error="errors.expires_at?.[0]"
      />

      <!-- Выбор сервера -->
      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Сервер
        </label>

        <div v-if="servers.length === 0" class="text-sm opacity-60">
          Нет доступных серверов
        </div>

        <select
          v-else
          v-model="serverId"
          class="w-full px-3 py-2 bg-white dark:bg-[#121212] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all"
          :class="errors.xray_server_id ? 'border-red-500' : 'border-black dark:border-white'"
        >
          <option value="">Автоматически</option>
          <option v-for="server in servers" :key="server.id" :value="server.id">
            {{ server.country_flag }} {{ server.name }} — {{ server.host }}
            ({{ server.vpn_clients_count }} клиентов)
          </option>
        </select>

        <p v-if="errors.xray_server_id" class="mt-1 text-sm font-medium text-red-600 dark:text-red-400">
          {{ errors.xray_server_id[0] }}
        </p>
        <p class="mt-1 text-xs opacity-60">
          «Автоматически» — выберется первый доступный сервер
        </p>
      </div>

      <!-- Выбор пользователя (только для админа) -->
      <div v-if="auth.isAdmin">
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Пользователь
        </label>
        <select
          v-model="userId"
          class="w-full px-3 py-2 bg-white dark:bg-[#121212] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all"
          :class="errors.user_id ? 'border-red-500' : 'border-black dark:border-white'"
        >
          <option value="">— Создать для себя —</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.full_name || user.email }} ({{ user.email }})
          </option>
        </select>
        <p v-if="errors.user_id" class="mt-1 text-sm font-medium text-red-600 dark:text-red-400">
          {{ errors.user_id[0] }}
        </p>
      </div>

      <!-- Кнопки -->
      <div class="flex gap-3 pt-2">
        <Button
          type="button"
          variant="secondary"
          class="flex-1"
          @click="$emit('update:modelValue', false)"
        >
          Отмена
        </Button>
        <Button
          type="submit"
          variant="primary"
          :loading="loading"
          class="flex-1"
          :disabled="servers.length === 0"
        >
          Создать
        </Button>
      </div>
    </form>
  </Modal>
</template>
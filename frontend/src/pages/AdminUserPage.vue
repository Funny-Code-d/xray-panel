<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import RejectModal from '@/components/admin/RejectModal.vue'
import BlockModal from '@/components/admin/BlockModal.vue'
import EditUserModal from '@/components/admin/EditUserModal.vue'
import api from '@/api/axios'
import { formatBytes, formatDate, formatDateTime } from '@/utils/format'
import UserDashboardPreview from '@/components/admin/UserDashboardPreview.vue'


const showDashboard = ref(false)

const route = useRoute()
const router = useRouter()

const user = ref(null)
const loading = ref(true)
const error = ref(null)
const processing = ref(false)

const showRejectModal = ref(false)
const showBlockModal = ref(false)
const showEditModal = ref(false)

async function fetchUser() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/admin/users/${route.params.id}`)
    user.value = data
  } catch (e) {
    error.value = e.response?.status === 404
      ? 'Пользователь не найден'
      : 'Не удалось загрузить данные'
  } finally {
    loading.value = false
  }
}

onMounted(fetchUser)

async function approve() {
  processing.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/approve`)
    await fetchUser()
  } catch (e) {
    alert('Не удалось одобрить')
  } finally {
    processing.value = false
  }
}

async function handleReject(reason) {
  processing.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/reject`, { reason })
    showRejectModal.value = false
    await fetchUser()
  } catch (e) {
    alert('Не удалось отклонить')
  } finally {
    processing.value = false
  }
}

async function handleBlock(reason) {
  processing.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/block`, { reason })
    showBlockModal.value = false
    await fetchUser()
  } catch (e) {
    alert(e.response?.data?.message || 'Не удалось заблокировать')
  } finally {
    processing.value = false
  }
}

async function handleUnblock() {
  if (!confirm('Разблокировать пользователя? Ключи останутся деактивированными.')) return

  processing.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/unblock`)
    await fetchUser()
  } catch (e) {
    alert('Не удалось разблокировать')
  } finally {
    processing.value = false
  }
}

async function handleSave(data) {
  processing.value = true
  try {
    await api.patch(`/admin/users/${user.value.id}`, data)
    showEditModal.value = false
    await fetchUser()
  } catch (e) {
    alert(e.response?.data?.message || 'Не удалось сохранить')
  } finally {
    processing.value = false
  }
}

function statusBadge(status) {
  const map = {
    approved: { label: 'Одобрен', class: 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400' },
    pending: { label: 'Ожидает', class: 'bg-amber-100 text-amber-700 border-amber-700 dark:bg-amber-900 dark:text-amber-200 dark:border-amber-400' },
    rejected: { label: 'Отклонён', class: 'bg-red-100 text-red-700 border-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-400' },
  }
  return map[status] || { label: status, class: 'bg-slate-100 text-slate-700 border-slate-700' }
}
</script>

<template>
  <AppLayout>
    <!-- Назад -->
    <button
      @click="router.push({ name: 'admin-users' })"
      class="mb-6 text-sm font-bold uppercase tracking-wide opacity-70 hover:opacity-100 flex items-center gap-2"
    >
      ← Назад к списку
    </button>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12 opacity-70">
      Загрузка...
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="bg-red-100 dark:bg-red-900 border-2 border-red-700 dark:border-red-400 shadow-brutal p-6 text-center"
    >
      <p class="font-bold">{{ error }}</p>
      <Button variant="secondary" class="mt-4" @click="fetchUser">
        Попробовать снова
      </Button>
    </div>

    <!-- Карточка -->
    <div v-else-if="user" class="space-y-6">
      <!-- Шапка -->
      <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
        <div class="flex justify-between items-start flex-wrap gap-4">
          <div class="min-w-0">
            <h1 class="text-3xl font-black uppercase tracking-wider break-words">
              {{ user.last_name }} {{ user.first_name }} {{ user.middle_name || '' }}
            </h1>
            <p class="text-sm font-mono opacity-70 mt-1 break-all">{{ user.email }}</p>

            <!-- Бейджи -->
            <div class="flex gap-2 mt-3 flex-wrap">
              <!-- Блокировка — важнее -->
              <span
                v-if="user.is_blocked"
                class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold uppercase border-2 bg-red-600 text-white border-red-700"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                  <rect x="3" y="11" width="18" height="11" />
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                Заблокирован
              </span>

              <!-- Статус одобрения -->
              <span
                v-else
                :class="['inline-block px-2 py-0.5 text-xs font-bold uppercase border-2', statusBadge(user.approval_status).class]"
              >
                {{ statusBadge(user.approval_status).label }}
              </span>

              <!-- Роли -->
              <span
                v-for="role in user.roles"
                :key="role.code"
                class="inline-block px-2 py-0.5 text-xs font-bold uppercase border-2 border-black dark:border-white"
              >
                {{ role.code }}
              </span>
            </div>
          </div>

          <!-- Действия -->
          <div class="flex gap-2 flex-wrap">
            <Button
              v-if="!user.is_blocked && user.approval_status !== 'approved'"
              variant="success"
              size="sm"
              :disabled="processing"
              @click="approve"
            >
              Одобрить
            </Button>

            <Button
              v-if="!user.is_blocked && user.approval_status !== 'rejected'"
              variant="danger"
              size="sm"
              :disabled="processing"
              @click="showRejectModal = true"
            >
              Отклонить
            </Button>

            <Button
              variant="secondary"
              size="sm"
              :disabled="processing"
              @click="showEditModal = true"
            >
              Редактировать
            </Button>

            <Button
              v-if="!user.is_blocked"
              variant="danger"
              size="sm"
              :disabled="processing"
              @click="showBlockModal = true"
            >
              Заблокировать
            </Button>

            <Button
              v-else
              variant="success"
              size="sm"
              :disabled="processing"
              @click="handleUnblock"
            >
              Разблокировать
            </Button>

            <Button
              variant="info"
              size="sm"
              :disabled="processing"
              @click="showDashboard = true"
            >
              Посмотреть дашборд
            </Button>
          </div>
        </div>

        <!-- Причина блокировки -->
        <div
          v-if="user.is_blocked && user.block_reason"
          class="mt-4 p-3 bg-red-100 dark:bg-red-900 border-2 border-red-700 dark:border-red-400"
        >
          <p class="text-xs font-bold uppercase tracking-wider mb-1">Причина блокировки</p>
          <p class="text-sm">{{ user.block_reason }}</p>
          <p v-if="user.blocked_at" class="text-xs opacity-60 mt-1">
            {{ formatDateTime(user.blocked_at) }}
          </p>
        </div>

        <!-- Причина отклонения -->
        <div
          v-else-if="user.rejection_reason"
          class="mt-4 p-3 bg-red-100 dark:bg-red-900 border-2 border-red-700 dark:border-red-400"
        >
          <p class="text-xs font-bold uppercase tracking-wider mb-1">Причина отклонения</p>
          <p class="text-sm">{{ user.rejection_reason }}</p>
        </div>
      </div>

      <!-- Информация -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Контакты -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <h2 class="text-sm font-bold uppercase tracking-wider opacity-60 mb-4">
            Контакты
          </h2>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
              <dt class="opacity-60 shrink-0">Email</dt>
              <dd class="font-mono text-right break-all">{{ user.email }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="opacity-60 shrink-0">Телефон</dt>
              <dd class="font-mono text-right">{{ user.phone || '—' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Активность -->
        <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
          <h2 class="text-sm font-bold uppercase tracking-wider opacity-60 mb-4">
            Активность
          </h2>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
              <dt class="opacity-60">Регистрация</dt>
              <dd>{{ formatDate(user.registration_date) }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="opacity-60">Последний вход</dt>
              <dd>{{ user.last_auth_date ? formatDateTime(user.last_auth_date) : '—' }}</dd>
            </div>
            <div v-if="user.approved_at" class="flex justify-between gap-4">
              <dt class="opacity-60">Одобрен</dt>
              <dd>{{ formatDate(user.approved_at) }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Трафик -->
      <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
        <h2 class="text-sm font-bold uppercase tracking-wider opacity-60 mb-4">
          Трафик
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-3xl font-black">{{ formatBytes(user.traffic_used) }}</p>
            <p class="text-xs opacity-60 mt-1">использовано</p>
          </div>
          <div>
            <p class="text-3xl font-black">
              {{ user.traffic_limit ? formatBytes(user.traffic_limit) : '∞' }}
            </p>
            <p class="text-xs opacity-60 mt-1">лимит</p>
          </div>
        </div>

        <!-- Прогресс-бар -->
        <div v-if="user.traffic_limit" class="mt-4">
          <div class="w-full border-2 border-black dark:border-white h-4">
            <div
              class="h-full transition-all"
              :class="(() => {
                const p = (user.traffic_used / user.traffic_limit) * 100
                if (p > 90) return 'bg-red-500'
                if (p > 70) return 'bg-amber-500'
                return 'bg-blue-600 dark:bg-orange-500'
              })()"
              :style="{ width: Math.min(100, (user.traffic_used / user.traffic_limit) * 100) + '%' }"
            ></div>
          </div>
          <p class="text-xs font-bold mt-1">
            {{ Math.min(100, Math.round((user.traffic_used / user.traffic_limit) * 100)) }}%
          </p>
        </div>
      </div>

      <!-- VPN-ключи -->
      <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-6">
        <h2 class="text-sm font-bold uppercase tracking-wider opacity-60 mb-4">
          VPN-ключи ({{ user.vpn_clients_count }})
        </h2>

        <div v-if="user.vpn_clients.length === 0" class="text-sm opacity-60 text-center py-4">
          У пользователя нет ключей
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="client in user.vpn_clients"
            :key="client.id"
            class="flex justify-between items-center p-3 border-2 border-black/20 dark:border-white/20"
          >
            <div class="min-w-0">
              <p class="font-bold truncate">{{ client.name }}</p>
              <p class="text-xs font-mono opacity-60 truncate">{{ client.email }}</p>
            </div>
            <div class="text-right shrink-0 ml-3">
              <p class="text-sm font-bold">{{ formatBytes(client.traffic_used) }}</p>
              <p
                class="text-xs"
                :class="client.is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
              >
                {{ client.is_active ? 'Активен' : 'Отключён' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Модалки -->
    <RejectModal
      v-model="showRejectModal"
      :user-name="user ? `${user.last_name} ${user.first_name}` : ''"
      :loading="processing"
      @confirm="handleReject"
    />

    <BlockModal
      v-model="showBlockModal"
      :user-name="user ? `${user.last_name} ${user.first_name}` : ''"
      :loading="processing"
      @confirm="handleBlock"
    />

    <EditUserModal
      v-model="showEditModal"
      :user="user"
      :loading="processing"
      @save="handleSave"
    />

    <UserDashboardPreview v-model="showDashboard" :user-id="user?.id" />
  </AppLayout>
</template>
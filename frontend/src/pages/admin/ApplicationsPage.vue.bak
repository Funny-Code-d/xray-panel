<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import RejectModal from '@/components/admin/RejectModal.vue'
import api from '@/api/axios'
import { formatDate, formatDateTime } from '@/utils/format'

const users = ref([])
const loading = ref(true)
const error = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const showRejectModal = ref(false)
const userToReject = ref(null)
const processing = ref(false)

async function fetchApplications(page = 1) {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/admin/users', {
      params: { status: 'pending', page },
    })
    users.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    }
  } catch (e) {
    error.value = 'Не удалось загрузить заявки'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchApplications())

async function approve(user) {
  processing.value = true
  try {
    await api.post(`/admin/users/${user.id}/approve`)
    users.value = users.value.filter(u => u.id !== user.id)
    pagination.value.total--
  } catch (e) {
    alert('Не удалось одобрить заявку')
  } finally {
    processing.value = false
  }
}

function openReject(user) {
  userToReject.value = user
  showRejectModal.value = true
}

async function handleReject(reason) {
  if (!userToReject.value) return

  processing.value = true
  try {
    await api.post(`/admin/users/${userToReject.value.id}/reject`, { reason })
    users.value = users.value.filter(u => u.id !== userToReject.value.id)
    pagination.value.total--
    showRejectModal.value = false
    userToReject.value = null
  } catch (e) {
    alert('Не удалось отклонить заявку')
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <AppLayout>
    <AdminLayout>
      <!-- Заголовок -->
      <div class="mb-6">
        <h2 class="text-xl font-black uppercase tracking-wider">Заявки на одобрение</h2>
        <p class="text-sm opacity-70 mt-1">
          Ожидают: {{ pagination.total }}
        </p>
      </div>

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
        <Button variant="secondary" class="mt-4" @click="fetchApplications()">
          Попробовать снова
        </Button>
      </div>

      <!-- Empty -->
      <div
        v-else-if="users.length === 0"
        class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-12 text-center"
      >
        <p class="text-xl font-bold mb-2">Заявок нет</p>
        <p class="text-sm opacity-70">Все заявки обработаны</p>
      </div>

      <!-- Список заявок -->
      <div v-else class="space-y-4">
        <div
          v-for="user in users"
          :key="user.id"
          class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5"
        >
          <div class="flex justify-between items-start flex-wrap gap-4">
            <!-- Информация -->
            <div class="min-w-0 flex-1">
              <RouterLink
                :to="{ name: 'admin-user', params: { id: user.id } }"
                class="font-bold text-lg hover:underline"
              >
                {{ user.last_name }} {{ user.first_name }} {{ user.middle_name || '' }}
              </RouterLink>

              <div class="mt-2 space-y-1 text-sm opacity-80">
                <p>
                  <span class="opacity-60">Email:</span>
                  <span class="font-mono ml-1">{{ user.email }}</span>
                </p>
                <p v-if="user.phone">
                  <span class="opacity-60">Телефон:</span>
                  <span class="font-mono ml-1">{{ user.phone }}</span>
                </p>
                <p>
                  <span class="opacity-60">Регистрация:</span>
                  <span class="ml-1">{{ formatDate(user.registration_date) }}</span>
                </p>
              </div>
            </div>

            <!-- Кнопки -->
            <div class="flex gap-2 shrink-0">
              <Button
                variant="primary"
                size="sm"
                :disabled="processing"
                @click="approve(user)"
              >
                Одобрить
              </Button>
              <Button
                variant="danger"
                size="sm"
                :disabled="processing"
                @click="openReject(user)"
              >
                Отклонить
              </Button>
            </div>
          </div>
        </div>

        <!-- Пагинация -->
        <div
          v-if="pagination.last_page > 1"
          class="flex justify-center items-center gap-4 pt-4"
        >
          <Button
            variant="secondary"
            size="sm"
            :disabled="pagination.current_page === 1"
            @click="fetchApplications(pagination.current_page - 1)"
          >
            ← Назад
          </Button>
          <span class="text-sm font-bold">
            {{ pagination.current_page }} / {{ pagination.last_page }}
          </span>
          <Button
            variant="secondary"
            size="sm"
            :disabled="pagination.current_page === pagination.last_page"
            @click="fetchApplications(pagination.current_page + 1)"
          >
            Вперёд →
          </Button>
        </div>
      </div>

      <!-- Модалка отклонения -->
      <RejectModal
        v-model="showRejectModal"
        :user-name="userToReject ? `${userToReject.last_name} ${userToReject.first_name}` : ''"
        :loading="processing"
        @confirm="handleReject"
      />
    </AdminLayout>
  </AppLayout>
</template>
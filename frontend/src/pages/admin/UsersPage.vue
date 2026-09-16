<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import api from '@/api/axios'
import { formatDate } from '@/utils/format'

const users = ref([])
const loading = ref(true)
const error = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const filters = ref({
  status: '',
  search: '',
})

async function fetchUsers(page = 1) {
  loading.value = true
  error.value = null

  try {
    const params = { page }
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.search) params.search = filters.value.search

    const { data } = await api.get('/admin/users', { params })
    users.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    }
  } catch (e) {
    error.value = 'Не удалось загрузить пользователей'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchUsers())

function applyFilters() {
  fetchUsers(1)
}

function resetFilters() {
  filters.value = { status: '', search: '' }
  fetchUsers(1)
}

function userBadge(user) {
  if (user.is_blocked) {
    return {
      label: 'Заблокирован',
      class: 'bg-red-600 text-white border-red-700',
    }
  }

  const map = {
    approved: {
      label: 'Одобрен',
      class: 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400',
    },
    pending: {
      label: 'Ожидает',
      class: 'bg-amber-100 text-amber-700 border-amber-700 dark:bg-amber-900 dark:text-amber-200 dark:border-amber-400',
    },
    rejected: {
      label: 'Отклонён',
      class: 'bg-red-100 text-red-700 border-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-400',
    },
  }

  return map[user.approval_status] || {
    label: user.approval_status,
    class: 'bg-slate-100 text-slate-700 border-slate-700',
  }
}
</script>

<template>
  <AppLayout>
    <AdminLayout>
      <!-- Заголовок -->
      <div class="mb-6">
        <h2 class="text-xl font-black uppercase tracking-wider">Пользователи</h2>
        <p class="text-sm opacity-70 mt-1">Всего: {{ pagination.total }}</p>
      </div>

      <!-- Фильтры -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-4 mb-6">
        <div class="flex gap-3 flex-wrap">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Поиск по email, имени, телефону"
            class="flex-1 min-w-[200px] px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all"
            @keyup.enter="applyFilters"
          />

          <select
            v-model="filters.status"
            class="px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all"
          >
            <option value="">Все статусы</option>
            <option value="approved">Одобренные</option>
            <option value="pending">Ожидают</option>
            <option value="rejected">Отклонённые</option>
          </select>

          <Button variant="primary" size="sm" @click="applyFilters">
            Найти
          </Button>
          <Button variant="ghost" size="sm" @click="resetFilters">
            Сбросить
          </Button>
        </div>
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
        <Button variant="secondary" class="mt-4" @click="fetchUsers()">
          Попробовать снова
        </Button>
      </div>

      <!-- Empty -->
      <div
        v-else-if="users.length === 0"
        class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-12 text-center"
      >
        <p class="text-xl font-bold mb-2">Пользователей нет</p>
        <p class="text-sm opacity-70">Попробуйте изменить фильтры</p>
      </div>

      <!-- Список -->
      <div v-else class="space-y-4">
        <!-- Десктоп: таблица -->
        <div class="hidden md:block bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal overflow-hidden">
          <table class="w-full">
            <thead class="border-b-2 border-black dark:border-white bg-slate-50 dark:bg-[#1a0b2e]">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Имя</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Роль</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Статус</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Ключей</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Регистрация</th>
                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(user, index) in users"
                :key="user.id"
                class="animate-list-item border-b-2 border-black/10 dark:border-white/10 last:border-0 hover:bg-slate-50 dark:hover:bg-[#1a0b2e] cursor-pointer"
                :style="{ animationDelay: `${Math.min(index, 10) * 30}ms` }"
                @click="$router.push({ name: 'admin-user', params: { id: user.id } })"
              >
                <td class="px-4 py-3 font-bold">
                  {{ user.last_name }} {{ user.first_name }}
                </td>
                <td class="px-4 py-3 text-sm font-mono opacity-70">{{ user.email }}</td>
                <td class="px-4 py-3">
                  <span
                    v-for="role in user.roles"
                    :key="role.code"
                    class="inline-block px-2 py-0.5 text-xs font-bold uppercase border-2 border-black dark:border-white mr-1"
                  >
                    {{ role.code }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold uppercase border-2 whitespace-nowrap',
                      userBadge(user).class,
                    ]"
                  >
                    <svg
                      v-if="user.is_blocked"
                      xmlns="http://www.w3.org/2000/svg"
                      width="11" height="11"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="3"
                      stroke-linecap="square"
                    >
                      <rect x="3" y="11" width="18" height="11" />
                      <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    {{ userBadge(user).label }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm">{{ user.vpn_clients_count ?? 0 }}</td>
                <td class="px-4 py-3 text-sm">{{ formatDate(user.registration_date) }}</td>
                <td class="px-4 py-3 text-right">
                  <span class="text-xs font-bold uppercase opacity-60">→</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Мобильный: карточки -->
        <div class="md:hidden space-y-3">
          <RouterLink
            v-for="(user, index) in users"
            :key="user.id"
            :to="{ name: 'admin-user', params: { id: user.id } }"
            class="animate-list-item block bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-4"
            :style="{ animationDelay: `${Math.min(index, 10) * 30}ms` }"
          >
            <div class="flex justify-between items-start mb-2">
              <div class="min-w-0">
                <p class="font-bold truncate">
                  {{ user.last_name }} {{ user.first_name }}
                </p>
                <p class="text-xs font-mono opacity-70 truncate">{{ user.email }}</p>
              </div>

              <span
                :class="[
                  'inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold uppercase border-2 whitespace-nowrap ml-2 shrink-0',
                  userBadge(user).class,
                ]"
              >
                <svg
                  v-if="user.is_blocked"
                  xmlns="http://www.w3.org/2000/svg"
                  width="11" height="11"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                  stroke-linecap="square"
                >
                  <rect x="3" y="11" width="18" height="11" />
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                {{ userBadge(user).label }}
              </span>
            </div>

            <div class="flex gap-4 text-xs opacity-70 mt-2 flex-wrap">
              <span>Ключей: {{ user.vpn_clients_count ?? 0 }}</span>
              <span>{{ formatDate(user.registration_date) }}</span>
            </div>

            <div v-if="user.roles?.length" class="flex gap-1 mt-2 flex-wrap">
              <span
                v-for="role in user.roles"
                :key="role.code"
                class="inline-block px-2 py-0.5 text-xs font-bold uppercase border-2 border-black dark:border-white"
              >
                {{ role.code }}
              </span>
            </div>
          </RouterLink>
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
            @click="fetchUsers(pagination.current_page - 1)"
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
            @click="fetchUsers(pagination.current_page + 1)"
          >
            Вперёд →
          </Button>
        </div>
      </div>
    </AdminLayout>
  </AppLayout>
</template>
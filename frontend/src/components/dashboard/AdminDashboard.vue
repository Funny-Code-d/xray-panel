<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import { formatBytes, formatDate } from '@/utils/format'

const data = ref(null)
const loading = ref(true)
const error = ref(null)

const latestPosts = ref([])
const loadingPosts = ref(true)

async function fetchDashboard() {
  loading.value = true
  error.value = null

  try {
    const { data: res } = await api.get('/admin/dashboard')
    data.value = res
  } catch (e) {
    error.value = 'Не удалось загрузить статистику'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function fetchLatestPosts() {
  try {
    const { data: res } = await api.get('/posts', { params: { per_page: 3 } })
    latestPosts.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingPosts.value = false
  }
}

onMounted(() => {
  fetchDashboard()
  fetchLatestPosts()
})
</script>

<template>
  <div class="mb-8">
    <h1 class="text-3xl font-black uppercase tracking-wider">Управление</h1>
    <p class="mt-2 text-sm opacity-70">Обзор состояния системы</p>
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
  </div>

  <!-- Dashboard -->
  <div v-else-if="data" class="space-y-6">
    <!-- Верхний ряд: метрики пользователей -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
        <p class="text-xs font-bold uppercase tracking-wider opacity-60 mb-1">Пользователей</p>
        <p class="text-3xl font-black">{{ data.users.total }}</p>
        <p class="text-xs opacity-60 mt-1">
          +{{ data.users.new_this_week }} за неделю
        </p>
      </div>

      <RouterLink
        :to="{ name: 'admin-applications' }"
        class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5 hover:shadow-brutal-hover transition-all"
      >
        <p class="text-xs font-bold uppercase tracking-wider opacity-60 mb-1">Ожидают</p>
        <p class="text-3xl font-black" :class="data.users.pending > 0 ? 'text-amber-600 dark:text-amber-400' : ''">
          {{ data.users.pending }}
        </p>
        <p class="text-xs opacity-60 mt-1">заявок</p>
      </RouterLink>

      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
        <p class="text-xs font-bold uppercase tracking-wider opacity-60 mb-1">Заблокировано</p>
        <p class="text-3xl font-black" :class="data.users.blocked > 0 ? 'text-red-600 dark:text-red-400' : ''">
          {{ data.users.blocked }}
        </p>
        <p class="text-xs opacity-60 mt-1">аккаунтов</p>
      </div>

      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
        <p class="text-xs font-bold uppercase tracking-wider opacity-60 mb-1">VPN-ключей</p>
        <p class="text-3xl font-black">{{ data.clients.total }}</p>
        <p class="text-xs opacity-60 mt-1">
          {{ data.clients.active }} активных
        </p>
      </div>
    </div>

    <!-- Трафик -->
    <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
      <h2 class="text-sm font-bold uppercase tracking-wider opacity-60 mb-4">Общий трафик</h2>
      <p class="text-4xl font-black">{{ formatBytes(data.traffic.total_used) }}</p>
      <p class="text-xs opacity-60 mt-1">
        Суммарно по всем ключам всех пользователей
      </p>
    </div>

    <!-- Два столбца: заявки + последние ключи -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Последние заявки -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-sm font-bold uppercase tracking-wider opacity-60">Последние заявки</h2>
          <RouterLink
            :to="{ name: 'admin-applications' }"
            class="text-xs font-bold uppercase text-[#FF4911] dark:text-[#FF00FF] border-b-2 border-[#FF4911] dark:border-[#FF00FF] hover:opacity-80 transition hover:underline"
          >
            Все →
          </RouterLink>
        </div>

        <div v-if="data.recent_pending.length === 0" class="text-sm opacity-60 text-center py-4">
          Нет новых заявок
        </div>

        <div v-else class="space-y-2">
          <RouterLink
            v-for="user in data.recent_pending"
            :key="user.id"
            :to="{ name: 'admin-user', params: { id: user.id } }"
            class="block p-3 border-2 border-black/20 dark:border-white/20 hover:bg-slate-50 dark:hover:bg-[#1a0b2e] transition"
          >
            <p class="font-bold text-sm truncate">{{ user.full_name }}</p>
            <p class="text-xs font-mono opacity-60 truncate">{{ user.email }}</p>
            <p class="text-xs opacity-60 mt-1">{{ formatDate(user.registration_date) }}</p>
          </RouterLink>
        </div>
      </div>

      <!-- Последние ключи -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-sm font-bold uppercase tracking-wider opacity-60">Последние ключи</h2>
          <RouterLink
            :to="{ name: 'admin-users' }"
            class="text-xs font-bold uppercase text-[#FF4911] dark:text-[#FF00FF] border-b-2 border-[#FF4911] dark:border-[#FF00FF] hover:opacity-80 transition hover:underline"
          >
            Все →
          </RouterLink>
        </div>

        <div v-if="data.recent_clients.length === 0" class="text-sm opacity-60 text-center py-4">
          Ключей ещё нет
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="client in data.recent_clients"
            :key="client.id"
            class="p-3 border-2 border-black/20 dark:border-white/20"
          >
            <div class="flex justify-between items-start gap-2">
              <div class="min-w-0">
                <p class="font-bold text-sm truncate">{{ client.name }}</p>
                <p class="text-xs opacity-60 truncate">
                  Владелец: {{ client.user_name || '—' }}
                </p>
              </div>
              <span
                class="text-xs font-bold uppercase px-2 py-0.5 border-2 shrink-0"
                :class="client.is_active
                  ? 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400'
                  : 'bg-slate-200 text-slate-700 border-slate-700 dark:bg-slate-700 dark:text-slate-200 dark:border-slate-300'"
              >
                {{ client.is_active ? 'Вкл' : 'Выкл' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Последние новости -->
    <div>
      <div class="flex justify-between items-end mb-4">
        <h2 class="text-xl font-black uppercase tracking-wider">Последние новости</h2>
        <RouterLink
          :to="{ name: 'admin-posts' }"
          class="text-xs font-bold uppercase tracking-wide text-[#FF4911] dark:text-[#FF00FF] border-b-2 border-[#FF4911] dark:border-[#FF00FF] hover:opacity-80 transition hover:underline"
        >
          Управление постами →
        </RouterLink>
      </div>

      <!-- Loading -->
      <div v-if="loadingPosts" class="text-center py-8 opacity-70 text-sm">
        Загрузка...
      </div>

      <!-- Пусто -->
      <div
        v-else-if="latestPosts.length === 0"
        class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6 text-center"
      >
        <p class="text-sm opacity-70 mb-3">Постов пока нет</p>
        <RouterLink
          :to="{ name: 'admin-posts' }"
          class="inline-block px-4 py-2 text-xs font-bold uppercase tracking-wide border-2 border-black dark:border-white hover:shadow-brutal-sm transition-all"
        >
          Создать первый пост
        </RouterLink>
      </div>

      <!-- Посты -->
      <div v-else class="space-y-3">
        <RouterLink
          v-for="post in latestPosts"
          :key="post.id"
          :to="{ name: 'news-post', params: { slug: post.slug } }"
          class="block bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-4 hover:shadow-brutal-hover transition-all"
        >
          <div class="flex justify-between items-start gap-4">
            <div class="min-w-0 flex-1">
              <!-- Теги -->
              <div v-if="post.tags?.length" class="flex flex-wrap gap-1.5 mb-2">
                <span
                  v-for="tag in post.tags"
                  :key="tag.code"
                  class="inline-block px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white border border-black dark:border-white"
                  :style="{ backgroundColor: tag.color }"
                >
                  {{ tag.name }}
                </span>
              </div>

              <!-- Заголовок -->
              <h3 class="font-bold text-base mb-1">
                {{ post.title }}
              </h3>

              <!-- Excerpt -->
              <p class="text-xs opacity-70 mb-2 line-clamp-2">
                {{ post.excerpt }}
              </p>

              <!-- Мета -->
              <div class="text-[10px] opacity-60 uppercase tracking-wider">
                {{ formatDate(post.published_at) }} · {{ post.reading_time }} мин
              </div>
            </div>

            <!-- Статус -->
            <span
              class="text-xs font-bold uppercase px-2 py-0.5 border-2 shrink-0"
              :class="post.is_published
                ? 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400'
                : 'bg-amber-100 text-amber-700 border-amber-700 dark:bg-amber-900 dark:text-amber-200 dark:border-amber-400'"
            >
              {{ post.is_published ? 'Live' : 'Черновик' }}
            </span>
          </div>
        </RouterLink>

        <div class="pt-2">
          <RouterLink
            :to="{ name: 'admin-posts' }"
            class="inline-block text-xs font-bold uppercase tracking-wide text-[#FF4911] dark:text-[#FF00FF] border-b-2 border-[#FF4911] dark:border-[#FF00FF] hover:opacity-80 transition hover:underline"
          >
            Все посты →
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
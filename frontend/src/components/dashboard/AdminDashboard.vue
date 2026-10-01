<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/api/axios'
import { formatBytes, formatDate } from '@/utils/format'
import Card from '@/components/ui/Card.vue'
import ServerMiniCard from '@/components/server/ServerMiniCard.vue'

const data = ref(null)
const loading = ref(true)
const error = ref(null)

// Серверы
const servers = ref([])
const loadingServers = ref(true)

const onlineNodesCount = computed(() =>
  servers.value.filter(s => s.status === 'online').length
)

// Последние посты
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

async function fetchServers() {
  try {
    const { data } = await api.get('/servers')
    servers.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loadingServers.value = false
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
  fetchServers()
  fetchLatestPosts()
})
</script>

<template>
  <!-- Заголовок страницы -->
  <div class="mb-8">
    <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-wider leading-none">
      Control
    </h1>
    <p class="mt-3 text-base opacity-70">System overview</p>
  </div>

  <!-- Loading -->
  <div v-if="loading" class="text-center py-12 opacity-70 text-sm">
    Loading...
  </div>

  <!-- Error -->
  <div
    v-else-if="error"
    class="bg-red-100 dark:bg-red-900 border-[3px] border-red-700 dark:border-red-400 shadow-brutal p-6 text-center"
  >
    <p class="font-bold">{{ error }}</p>
  </div>

  <!-- Dashboard -->
  <div v-else-if="data" class="space-y-10">
    <!-- Топ-метрики -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Users -->
      <Card dense accent="yellow" class="flex flex-col card-tilt">
        <div class="text-2xl font-black uppercase tracking-wider mb-2">Users</div>
        <div class="text-5xl font-black leading-none">{{ data.users.total }}</div>
        <div class="text-sm opacity-60 mt-2">
          +{{ data.users.new_this_week }} this week
        </div>
      </Card>

      <!-- Pending -->
      <RouterLink :to="{ name: 'admin-applications' }" class="block">
        <Card dense accent="black" class="flex flex-col card-tilt-r h-full">
          <div class="text-2xl font-black uppercase tracking-wider mb-2">Pending</div>
          <div
            class="text-5xl font-black leading-none"
            :class="data.users.pending > 0 ? 'text-amber-600 dark:text-amber-400' : ''"
          >
            {{ data.users.pending }}
          </div>
          <div class="text-sm opacity-60 mt-2">applications</div>
        </Card>
      </RouterLink>

      <!-- Blocked -->
      <Card dense accent="yellow" class="flex flex-col card-tilt">
        <div class="text-2xl font-black uppercase tracking-wider mb-2">Blocked</div>
        <div
          class="text-5xl font-black leading-none"
          :class="data.users.blocked > 0 ? 'text-red-600 dark:text-red-400' : ''"
        >
          {{ data.users.blocked }}
        </div>
        <div class="text-sm opacity-60 mt-2">accounts</div>
      </Card>

      <!-- Keys -->
      <Card dense accent="black" class="flex flex-col card-tilt-r">
        <div class="text-2xl font-black uppercase tracking-wider mb-2">Keys</div>
        <div class="text-5xl font-black leading-none">{{ data.clients.total }}</div>
        <div class="text-sm opacity-60 mt-2">
          {{ data.clients.active }} active
        </div>
      </Card>
    </div>

    <!-- Трафик -->
    <Card dense accent="yellow">
      <div class="text-2xl font-black uppercase tracking-wider mb-2">Total traffic</div>
      <div class="text-5xl font-black leading-none">
        {{ formatBytes(data.traffic.total_used) }}
      </div>
      <div class="text-sm opacity-60 mt-2">
        Across all keys of all users
      </div>
    </Card>

    <!-- NODES -->
    <div>
      <div class="flex justify-between items-end mb-4">
        <h2 class="text-3xl font-black uppercase tracking-wider">Nodes</h2>
        <RouterLink
          :to="{ name: 'admin-servers' }"
          class="text-xs font-black uppercase tracking-wider underline hover:no-underline transition"
        >
          Manage nodes →
        </RouterLink>
      </div>

      <div v-if="loadingServers" class="text-center py-8 opacity-70 text-sm">
        Loading...
      </div>

      <Card v-else-if="servers.length === 0" dense class="text-center">
        <p class="text-sm opacity-70 mb-3">No nodes yet</p>
        <RouterLink
          :to="{ name: 'admin-servers' }"
          class="inline-block px-4 py-2 text-xs font-black uppercase tracking-wider border-[3px] border-black dark:border-white hover:bg-black hover:text-white transition-colors"
        >
          Add node
        </RouterLink>
      </Card>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <RouterLink
          v-for="(server, index) in servers"
          :key="server.id"
          :to="{ name: 'admin-servers' }"
          class="animate-list-item block"
          :class="index % 2 === 0 ? 'card-tilt' : 'card-tilt-r'"
          :style="{ animationDelay: `${Math.min(index, 10) * 50}ms` }"
        >
          <ServerMiniCard :server="server" />
        </RouterLink>
      </div>
    </div>

    <!-- Два столбца: заявки + ключи -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Последние заявки -->
      <Card dense accent="black" class="flex flex-col">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-black uppercase tracking-wider">Applications</h2>
          <RouterLink
            :to="{ name: 'admin-applications' }"
            class="text-xs font-black uppercase tracking-wider underline hover:no-underline transition"
          >
            All →
          </RouterLink>
        </div>

        <div v-if="data.recent_pending.length === 0" class="text-sm opacity-60 text-center py-4">
          No new applications
        </div>

        <div v-else class="space-y-2">
          <RouterLink
            v-for="user in data.recent_pending"
            :key="user.id"
            :to="{ name: 'admin-user', params: { id: user.id } }"
            class="block p-3 border-[3px] border-black dark:border-white hover:bg-[#FFD700] transition-colors menu-tilt"
          >
            <p class="font-black text-sm truncate">{{ user.full_name }}</p>
            <p class="text-xs font-mono opacity-70 truncate">{{ user.email }}</p>
            <p class="text-xs opacity-60 mt-1">{{ formatDate(user.registration_date) }}</p>
          </RouterLink>
        </div>
      </Card>

      <!-- Последние ключи -->
      <Card dense accent="yellow" class="flex flex-col">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-black uppercase tracking-wider">Latest keys</h2>
          <RouterLink
            :to="{ name: 'admin-users' }"
            class="text-xs font-black uppercase tracking-wider underline hover:no-underline transition"
          >
            All →
          </RouterLink>
        </div>

        <div v-if="data.recent_clients.length === 0" class="text-sm opacity-60 text-center py-4">
          No keys yet
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="client in data.recent_clients"
            :key="client.id"
            class="p-3 border-[3px] border-black dark:border-white"
          >
            <div class="flex justify-between items-start gap-2">
              <div class="min-w-0">
                <p class="font-black text-sm truncate">{{ client.name }}</p>
                <p class="text-xs opacity-60 truncate">
                  Owner: {{ client.user_name || '—' }}
                </p>
              </div>
              <span
                class="text-xs font-black uppercase px-2 py-0.5 border-2 shrink-0"
                :class="client.is_active
                  ? 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400'
                  : 'bg-slate-200 text-slate-700 border-slate-700 dark:bg-slate-700 dark:text-slate-200 dark:border-slate-300'"
              >
                {{ client.is_active ? 'On' : 'Off' }}
              </span>
            </div>
          </div>
        </div>
      </Card>
    </div>

    <!-- NEWS -->
    <div>
      <div class="flex justify-between items-end mb-4">
        <h2 class="text-3xl font-black uppercase tracking-wider">News</h2>
        <RouterLink
          :to="{ name: 'admin-posts' }"
          class="text-xs font-black uppercase tracking-wider underline hover:no-underline transition"
        >
          Manage posts →
        </RouterLink>
      </div>

      <div v-if="loadingPosts" class="text-center py-8 opacity-70 text-sm">
        Loading...
      </div>

      <Card v-else-if="latestPosts.length === 0" dense class="text-center">
        <p class="text-sm opacity-70 mb-3">No posts yet</p>
        <RouterLink
          :to="{ name: 'admin-posts' }"
          class="inline-block px-4 py-2 text-xs font-black uppercase tracking-wider border-[3px] border-black dark:border-white hover:bg-black hover:text-white transition-colors"
        >
          Create first post
        </RouterLink>
      </Card>

      <div v-else class="space-y-3">
        <RouterLink
          v-for="(post, index) in latestPosts"
          :key="post.id"
          :to="{ name: 'news-post', params: { slug: post.slug } }"
          class="block bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal flex flex-col sm:flex-row"
          :class="index % 2 === 0 ? 'card-tilt' : 'card-tilt-r'"
        >
          <!-- Акцентная полоса слева (на мобильном — сверху) -->
          <div class="h-2 sm:h-auto sm:w-2 bg-black dark:bg-white border-b-[3px] sm:border-b-0 sm:border-r-[3px] border-black dark:border-white shrink-0"></div>

          <!-- Контент -->
          <div class="flex-1 p-4 min-w-0">
            <div v-if="post.tags?.length" class="flex flex-wrap gap-1.5 mb-2">
              <span
                v-for="tag in post.tags"
                :key="tag.code"
                class="inline-block px-2 py-0.5 text-xs font-black uppercase tracking-wider text-white border-2 border-black dark:border-white"
                :style="{ backgroundColor: tag.color }"
              >
                {{ tag.name }}
              </span>
            </div>

            <h3 class="font-black text-xl sm:text-2xl uppercase tracking-wider leading-tight mb-2 break-words">
              {{ post.title }}
            </h3>

            <p class="text-sm opacity-70 mb-3 line-clamp-2">
              {{ post.excerpt }}
            </p>

            <div class="flex items-center gap-2 text-[11px] opacity-60 uppercase tracking-wider font-bold">
              <span>{{ formatDate(post.published_at) }}</span>
              <span>·</span>
              <span>{{ post.reading_time }} min</span>
            </div>
          </div>

          <!-- Статус -->
          <div class="flex items-center justify-between sm:justify-center px-4 py-3 sm:py-0 border-t-[3px] sm:border-t-0 sm:border-l-[3px] border-black dark:border-white shrink-0">
            <span
              class="text-xs font-black uppercase px-2 py-1 border-2"
              :class="post.is_published
                ? 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400'
                : 'bg-amber-100 text-amber-700 border-amber-700 dark:bg-amber-900 dark:text-amber-200 dark:border-amber-400'"
            >
              {{ post.is_published ? 'Live' : 'Draft' }}
            </span>
          </div>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
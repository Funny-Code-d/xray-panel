<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import { formatBytes, formatDate } from '@/utils/format'
import Card from '@/components/ui/Card.vue'
import ServerMiniCard from '@/components/server/ServerMiniCard.vue'

const auth = useAuthStore()

// Трафик
const trafficUsed = computed(() => formatBytes(auth.user?.traffic_used ?? 0))

const trafficLimit = computed(() => {
  const limit = auth.user?.traffic_limit
  if (limit === null || limit === undefined) return 'Unlimited'
  return formatBytes(limit)
})

const trafficPercent = computed(() => {
  const limit = auth.user?.traffic_limit
  const used = auth.user?.traffic_used ?? 0
  if (!limit || used === 0) return 0
  return Math.min(100, Math.round((used / limit) * 100))
})

const clientsCount = computed(() => auth.user?.vpn_clients_count ?? 0)

// Серверы
const servers = ref([])
const loadingServers = ref(true)

const onlineNodesCount = computed(() =>
  servers.value.filter(s => s.status === 'online').length
)

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

// Последние посты
const latestPosts = ref([])
const loadingPosts = ref(true)

async function fetchLatestPosts() {
  try {
    const { data } = await api.get('/posts', { params: { per_page: 3 } })
    latestPosts.value = data.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingPosts.value = false
  }
}

onMounted(() => {
  fetchServers()
  fetchLatestPosts()
})
</script>

<template>
  <!-- Заголовок страницы -->
  <div class="mb-8">
    <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-wider leading-none">
      What's up?
    </h1>
    <p class="mt-3 text-base opacity-70">
      {{ auth.user?.full_name || auth.user?.email }}
    </p>
  </div>

  <!-- Топ-метрики -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
    <!-- Traffic -->
    <Card dense accent="yellow" class="flex flex-col card-tilt">
      <div class="flex-1">
        <div class="text-2xl font-black uppercase tracking-wider mb-2">Traffic</div>
        <div class="text-5xl font-black leading-none">{{ trafficUsed }}</div>
        <div class="text-sm opacity-60 mt-2">of {{ trafficLimit }}</div>

        <div v-if="auth.user?.traffic_limit" class="mt-3">
          <div class="w-full border-[3px] border-black dark:border-white h-2">
            <div
              class="h-full transition-all"
              :class="trafficPercent > 90 ? 'bg-red-500' : trafficPercent > 70 ? 'bg-amber-500' : 'bg-[#FFD700]'"
              :style="{ width: `${trafficPercent}%` }"
            ></div>
          </div>
          <div class="text-xs opacity-60 mt-1">{{ trafficPercent }}%</div>
        </div>
      </div>
    </Card>

    <!-- Keys -->
    <Card dense accent="black" class="flex flex-col card-tilt">
      <div class="flex-1">
        <div class="text-2xl font-black uppercase tracking-wider mb-2">Keys</div>
        <div class="text-5xl font-black leading-none">{{ clientsCount }}</div>
        <div class="text-sm opacity-60 mt-2">active</div>
      </div>
      <RouterLink
        :to="{ name: 'clients' }"
        class="mt-3 inline-flex items-center justify-center px-3 py-2 bg-[#FFD700] border-[3px] border-black dark:border-white font-black uppercase text-xs tracking-wider hover:bg-black hover:text-white transition-colors"
      >
        Manage →
      </RouterLink>
    </Card>

    <!-- Nodes -->
    <Card dense accent="yellow" class="flex flex-col card-tilt">
      <div class="flex-1">
        <div class="text-2xl font-black uppercase tracking-wider mb-2">Nodes</div>
        <div class="text-5xl font-black leading-none">
          {{ onlineNodesCount }}<span class="opacity-40">/</span>{{ servers.length }}
        </div>
        <div class="text-sm opacity-60 mt-2">online</div>
      </div>
      <RouterLink
        :to="{ name: 'servers' }"
        class="mt-3 inline-flex items-center justify-center px-3 py-2 bg-black text-white border-[3px] border-black dark:border-white font-black uppercase text-xs tracking-wider hover:bg-[#FFD700] hover:text-black transition-colors"
      >
        View →
      </RouterLink>
    </Card>
  </div>

  <!-- NODES -->
  <!-- <div class="mb-10">
    <div class="flex justify-between items-end mb-4">
      <h2 class="text-3xl font-black uppercase tracking-wider">Nodes</h2>
      <RouterLink
        :to="{ name: 'servers' }"
        class="text-xs font-black uppercase tracking-wider underline hover:no-underline transition"
      >
        All nodes →
      </RouterLink>
    </div>

    <div v-if="loadingServers" class="text-center py-8 opacity-70 text-sm">
      Loading...
    </div>

    <Card v-else-if="servers.length === 0" dense class="text-center">
      <p class="text-sm opacity-70">No nodes available</p>
    </Card>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <RouterLink
        v-for="(server, index) in servers"
        :key="server.id"
        :to="{ name: 'servers' }"
        class="animate-list-item block"
        :class="index % 2 === 0 ? 'card-tilt' : 'card-tilt-r'"
        :style="{ animationDelay: `${Math.min(index, 10) * 50}ms` }"
      >
        <ServerMiniCard :server="server" />
      </RouterLink>
    </div>
  </div> -->

  <!-- NEWS -->
  <div>
    <div class="flex justify-between items-end mb-4">
      <h2 class="text-3xl font-black uppercase tracking-wider">News</h2>
      <RouterLink
        :to="{ name: 'news' }"
        class="text-xs font-black uppercase tracking-wider underline hover:no-underline transition"
      >
        All news →
      </RouterLink>
    </div>

    <div v-if="loadingPosts" class="text-center py-8 opacity-70 text-sm">
      Loading...
    </div>

    <Card v-else-if="latestPosts.length === 0" dense class="text-center">
      <p class="text-sm opacity-70">No news yet</p>
    </Card>

    <div v-else class="space-y-3">
      <RouterLink
        v-for="(post, index) in latestPosts"
        :key="post.id"
        :to="{ name: 'news-post', params: { slug: post.slug } }"
        class="block bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal flex flex-col sm:flex-row"
        :class="index % 2 === 0 ? 'card-tilt' : 'card-tilt-r'"
      >
        <!-- Акцентная полоса: сверху на мобильном, слева на десктопе -->
        <div class="h-2 sm:h-auto sm:w-2 bg-black dark:bg-white border-b-[3px] sm:border-b-0 sm:border-r-[3px] border-black dark:border-white shrink-0"></div>

        <!-- Контент -->
        <div class="flex-1 p-4 min-w-0">
          <!-- Теги -->
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

          <!-- Заголовок -->
          <h3 class="font-black text-xl sm:text-2xl uppercase tracking-normal sm:tracking-wider leading-tight mb-2 break-words">
            {{ post.title }}
          </h3>

          <!-- Excerpt -->
          <p class="text-sm opacity-70 mb-3 line-clamp-2">
            {{ post.excerpt }}
          </p>

          <!-- Мета -->
          <div class="flex items-center gap-2 text-[11px] opacity-60 uppercase tracking-wider font-bold">
            <span>{{ formatDate(post.published_at) }}</span>
            <span>·</span>
            <span>{{ post.reading_time }} min</span>
          </div>
        </div>

        <!-- Стрелка: снизу на мобильном, справа на десктопе -->
        <div class="flex items-center justify-end sm:justify-center px-4 py-3 sm:py-0 border-t-[3px] sm:border-t-0 sm:border-l-[3px] border-black dark:border-white shrink-0">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
          </svg>
        </div>
      </RouterLink>
    </div>
  </div>
</template>
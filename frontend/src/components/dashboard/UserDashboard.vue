<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import { formatBytes, formatDate } from '@/utils/format'

const auth = useAuthStore()

// Трафик
const trafficUsed = computed(() => formatBytes(auth.user?.traffic_used ?? 0))

const trafficLimit = computed(() => {
  const limit = auth.user?.traffic_limit
  if (limit === null || limit === undefined) return 'Безлимит'
  return formatBytes(limit)
})

const trafficPercent = computed(() => {
  const limit = auth.user?.traffic_limit
  const used = auth.user?.traffic_used ?? 0
  if (!limit || used === 0) return 0
  return Math.min(100, Math.round((used / limit) * 100))
})

const clientsCount = computed(() => auth.user?.vpn_clients_count ?? 0)

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

onMounted(fetchLatestPosts)
</script>

<template>
  <div class="mb-8">
    <h1 class="text-3xl font-black uppercase tracking-wider">Dashboard</h1>
    <p class="mt-2 text-sm opacity-70">
      {{ auth.user?.full_name || auth.user?.email }}
    </p>
  </div>

  <!-- Компактные карточки -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <!-- Трафик — компактный -->
    <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
      <div class="flex justify-between items-baseline mb-3">
        <h2 class="text-xs font-bold uppercase tracking-wider opacity-60">
          Трафик
        </h2>
        <span v-if="auth.user?.traffic_limit" class="text-xs font-bold opacity-60">
          {{ trafficPercent }}%
        </span>
      </div>

      <div class="flex items-baseline gap-2 mb-3">
        <span class="text-2xl font-black">{{ trafficUsed }}</span>
        <span class="text-xs opacity-60">из {{ trafficLimit }}</span>
      </div>

      <div v-if="auth.user?.traffic_limit" class="w-full border-2 border-black dark:border-white h-3">
        <div
          class="h-full transition-all"
          :class="trafficPercent > 90 ? 'bg-red-500' : trafficPercent > 70 ? 'bg-amber-500' : 'bg-blue-600 dark:bg-orange-500'"
          :style="{ width: `${trafficPercent}%` }"
        ></div>
      </div>
    </div>

    <!-- Ключи — компактный -->
    <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
      <h2 class="text-xs font-bold uppercase tracking-wider opacity-60 mb-3">
        Ключи доступа
      </h2>

      <div class="flex items-baseline gap-2 mb-3">
        <span class="text-2xl font-black">{{ clientsCount }}</span>
        <span class="text-xs opacity-60">
          {{ clientsCount === 1 ? 'активный' : 'активных' }}
        </span>
      </div>

      <RouterLink
        :to="{ name: 'clients' }"
        class="text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-orange-500 hover:underline"
      >
        Управление ключами →
      </RouterLink>
    </div>
  </div>

  <!-- Последние новости -->
  <div>
    <div class="flex justify-between items-end mb-4">
      <h2 class="text-xl font-black uppercase tracking-wider">Последние новости</h2>
      <RouterLink
        :to="{ name: 'news' }"
        class="text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-orange-500 hover:underline"
      >
        Все новости →
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
      <p class="text-sm opacity-70">Новостей пока нет</p>
    </div>

    <!-- Посты -->
    <div v-else class="space-y-3">
      <RouterLink
        v-for="post in latestPosts"
        :key="post.id"
        :to="{ name: 'news-post', params: { slug: post.slug } }"
        class="block bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-4 hover:shadow-brutal-hover transition-all"
      >
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
      </RouterLink>
    </div>
  </div>
</template>
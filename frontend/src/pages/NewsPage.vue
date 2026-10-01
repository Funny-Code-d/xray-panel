<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/api/axios'
import { formatDate } from '@/utils/format'

const route = useRoute()

const posts = ref([])
const tags = ref([])
const loading = ref(true)
const error = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const activeTag = ref(route.query.tag || '')

async function fetchPosts(page = 1) {
  loading.value = true
  error.value = null

  try {
    const params = { page }
    if (activeTag.value) params.tag = activeTag.value

    const { data } = await api.get('/posts', { params })
    posts.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    }
  } catch (e) {
    error.value = 'Не удалось загрузить новости'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function fetchTags() {
  try {
    const { data } = await api.get('/tags')
    tags.value = data
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  fetchPosts()
  fetchTags()
})

function selectTag(code) {
  activeTag.value = activeTag.value === code ? '' : code
  fetchPosts(1)
}

const hasPosts = computed(() => posts.value.length > 0)
</script>

<template>
  <AppLayout>
    <!-- Заголовок -->
    <div class="mb-8">
      <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-wider leading-none">
        News
      </h1>
      <p class="mt-3 text-base opacity-70">
        {{ pagination.total }} {{ pagination.total === 1 ? 'post' : 'posts' }}
      </p>
    </div>

    <!-- Фильтр по тегам -->
    <div class="mb-8 flex flex-wrap gap-2">
      <button
        @click="activeTag = ''; fetchPosts(1)"
        :class="[
          'px-3 py-1.5 text-xs font-black uppercase tracking-wider border-[3px] transition-all',
          !activeTag
            ? 'bg-black text-white border-black dark:border-white'
            : 'bg-white dark:bg-[#1A1A1A] border-black dark:border-white hover:bg-[#FFD700] hover:text-black',
        ]"
      >
        All
      </button>
      <button
        v-for="tag in tags"
        :key="tag.code"
        @click="selectTag(tag.code)"
        :class="[
          'px-3 py-1.5 text-xs font-black uppercase tracking-wider border-[3px] border-black dark:border-white transition-all',
          activeTag === tag.code
            ? 'text-white'
            : 'bg-white dark:bg-[#1A1A1A] hover:bg-[#FFD700] hover:text-black',
        ]"
        :style="activeTag === tag.code ? { backgroundColor: tag.color } : {}"
      >
        {{ tag.name }}
      </button>
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
      <p class="font-black">{{ error }}</p>
    </div>

    <!-- Empty -->
    <div
      v-else-if="!hasPosts"
      class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-12 text-center"
    >
      <p class="text-2xl font-black uppercase tracking-wider mb-2">No news yet</p>
      <p class="text-sm opacity-70">Check back later</p>
    </div>

    <!-- Список постов -->
    <div v-else class="space-y-3">
      <RouterLink
        v-for="(post, index) in posts"
        :key="post.id"
        :to="{ name: 'news-post', params: { slug: post.slug } }"
        class="animate-list-item block bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal flex flex-col sm:flex-row"
        :class="index % 2 === 0 ? 'card-tilt' : 'card-tilt-r'"
        :style="{ animationDelay: `${Math.min(index, 10) * 30}ms` }"
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
          <h2 class="font-black text-xl sm:text-2xl uppercase tracking-normal sm:tracking-wider leading-tight mb-2 break-words">
            {{ post.title }}
          </h2>

          <!-- Excerpt -->
          <p class="text-sm opacity-70 mb-3 line-clamp-2">
            {{ post.excerpt }}
          </p>

          <!-- Мета -->
          <div class="flex items-center gap-2 text-[11px] opacity-60 uppercase tracking-wider font-bold">
            <span>{{ post.author?.name || 'VPN Admin' }}</span>
            <span>·</span>
            <span>{{ formatDate(post.published_at) }}</span>
            <span class="hidden sm:inline">·</span>
            <span class="hidden sm:inline">{{ post.reading_time }} min read</span>
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

      <!-- Пагинация -->
      <div
        v-if="pagination.last_page > 1"
        class="flex justify-center items-center gap-4 pt-6"
      >
        <button
          @click="fetchPosts(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="px-4 py-2 text-xs font-black uppercase tracking-wider border-[3px] border-black dark:border-white bg-white dark:bg-[#1A1A1A] disabled:opacity-30 disabled:cursor-not-allowed hover:bg-[#FFD700] hover:text-black transition-colors"
        >
          ← Prev
        </button>
        <span class="text-sm font-black uppercase tracking-wider">
          {{ pagination.current_page }} / {{ pagination.last_page }}
        </span>
        <button
          @click="fetchPosts(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-4 py-2 text-xs font-black uppercase tracking-wider border-[3px] border-black dark:border-white bg-white dark:bg-[#1A1A1A] disabled:opacity-30 disabled:cursor-not-allowed hover:bg-[#FFD700] hover:text-black transition-colors"
        >
          Next →
        </button>
      </div>
    </div>
  </AppLayout>
</template>
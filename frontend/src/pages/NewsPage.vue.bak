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
      <h1 class="text-3xl font-black uppercase tracking-wider">Новости</h1>
      <p class="mt-2 text-sm opacity-70">Всего: {{ pagination.total }}</p>
    </div>

    <!-- Фильтр по тегам -->
    <div class="mb-6 flex flex-wrap gap-2">
      <button
        @click="activeTag = ''; fetchPosts(1)"
        :class="[
          'px-3 py-1.5 text-xs font-bold uppercase tracking-wide border-2 transition-all',
          !activeTag
            ? 'bg-black dark:bg-white text-white dark:text-[#1a0b2e] border-black dark:border-white'
            : 'border-black dark:border-white hover:shadow-brutal-sm',
        ]"
      >
        Все
      </button>
      <button
        v-for="tag in tags"
        :key="tag.code"
        @click="selectTag(tag.code)"
        :class="[
          'px-3 py-1.5 text-xs font-bold uppercase tracking-wide border-2 transition-all',
          activeTag === tag.code
            ? 'text-white border-black dark:border-white'
            : 'border-black dark:border-white hover:shadow-brutal-sm',
        ]"
        :style="activeTag === tag.code ? { backgroundColor: tag.color } : {}"
      >
        {{ tag.name }}
      </button>
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

    <!-- Empty -->
    <div
      v-else-if="!hasPosts"
      class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-12 text-center"
    >
      <p class="text-xl font-bold mb-2">Новостей пока нет</p>
      <p class="text-sm opacity-70">Скоро появятся</p>
    </div>

    <!-- Список постов -->
    <div v-else class="space-y-4">
      <RouterLink
        v-for="(post, index) in posts"
        :key="post.id"
        :to="{ name: 'news-post', params: { slug: post.slug } }"
        class="animate-list-item block bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-6 hover:shadow-brutal-hover transition-all"
        :style="{ animationDelay: `${Math.min(index, 10) * 30}ms` }"
      >
        <!-- Теги -->
        <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mb-3">
          <span
            v-for="tag in post.tags"
            :key="tag.code"
            class="inline-block px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-white border-2 border-black dark:border-white"
            :style="{ backgroundColor: tag.color }"
          >
            {{ tag.name }}
          </span>
        </div>

        <!-- Заголовок -->
        <h2 class="text-xl font-black mb-2 hover:opacity-80 transition">
          {{ post.title }}
        </h2>

        <!-- Excerpt -->
        <p class="text-sm opacity-70 mb-4 leading-relaxed">
          {{ post.excerpt }}
        </p>

        <!-- Мета -->
        <div class="flex justify-between items-center text-xs opacity-60">
          <span>{{ post.author?.name || 'Аноним' }}</span>
          <span>{{ formatDate(post.published_at) }} · {{ post.reading_time }} мин</span>
        </div>
      </RouterLink>

      <!-- Пагинация -->
      <div
        v-if="pagination.last_page > 1"
        class="flex justify-center items-center gap-4 pt-4"
      >
        <button
          @click="fetchPosts(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="px-3 py-1.5 text-xs font-bold uppercase border-2 border-black dark:border-white disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-brutal-sm transition-all"
        >
          ← Назад
        </button>
        <span class="text-sm font-bold">
          {{ pagination.current_page }} / {{ pagination.last_page }}
        </span>
        <button
          @click="fetchPosts(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-3 py-1.5 text-xs font-bold uppercase border-2 border-black dark:border-white disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-brutal-sm transition-all"
        >
          Вперёд →
        </button>
      </div>
    </div>
  </AppLayout>
</template>
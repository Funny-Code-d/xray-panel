<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/api/axios'
import { formatDate } from '@/utils/format'

const route = useRoute()
const router = useRouter()

const post = ref(null)
const loading = ref(true)
const error = ref(null)

async function fetchPost() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/posts/${route.params.slug}`)
    post.value = data
  } catch (e) {
    if (e.response?.status === 404) {
      error.value = 'Пост не найден'
    } else {
      error.value = 'Не удалось загрузить пост'
    }
  } finally {
    loading.value = false
  }
}

onMounted(fetchPost)
</script>

<template>
  <AppLayout>
    <!-- Назад -->
    <button
      @click="router.push({ name: 'news' })"
      class="mb-6 text-sm font-bold uppercase tracking-wide opacity-70 hover:opacity-100 flex items-center gap-2"
    >
      ← Все новости
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
    </div>

    <!-- Пост -->
    <article
      v-else-if="post"
      class="max-w-3xl mx-auto"
    >
      <!-- Теги -->
      <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mb-4">
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
      <h1 class="text-4xl font-black mb-4 leading-tight">
        {{ post.title }}
      </h1>

      <!-- Мета -->
      <div class="flex gap-4 text-sm opacity-60 mb-8 pb-6 border-b-2 border-black/10 dark:border-white/10">
        <span>{{ post.author?.name || 'Аноним' }}</span>
        <span>{{ formatDate(post.published_at) }}</span>
        <span>{{ post.reading_time }} мин чтения</span>
      </div>

      <!-- Контент -->
      <div class="prose prose-lg max-w-none dark:prose-invert
            prose-headings:font-black prose-headings:uppercase prose-headings:tracking-wider
            prose-h2:border-b-2 prose-h2:border-black dark:prose-h2:border-white prose-h2:pb-2
            prose-a:text-blue-600 dark:prose-a:text-orange-500 prose-a:font-bold
            prose-code:bg-black/5 dark:prose-code:bg-white/10 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded
            prose-strong:font-black">
            <div v-html="post.content"></div>
        </div>
    </article>
  </AppLayout>
</template>
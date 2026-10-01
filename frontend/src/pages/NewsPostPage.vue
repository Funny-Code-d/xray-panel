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
    <RouterLink
      :to="{ name: 'news' }"
      class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-wider underline hover:no-underline mb-6"
    >
      ← All news
    </RouterLink>

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

    <!-- Пост -->
    <article
      v-else-if="post"
      class="max-w-4xl mx-auto"
    >
      <div class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal-lg overflow-hidden">
        <!-- Жёлтая акцентная полоса -->
        <div class="h-3 bg-[#FFD700] border-b-[3px] border-black dark:border-white"></div>

        <!-- Шапка -->
        <header class="p-4 sm:p-8 border-b-[3px] border-black dark:border-white">
          <!-- Теги -->
          <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mb-4">
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
          <h1 class="text-2xl sm:text-5xl font-black uppercase tracking-wider leading-tight mb-4 break-words">
            {{ post.title }}
          </h1>

          <!-- Мета -->
          <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-[11px] sm:text-xs opacity-60 uppercase tracking-wider font-bold">
            <span>{{ post.author?.name || 'VPN Admin' }}</span>
            <span>·</span>
            <span>{{ formatDate(post.published_at) }}</span>
            <span class="hidden sm:inline">·</span>
            <span class="hidden sm:inline">{{ post.reading_time }} min read</span>
          </div>
        </header>

        <!-- Тело статьи -->
        <div class="p-4 sm:p-8">
          <div class="post-content" v-html="post.content"></div>
        </div>
      </div>
    </article>
  </AppLayout>
</template>
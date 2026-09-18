<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import IconButton from '@/components/ui/IconButton.vue'
import PostEditorModal from '@/components/admin/PostEditorModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import api from '@/api/axios'
import { formatDate } from '@/utils/format'

const posts = ref([])
const loading = ref(true)
const error = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const filters = ref({ status: '', search: '' })

const showEditorModal = ref(false)
const editingPost = ref(null)
const showDeleteModal = ref(false)
const postToDelete = ref(null)
const deleting = ref(false)

async function fetchPosts(page = 1) {
  loading.value = true
  error.value = null

  try {
    const params = { page }
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.search) params.search = filters.value.search

    const { data } = await api.get('/admin/posts', { params })
    posts.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    }
  } catch (e) {
    error.value = 'Не удалось загрузить посты'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchPosts())

function openCreate() {
  editingPost.value = null
  showEditorModal.value = true
}

function openEdit(post) {
  editingPost.value = post
  showEditorModal.value = true
}

function onSaved() {
  fetchPosts(pagination.value.current_page)
}

function confirmDelete(post) {
  postToDelete.value = post
  showDeleteModal.value = true
}

async function handleDelete() {
  if (!postToDelete.value) return
  deleting.value = true
  try {
    await api.delete(`/admin/posts/${postToDelete.value.id}`)
    posts.value = posts.value.filter(p => p.id !== postToDelete.value.id)
    pagination.value.total--
    showDeleteModal.value = false
    postToDelete.value = null
  } catch (e) {
    alert('Не удалось удалить пост')
  } finally {
    deleting.value = false
  }
}

function applyFilters() {
  fetchPosts(1)
}

function resetFilters() {
  filters.value = { status: '', search: '' }
  fetchPosts(1)
}

const hasPosts = computed(() => posts.value.length > 0)
</script>

<template>
  <AppLayout>
    <AdminLayout>
      <!-- Заголовок -->
      <div class="flex justify-between items-end mb-6 flex-wrap gap-4">
        <div>
          <h2 class="text-xl font-black uppercase tracking-wider">Посты</h2>
          <p class="text-sm opacity-70 mt-1">Всего: {{ pagination.total }}</p>
        </div>
        <Button variant="primary" @click="openCreate">
          + Создать пост
        </Button>
      </div>

      <!-- Фильтры -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-4 mb-6">
        <div class="flex gap-3 flex-wrap">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Поиск по заголовку"
            class="flex-1 min-w-[200px] px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all"
            @keyup.enter="applyFilters"
          />

          <select
            v-model="filters.status"
            class="px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all"
          >
            <option value="">Все статусы</option>
            <option value="published">Опубликованные</option>
            <option value="draft">Черновики</option>
          </select>

          <Button variant="primary" size="sm" @click="applyFilters">Найти</Button>
          <Button variant="ghost" size="sm" @click="resetFilters">Сбросить</Button>
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
      </div>

      <!-- Empty -->
      <div
        v-else-if="!hasPosts"
        class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-12 text-center"
      >
        <p class="text-xl font-bold mb-2">Постов нет</p>
        <p class="text-sm opacity-70 mb-6">Создайте первый пост</p>
        <Button variant="primary" @click="openCreate">+ Создать пост</Button>
      </div>

      <!-- Список -->
      <div v-else class="space-y-4">
        <div
          v-for="post in posts"
          :key="post.id"
          class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5"
        >
          <div class="flex justify-between items-start gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
              <!-- Статус + Теги -->
              <div class="flex flex-wrap gap-2 mb-2">
                <span
                  :class="[
                    'inline-block px-2 py-0.5 text-xs font-bold uppercase border-2',
                    post.is_published
                      ? 'bg-green-100 text-green-700 border-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-400'
                      : 'bg-amber-100 text-amber-700 border-amber-700 dark:bg-amber-900 dark:text-amber-200 dark:border-amber-400',
                  ]"
                >
                  {{ post.is_published ? 'Опубликован' : 'Черновик' }}
                </span>

                <span
                  v-for="tag in post.tags"
                  :key="tag.code"
                  class="inline-block px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-white border-2 border-black dark:border-white"
                  :style="{ backgroundColor: tag.color }"
                >
                  {{ tag.name }}
                </span>
              </div>

              <h3 class="font-bold text-lg mb-1">{{ post.title }}</h3>
              <p class="text-xs font-mono opacity-60 mb-2">{{ post.slug }}</p>

              <div class="text-xs opacity-60">
                {{ formatDate(post.published_at || post.created_at) }}
                <template v-if="post.user"> · {{ post.user.full_name }}</template>
              </div>
            </div>

            <!-- Действия -->
            <div class="flex gap-2 shrink-0">
              <IconButton title="Редактировать" @click="openEdit(post)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </IconButton>

              <IconButton variant="danger" title="Удалить" @click="confirmDelete(post)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </IconButton>
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
            @click="fetchPosts(pagination.current_page - 1)"
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
            @click="fetchPosts(pagination.current_page + 1)"
          >
            Вперёд →
          </Button>
        </div>
      </div>

      <!-- Модалки -->
      <PostEditorModal
        v-model="showEditorModal"
        :post="editingPost"
        @saved="onSaved"
      />

      <ConfirmModal
        v-model="showDeleteModal"
        title="Удалить пост?"
        :message="`Пост «${postToDelete?.title}» будет удалён безвозвратно. Продолжить?`"
        confirm-text="Удалить"
        :loading="deleting"
        @confirm="handleDelete"
      />
    </AdminLayout>
  </AppLayout>
</template>
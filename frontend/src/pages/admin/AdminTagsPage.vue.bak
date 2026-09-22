<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import IconButton from '@/components/ui/IconButton.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import api from '@/api/axios'

const tags = ref([])
const loading = ref(true)
const error = ref(null)

// Модалка создания/редактирования
const showEditorModal = ref(false)
const editingTag = ref(null)
const saving = ref(false)
const errors = ref({})

const form = ref({
  code: '',
  name: '',
  description: '',
  color: '#3b82f6',
})

// Модалка удаления
const showDeleteModal = ref(false)
const tagToDelete = ref(null)
const deleting = ref(false)

async function fetchTags() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/admin/tags')
    tags.value = data
  } catch (e) {
    error.value = 'Не удалось загрузить теги'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchTags)

function openCreate() {
  editingTag.value = null
  form.value = {
    code: '',
    name: '',
    description: '',
    color: '#3b82f6',
  }
  errors.value = {}
  showEditorModal.value = true
}

function openEdit(tag) {
  editingTag.value = tag
  form.value = {
    code: tag.code,
    name: tag.name,
    description: tag.description || '',
    color: tag.color,
  }
  errors.value = {}
  showEditorModal.value = true
}

async function handleSave() {
  saving.value = true
  errors.value = {}

  const payload = {
    code: form.value.code,
    name: form.value.name,
    description: form.value.description || null,
    color: form.value.color,
  }

  try {
    if (editingTag.value) {
      await api.patch(`/admin/tags/${editingTag.value.id}`, payload)
    } else {
      await api.post('/admin/tags', payload)
    }
    showEditorModal.value = false
    await fetchTags()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      errors.value = { name: ['Что-то пошло не так'] }
    }
  } finally {
    saving.value = false
  }
}

function confirmDelete(tag) {
  tagToDelete.value = tag
  showDeleteModal.value = true
}

async function handleDelete() {
  if (!tagToDelete.value) return

  deleting.value = true
  try {
    await api.delete(`/admin/tags/${tagToDelete.value.id}`)
    tags.value = tags.value.filter(t => t.id !== tagToDelete.value.id)
    showDeleteModal.value = false
    tagToDelete.value = null
  } catch (e) {
    alert('Не удалось удалить тег')
  } finally {
    deleting.value = false
  }
}

const hasTags = computed(() => tags.value.length > 0)
</script>

<template>
  <AppLayout>
    <AdminLayout>
      <!-- Заголовок -->
      <div class="flex justify-between items-end mb-6 flex-wrap gap-4">
        <div>
          <h2 class="text-xl font-black uppercase tracking-wider">Теги</h2>
          <p class="text-sm opacity-70 mt-1">Всего: {{ tags.length }}</p>
        </div>
        <Button variant="primary" @click="openCreate">
          + Создать тег
        </Button>
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
        v-else-if="!hasTags"
        class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-12 text-center"
      >
        <p class="text-xl font-bold mb-2">Тегов нет</p>
        <p class="text-sm opacity-70 mb-6">Создайте первый тег</p>
        <Button variant="primary" @click="openCreate">+ Создать тег</Button>
      </div>

      <!-- Список тегов -->
      <div v-else class="space-y-4">
        <div
          v-for="tag in tags"
          :key="tag.id"
          class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5"
        >
          <div class="flex justify-between items-start gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
              <!-- Имя + цвет -->
              <div class="flex items-center gap-3 mb-2">
                <span
                  class="inline-block w-5 h-5 border-2 border-black dark:border-white shrink-0"
                  :style="{ backgroundColor: tag.color }"
                ></span>
                <h3 class="font-bold text-lg">{{ tag.name }}</h3>
              </div>

              <!-- Code -->
              <p class="text-xs font-mono opacity-60 mb-2">{{ tag.code }}</p>

              <!-- Description -->
              <p v-if="tag.description" class="text-sm opacity-70 mb-2">
                {{ tag.description }}
              </p>

              <!-- Счётчики -->
              <div class="flex gap-4 text-xs opacity-60">
                <span>{{ tag.posts_count ?? 0 }} постов</span>
                <span>{{ tag.subscriptions_count ?? 0 }} подписчиков</span>
              </div>
            </div>

            <!-- Действия -->
            <div class="flex gap-2 shrink-0">
              <IconButton title="Редактировать" @click="openEdit(tag)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </IconButton>

              <IconButton variant="danger" title="Удалить" @click="confirmDelete(tag)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </IconButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Модалка редактирования -->
      <Modal
        v-model="showEditorModal"
        :title="editingTag ? 'Редактировать тег' : 'Новый тег'"
      >
        <form @submit.prevent="handleSave" class="space-y-4">
          <Input
            v-model="form.code"
            label="Код (латиница)"
            placeholder="updates"
            :error="errors.code?.[0]"
          />
          <p class="-mt-3 text-xs opacity-50">
            Только латиница, цифры, дефис и подчёркивание. Используется в коде.
          </p>

          <Input
            v-model="form.name"
            label="Название"
            placeholder="Обновления"
            :error="errors.name?.[0]"
          />

          <Input
            v-model="form.description"
            label="Описание"
            placeholder="Новые возможности и улучшения"
            :error="errors.description?.[0]"
          />

          <!-- Цвет -->
          <div>
            <label class="block text-sm font-bold uppercase tracking-wide mb-2">
              Цвет
            </label>
            <div class="flex gap-3 items-center">
              <input
                v-model="form.color"
                type="color"
                class="w-12 h-12 border-2 border-black dark:border-white cursor-pointer bg-transparent"
              />
              <input
                v-model="form.color"
                type="text"
                placeholder="#3b82f6"
                class="flex-1 px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all font-mono"
                :class="errors.color ? 'border-red-500' : ''"
              />
            </div>
            <p v-if="errors.color" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.color[0] }}
            </p>
          </div>

          <!-- Предпросмотр -->
          <div>
            <label class="block text-sm font-bold uppercase tracking-wide mb-2">
              Предпросмотр
            </label>
            <span
              class="inline-block px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-white border-2 border-black dark:border-white"
              :style="{ backgroundColor: form.color }"
            >
              {{ form.name || 'Название' }}
            </span>
          </div>

          <!-- Кнопки -->
          <div class="flex gap-3 pt-2">
            <Button
              type="button"
              variant="secondary"
              class="flex-1"
              @click="showEditorModal = false"
            >
              Отмена
            </Button>
            <Button type="submit" variant="primary" :loading="saving" class="flex-1">
              {{ editingTag ? 'Сохранить' : 'Создать' }}
            </Button>
          </div>
        </form>
      </Modal>

      <!-- Модалка удаления -->
      <ConfirmModal
        v-model="showDeleteModal"
        title="Удалить тег?"
        :message="`Тег «${tagToDelete?.name}» будет удалён. Посты и подписки, связанные с ним, потеряют этот тег. Продолжить?`"
        confirm-text="Удалить"
        :loading="deleting"
        @confirm="handleDelete"
      />
    </AdminLayout>
  </AppLayout>
</template>
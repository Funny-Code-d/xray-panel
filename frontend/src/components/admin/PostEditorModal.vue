<script setup>
import { ref, watch, onMounted } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import api from '@/api/axios'

const props = defineProps({
  modelValue: Boolean,
  post: Object,
})
const emit = defineEmits(['update:modelValue', 'saved'])

const form = ref({
  title: '',
  excerpt: '',
  content: '',
  is_published: false,
  tags: [],
})

const allTags = ref([])
const loading = ref(false)
const errors = ref({})

async function fetchTags() {
  try {
    const { data } = await api.get('/tags')
    allTags.value = data
  } catch (e) {
    console.error(e)
  }
}

onMounted(fetchTags)

watch(() => props.modelValue, (open) => {
  if (!open) return

  errors.value = {}

  if (props.post) {
    form.value = {
      title: props.post.title,
      excerpt: props.post.excerpt || '',
      content: props.post.content,
      is_published: props.post.is_published,
      tags: props.post.tags?.map(t => t.id) || [],
    }
  } else {
    form.value = {
      title: '',
      excerpt: '',
      content: '',
      is_published: false,
      tags: [],
    }
  }
})

function toggleTag(tagId) {
  const index = form.value.tags.indexOf(tagId)
  if (index === -1) {
    form.value.tags.push(tagId)
  } else {
    form.value.tags.splice(index, 1)
  }
}

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  const payload = {
    title: form.value.title,
    excerpt: form.value.excerpt || null,
    content: form.value.content,
    is_published: form.value.is_published,
    tags: form.value.tags,
  }

  try {
    if (props.post) {
      await api.patch(`/admin/posts/${props.post.id}`, payload)
    } else {
      await api.post('/admin/posts', payload)
    }
    emit('saved')
    emit('update:modelValue', false)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      errors.value = { title: ['Что-то пошло не так'] }
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :title="post ? 'Редактировать пост' : 'Новый пост'"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <Input
        v-model="form.title"
        label="Заголовок"
        :error="errors.title?.[0]"
      />

      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Краткое описание
        </label>
        <textarea
          v-model="form.excerpt"
          rows="2"
          placeholder="Опционально. Если пусто — обрежется из контента."
          class="w-full px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all resize-none"
        ></textarea>
      </div>

      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Контент (HTML)
        </label>
        <textarea
          v-model="form.content"
          rows="12"
          placeholder="<p>Текст поста...</p>"
          class="w-full px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all resize-y font-mono text-sm"
          :class="errors.content ? 'border-red-500' : ''"
        ></textarea>
        <p v-if="errors.content" class="mt-1 text-sm text-red-600 dark:text-red-400">
          {{ errors.content[0] }}
        </p>
        <p class="mt-1 text-xs opacity-50">
          Можно использовать HTML: заголовки, списки, ссылки, картинки.
        </p>
      </div>

      <!-- Теги -->
      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Теги
        </label>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="tag in allTags"
            :key="tag.id"
            type="button"
            @click="toggleTag(tag.id)"
            :class="[
              'px-3 py-1.5 text-xs font-bold uppercase tracking-wide border-2 transition-all',
              form.tags.includes(tag.id)
                ? 'text-white border-black dark:border-white shadow-brutal-sm'
                : 'border-black dark:border-white hover:shadow-brutal-sm',
            ]"
            :style="form.tags.includes(tag.id) ? { backgroundColor: tag.color } : {}"
          >
            {{ tag.name }}
          </button>
        </div>
      </div>

      <!-- Публикация -->
      <label class="flex items-center gap-3 cursor-pointer">
        <input
          type="checkbox"
          v-model="form.is_published"
          class="w-4 h-4 accent-blue-600 dark:accent-orange-500"
        />
        <span class="text-sm font-bold uppercase tracking-wide">
          Опубликовать сразу
        </span>
      </label>

      <!-- Кнопки -->
      <div class="flex gap-3 pt-2">
        <Button
          type="button"
          variant="secondary"
          class="flex-1"
          @click="$emit('update:modelValue', false)"
        >
          Отмена
        </Button>
        <Button type="submit" variant="primary" :loading="loading" class="flex-1">
          {{ post ? 'Сохранить' : 'Создать' }}
        </Button>
      </div>
    </form>
  </Modal>
</template>
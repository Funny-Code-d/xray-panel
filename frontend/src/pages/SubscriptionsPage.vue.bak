<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const tags = ref([])
const subscriptions = ref([])
const loading = ref(true)
const saving = ref(false)
const error = ref(null)
const saved = ref(false)

// Локальное состояние: map "tagId:channel" -> boolean
const state = ref({})

const channels = [
  { code: 'email', label: 'Email' },
  { code: 'telegram', label: 'Telegram' },
]

const hasTelegram = computed(() => !!auth.user?.telegram_chat_id)

async function fetchData() {
  loading.value = true
  error.value = null

  try {
    const [tagsRes, subsRes] = await Promise.all([
      api.get('/tags'),
      api.get('/subscriptions'),
    ])

    tags.value = tagsRes.data
    subscriptions.value = subsRes.data

    const newState = {}

    // "Все посты" — tag_id = null
    for (const channel of channels) {
      const sub = subsRes.data.find(
        s => s.tag === null && s.channel === channel.code
      )
      newState[`null:${channel.code}`] = sub?.is_active ?? false
    }

    // По каждому тегу
    for (const tag of tagsRes.data) {
      for (const channel of channels) {
        const sub = subsRes.data.find(
          s => s.tag?.id === tag.id && s.channel === channel.code
        )
        newState[`${tag.id}:${channel.code}`] = sub?.is_active ?? false
      }
    }

    state.value = newState
  } catch (e) {
    error.value = 'Не удалось загрузить подписки'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

/**
 * Переключить подписку на "Все новости".
 * Если включаем — снимаем все теги.
 * Если выключаем — ничего не трогаем.
 */
function toggleAllNews(channel) {
  const key = `null:${channel}`
  const newValue = !state.value[key]

  state.value[key] = newValue

  // Если включаем "Все новости" — снимаем все теги
  if (newValue) {
    for (const tag of tags.value) {
      state.value[`${tag.id}:${channel}`] = false
    }
  }

  saved.value = false
}

/**
 * Переключить подписку на конкретный тег.
 * Если включаем — снимаем "Все новости".
 */
function toggleTag(tagId, channel) {
  const key = `${tagId}:${channel}`
  const newValue = !state.value[key]

  state.value[key] = newValue

  // Если включаем тег — снимаем "Все новости"
  if (newValue) {
    state.value[`null:${channel}`] = false
  }

  saved.value = false
}

/**
 * Отписаться от всего (для конкретного канала).
 */
function unsubscribeAll(channel) {
  state.value[`null:${channel}`] = false
  for (const tag of tags.value) {
    state.value[`${tag.id}:${channel}`] = false
  }
  saved.value = false
}

async function save() {
  saving.value = true
  saved.value = false

  const subscriptionsToSave = []

  for (const [key, isActive] of Object.entries(state.value)) {
    if (!isActive) continue

    const [tagId, channel] = key.split(':')
    subscriptionsToSave.push({
      tag_id: tagId === 'null' ? null : parseInt(tagId),
      channel,
      is_active: true,
    })
  }

  try {
    await api.post('/subscriptions/bulk', {
      subscriptions: subscriptionsToSave,
    })

    saved.value = true
    setTimeout(() => { saved.value = false }, 3000)
  } catch (e) {
    alert('Не удалось сохранить подписки')
    console.error(e)
  } finally {
    saving.value = false
  }
}

const activeCount = computed(() => {
  return Object.values(state.value).filter(v => v).length
})

/**
 * Есть ли активные подписки хотя бы на одном канале.
 */
const hasAnySubscriptions = computed(() => activeCount.value > 0)
</script>

<template>
  <AppLayout>
    <!-- Заголовок -->
    <div class="mb-8">
      <h1 class="text-3xl font-black uppercase tracking-wider">Подписки</h1>
      <p class="mt-2 text-sm opacity-70">
        Выберите, на что вы хотите получать уведомления
      </p>
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
      <Button variant="secondary" class="mt-4" @click="fetchData">
        Попробовать снова
      </Button>
    </div>

    <!-- Контент -->
    <div v-else class="space-y-6 max-w-3xl">
      <!-- Каналы -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-4">
        <p class="text-xs font-bold uppercase tracking-wider opacity-60 mb-2">
          Каналы доставки
        </p>
        <div class="flex flex-wrap gap-2 text-sm">
          <span class="px-3 py-1 border-2 border-green-600 text-green-700 dark:text-green-400 dark:border-green-400 font-bold uppercase text-xs">
            ✓ Email
          </span>
          <span
            v-if="hasTelegram"
            class="px-3 py-1 border-2 border-green-600 text-green-700 dark:text-green-400 dark:border-green-400 font-bold uppercase text-xs"
          >
            ✓ Telegram
          </span>
          <span
            v-else
            class="px-3 py-1 border-2 border-slate-400 text-slate-500 dark:border-slate-600 dark:text-slate-500 font-bold uppercase text-xs"
            title="Привяжите Telegram в настройках профиля"
          >
            Telegram (не подключён)
          </span>
        </div>
      </div>

      <!-- Нет подписок -->
      <div
        v-if="!hasAnySubscriptions"
        class="bg-amber-100 dark:bg-amber-900 border-2 border-amber-700 dark:border-amber-400 shadow-brutal p-4"
      >
        <p class="text-sm font-bold">
          Вы не подписаны ни на что. Уведомления приходить не будут.
        </p>
      </div>

      <!-- Все новости -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
        <div class="flex justify-between items-center gap-4 flex-wrap">
          <div class="min-w-0">
            <h2 class="text-lg font-black uppercase tracking-wider mb-1">
              Все новости
            </h2>
            <p class="text-xs opacity-70">
              Получать абсолютно всё, что публикуется
            </p>
          </div>

          <div class="flex gap-2 shrink-0">
            <button
              v-for="channel in channels"
              :key="`null-${channel.code}`"
              @click="toggleAllNews(channel.code)"
              :disabled="channel.code === 'telegram' && !hasTelegram"
              :class="[
                'px-3 py-1.5 text-xs font-bold uppercase tracking-wide border-2 transition-all',
                state[`null:${channel.code}`]
                  ? 'bg-blue-600 dark:bg-orange-500 text-white border-black dark:border-white'
                  : 'border-black dark:border-white hover:shadow-brutal-sm',
                channel.code === 'telegram' && !hasTelegram
                  ? 'opacity-40 cursor-not-allowed'
                  : '',
              ]"
            >
              {{ channel.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- По тегам -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
        <h2 class="text-lg font-black uppercase tracking-wider mb-1">
          По темам
        </h2>
        <p class="text-xs opacity-70 mb-4">
          Только выбранные темы. Если включить тег — «Все новости» отключается.
        </p>

        <div class="space-y-3">
          <div
            v-for="tag in tags"
            :key="tag.id"
            class="flex justify-between items-center gap-4 flex-wrap pb-3 border-b-2 border-black/10 dark:border-white/10 last:border-0 last:pb-0"
          >
            <div class="min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span
                  class="inline-block w-3 h-3 border-2 border-black dark:border-white"
                  :style="{ backgroundColor: tag.color }"
                ></span>
                <h3 class="font-bold">{{ tag.name }}</h3>
              </div>
              <p v-if="tag.description" class="text-xs opacity-60">
                {{ tag.description }}
              </p>
            </div>

            <div class="flex gap-2 shrink-0">
              <button
                v-for="channel in channels"
                :key="`${tag.id}-${channel.code}`"
                @click="toggleTag(tag.id, channel.code)"
                :disabled="channel.code === 'telegram' && !hasTelegram"
                :class="[
                  'px-3 py-1.5 text-xs font-bold uppercase tracking-wide border-2 transition-all',
                  state[`${tag.id}:${channel.code}`]
                    ? 'bg-blue-600 dark:bg-orange-500 text-white border-black dark:border-white'
                    : 'border-black dark:border-white hover:shadow-brutal-sm',
                  channel.code === 'telegram' && !hasTelegram
                    ? 'opacity-40 cursor-not-allowed'
                    : '',
                ]"
              >
                {{ channel.label }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Действия -->
      <div class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-5">
        <div class="flex justify-between items-center gap-4 flex-wrap">
          <div>
            <p class="text-sm font-bold mb-1">Массовые действия</p>
            <p class="text-xs opacity-60">Отписаться от всего сразу</p>
          </div>

          <div class="flex gap-2 flex-wrap">
            <button
              v-for="channel in channels"
              :key="`unsub-${channel.code}`"
              @click="unsubscribeAll(channel.code)"
              :disabled="channel.code === 'telegram' && !hasTelegram"
              class="px-3 py-1.5 text-xs font-bold uppercase tracking-wide border-2 border-red-600 text-red-600 dark:border-red-400 dark:text-red-400 hover:shadow-brutal-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed"
            >
              Отписаться от {{ channel.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Кнопка сохранения -->
      <div class="flex items-center gap-4 flex-wrap">
        <Button
          variant="primary"
          :loading="saving"
          @click="save"
        >
          Сохранить подписки
        </Button>

        <span class="text-xs opacity-60">
          Активных: {{ activeCount }}
        </span>

        <Transition name="fade-slide">
          <span
            v-if="saved"
            class="text-sm font-bold text-green-600 dark:text-green-400"
          >
            ✓ Сохранено
          </span>
        </Transition>
      </div>
    </div>
  </AppLayout>
</template>
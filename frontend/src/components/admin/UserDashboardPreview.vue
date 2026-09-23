<script setup>
import { ref, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'
import api from '@/api/axios'
import { formatBytes, formatDate } from '@/utils/format'

const props = defineProps({
  modelValue: Boolean,
  userId: [Number, String],
})
const emit = defineEmits(['update:modelValue'])

const data = ref(null)
const loading = ref(false)
const error = ref(null)

watch(() => props.modelValue, async (open) => {
  if (!open || !props.userId) return

  loading.value = true
  error.value = null
  data.value = null

  try {
    const { data: res } = await api.get(`/admin/users/${props.userId}/dashboard`)
    data.value = res
  } catch (e) {
    error.value = 'Не удалось загрузить дашборд'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    title="Дашборд пользователя"
    size="xl"
  >
    <div v-if="loading" class="text-center py-12 opacity-70">
      Загрузка...
    </div>

    <div v-else-if="error" class="text-center py-8">
      <p class="text-red-600 dark:text-red-400 font-bold">{{ error }}</p>
    </div>

    <div v-else-if="data" class="space-y-4">
      <!-- Инфо о пользователе -->
      <div class="p-3 bg-amber-100 dark:bg-amber-900 border-2 border-amber-700 dark:border-amber-400">
        <p class="text-xs font-bold uppercase tracking-wider mb-1">
          ⚠️ Режим просмотра
        </p>
        <p class="text-sm">
          Вы смотрите дашборд от имени <b>{{ data.user.full_name }}</b>.
          Это только чтение — действия недоступны.
        </p>
      </div>

      <!-- Те же карточки, что в UserDashboard -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Трафик -->
        <div class="bg-white dark:bg-[#2a1548] border-[3px] border-black dark:border-white shadow-brutal p-5">
          <div class="flex justify-between items-baseline mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider opacity-60">Трафик</h2>
            <span v-if="data.traffic.limit" class="text-xs font-bold opacity-60">
              {{ data.traffic.percent }}%
            </span>
          </div>

          <div class="flex items-baseline gap-2 mb-3">
            <span class="text-2xl font-black">{{ formatBytes(data.traffic.used) }}</span>
            <span class="text-xs opacity-60">
              из {{ data.traffic.limit ? formatBytes(data.traffic.limit) : 'Безлимит' }}
            </span>
          </div>

          <div v-if="data.traffic.limit" class="w-full border-2 border-black dark:border-white h-3">
            <div
              class="h-full transition-all"
              :class="data.traffic.percent > 90 ? 'bg-red-500' : data.traffic.percent > 70 ? 'bg-amber-500' : 'bg-[#FFD700]'"
              :style="{ width: `${data.traffic.percent}%` }"
            ></div>
          </div>
        </div>

        <!-- Ключи -->
        <div class="bg-white dark:bg-[#2a1548] border-[3px] border-black dark:border-white shadow-brutal p-5">
          <h2 class="text-xs font-bold uppercase tracking-wider opacity-60 mb-3">
            Ключи доступа
          </h2>
          <div class="flex items-baseline gap-2 mb-3">
            <span class="text-2xl font-black">{{ data.clients_count }}</span>
            <span class="text-xs opacity-60">
              {{ data.clients_count === 1 ? 'активный' : 'активных' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Последние ключи -->
      <div v-if="data.recent_clients.length" class="bg-white dark:bg-[#2a1548] border-[3px] border-black dark:border-white shadow-brutal p-5">
        <h2 class="text-xs font-bold uppercase tracking-wider opacity-60 mb-3">
          Последние ключи
        </h2>
        <div class="space-y-2">
          <div
            v-for="client in data.recent_clients"
            :key="client.id"
            class="flex justify-between items-center p-3 border-2 border-black/20 dark:border-white/20"
          >
            <div>
              <p class="font-bold text-sm">{{ client.name }}</p>
              <p class="text-xs opacity-60">{{ formatBytes(client.traffic_used) }}</p>
            </div>
            <span
              :class="[
                'px-2 py-0.5 text-xs font-bold uppercase border-2',
                client.is_active
                  ? 'bg-green-100 text-green-700 border-green-700'
                  : 'bg-red-100 text-red-700 border-red-700',
              ]"
            >
              {{ client.is_active ? 'Активен' : 'Отключён' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Кнопка закрыть -->
      <div class="flex justify-end pt-2">
        <Button variant="secondary" @click="$emit('update:modelValue', false)">
          Закрыть
        </Button>
      </div>
    </div>
  </Modal>
</template>
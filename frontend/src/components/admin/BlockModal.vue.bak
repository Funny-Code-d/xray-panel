<script setup>
import { ref, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps({
  modelValue: Boolean,
  userName: String,
  loading: Boolean,
})
const emit = defineEmits(['update:modelValue', 'confirm'])

const reason = ref('')

watch(() => props.modelValue, (open) => {
  if (open) reason.value = ''
})

function handleConfirm() {
  emit('confirm', reason.value || null)
}
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    title="Заблокировать пользователя"
  >
    <div class="space-y-4">
      <p class="opacity-80">
        Заблокировать <b>{{ userName }}</b>?
      </p>

      <div class="p-3 bg-red-100 dark:bg-red-900 border-2 border-red-700 dark:border-red-400 text-sm">
        <p class="font-bold mb-1">Что произойдёт:</p>
        <ul class="list-disc list-inside opacity-90 space-y-0.5">
          <li>Все VPN-ключи будут деактивированы</li>
          <li>Все активные сессии будут завершены</li>
          <li>Пользователь потеряет доступ к панели</li>
        </ul>
      </div>

      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Причина (опционально)
        </label>
        <textarea
          v-model="reason"
          rows="3"
          placeholder="Например: раздача ключей третьим лицам"
          class="w-full px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all resize-none"
        ></textarea>
      </div>
    </div>

    <div class="flex gap-3 mt-6">
      <Button
        variant="secondary"
        class="flex-1"
        @click="$emit('update:modelValue', false)"
      >
        Отмена
      </Button>
      <Button
        variant="danger"
        :loading="loading"
        class="flex-1"
        @click="handleConfirm"
      >
        Заблокировать
      </Button>
    </div>
  </Modal>
</template>
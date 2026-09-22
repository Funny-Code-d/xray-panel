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
    title="Отклонить заявку"
  >
    <p class="mb-4 opacity-80">
      Отклонить заявку от <b>{{ userName }}</b>?
      Пользователь не сможет создавать VPN-ключи.
    </p>

    <div class="mb-6">
      <label class="block text-sm font-bold uppercase tracking-wide mb-2">
        Причина (опционально)
      </label>
      <textarea
        v-model="reason"
        rows="3"
        placeholder="Например: не прошёл верификацию"
        class="w-full px-3 py-2 bg-white dark:bg-[#1a0b2e] text-black dark:text-white border-2 border-black dark:border-white focus:outline-none focus:shadow-brutal-sm transition-all resize-none"
      ></textarea>
    </div>

    <div class="flex gap-3">
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
        Отклонить
      </Button>
    </div>
  </Modal>
</template>
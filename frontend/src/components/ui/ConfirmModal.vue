<script setup>
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'

defineProps({
  modelValue: Boolean,
  title: { type: String, default: 'Подтверждение' },
  message: String,
  confirmText: { type: String, default: 'Удалить' },
  loading: Boolean,
})
const emit = defineEmits(['update:modelValue', 'confirm'])
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :title="title"
  >
    <p class="mb-6 opacity-80">{{ message }}</p>

    <div class="flex gap-3">
      <Button
        type="button"
        variant="secondary"
        class="flex-1"
        @click="$emit('update:modelValue', false)"
      >
        Отмена
      </Button>
      <Button
        type="button"
        variant="danger"
        :loading="loading"
        class="flex-1"
        @click="$emit('confirm')"
      >
        {{ confirmText }}
      </Button>
    </div>
  </Modal>
</template>
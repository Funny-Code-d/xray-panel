<script setup>
import { ref, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import api from '@/api/axios'

const props = defineProps({
  modelValue: Boolean,
  client: Object,
  servers: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'saved'])

const loading = ref(false)
const errors = ref({})

const form = ref({
  name: '',
  xray_server_id: null,
  expires_at: '',
})

watch(() => props.modelValue, (open) => {
  if (!open) return
  errors.value = {}

  if (props.client) {
    form.value = {
      name: props.client.name ?? '',
      xray_server_id: props.client.xray_server_id ?? null,
      expires_at: props.client.expires_at ?? '',
    }
  } else {
    form.value = {
      name: '',
      xray_server_id: props.servers[0]?.id ?? null,
      expires_at: '',
    }
  }
})

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  const payload = {
    name: form.value.name,
    xray_server_id: form.value.xray_server_id,
    expires_at: form.value.expires_at || null,
  }

  try {
    if (props.client) {
      await api.patch(`/clients/${props.client.id}`, payload)
    } else {
      await api.post('/clients', payload)
    }
    emit('saved')
    emit('update:modelValue', false)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
    } else {
      errors.value = { name: ['Что-то пошло не так'] }
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
    :title="client ? 'Редактировать ключ' : 'Новый ключ'"
    size="md"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <Input
        v-model="form.name"
        label="Название"
        placeholder="Мой iPhone"
        :error="errors.name?.[0]"
      />

      <div v-if="!client">
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Сервер
        </label>
        <select
          v-model.number="form.xray_server_id"
          class="w-full px-3 py-2 border-[3px] border-black dark:border-white bg-white dark:bg-[#1A1A1A] font-bold"
        >
          <option v-for="s in servers" :key="s.id" :value="s.id">
            {{ s.country_flag }} {{ s.name }} — {{ s.city }}
          </option>
        </select>
      </div>

      <Input
        v-model="form.expires_at"
        type="datetime-local"
        label="Срок действия (опционально)"
        :error="errors.expires_at?.[0]"
      />

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
          {{ client ? 'Сохранить' : 'Создать' }}
        </Button>
      </div>
    </form>
  </Modal>
</template>
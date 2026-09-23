<script setup>
import { ref, watch } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import api from '@/api/axios'

const props = defineProps({
  modelValue: Boolean,
  server: Object,
})
const emit = defineEmits(['update:modelValue', 'saved'])

const loading = ref(false)
const errors = ref({})

const form = ref({
  name: '',
  host: '',
  port: 443,
  api_host: '127.0.0.1',
  api_port: 10085,
  agent_port: 8080,
  protocol: 'vless',
  inbound_tag: 'vless-inbound',
  network: 'tcp',
  security: 'reality',
  flow: 'xtls-rprx-vision',
  reality_dest: 'dl.google.com:443',
  reality_server_names: ['dl.google.com'],
  reality_private_key: '',
  reality_public_key: '',
  reality_short_ids: ['0123456789abcdef'],
  fingerprint: 'chrome',
  country: '',
  country_name: '',
  city: '',
  is_active: true,
})

watch(() => props.modelValue, (open) => {
  if (!open) return
  errors.value = {}

  if (props.server) {
    form.value = { ...props.server }
  } else {
    form.value = {
      name: '',
      host: '',
      port: 443,
      api_host: '127.0.0.1',
      api_port: 10085,
      agent_port: 8080,
      protocol: 'vless',
      inbound_tag: 'vless-inbound',
      network: 'tcp',
      security: 'reality',
      flow: 'xtls-rprx-vision',
      reality_dest: 'dl.google.com:443',
      reality_server_names: ['dl.google.com'],
      reality_private_key: '',
      reality_public_key: '',
      reality_short_ids: ['0123456789abcdef'],
      fingerprint: 'chrome',
      country: '',
      country_name: '',
      city: '',
      is_active: true,
    }
  }
})

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  try {
    if (props.server) {
      await api.patch(`/admin/servers/${props.server.id}`, form.value)
    } else {
      await api.post('/admin/servers', form.value)
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
    :title="server ? 'Редактировать сервер' : 'Новый сервер'"
    size="xl"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Основное -->
      <div class="grid grid-cols-2 gap-3">
        <Input v-model="form.name" label="Название" placeholder="Германия #1" :error="errors.name?.[0]" />
        <Input v-model="form.host" label="IP / домен" placeholder="82.40.38.102" :error="errors.host?.[0]" />
      </div>

      <div class="grid grid-cols-3 gap-3">
        <Input v-model.number="form.port" type="number" label="Порт Xray" />
        <Input v-model.number="form.api_port" type="number" label="Порт API" />
        <Input v-model.number="form.agent_port" type="number" label="Порт агента" />
      </div>

      <!-- Локация -->
      <div class="grid grid-cols-3 gap-3">
        <Input v-model="form.country" label="Код страны" placeholder="DE" maxlength="2" />
        <Input v-model="form.country_name" label="Страна" placeholder="Германия" />
        <Input v-model="form.city" label="Город" placeholder="Франкфурт" />
      </div>

      <!-- Reality -->
      <div>
        <label class="block text-sm font-bold uppercase tracking-wide mb-2">
          Reality-ключи
        </label>
        <p class="text-xs opacity-60 mb-2">
          Сгенерировать: <code class="bg-black/10 px-1">xray x25519</code>
        </p>
        <div class="space-y-3">
          <Input v-model="form.reality_private_key" label="Private key" />
          <Input v-model="form.reality_public_key" label="Public key" />
        </div>
      </div>

      <!-- Активность -->
      <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-[#FFD700]" />
        <span class="text-sm font-bold uppercase tracking-wide">Активен</span>
      </label>

      <!-- Кнопки -->
      <div class="flex gap-3 pt-2">
        <Button type="button" variant="secondary" class="flex-1" @click="$emit('update:modelValue', false)">
          Отмена
        </Button>
        <Button type="submit" variant="primary" :loading="loading" class="flex-1">
          {{ server ? 'Сохранить' : 'Создать' }}
        </Button>
      </div>
    </form>
  </Modal>
</template>
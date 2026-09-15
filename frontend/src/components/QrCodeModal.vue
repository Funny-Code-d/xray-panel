<script setup>
import { ref, watch } from 'vue'
import QRCode from 'qrcode'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'
import api from '@/api/axios'

const props = defineProps({
  modelValue: Boolean,
  clientId: [Number, String],
})
const emit = defineEmits(['update:modelValue'])

const loading = ref(false)
const error = ref(null)
const config = ref(null)
const qrDataUrl = ref('')
const copied = ref(false)

watch(() => props.modelValue, async (open) => {
  if (!open || !props.clientId) return

  loading.value = true
  error.value = null
  config.value = null
  qrDataUrl.value = ''
  copied.value = false

  try {
    const { data } = await api.get(`/clients/${props.clientId}/config`)
    config.value = data

    // Генерируем QR как DataURL (PNG)
    qrDataUrl.value = await QRCode.toDataURL(data.vmess_link, {
      width: 400,
      margin: 1,
      color: {
        dark: '#000000',
        light: '#ffffff',
      },
      errorCorrectionLevel: 'M',
    })
  } catch (e) {
    error.value = e.response?.data?.message || 'Не удалось загрузить конфиг'
  } finally {
    loading.value = false
  }
})

async function copyLink() {
  if (!config.value?.vmess_link) return

  try {
    await navigator.clipboard.writeText(config.value.vmess_link)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch (e) {
    // Fallback для старых браузеров
    const textarea = document.createElement('textarea')
    textarea.value = config.value.vmess_link
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand('copy')
    document.body.removeChild(textarea)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  }
}
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    title="Подключение"
  >
    <!-- Loading -->
    <div v-if="loading" class="text-center py-12 opacity-70">
      Генерация QR-кода...
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-8">
      <p class="text-red-600 dark:text-red-400 font-bold mb-4">{{ error }}</p>
      <Button variant="secondary" @click="$emit('update:modelValue', false)">
        Закрыть
      </Button>
    </div>

    <!-- Config -->
    <div v-else-if="config" class="space-y-5">
      <!-- Имя ключа -->
      <div class="text-center">
        <p class="font-bold text-lg">{{ config.name }}</p>
        <p class="text-xs font-mono opacity-60 mt-1">{{ config.email }}</p>
      </div>

      <!-- QR-код -->
      <div class="flex justify-center">
        <div class="bg-white border-2 border-black dark:border-white p-3">
          <img :src="qrDataUrl" alt="QR-код" class="w-64 h-64" />
        </div>
      </div>

      <!-- Инструкция -->
      <div class="bg-slate-50 dark:bg-[#1a0b2e] border-2 border-black dark:border-white p-4">
        <p class="text-xs font-bold uppercase tracking-wider mb-2">Как подключиться</p>
        <ol class="text-xs space-y-1 opacity-80 list-decimal list-inside">
          <li>Установите VPN-клиент: <b>v2rayNG</b> (Android) или <b>V2RayTun</b> (iOS)</li>
          <li>Откройте клиент → «Импорт из QR-кода»</li>
          <li>Наведите камеру на этот код</li>
          <li>Нажмите «Подключиться»</li>
        </ol>
      </div>

      <!-- Ссылка (для копирования) -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider mb-2 opacity-60">
          Или скопируйте ссылку
        </label>
        <div class="flex gap-2">
          <input
            :value="config.vmess_link"
            readonly
            class="flex-1 px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-[#1a0b2e] border-2 border-black dark:border-white focus:outline-none"
          />
          <Button variant="primary" @click="copyLink">
            {{ copied ? '✓' : 'Копи' }}
          </Button>
        </div>
      </div>
    </div>
  </Modal>
</template>
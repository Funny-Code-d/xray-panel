<script setup>
import { ref, watch, computed } from 'vue'
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

// Текущая ссылка в зависимости от протокола
const currentLink = computed(() => {
  if (!config.value) return ''

  return config.value.protocol === 'vless'
    ? config.value.vless_link
    : config.value.vmess_link
})

// Человекочитаемое название протокола
const protocolLabel = computed(() => {
  if (!config.value) return ''
  return config.value.protocol === 'vless' ? 'VLESS + Reality' : 'VMess'
})

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

    await regenerateQr()
  } catch (e) {
    error.value = e.response?.data?.message || 'Не удалось загрузить конфиг'
  } finally {
    loading.value = false
  }
})

async function regenerateQr() {
  if (!currentLink.value) {
    qrDataUrl.value = ''
    return
  }

  qrDataUrl.value = await QRCode.toDataURL(currentLink.value, {
    width: 400,
    margin: 1,
    color: {
      dark: '#000000',
      light: '#ffffff',
    },
    errorCorrectionLevel: 'M',
  })
}

async function copyLink() {
  if (!currentLink.value) return

  try {
    await navigator.clipboard.writeText(currentLink.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch (e) {
    const textarea = document.createElement('textarea')
    textarea.value = currentLink.value
    textarea.style.position = 'fixed'
    textarea.style.opacity = '0'
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

      <!-- Бейдж протокола -->
      <div class="flex justify-center">
        <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider border-2 border-black dark:border-white">
          {{ protocolLabel }}
        </span>
      </div>

      <!-- QR-код -->
      <div v-if="qrDataUrl" class="flex justify-center">
        <div class="bg-white border-2 border-black dark:border-white p-3">
          <img :src="qrDataUrl" alt="QR-код" class="w-64 h-64" />
        </div>
      </div>

      <!-- Нет ссылки -->
      <div v-else class="text-center py-4 opacity-60">
        <p class="text-sm">Ссылка недоступна</p>
      </div>

      <!-- Инструкция -->
      <div class="bg-slate-50 dark:bg-[#1a0b2e] border-2 border-black dark:border-white p-4">
        <p class="text-xs font-bold uppercase tracking-wider mb-2">Как подключиться</p>
        <ol class="text-xs space-y-1 opacity-80 list-decimal list-inside">
          <li>Установите VPN-клиент: <b>OneXray</b>, <b>v2rayTun</b> (iOS) или <b>v2rayNG</b> (Android)</li>
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
            :value="currentLink"
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
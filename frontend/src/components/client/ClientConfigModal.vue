<script setup>
import { ref, watch, computed } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'
import api from '@/api/axios'
import QRCode from 'qrcode'

const props = defineProps({
  modelValue: Boolean,
  client: Object,
})

const emit = defineEmits(['update:modelValue'])

const loading = ref(false)
const error = ref(null)
const config = ref(null)
const activeProtocol = ref(null)
const qrDataUrl = ref('')
const copied = ref(false)

const links = computed(() => config.value?.links ?? {})

const availableProtocols = computed(() =>
  Object.keys(links.value)
)

const activeLink = computed(() =>
  activeProtocol.value ? links.value[activeProtocol.value] : null
)

async function fetchConfig() {
  if (!props.client) return

  loading.value = true
  error.value = null

  try {
    const { data } = await api.get(`/clients/${props.client.id}/config`)
    config.value = data

    // Выбираем первый доступный протокол
    const protocols = Object.keys(data.links ?? {})
    activeProtocol.value = protocols[0] ?? null
  } catch (e) {
    error.value = 'Не удалось загрузить конфиг'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function generateQr() {
  qrDataUrl.value = ''

  if (!activeLink.value) return

  try {
    qrDataUrl.value = await QRCode.toDataURL(activeLink.value, {
      width: 320,
      margin: 1,
      color: {
        dark: '#000000',
        light: '#FFFFFF',
      },
    })
  } catch (e) {
    console.error('QR generation failed', e)
  }
}

async function copyLink() {
  if (!activeLink.value) return

  try {
    await navigator.clipboard.writeText(activeLink.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch (e) {
    console.error('Copy failed', e)
  }
}

watch(() => props.modelValue, (open) => {
  if (open) {
    fetchConfig()
  } else {
    config.value = null
    activeProtocol.value = null
    qrDataUrl.value = ''
    copied.value = false
  }
})

watch(activeProtocol, () => {
  generateQr()
})

watch(activeLink, () => {
  generateQr()
})

function close() {
  emit('update:modelValue', false)
}
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    title="Подключение"
    size="lg"
  >
    <div v-if="loading" class="text-center py-12 opacity-70">
      Загрузка...
    </div>

    <div
      v-else-if="error"
      class="bg-red-100 dark:bg-red-900 border-[3px] border-red-700 dark:border-red-400 p-4 text-center"
    >
      <p class="font-bold">{{ error }}</p>
    </div>

    <div v-else-if="config" class="space-y-5">
      <!-- Инфо о ключе -->
      <div>
        <h3 class="text-2xl font-black uppercase tracking-wider mb-1">
          {{ config.name }}
        </h3>
        <p class="text-sm opacity-60">
          {{ config.server.name }} · {{ config.server.host }}
        </p>
      </div>

      <!-- Табы протоколов -->
      <div v-if="availableProtocols.length > 1" class="flex flex-wrap gap-2">
        <button
          v-for="protocol in availableProtocols"
          :key="protocol"
          type="button"
          class="px-4 py-2 text-xs font-black uppercase tracking-wider border-[3px] border-black dark:border-white transition-colors"
          :class="activeProtocol === protocol
            ? 'bg-black text-white'
            : 'bg-white dark:bg-[#1A1A1A] hover:bg-[#FFD700] hover:text-black'"
          @click="activeProtocol = protocol"
        >
          {{ protocol }}
        </button>
      </div>

      <p
        v-else-if="availableProtocols.length === 1"
        class="text-xs font-black uppercase tracking-wider opacity-60"
      >
        Протокол: {{ availableProtocols[0] }}
      </p>

      <!-- QR-код -->
      <div v-if="qrDataUrl" class="flex justify-center">
        <div class="bg-white border-[3px] border-black dark:border-white p-4 shadow-brutal">
          <img :src="qrDataUrl" alt="QR" class="w-64 h-64" />
        </div>
      </div>

      <!-- Ссылка -->
      <div>
        <label class="block text-xs font-black uppercase tracking-wider opacity-60 mb-2">
          Ссылка для подключения
        </label>
        <div class="flex gap-2">
          <input
            :value="activeLink"
            readonly
            class="flex-1 px-3 py-2 border-[3px] border-black dark:border-white bg-white dark:bg-[#0A0A0A] font-mono text-xs truncate"
            @focus="$event.target.select()"
          />
          <Button variant="primary" @click="copyLink">
            {{ copied ? '✓' : 'Копировать' }}
          </Button>
        </div>
      </div>

      <!-- Подсказка -->
      <p class="text-xs opacity-60 leading-relaxed">
        Отсканируйте QR-код или скопируйте ссылку и импортируйте её
        в приложение: <b>v2rayNG</b>, <b>OneXray</b>, <b>Streisand</b>,
        <b>v2rayTun</b>, <b>NekoBox</b>.
      </p>
    </div>

    <template #footer>
      <Button variant="secondary" @click="close">Закрыть</Button>
    </template>
  </Modal>
</template>
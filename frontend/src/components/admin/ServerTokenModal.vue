<script setup>
import { ref } from 'vue'
import Modal from '@/components/ui/Modal.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps({
  modelValue: Boolean,
  token: String,
  serverName: String,
})
const emit = defineEmits(['update:modelValue'])

const copied = ref(false)

async function copyToken() {
  if (!props.token) return

  try {
    await navigator.clipboard.writeText(props.token)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch (e) {
    // Fallback для http://192.168.x.x
    const textarea = document.createElement('textarea')
    textarea.value = props.token
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
    title="Токен сервера"
  >
    <div class="space-y-4">
      <!-- Инфо -->
      <div class="p-3 bg-amber-100 dark:bg-amber-900 border-2 border-amber-700 dark:border-amber-400">
        <p class="text-xs font-bold uppercase tracking-wider mb-1">
          ⚠️ Секретный токен
        </p>
        <p class="text-sm">
          Токен для авторизации сервера <b>{{ serverName }}</b> в панели.
          Не передавайте его третьим лицам.
        </p>
      </div>

      <!-- Токен -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider mb-2 opacity-60">
          API Token
        </label>
        <div class="flex gap-2">
          <input
            :value="token"
            readonly
            class="flex-1 px-3 py-2 text-xs font-mono bg-slate-50 dark:bg-[#121212] border-2 border-black dark:border-white focus:outline-none"
          />
          <Button variant="primary" @click="copyToken">
            {{ copied ? '✓' : 'Копи' }}
          </Button>
        </div>
      </div>

      <!-- Куда вставить -->
      <div class="p-3 bg-slate-50 dark:bg-[#121212] border-2 border-black dark:border-white">
        <p class="text-xs font-bold uppercase tracking-wider mb-2">Куда вставить</p>
        <p class="text-xs opacity-70 mb-2">
          На VPN-узле, в файле <code>.env</code>:
        </p>
        <pre class="text-xs font-mono bg-black/5 dark:bg-white/5 p-2 overflow-x-auto">XRAY_API_TOKEN={{ token }}</pre>
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
<script setup>
import { ref, watch, computed } from 'vue'
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

const isEditing = computed(() => !!props.server)

function generatePassword(length = 32) {
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
  return Array.from({ length }, () => chars[Math.floor(Math.random() * chars.length)]).join('')
}

function defaultProtocols() {
  return [
    {
      protocol: 'vless',
      is_enabled: true,
      port: 443,
      tag: 'vless-reality',
      settings: {
        sni: 'dl.google.com',
        dest: 'dl.google.com:443',
        private_key: '',
        public_key: '',
        short_id: '',
        flow: 'xtls-rprx-vision',
        fingerprint: 'firefox',
      },
    },
  ]
}

const form = ref({
  name: '',
  host: '',
  domain: '',
  country: '',
  country_name: '',
  city: '',
  api_host: '127.0.0.1',
  api_port: 10085,
  agent_port: 8080,
  is_active: true,
  protocols: defaultProtocols(),
})

watch(() => props.modelValue, (open) => {
  if (!open) return
  errors.value = {}

  if (props.server) {
    form.value = {
      name: props.server.name ?? '',
      host: props.server.host ?? '',
      domain: props.server.domain ?? '',
      country: props.server.country ?? '',
      country_name: props.server.country_name ?? '',
      city: props.server.city ?? '',
      api_host: props.server.api_host ?? '127.0.0.1',
      api_port: props.server.api_port ?? 10085,
      agent_port: props.server.agent_port ?? 8080,
      is_active: props.server.is_active ?? true,
      protocols: (props.server.protocols ?? []).map((p) => ({
        protocol: p.protocol,
        is_enabled: p.is_enabled,
        port: p.port,
        tag: p.tag,
        settings: { ...p.settings },
      })),
    }
  } else {
    form.value = {
      name: '',
      host: '',
      domain: '',
      country: '',
      country_name: '',
      city: '',
      api_host: '127.0.0.1',
      api_port: 10085,
      agent_port: 8080,
      is_active: true,
      protocols: defaultProtocols(),
    }
  }
})

function getProtocol(name) {
  return form.value.protocols.find((p) => p.protocol === name)
}

function isProtocolEnabled(name) {
  return !!getProtocol(name)?.is_enabled
}

function toggleProtocol(name, enabled) {
  const existing = getProtocol(name)

  if (existing) {
    existing.is_enabled = enabled
    return
  }

  if (!enabled) return

  const defaults = {
    protocol: name,
    is_enabled: true,
    port: name === 'vmess' ? 8443 : 2053,
    tag: name === 'vmess' ? 'vmess-ws-tls' : 'trojan-tcp-tls',
    settings: {},
  }

  if (name === 'vmess') {
    defaults.settings = { path: '/vmess' }
  }

  if (name === 'trojan') {
    defaults.settings = { password: generatePassword() }
  }

  form.value.protocols.push(defaults)
}

async function handleSubmit() {
  loading.value = true
  errors.value = {}

  const payload = {
    ...form.value,
    protocols: form.value.protocols.filter((p) => p.is_enabled),
  }

  try {
    if (props.server) {
      await api.patch(`/admin/servers/${props.server.id}`, payload)
    } else {
      await api.post('/admin/servers', payload)
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

function errorFor(field) {
  return errors.value[field]?.[0]
}
</script>

<template>
  <Modal
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :title="server ? 'Редактировать сервер' : 'Новый сервер'"
    size="xl"
  >
    <form @submit.prevent="handleSubmit" class="space-y-5">
      <!-- Основное -->
      <div>
        <h3 class="text-xl font-black uppercase tracking-wider mb-3">Основное</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <Input v-model="form.name" label="Название" placeholder="Нидерланды #1" :error="errorFor('name')" />
          <Input v-model="form.host" label="IP сервера" placeholder="82.40.38.102" :error="errorFor('host')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
          <Input v-model="form.domain" label="Домен" placeholder="nl.funny-code.space" :error="errorFor('domain')" />
          <Input v-model="form.city" label="Город" placeholder="Амстердам" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
          <Input v-model="form.country" label="Код страны" placeholder="NL" maxlength="2" />
          <Input v-model="form.country_name" label="Страна" placeholder="Нидерланды" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
          <Input v-model="form.api_host" label="API host" />
          <Input v-model.number="form.api_port" type="number" label="API порт" />
          <Input v-model.number="form.agent_port" type="number" label="Порт агента" />
        </div>

        <label class="flex items-center gap-3 cursor-pointer mt-4">
          <input type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-[#FFD700]" />
          <span class="text-sm font-black uppercase tracking-wide">Активен</span>
        </label>
      </div>

      <!-- VLESS -->
      <div class="border-t-[3px] border-black dark:border-white pt-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-black uppercase tracking-wider">VLESS + Reality</h3>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :checked="isProtocolEnabled('vless')"
              class="w-4 h-4 accent-[#FFD700]"
              @change="toggleProtocol('vless', $event.target.checked)"
            />
            <span class="text-xs font-black uppercase tracking-wide">Включить</span>
          </label>
        </div>

        <div v-if="isProtocolEnabled('vless')" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Input v-model.number="getProtocol('vless').port" type="number" label="Порт" />
            <Input v-model="getProtocol('vless').tag" label="Тег" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Input v-model="getProtocol('vless').settings.sni" label="SNI" placeholder="dl.google.com" />
            <Input v-model="getProtocol('vless').settings.dest" label="Dest" placeholder="dl.google.com:443" />
          </div>

          <Input v-model="getProtocol('vless').settings.private_key" label="Private key" />
          <Input v-model="getProtocol('vless').settings.public_key" label="Public key" />

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <Input v-model="getProtocol('vless').settings.short_id" label="Short ID" />
            <Input v-model="getProtocol('vless').settings.flow" label="Flow" />
            <Input v-model="getProtocol('vless').settings.fingerprint" label="Fingerprint" />
          </div>
        </div>
      </div>

      <!-- VMess -->
      <div class="border-t-[3px] border-black dark:border-white pt-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-black uppercase tracking-wider">VMess + WS + TLS</h3>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :checked="isProtocolEnabled('vmess')"
              class="w-4 h-4 accent-[#FFD700]"
              @change="toggleProtocol('vmess', $event.target.checked)"
            />
            <span class="text-xs font-black uppercase tracking-wide">Включить</span>
          </label>
        </div>

        <div v-if="isProtocolEnabled('vmess')" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Input v-model.number="getProtocol('vmess').port" type="number" label="Порт" />
            <Input v-model="getProtocol('vmess').tag" label="Тег" />
          </div>
          <Input v-model="getProtocol('vmess').settings.path" label="WebSocket path" placeholder="/vmess" />
        </div>

        <p v-else class="text-xs opacity-60">Отключён. Включите, чтобы добавить второй inbound.</p>
      </div>

      <!-- Trojan -->
      <div class="border-t-[3px] border-black dark:border-white pt-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xl font-black uppercase tracking-wider">Trojan + TCP + TLS</h3>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :checked="isProtocolEnabled('trojan')"
              class="w-4 h-4 accent-[#FFD700]"
              @change="toggleProtocol('trojan', $event.target.checked)"
            />
            <span class="text-xs font-black uppercase tracking-wide">Включить</span>
          </label>
        </div>

        <div v-if="isProtocolEnabled('trojan')" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <Input v-model.number="getProtocol('trojan').port" type="number" label="Порт" />
            <Input v-model="getProtocol('trojan').tag" label="Тег" />
          </div>
          <Input v-model="getProtocol('trojan').settings.password" label="Password" />
        </div>

        <p v-else class="text-xs opacity-60">Отключён. Включите, чтобы добавить третий inbound.</p>
      </div>

      <!-- Ошибка -->
      <p v-if="errors.general" class="text-red-600 dark:text-red-400 font-bold text-sm">
        {{ errors.general[0] }}
      </p>

      <!-- Кнопки -->
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
          {{ server ? 'Сохранить' : 'Создать' }}
        </Button>
      </div>
    </form>
  </Modal>
</template>
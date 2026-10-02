<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import ClientCard from '@/components/client/ClientCard.vue'
import ClientEditorModal from '@/components/client/ClientEditorModal.vue'
import ClientConfigModal from '@/components/client/ClientConfigModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import api from '@/api/axios'

const clients = ref([])
const servers = ref([])
const loading = ref(true)
const error = ref(null)

const showEditor = ref(false)
const editingClient = ref(null)

const showConfig = ref(false)
const configClient = ref(null)

const showDelete = ref(false)
const clientToDelete = ref(null)
const deleting = ref(false)

async function fetchClients() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/clients')
    clients.value = data.data ?? data
  } catch (e) {
    error.value = 'Не удалось загрузить ключи'
  } finally {
    loading.value = false
  }
}

async function fetchServers() {
  try {
    const { data } = await api.get('/servers')
    servers.value = data
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  fetchClients()
  fetchServers()
})

function openCreate() {
  editingClient.value = null
  showEditor.value = true
}

function openEdit(client) {
  editingClient.value = client
  showEditor.value = true
}

function openConfig(client) {
  configClient.value = client
  showConfig.value = true
}

function confirmDelete(client) {
  clientToDelete.value = client
  showDelete.value = true
}

async function handleDelete() {
  if (!clientToDelete.value) return

  deleting.value = true
  try {
    await api.delete(`/clients/${clientToDelete.value.id}`)
    clients.value = clients.value.filter(c => c.id !== clientToDelete.value.id)
    showDelete.value = false
    clientToDelete.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Не удалось удалить ключ')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <AppLayout>
    <div class="mb-8">
      <h1 class="text-5xl sm:text-6xl font-black uppercase tracking-wider leading-none">
        Keys
      </h1>
      <p class="mt-3 text-base opacity-70">
        Управление доступом и подключениями
      </p>
    </div>

    <div class="flex justify-between items-end mb-6 flex-wrap gap-4">
      <p class="text-sm opacity-70">Всего: {{ clients.length }}</p>
      <Button variant="primary" @click="openCreate">
        + Новый ключ
      </Button>
    </div>

    <div v-if="loading" class="text-center py-12 opacity-70 text-sm">
      Loading...
    </div>

    <div
      v-else-if="error"
      class="bg-red-100 dark:bg-red-900 border-[3px] border-red-700 dark:border-red-400 p-6 text-center"
    >
      <p class="font-bold">{{ error }}</p>
    </div>

    <div
      v-else-if="clients.length === 0"
      class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-12 text-center"
    >
      <p class="text-2xl font-black uppercase tracking-wider mb-2">No keys yet</p>
      <p class="text-sm opacity-70 mb-6">Создайте первый ключ, чтобы подключиться</p>
      <Button variant="primary" @click="openCreate">+ Новый ключ</Button>
    </div>

    <div v-else class="space-y-3">
      <ClientCard
        v-for="client in clients"
        :key="client.id"
        :client="client"
        @show-config="openConfig"
        @edit="openEdit"
        @delete="confirmDelete"
      />
    </div>

    <ClientEditorModal
      v-model="showEditor"
      :client="editingClient"
      :servers="servers"
      @saved="fetchClients"
    />

    <ClientConfigModal
      v-model="showConfig"
      :client="configClient"
    />

    <ConfirmModal
      v-model="showDelete"
      title="Удалить ключ?"
      :message="`Ключ «${clientToDelete?.name}» будет удалён. Продолжить?`"
      confirm-text="Удалить"
      :loading="deleting"
      @confirm="handleDelete"
    />
  </AppLayout>
</template>
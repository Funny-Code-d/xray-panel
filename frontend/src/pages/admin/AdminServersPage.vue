<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'
import Button from '@/components/ui/Button.vue'
import IconButton from '@/components/ui/IconButton.vue'
import ServerEditorModal from '@/components/admin/ServerEditorModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import api from '@/api/axios'
import ServerTokenModal from '@/components/admin/ServerTokenModal.vue'

const showTokenModal = ref(false)
const selectedToken = ref('')
const selectedServerName = ref('')

function showToken(server) {
  selectedToken.value = server.api_token
  selectedServerName.value = server.name
  showTokenModal.value = true
}

const servers = ref([])
const loading = ref(true)
const error = ref(null)

const showEditorModal = ref(false)
const editingServer = ref(null)

const showDeleteModal = ref(false)
const serverToDelete = ref(null)
const deleting = ref(false)

async function fetchServers() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/admin/servers')
    servers.value = data
  } catch (e) {
    error.value = 'Не удалось загрузить серверы'
  } finally {
    loading.value = false
  }
}

onMounted(fetchServers)

function openCreate() {
  editingServer.value = null
  showEditorModal.value = true
}

function openEdit(server) {
  editingServer.value = server
  showEditorModal.value = true
}

function confirmDelete(server) {
  serverToDelete.value = server
  showDeleteModal.value = true
}

async function handleDelete() {
  if (!serverToDelete.value) return

  deleting.value = true
  try {
    await api.delete(`/admin/servers/${serverToDelete.value.id}`)
    servers.value = servers.value.filter(s => s.id !== serverToDelete.value.id)
    showDeleteModal.value = false
    serverToDelete.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Не удалось удалить сервер')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <AppLayout>
    <AdminLayout>
      <div class="flex justify-between items-end mb-6 flex-wrap gap-4">
        <div>
          <h2 class="text-xl font-black uppercase tracking-wider">Серверы</h2>
          <p class="text-sm opacity-70 mt-1">Всего: {{ servers.length }}</p>
        </div>
        <Button variant="primary" @click="openCreate">
          + Добавить сервер
        </Button>
      </div>

      <div v-if="loading" class="text-center py-12 opacity-70">Загрузка...</div>

      <div
        v-else-if="error"
        class="bg-red-100 dark:bg-red-900 border-[3px] border-red-700 dark:border-red-400 shadow-brutal p-6 text-center"
      >
        <p class="font-bold">{{ error }}</p>
      </div>

      <div v-else-if="servers.length === 0" class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-12 text-center">
        <p class="text-xl font-bold mb-2">Серверов нет</p>
        <p class="text-sm opacity-70 mb-6">Добавьте первый Xray-узел</p>
        <Button variant="primary" @click="openCreate">+ Добавить сервер</Button>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="server in servers"
          :key="server.id"
          class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-5"
        >
          <div class="flex justify-between items-start gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">{{ server.country_flag }}</span>
                <h3 class="font-bold text-lg">{{ server.name }}</h3>
                <span
                  :class="[
                    'px-2 py-0.5 text-xs font-bold uppercase border-2',
                    server.is_active
                      ? 'bg-green-100 text-green-700 border-green-700'
                      : 'bg-slate-200 text-slate-700 border-slate-700',
                  ]"
                >
                  {{ server.is_active ? 'Активен' : 'Отключён' }}
                </span>
              </div>

              <p class="text-xs font-mono opacity-60 mb-1">
                {{ server.host }}:{{ server.port }}
              </p>
              <p class="text-xs opacity-60">
                {{ server.city || '—' }}, {{ server.country_name || '—' }}
              </p>

              <div class="flex gap-4 text-xs opacity-60 mt-2">
                <span>Клиентов: {{ server.vpn_clients_count ?? 0 }}</span>
                <span>Протокол: {{ server.protocol.toUpperCase() }}</span>
              </div>
            </div>

            <div class="flex gap-2 shrink-0">
              <IconButton title="Редактировать" @click="openEdit(server)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </IconButton>

              <IconButton title="Показать токен" @click="showToken(server)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                </IconButton>

              <IconButton variant="danger" title="Удалить" @click="confirmDelete(server)">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </IconButton>
            </div>
          </div>
        </div>
      </div>

      <ServerEditorModal
        v-model="showEditorModal"
        :server="editingServer"
        @saved="fetchServers"
      />

      <ConfirmModal
        v-model="showDeleteModal"
        title="Удалить сервер?"
        :message="`Сервер «${serverToDelete?.name}» будет удалён. Продолжить?`"
        confirm-text="Удалить"
        :loading="deleting"
        @confirm="handleDelete"
      />

      <ServerTokenModal
        v-model="showTokenModal"
        :token="selectedToken"
        :server-name="selectedServerName"
        />
    </AdminLayout>
  </AppLayout>
</template>
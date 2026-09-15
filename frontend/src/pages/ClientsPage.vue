<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import ClientStatus from '@/components/ClientStatus.vue'
import Button from '@/components/ui/Button.vue'
import IconButton from '@/components/ui/IconButton.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import CreateClientModal from '@/components/CreateClientModal.vue'
import QrCodeModal from '@/components/QrCodeModal.vue'
import api from '@/api/axios'
import { formatBytes, formatDate } from '@/utils/format'

const clients = ref([])
const loading = ref(true)
const error = ref(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
})

// Модалки
const showCreateModal = ref(false)
const showQrModal = ref(false)
const showDeleteModal = ref(false)
const selectedClientId = ref(null)
const clientToDelete = ref(null)
const deleting = ref(false)

// Копирование
const copiedId = ref(null)

async function fetchClients(page = 1) {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/clients', { params: { page } })
    clients.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    }
  } catch (e) {
    error.value = 'Не удалось загрузить ключи'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchClients())

const hasClients = computed(() => clients.value.length > 0)

// Создание
function onClientCreated(client) {
  clients.value.unshift(client)
  pagination.value.total++
}

// QR
function openQr(client) {
  selectedClientId.value = client.id
  showQrModal.value = true
}

// Копирование ссылки
async function copyLink(client) {
  try {
    const { data } = await api.get(`/clients/${client.id}/config`)
    await copyToClipboard(data.vmess_link)
    copiedId.value = client.id
    setTimeout(() => { copiedId.value = null }, 2000)
  } catch (e) {
    alert('Не удалось скопировать ссылку')
  }
}

async function copyToClipboard(text) {
  // Современный API (работает в HTTPS и localhost)
  if (navigator.clipboard && window.isSecureContext) {
    return navigator.clipboard.writeText(text)
  }

  // Fallback для http://192.168.x.x
  const textarea = document.createElement('textarea')
  textarea.value = text
  textarea.style.position = 'fixed'
  textarea.style.opacity = '0'
  document.body.appendChild(textarea)
  textarea.select()
  document.execCommand('copy')
  document.body.removeChild(textarea)
}

// Удаление
function confirmDelete(client) {
  clientToDelete.value = client
  showDeleteModal.value = true
}

async function handleDelete() {
  if (!clientToDelete.value) return

  deleting.value = true
  try {
    await api.delete(`/clients/${clientToDelete.value.id}`)
    clients.value = clients.value.filter(c => c.id !== clientToDelete.value.id)
    pagination.value.total--
    showDeleteModal.value = false
    clientToDelete.value = null
  } catch (e) {
    alert('Не удалось удалить ключ')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <AppLayout>
    <!-- Заголовок -->
    <div class="flex justify-between items-end mb-8 flex-wrap gap-4">
      <div>
        <h1 class="text-3xl font-black uppercase tracking-wider">Мои ключи</h1>
        <p class="mt-2 text-sm opacity-70">Всего: {{ pagination.total }}</p>
      </div>
      <Button variant="primary" @click="showCreateModal = true">
        + Создать ключ
      </Button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12 opacity-70">
      Загрузка...
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="bg-red-100 dark:bg-red-900 border-2 border-red-700 dark:border-red-400 shadow-brutal p-6 text-center"
    >
      <p class="font-bold">{{ error }}</p>
      <Button variant="secondary" class="mt-4" @click="fetchClients()">
        Попробовать снова
      </Button>
    </div>

    <!-- Empty -->
    <div
      v-else-if="!hasClients"
      class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-12 text-center"
    >
      <p class="text-xl font-bold mb-2">У вас пока нет ключей</p>
      <p class="text-sm opacity-70 mb-6">Создайте первый ключ, чтобы начать пользоваться VPN</p>
      <Button variant="primary" @click="showCreateModal = true">
        + Создать ключ
      </Button>
    </div>

    <!-- Список -->
    <div v-else class="space-y-4">
      <!-- Десктоп: таблица -->
      <div class="hidden md:block bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal overflow-hidden">
        <table class="w-full">
          <thead class="border-b-2 border-black dark:border-white bg-slate-50 dark:bg-[#1a0b2e]">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Имя</th>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Статус</th>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Трафик</th>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Срок</th>
              <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Действия</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="client in clients"
              :key="client.id"
              class="border-b-2 border-black/10 dark:border-white/10 last:border-0 hover:bg-slate-50 dark:hover:bg-[#1a0b2e]"
            >
              <td class="px-4 py-3 font-bold">{{ client.name }}</td>
              <td class="px-4 py-3 text-sm font-mono opacity-70">{{ client.email }}</td>
              <td class="px-4 py-3">
                <ClientStatus :client="client" />
              </td>
              <td class="px-4 py-3 text-sm">{{ formatBytes(client.traffic_used) }}</td>
              <td class="px-4 py-3 text-sm">{{ formatDate(client.expires_at) }}</td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2">
                  <!-- QR -->
                  <IconButton title="QR-код" @click="openQr(client)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                      <rect x="3" y="3" width="7" height="7" />
                      <rect x="14" y="3" width="7" height="7" />
                      <rect x="3" y="14" width="7" height="7" />
                      <path d="M14 14h3v3h-3zM18 18h3v3h-3z" />
                    </svg>
                  </IconButton>

                  <!-- Копировать -->
                  <IconButton title="Скопировать ссылку" @click="copyLink(client)">
                    <svg v-if="copiedId !== client.id" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                      <rect x="9" y="9" width="13" height="13" />
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                      <path d="M20 6L9 17l-5-5" />
                    </svg>
                  </IconButton>

                  <!-- Удалить -->
                  <IconButton variant="danger" title="Удалить" @click="confirmDelete(client)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                      <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                  </IconButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Мобильный: карточки -->
      <div class="md:hidden space-y-4">
        <div
          v-for="client in clients"
          :key="client.id"
          class="bg-white dark:bg-[#2a1548] border-2 border-black dark:border-white shadow-brutal p-4"
        >
          <div class="flex justify-between items-start mb-3">
            <div class="min-w-0">
              <p class="font-bold text-lg truncate">{{ client.name }}</p>
              <p class="text-xs font-mono opacity-70 truncate">{{ client.email }}</p>
            </div>
            <ClientStatus :client="client" class="ml-2 shrink-0" />
          </div>

          <div class="grid grid-cols-2 gap-2 text-sm mb-4">
            <div>
              <p class="text-xs opacity-60 uppercase tracking-wider">Трафик</p>
              <p class="font-bold">{{ formatBytes(client.traffic_used) }}</p>
            </div>
            <div>
              <p class="text-xs opacity-60 uppercase tracking-wider">Срок</p>
              <p class="font-bold">{{ formatDate(client.expires_at) }}</p>
            </div>
          </div>

          <div class="flex gap-2">
            <IconButton title="QR-код" class="flex-1 !w-auto" @click="openQr(client)">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
                <path d="M14 14h3v3h-3zM18 18h3v3h-3z" />
              </svg>
            </IconButton>

            <IconButton title="Скопировать ссылку" class="flex-1 !w-auto" @click="copyLink(client)">
              <svg v-if="copiedId !== client.id" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                <rect x="9" y="9" width="13" height="13" />
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                <path d="M20 6L9 17l-5-5" />
              </svg>
            </IconButton>

            <IconButton variant="danger" title="Удалить" @click="confirmDelete(client)">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                <path d="M18 6L6 18M6 6l12 12" />
              </svg>
            </IconButton>
          </div>
        </div>
      </div>

      <!-- Пагинация -->
      <div
        v-if="pagination.last_page > 1"
        class="flex justify-center items-center gap-4 pt-4"
      >
        <Button
          variant="secondary"
          size="sm"
          :disabled="pagination.current_page === 1"
          @click="fetchClients(pagination.current_page - 1)"
        >
          ← Назад
        </Button>
        <span class="text-sm font-bold">
          {{ pagination.current_page }} / {{ pagination.last_page }}
        </span>
        <Button
          variant="secondary"
          size="sm"
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchClients(pagination.current_page + 1)"
        >
          Вперёд →
        </Button>
      </div>
    </div>

    <!-- Модалки -->
    <CreateClientModal v-model="showCreateModal" @created="onClientCreated" />
    <QrCodeModal v-model="showQrModal" :client-id="selectedClientId" />
    <ConfirmModal
      v-model="showDeleteModal"
      title="Удалить ключ?"
      :message="`Ключ «${clientToDelete?.name}» будет удалён безвозвратно. Продолжить?`"
      confirm-text="Удалить"
      :loading="deleting"
      @confirm="handleDelete"
    />
  </AppLayout>
</template>
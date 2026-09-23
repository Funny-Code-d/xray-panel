<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import ServerRack from '@/components/server/ServerRack.vue'
import Button from '@/components/ui/Button.vue'
import api from '@/api/axios'

const servers = ref([])
const loading = ref(true)
const error = ref(null)

async function fetchServers() {
  loading.value = true
  error.value = null

  try {
    const { data } = await api.get('/servers')
    servers.value = data
  } catch (e) {
    error.value = 'Не удалось загрузить серверы'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchServers()
  setInterval(fetchServers, 30000)
})
</script>

<template>
  <AppLayout>
    <div class="mb-8">
      <h1 class="text-3xl font-black uppercase tracking-wider">Серверы</h1>
      <p class="mt-2 text-sm opacity-70">Состояние узлов инфраструктуры</p>
    </div>

    <div v-if="loading && servers.length === 0" class="text-center py-12 opacity-70">
      Загрузка...
    </div>

    <div
      v-else-if="error"
      class="bg-red-100 dark:bg-red-900 border-[3px] border-red-700 dark:border-red-400 shadow-brutal p-6 text-center"
    >
      <p class="font-bold">{{ error }}</p>
      <Button variant="secondary" class="mt-4" @click="fetchServers">
        Попробовать снова
      </Button>
    </div>

    <div
      v-else-if="servers.length === 0"
      class="bg-white dark:bg-[#1A1A1A] border-[3px] border-black dark:border-white shadow-brutal p-12 text-center"
    >
      <p class="text-xl font-bold mb-2">Серверов нет</p>
      <p class="text-sm opacity-70">Обратитесь к администратору</p>
    </div>

    <ServerRack v-else :servers="servers" />
  </AppLayout>
</template>
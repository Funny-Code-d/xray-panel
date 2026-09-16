<script setup>
import { computed, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import UserDashboard from '@/components/dashboard/UserDashboard.vue'
import AdminDashboard from '@/components/dashboard/AdminDashboard.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

onMounted(async () => {
  try {
    await auth.fetchMe()
  } catch (e) {
    // ignore
  }
})
</script>

<template>
  <AppLayout>
    <AdminDashboard v-if="auth.isAdmin" />
    <UserDashboard v-else />
  </AppLayout>
</template>
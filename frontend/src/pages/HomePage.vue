<script setup>
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/AppLayout.vue'
import UserDashboard from '@/components/dashboard/UserDashboard.vue'
import AdminDashboard from '@/components/dashboard/AdminDashboard.vue'
import LandingPage from '@/pages/LandingPage.vue'

const auth = useAuthStore()
</script>

<template>
  <!-- Не авторизован → лендинг -->
  <LandingPage v-if="!auth.isAuthenticated" />

  <!-- Авторизован → дашборд -->
  <AppLayout v-else>
    <AdminDashboard v-if="auth.isAdmin" />
    <UserDashboard v-else />
  </AppLayout>
</template>
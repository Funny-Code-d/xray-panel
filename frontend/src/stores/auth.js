import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token'))
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.is_admin === true)
  const isApproved = computed(() => user.value?.approval_status === 'approved')
  const isPending = computed(() => user.value?.approval_status === 'pending')
  const isRejected = computed(() => user.value?.approval_status === 'rejected')

  function saveSession(data) {
    token.value = data.token
    user.value = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
  }

  function clearSession() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function login(email, password, deviceName = 'web') {
    const { data } = await api.post('/login', { email, password, device_name: deviceName })
    saveSession(data)
    return data
  }

  async function register(payload) {
    const { data } = await api.post('/register', payload)
    saveSession(data)
    return data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } finally {
      clearSession()
    }
  }

  async function fetchMe() {
    const { data } = await api.get('/me')
    user.value = data
    localStorage.setItem('user', JSON.stringify(data))
    return data
  }

  return {
    token, user,
    isAuthenticated, isAdmin, isApproved, isPending, isRejected,
    login, register, logout, fetchMe,
  }
})
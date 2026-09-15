import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/RegisterPage.vue'),
    meta: { guest: true },
    },
    {
    path: '/pending',
    name: 'pending',
    component: () => import('@/pages/PendingPage.vue'),
    meta: { requiresAuth: true },
    },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('@/pages/DashboardPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/clients',
    name: 'clients',
    component: () => import('@/pages/ClientsPage.vue'),
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' })
  }

  // Pending/rejected → только на /pending
  if (auth.isAuthenticated && !auth.isApproved && !auth.isAdmin) {
    if (to.name !== 'pending') {
      return next({ name: 'pending' })
    }
    return next()
  }

  if (auth.isAuthenticated && (auth.isApproved || auth.isAdmin) && to.name === 'pending') {
    return next({ name: 'dashboard' })
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
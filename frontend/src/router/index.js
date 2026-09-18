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
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/pages/ForgotPasswordPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@/pages/ResetPasswordPage.vue'),
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
  {
    path: '/admin/applications',
    name: 'admin-applications',
    component: () => import('@/pages/admin/ApplicationsPage.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: () => import('@/pages/admin/UsersPage.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/admin/users/:id',
    name: 'admin-user',
    component: () => import('@/pages/AdminUserPage.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/blocked',
    name: 'blocked',
    component: () => import('@/pages/BlockedPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/about',
    name: 'about',
    component: () => import('@/pages/AboutPage.vue'),
  },
  {
    path: '/news',
    name: 'news',
    component: () => import('@/pages/NewsPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/news/:slug',
    name: 'news-post',
    component: () => import('@/pages/NewsPostPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/subscriptions',
    name: 'subscriptions',
    component: () => import('@/pages/SubscriptionsPage.vue'),
    meta: { requiresAuth: true, requiresApproved: true },
  },

  // Админские
  {
    path: '/admin/posts',
    name: 'admin-posts',
    component: () => import('@/pages/admin/AdminPostsPage.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
  },

  {
    path: '/admin/tags',
    name: 'admin-tags',
    component: () => import('@/pages/admin/AdminTagsPage.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
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

  // Заблокированный → только /blocked
  if (auth.isAuthenticated && auth.user?.is_blocked && to.name !== 'blocked') {
    return next({ name: 'blocked' })
  }

  // Разблокированный, но идёт на /blocked → на dashboard
  if (auth.isAuthenticated && !auth.user?.is_blocked && to.name === 'blocked') {
    return next({ name: 'dashboard' })
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return next({ name: 'dashboard' })
  }

  if (auth.isAuthenticated && !auth.isApproved && !auth.isAdmin && !auth.user?.is_blocked) {
    if (to.name !== 'pending') {
      return next({ name: 'pending' })
    }
    return next()
  }

  if (auth.isAuthenticated && (auth.isApproved || auth.isAdmin) && to.name === 'pending' && !auth.user?.is_blocked) {
    return next({ name: 'dashboard' })
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
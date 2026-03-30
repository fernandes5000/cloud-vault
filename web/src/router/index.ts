import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginView from '@/modules/auth/LoginView.vue'
import RegisterView from '@/modules/auth/RegisterView.vue'
import DriveView from '@/modules/drive/DriveView.vue'
import PublicShareView from '@/modules/shared/PublicShareView.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/app',
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { guestOnly: true },
    },
    {
      path: '/app',
      name: 'drive',
      component: DriveView,
      meta: { requiresAuth: true },
    },
    {
      path: '/share/:token',
      name: 'public-share',
      component: PublicShareView,
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchMe()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'drive' }
  }

  return true
})

import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { http } from '@/app/http'
import { i18n } from '@/app/i18n'
import type { User } from '@/app/types'

interface AuthPayload {
  name?: string
  email: string
  password: string
  password_confirmation?: string
  preferred_locale?: string
  timezone?: string
  device_name?: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('cloudvault.token') || '')
  const user = ref<User | null>(null)
  const loading = ref(false)
  const locale = ref(localStorage.getItem('cloudvault.locale') || 'en')

  const isAuthenticated = computed(() => Boolean(token.value))

  const persistLocale = (value: string) => {
    locale.value = value
    localStorage.setItem('cloudvault.locale', value)
    i18n.global.locale.value = value as never

    if (user.value) {
      user.value.preferredLocale = value
    }
  }

  const setSession = (nextToken: string, nextUser: User) => {
    token.value = nextToken
    user.value = nextUser
    localStorage.setItem('cloudvault.token', nextToken)
    persistLocale(nextUser.preferredLocale || locale.value)
  }

  const clearSession = () => {
    token.value = ''
    user.value = null
    localStorage.removeItem('cloudvault.token')
  }

  const register = async (payload: AuthPayload) => {
    loading.value = true

    try {
      const { data } = await http.post('/auth/register', payload)
      setSession(data.token, data.user)
    } finally {
      loading.value = false
    }
  }

  const login = async (payload: AuthPayload) => {
    loading.value = true

    try {
      const { data } = await http.post('/auth/login', payload)
      setSession(data.token, data.user)
    } finally {
      loading.value = false
    }
  }

  const fetchMe = async () => {
    if (!token.value) {
      return
    }

    try {
      const { data } = await http.get<User>('/auth/me')
      user.value = data
      persistLocale(data.preferredLocale || locale.value)
    } catch {
      clearSession()
    }
  }

  const logout = async () => {
    if (token.value) {
      try {
        await http.post('/auth/logout')
      } catch {
        // We still clear local credentials to keep the client recoverable.
      }
    }

    clearSession()
  }

  persistLocale(locale.value)

  return {
    token,
    user,
    loading,
    locale,
    isAuthenticated,
    register,
    login,
    fetchMe,
    logout,
    persistLocale,
  }
})

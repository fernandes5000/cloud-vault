import axios from 'axios'

const fallbackBaseUrl = 'http://localhost:8080/api/v1'

export const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || fallbackBaseUrl

export const http = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    Accept: 'application/json',
  },
})

http.interceptors.request.use((config) => {
  const token = localStorage.getItem('cloudvault.token')
  const locale = localStorage.getItem('cloudvault.locale') || 'en'

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  config.headers['Accept-Language'] = locale.replace('_', '-')

  return config
})

export const assetUrl = (path: string | null | undefined) => {
  if (!path) {
    return ''
  }

  if (/^https?:\/\//.test(path)) {
    return path
  }

  return new URL(path, apiBaseUrl).toString()
}

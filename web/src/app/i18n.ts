import { createI18n } from 'vue-i18n'
import { messages } from '@/locales/messages'

const locale = localStorage.getItem('cloudvault.locale') || 'en'

export const i18n = createI18n({
  legacy: false,
  locale,
  fallbackLocale: 'en',
  messages,
})

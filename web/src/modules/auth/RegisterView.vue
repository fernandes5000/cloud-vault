<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const { t } = useI18n()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  preferred_locale: auth.locale,
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
})

const submit = async () => {
  await auth.register(form)
  await router.push({ name: 'drive' })
}
</script>

<template>
  <div class="grid min-h-screen place-items-center px-4 py-10">
    <section class="glass-panel fade-up w-full max-w-2xl rounded-[2rem] p-6 sm:p-8">
      <p class="section-title">V1 onboarding</p>
      <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ t('app.register') }}</h1>
      <p class="mt-3 max-w-xl text-sm text-slate-500 dark:text-slate-400">
        Create a personal workspace with language, timezone and secure API access already configured.
      </p>

      <form class="mt-8 grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
        <label class="block sm:col-span-2">
          <span class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Name</span>
          <input v-model="form.name" class="field" required />
        </label>

        <label class="block sm:col-span-2">
          <span class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Email</span>
          <input v-model="form.email" class="field" type="email" required />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Password</span>
          <input v-model="form.password" class="field" type="password" required />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Confirm password</span>
          <input v-model="form.password_confirmation" class="field" type="password" required />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Language</span>
          <select v-model="form.preferred_locale" class="field">
            <option value="en">English</option>
            <option value="pt_BR">Português (Brasil)</option>
            <option value="es">Español</option>
          </select>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">Timezone</span>
          <input v-model="form.timezone" class="field" required />
        </label>

        <button class="solid-button mt-2 w-full sm:col-span-2" :disabled="auth.loading" type="submit">
          {{ t('app.register') }}
        </button>
      </form>

      <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">
        Already have an account?
        <RouterLink class="font-semibold text-teal-700 dark:text-teal-300" to="/login">
          {{ t('app.login') }}
        </RouterLink>
      </p>
    </section>
  </div>
</template>

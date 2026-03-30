<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Cloud, LockKeyhole, Mail } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const { t } = useI18n()

const form = reactive({
  email: 'ana@cloudvault.test',
  password: 'password',
  device_name: 'web-app',
})

const submit = async () => {
  await auth.login(form)
  await router.push({ name: 'drive' })
}
</script>

<template>
  <div class="grid min-h-screen place-items-center px-4 py-10">
    <div class="grid w-full max-w-5xl gap-6 lg:grid-cols-[1.2fr_0.9fr]">
      <section class="glass-panel grid-surface fade-up hidden overflow-hidden rounded-[2rem] p-8 lg:block">
        <div class="max-w-lg">
          <p class="section-title">Product</p>
          <h1 class="mt-5 text-5xl font-semibold tracking-tight text-slate-950 dark:text-white">
            A calmer personal cloud for photos, files and mobile backup.
          </h1>
          <p class="mt-6 text-lg text-slate-600 dark:text-slate-300">
            Clean web UX, private-by-default storage, chunked uploads and a backend built to scale from self-hosted to SaaS.
          </p>
          <div class="mt-10 grid gap-3">
            <div class="glass-panel rounded-3xl p-5">
              <Cloud class="h-5 w-5 text-teal-600" />
              <p class="mt-4 text-lg font-semibold">Drive-first UX</p>
              <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Fast listing, favorites, recent files and share links without clutter.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="glass-panel fade-up rounded-[2rem] p-6 sm:p-8">
        <p class="section-title">CloudVault</p>
        <h2 class="mt-3 text-3xl font-semibold text-slate-950 dark:text-white">{{ t('app.login') }}</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Use the seeded demo account or your own credentials.</p>

        <form class="mt-8 space-y-4" @submit.prevent="submit">
          <label class="block">
            <span class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300">
              <Mail class="h-4 w-4" />
              Email
            </span>
            <input v-model="form.email" class="field" type="email" required />
          </label>

          <label class="block">
            <span class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300">
              <LockKeyhole class="h-4 w-4" />
              Password
            </span>
            <input v-model="form.password" class="field" type="password" required />
          </label>

          <button class="solid-button w-full" :disabled="auth.loading" type="submit">
            {{ t('app.login') }}
          </button>
        </form>

        <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">
          Need a fresh account?
          <RouterLink class="font-semibold text-teal-700 dark:text-teal-300" to="/register">
            {{ t('app.register') }}
          </RouterLink>
        </p>
      </section>
    </div>
  </div>
</template>

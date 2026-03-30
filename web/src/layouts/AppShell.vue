<script setup lang="ts">
import { LogOut, Languages, Star, Clock4, Trash2, FolderOpen } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDriveStore } from '@/stores/drive'

const auth = useAuthStore()
const drive = useDriveStore()
const { t } = useI18n()

const usage = computed(() => {
  const storage = auth.user?.storage

  if (!storage) {
    return 0
  }

  return Math.round((storage.usedBytes / Math.max(storage.quotaBytes, 1)) * 100)
})
</script>

<template>
  <div class="min-h-screen px-4 py-4 text-slate-900 sm:px-6 lg:px-8 dark:text-slate-100">
    <div class="mx-auto flex min-h-[calc(100vh-2rem)] max-w-7xl flex-col gap-4 lg:flex-row">
      <aside class="glass-panel fade-up grid-surface flex w-full flex-col justify-between p-4 lg:w-[280px] lg:p-6">
        <div class="space-y-6">
          <div>
            <p class="section-title">CloudVault</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
              <span class="gradient-text">{{ t('app.title') }}</span>
            </h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
              {{ t('app.subtitle') }}
            </p>
          </div>

          <nav class="grid gap-2">
            <button class="nav-pill" :class="{ 'nav-pill-active': drive.scope === 'root' }" @click="drive.jumpToScope('root')">
              <FolderOpen class="h-4 w-4" />
              {{ t('app.allFiles') }}
            </button>
            <button class="nav-pill" :class="{ 'nav-pill-active': drive.scope === 'recent' }" @click="drive.jumpToScope('recent')">
              <Clock4 class="h-4 w-4" />
              {{ t('app.recent') }}
            </button>
            <button class="nav-pill" :class="{ 'nav-pill-active': drive.scope === 'favorites' }" @click="drive.jumpToScope('favorites')">
              <Star class="h-4 w-4" />
              {{ t('app.favorites') }}
            </button>
            <button class="nav-pill" :class="{ 'nav-pill-active': drive.scope === 'trash' }" @click="drive.jumpToScope('trash')">
              <Trash2 class="h-4 w-4" />
              {{ t('app.trash') }}
            </button>
          </nav>

          <div class="rounded-3xl bg-slate-950 px-4 py-5 text-white dark:bg-white dark:text-slate-950">
            <p class="text-xs uppercase tracking-[0.25em] text-white/60 dark:text-slate-500">
              {{ t('app.quota') }}
            </p>
            <div class="mt-4 h-3 overflow-hidden rounded-full bg-white/10 dark:bg-slate-200">
              <div class="h-full rounded-full bg-amber-300 dark:bg-teal-500" :style="{ width: `${usage}%` }" />
            </div>
            <p class="mt-3 text-sm font-medium">
              {{ usage }}%
            </p>
          </div>
        </div>

        <div class="space-y-3 rounded-3xl border border-slate-200/80 bg-white/70 p-4 dark:border-white/10 dark:bg-slate-950/70">
          <div>
            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">
              Workspace
            </p>
            <p class="mt-1 text-base font-semibold">
              {{ auth.user?.name }}
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              {{ auth.user?.email }}
            </p>
          </div>
          <div class="flex flex-wrap gap-2">
            <button class="soft-button" @click="auth.persistLocale(auth.locale === 'en' ? 'pt_BR' : auth.locale === 'pt_BR' ? 'es' : 'en')">
              <Languages class="h-4 w-4" />
              {{ auth.locale }}
            </button>
            <button class="soft-button" @click="auth.logout()">
              <LogOut class="h-4 w-4" />
              {{ t('app.logout') }}
            </button>
          </div>
        </div>
      </aside>

      <main class="glass-panel fade-up flex-1 overflow-hidden p-4 sm:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

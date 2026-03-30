<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Copy, FolderPlus, Link2, LoaderCircle, Star, Upload } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useDriveStore } from '@/stores/drive'
import { assetUrl, http } from '@/app/http'
import AppShell from '@/layouts/AppShell.vue'
import type { DriveItem } from '@/app/types'

const auth = useAuthStore()
const drive = useDriveStore()
const { t } = useI18n()
const newFolderName = ref('')

const previewable = (item: DriveItem) =>
  item.type === 'file' && ['ready', 'pending'].includes(item.previewStatus)

const shareUrl = computed(() => drive.lastShare?.publicUrl ?? '')

const submitFolder = async () => {
  if (!newFolderName.value.trim()) {
    return
  }

  await drive.createFolder(newFolderName.value.trim())
  newFolderName.value = ''
}

const onFilesSelected = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = Array.from(target.files ?? [])

  for (const file of files) {
    await drive.uploadFile(file)
  }

  target.value = ''
}

const openPreview = async (item: DriveItem) => {
  if (!item.previewUrl) {
    return
  }

  const previewPath = new URL(assetUrl(item.previewUrl)).pathname.replace(/^\/api\/v1/, '')

  const response = await http.get(previewPath, {
    responseType: 'blob',
    headers: {
      Accept: item.mimeType || 'application/octet-stream',
    },
  })

  const blob = new Blob([response.data], {
    type: item.mimeType || 'application/octet-stream',
  })

  const blobUrl = URL.createObjectURL(blob)
  window.open(blobUrl, '_blank', 'noopener,noreferrer')
}

const openItem = async (item: DriveItem) => {
  if (item.type === 'folder') {
    await drive.openFolder(item)
    return
  }

  if (previewable(item) && item.previewUrl) {
    await openPreview(item)
  }
}

const copyShareUrl = async () => {
  if (!shareUrl.value) {
    return
  }

  await navigator.clipboard.writeText(shareUrl.value)
}

onMounted(async () => {
  await auth.fetchMe()
  await drive.load()
})
</script>

<template>
  <AppShell>
    <div class="grid h-full gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
      <section class="flex min-h-[70vh] flex-col gap-5">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="section-title">Drive</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
              {{ drive.breadcrumb.at(-1)?.name }}
            </h2>
            <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-500 dark:text-slate-400">
              <button
                v-if="drive.breadcrumb.length > 1"
                class="soft-button"
                @click="drive.goBack()"
              >
                {{ t('app.back') }}
              </button>
              <span v-for="crumb in drive.breadcrumb" :key="crumb.id ?? 'root'" class="rounded-full border border-slate-200/80 px-3 py-1 dark:border-white/10">
                {{ crumb.name }}
              </span>
            </div>
          </div>

          <div class="flex flex-wrap gap-2">
            <label class="solid-button cursor-pointer">
              <Upload class="h-4 w-4" />
              {{ t('app.upload') }}
              <input class="hidden" multiple type="file" @change="onFilesSelected" />
            </label>
          </div>
        </header>

        <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
          <form class="glass-panel rounded-[1.75rem] p-4" @submit.prevent="submitFolder">
            <p class="section-title">Quick actions</p>
            <div class="mt-4 flex gap-3">
              <input
                v-model="newFolderName"
                class="field"
                placeholder="Brand assets"
              />
              <button class="soft-button shrink-0" type="submit">
                <FolderPlus class="h-4 w-4" />
                {{ t('app.createFolder') }}
              </button>
            </div>
          </form>

          <div class="glass-panel rounded-[1.75rem] p-4">
            <p class="section-title">Workspace</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
              <div class="rounded-3xl border border-slate-200/80 p-4 dark:border-white/10">
                <p class="text-sm text-slate-500 dark:text-slate-400">User</p>
                <p class="mt-2 text-lg font-semibold">{{ auth.user?.name }}</p>
              </div>
              <div class="rounded-3xl border border-slate-200/80 p-4 dark:border-white/10">
                <p class="text-sm text-slate-500 dark:text-slate-400">Locale</p>
                <p class="mt-2 text-lg font-semibold">{{ auth.user?.preferredLocale }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="glass-panel overflow-hidden rounded-[1.75rem]">
          <div class="flex items-center justify-between border-b border-slate-200/80 px-5 py-4 dark:border-white/10">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Items</p>
            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
              <LoaderCircle v-if="drive.loading || drive.uploading" class="h-4 w-4 animate-spin" />
              <span v-if="drive.loading">{{ t('app.loading') }}</span>
            </div>
          </div>

          <div v-if="!drive.items.length && !drive.loading" class="px-5 py-16 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ t('app.empty') }}
          </div>

          <div v-else class="grid gap-3 p-4">
            <button
              v-for="item in drive.items"
              :key="item.id"
              class="flex flex-col gap-4 rounded-[1.5rem] border border-slate-200/80 bg-white/70 p-4 text-left transition hover:border-slate-300 hover:bg-white dark:border-white/10 dark:bg-slate-950/40 dark:hover:border-white/20"
              @click="openItem(item)"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-lg font-semibold text-slate-950 dark:text-white">{{ item.name }}</p>
                  <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ item.type }} · {{ item.mimeType || 'folder' }}</p>
                </div>
                <button
                  class="soft-button"
                  type="button"
                  @click.stop="drive.toggleFavorite(item)"
                >
                  <Star class="h-4 w-4" :class="{ 'fill-current text-amber-500': item.isFavorite }" />
                </button>
              </div>

              <div class="flex flex-wrap gap-2">
                <a
                  v-if="item.downloadUrl"
                  :href="assetUrl(item.downloadUrl)"
                  class="soft-button"
                  target="_blank"
                  rel="noopener noreferrer"
                  @click.stop
                >
                  {{ t('app.download') }}
                </a>
                <button
                  v-if="previewable(item) && item.previewUrl"
                  class="soft-button"
                  type="button"
                  @click.stop="openPreview(item)"
                >
                  {{ t('app.preview') }}
                </button>
                <button
                  v-if="item.type === 'file'"
                  class="soft-button"
                  type="button"
                  @click.stop="drive.share(item)"
                >
                  <Link2 class="h-4 w-4" />
                  {{ t('app.share') }}
                </button>
              </div>
            </button>
          </div>
        </div>
      </section>

      <aside class="space-y-5">
        <div class="glass-panel rounded-[1.75rem] p-5">
          <p class="section-title">{{ t('app.shareReady') }}</p>
          <div class="mt-4 rounded-3xl border border-slate-200/80 bg-white/70 p-4 text-sm break-all dark:border-white/10 dark:bg-slate-950/40">
            {{ shareUrl || 'Create a public link from any file card.' }}
          </div>
          <button class="soft-button mt-4 w-full" :disabled="!shareUrl" @click="copyShareUrl">
            <Copy class="h-4 w-4" />
            Copy link
          </button>
        </div>

        <div class="glass-panel rounded-[1.75rem] p-5">
          <p class="section-title">Upload engine</p>
          <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
            Browser uploads use the same chunked flow as mobile: create session, upload chunks, finalize, refresh.
          </p>
        </div>
      </aside>
    </div>
  </AppShell>
</template>
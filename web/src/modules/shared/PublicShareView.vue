<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { Download } from 'lucide-vue-next'
import { http, assetUrl } from '@/app/http'
import type { DriveItem, ShareLink } from '@/app/types'

const route = useRoute()
const loading = ref(true)
const item = ref<DriveItem | null>(null)
const share = ref<ShareLink | null>(null)

const token = computed(() => String(route.params.token))

const previewKind = computed(() => {
  const mime = item.value?.mimeType || ''

  if (mime.startsWith('image/')) {
    return 'image'
  }

  if (mime === 'application/pdf') {
    return 'pdf'
  }

  if (mime.startsWith('video/')) {
    return 'video'
  }

  return 'none'
})

onMounted(async () => {
  try {
    const { data } = await http.get(`/shares/public/${token.value}`)
    item.value = data.data.item
    share.value = data.data.share
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="grid min-h-screen place-items-center px-4 py-10">
    <section class="glass-panel fade-up w-full max-w-4xl rounded-[2rem] p-6 sm:p-8">
      <p class="section-title">Shared file</p>
      <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">
        {{ item?.name || 'Loading...' }}
      </h1>

      <div v-if="loading" class="mt-8 text-sm text-slate-500 dark:text-slate-400">
        Loading shared item...
      </div>

      <div v-else class="mt-8 space-y-6">
        <div class="rounded-[1.75rem] border border-slate-200/80 bg-white/70 p-4 dark:border-white/10 dark:bg-slate-950/40">
          <img
            v-if="previewKind === 'image' && item?.previewUrl"
            :src="assetUrl(item.previewUrl)"
            alt=""
            class="max-h-[28rem] w-full rounded-[1.25rem] object-cover"
          />
          <iframe
            v-else-if="previewKind === 'pdf' && item?.previewUrl"
            :src="assetUrl(item.previewUrl)"
            class="h-[32rem] w-full rounded-[1.25rem]"
            title="PDF preview"
          />
          <video
            v-else-if="previewKind === 'video' && item?.previewUrl"
            :src="assetUrl(item.previewUrl)"
            class="w-full rounded-[1.25rem]"
            controls
          />
          <div v-else class="rounded-[1.25rem] border border-dashed border-slate-300 px-6 py-12 text-center text-sm text-slate-500 dark:border-white/10 dark:text-slate-400">
            This file type does not have a safe inline preview yet.
          </div>
        </div>

        <div class="flex flex-wrap gap-3">
          <a
            v-if="share?.downloadUrl"
            :href="assetUrl(share.downloadUrl)"
            class="solid-button"
            target="_blank"
            rel="noopener noreferrer"
          >
            <Download class="h-4 w-4" />
            Download
          </a>
        </div>
      </div>
    </section>
  </div>
</template>

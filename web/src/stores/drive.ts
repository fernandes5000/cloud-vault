import { ref } from 'vue'
import { defineStore } from 'pinia'
import { http } from '@/app/http'
import type { DriveItem, PagedResponse, ShareLink } from '@/app/types'

const chunkSize = 5 * 1024 * 1024

export const useDriveStore = defineStore('drive', () => {
  const items = ref<DriveItem[]>([])
  const loading = ref(false)
  const uploading = ref(false)
  const currentFolderId = ref<string | null>(null)
  const scope = ref<'root' | 'recent' | 'favorites' | 'trash'>('root')
  const breadcrumb = ref<Array<{ id: string | null; name: string }>>([{ id: null, name: 'Home' }])
  const lastShare = ref<ShareLink | null>(null)

  const load = async (nextScope = scope.value, nextParentId = currentFolderId.value) => {
    loading.value = true

    try {
      scope.value = nextScope
      currentFolderId.value = nextScope === 'root' ? nextParentId : null

      const { data } = await http.get<PagedResponse<DriveItem>>('/drive', {
        params: {
          scope: nextScope,
          parent_id: nextScope === 'root' ? nextParentId : undefined,
        },
      })

      items.value = data.data
    } finally {
      loading.value = false
    }
  }

  const openFolder = async (item: DriveItem) => {
    if (item.type !== 'folder') {
      return
    }

    scope.value = 'root'
    currentFolderId.value = item.id
    breadcrumb.value = [...breadcrumb.value, { id: item.id, name: item.name }]
    await load('root', item.id)
  }

  const goBack = async () => {
    if (breadcrumb.value.length <= 1) {
      currentFolderId.value = null
      breadcrumb.value = [{ id: null, name: 'Home' }]
      await load('root', null)
      return
    }

    breadcrumb.value = breadcrumb.value.slice(0, -1)
    const previous = breadcrumb.value.at(-1)
    currentFolderId.value = previous?.id ?? null
    await load('root', currentFolderId.value)
  }

  const jumpToScope = async (nextScope: 'root' | 'recent' | 'favorites' | 'trash') => {
    if (nextScope === 'root') {
      breadcrumb.value = [{ id: null, name: 'Home' }]
      currentFolderId.value = null
    }

    await load(nextScope, nextScope === 'root' ? currentFolderId.value : null)
  }

  const createFolder = async (name: string) => {
    await http.post('/drive/folders', {
      name,
      parent_id: currentFolderId.value,
    })

    await load(scope.value, currentFolderId.value)
  }

  const toggleFavorite = async (item: DriveItem) => {
    await http.patch(`/drive/items/${item.id}/favorite`, {
      is_favorite: !item.isFavorite,
    })

    await load(scope.value, currentFolderId.value)
  }

  const share = async (item: DriveItem) => {
    const { data } = await http.post<{ data: ShareLink }>('/shares', {
      drive_item_id: item.id,
      visibility: 'public',
      permission: 'download',
    })

    lastShare.value = {
      ...data.data,
      publicUrl: `${window.location.origin}/share/${data.data.token}`,
    }
  }

  const uploadFile = async (file: File) => {
    uploading.value = true

    try {
      const totalChunks = Math.ceil(file.size / chunkSize)
      const initResponse = await http.post('/uploads', {
        name: file.name,
        folder_id: currentFolderId.value,
        total_chunks: totalChunks,
        total_size_bytes: file.size,
        mime_type: file.type || 'application/octet-stream',
      })

      const uploadSessionId = initResponse.data.data.id as string

      for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex += 1) {
        const start = chunkIndex * chunkSize
        const end = Math.min(file.size, start + chunkSize)
        const chunk = file.slice(start, end)
        const formData = new FormData()

        formData.append('chunk_index', String(chunkIndex))
        formData.append('chunk', chunk, `${file.name}.part`)

        await http.post(`/uploads/${uploadSessionId}/chunks`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
      }

      await http.post(`/uploads/${uploadSessionId}/complete`)
      await load(scope.value, currentFolderId.value)
    } finally {
      uploading.value = false
    }
  }

  return {
    items,
    loading,
    uploading,
    scope,
    currentFolderId,
    breadcrumb,
    lastShare,
    load,
    openFolder,
    goBack,
    jumpToScope,
    createFolder,
    toggleFavorite,
    share,
    uploadFile,
  }
})

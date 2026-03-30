export interface StorageSummary {
  quotaBytes: number
  usedBytes: number
  freeBytes: number
}

export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'user'
  preferredLocale: string
  timezone: string
  emailVerifiedAt: string | null
  storage: StorageSummary
}

export interface DriveItem {
  id: string
  type: 'file' | 'folder'
  name: string
  parentId: string | null
  mimeType: string | null
  extension: string | null
  sizeBytes: number
  isFavorite: boolean
  previewStatus: string
  metadata: Record<string, unknown>
  downloadUrl: string | null
  previewUrl: string | null
  createdAt: string
  updatedAt: string
}

export interface ShareLink {
  id: string
  token: string
  visibility: 'public' | 'private'
  permission: 'view' | 'download'
  publicUrl: string
  downloadUrl: string
  requiresPassword: boolean
  expiresAt: string | null
}

export interface PagedResponse<T> {
  data: T[]
  meta: {
    total: number
    currentPage: number
    lastPage: number
    scope?: string
  }
}

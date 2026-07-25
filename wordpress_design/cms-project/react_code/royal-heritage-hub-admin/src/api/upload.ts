/**
 * Upload API — File upload with mock/localStorage support.
 * Supports images, videos, and documents.
 */

import { apiClient, safe } from './client';
import type { UploadedFile } from '@/types';

const MOCK_STORAGE_KEY = 'rhh_admin_uploads';

function getStoredFiles(): UploadedFile[] {
  try {
    const raw = localStorage.getItem(MOCK_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch { return []; }
}

function storeFile(file: UploadedFile): void {
  const files = getStoredFiles();
  files.unshift(file);
  localStorage.setItem(MOCK_STORAGE_KEY, JSON.stringify(files));
}

function removeStoredFile(id: string): void {
  const files = getStoredFiles().filter((f) => f.id !== id);
  localStorage.setItem(MOCK_STORAGE_KEY, JSON.stringify(files));
}

function getFileType(file: File): 'image' | 'video' | 'document' {
  if (file.type.startsWith('image/')) return 'image';
  if (file.type.startsWith('video/')) return 'video';
  return 'document';
}

async function uploadFile(file: File): Promise<UploadedFile> {
  if (apiClient.useMock) {
    // Mock: convert to base64 data URL stored in localStorage
    const dataUrl = await new Promise<string>((resolve) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result as string);
      reader.readAsDataURL(file);
    });

    const uploaded: UploadedFile = {
      id: `upload-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
      name: file.name,
      url: dataUrl,
      type: getFileType(file),
      mimeType: file.type,
      size: file.size,
      uploadedAt: new Date().toISOString(),
    };

    storeFile(uploaded);
    return uploaded;
  }

  // Real: multipart upload
  const formData = new FormData();
  formData.append('file', file);
  const res = await fetch(`${apiClient.useMock ? '/mock' : ''}/api/admin/upload`, {
    method: 'POST',
    body: formData,
  });
  return res.json();
}

export const uploadApi = {
  upload: (file: File) => safe(async () => uploadFile(file)),

  uploadMultiple: (files: File[]) => safe(async () => {
    const results = await Promise.all(files.map(uploadFile));
    return results;
  }),

  getAll: () => safe(async (): Promise<UploadedFile[]> => {
    if (apiClient.useMock) return getStoredFiles();
    return apiClient.get<UploadedFile[]>('/api/admin/uploads');
  }),

  delete: (id: string) => safe(async () => {
    if (apiClient.useMock) { removeStoredFile(id); return true; }
    return apiClient.delete<boolean>(`/api/admin/uploads/${id}`);
  }),
};

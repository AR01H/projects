import { useState, useRef, useCallback } from 'react';
import { uploadApi } from '@/api/upload';
import type { UploadedFile } from '@/types';

interface FileUploadProps {
  accept?: string;
  multiple?: boolean;
  maxFiles?: number;
  maxSizeMB?: number;
  type?: 'image' | 'video' | 'document' | 'all';
  onUpload?: (files: UploadedFile[]) => void;
  onRemove?: (id: string) => void;
  existingFiles?: UploadedFile[];
  label?: string;
  description?: string;
}

const TYPE_ACCEPT: Record<string, string> = {
  image: 'image/*',
  video: 'video/*',
  document: '.pdf,.doc,.docx,.txt,.csv',
  all: '*/*',
};

export function FileUpload({
  accept,
  multiple = false,
  maxFiles = 10,
  maxSizeMB = 50,
  type = 'image',
  onUpload,
  onRemove,
  existingFiles = [],
  label = 'Upload Files',
  description,
}: FileUploadProps) {
  const [uploading, setUploading] = useState(false);
  const [dragOver, setDragOver] = useState(false);
  const [error, setError] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  const handleFiles = useCallback(async (fileList: FileList | null) => {
    if (!fileList || fileList.length === 0) return;
    setError('');

    const files = Array.from(fileList);

    // Validate count
    if (!multiple && files.length > 1) {
      setError('Only one file allowed');
      return;
    }
    if (existingFiles.length + files.length > maxFiles) {
      setError(`Max ${maxFiles} files allowed`);
      return;
    }

    // Validate sizes
    const oversized = files.find((f) => f.size > maxSizeMB * 1024 * 1024);
    if (oversized) {
      setError(`File "${oversized.name}" exceeds ${maxSizeMB}MB limit`);
      return;
    }

    setUploading(true);
    try {
      const results = await uploadApi.uploadMultiple(files);
      if (results.data) {
        onUpload?.(results.data);
      } else if (results.error) {
        setError(results.error);
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Upload failed');
    } finally {
      setUploading(false);
      if (inputRef.current) inputRef.current.value = '';
    }
  }, [existingFiles.length, maxFiles, maxSizeMB, multiple, onUpload]);

  function handleDrop(e: React.DragEvent) {
    e.preventDefault();
    setDragOver(false);
    handleFiles(e.dataTransfer.files);
  }

  function handleRemove(id: string) {
    onRemove?.(id);
  }

  function formatSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }

  return (
    <div className="space-y-3">
      {/* Drop zone */}
      <div
        onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
        onDragLeave={() => setDragOver(false)}
        onDrop={handleDrop}
        onClick={() => inputRef.current?.click()}
        className={`flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 transition-colors ${
          dragOver ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
        }`}
      >
        {uploading ? (
          <div className="flex items-center gap-2 text-sm text-gray-500">
            <svg className="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <circle cx="12" cy="12" r="10" strokeOpacity="0.25" />
              <path d="M12 2a10 10 0 0110 10" />
            </svg>
            Uploading...
          </div>
        ) : (
          <>
            <svg className="mb-2 h-8 w-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
              <path d="M12 16V4m0 0l-4 4m4-4l4 4M4 20h16" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            <p className="text-sm font-medium text-gray-700">{label}</p>
            <p className="mt-1 text-xs text-gray-500">
              {description || `Drag & drop or click to upload ${type === 'all' ? 'files' : type + 's'} (max ${maxSizeMB}MB)`}
            </p>
          </>
        )}
      </div>

      <input
        ref={inputRef}
        type="file"
        accept={accept || TYPE_ACCEPT[type]}
        multiple={multiple}
        onChange={(e) => handleFiles(e.target.files)}
        className="hidden"
      />

      {error && <p className="text-xs text-red-600">{error}</p>}

      {/* Existing files */}
      {existingFiles.length > 0 && (
        <div className="space-y-2">
          {existingFiles.map((f) => (
            <div key={f.id} className="flex items-center gap-3 rounded-lg border bg-white px-3 py-2">
              {/* Preview */}
              {f.type === 'image' ? (
                <img src={f.url} alt={f.name} className="h-12 w-12 rounded object-cover" />
              ) : f.type === 'video' ? (
                <div className="flex h-12 w-12 items-center justify-center rounded bg-gray-100">
                  <svg className="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </div>
              ) : (
                <div className="flex h-12 w-12 items-center justify-center rounded bg-gray-100">
                  <svg className="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
              )}

              <div className="min-w-0 flex-1">
                <p className="truncate text-sm font-medium">{f.name}</p>
                <p className="text-xs text-gray-400">{formatSize(f.size)} · {f.mimeType}</p>
              </div>

              <a href={f.url} target="_blank" rel="noreferrer" className="text-xs text-indigo-600 hover:underline">Open</a>

              {onRemove && (
                <button onClick={() => handleRemove(f.id)} className="text-gray-400 hover:text-red-600">
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M6 18L18 6M6 6l12 12" strokeLinecap="round" />
                  </svg>
                </button>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

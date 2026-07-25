import { useState, useEffect } from 'react';
import { certificationsApi } from '@/api/certifications';
import type { CertificationEntry } from '@/types/product';

export function useCertifications() {
  const [data, setData] = useState<CertificationEntry[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    certificationsApi.getAll().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

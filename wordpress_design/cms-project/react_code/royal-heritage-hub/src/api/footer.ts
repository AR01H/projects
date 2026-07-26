import { apiClient } from './client';
import { ENDPOINTS } from './endpoints';
import { MOCK_FOOTER } from '@/data/mockData';

export interface FooterLink {
  label: string;
  href: string;
  icon?: string;
}

export interface FooterSocialLink {
  platform: string;
  url: string;
  icon: string;
}

export interface FooterData {
  quickLinks: FooterLink[];
  policyLinks: FooterLink[];
  socialLinks: FooterSocialLink[];
  paymentMethods: { name: string; icon: string }[];
  trustBadges: { label: string; description: string; icon: string }[];
  certifications: { name: string; description: string }[];
  workingHours: { label: string; days: string; time: string; closed: string };
}

export const footerApi = {
  getAll: async (): Promise<FooterData> => {
    if (apiClient.useMock) return MOCK_FOOTER as FooterData;
    try {
      const res = await apiClient.get<{ data: FooterData }>(ENDPOINTS.footer.get);
      return res.data ?? (MOCK_FOOTER as FooterData);
    } catch { return MOCK_FOOTER as FooterData; }
  },
};

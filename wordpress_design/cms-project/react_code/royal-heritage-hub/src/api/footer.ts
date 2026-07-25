/**
 * Footer API — Footer data from mock or backend
 */

import { apiClient } from './client';
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
    return apiClient.get<FooterData>('/api/footer');
  },
};

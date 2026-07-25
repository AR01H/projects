/**
 * THEME REGISTRY
 * ---------------------------------------------------------------------------
 * To change the site's entire look, edit ACTIVE_THEME below — nothing else.
 * Each theme provides the full token set consumed by theme.css via CSS vars.
 * Add a new theme by adding another entry to THEMES and it becomes selectable.
 */

export interface ThemeTokens {
  name: string;
  colors: {
    primary: string;
    primaryLight: string;
    primaryDark: string;
    secondary: string;
    secondaryLight: string;
    secondaryDark: string;
    accent: string;
    bg: string;
    bgLight: string;
    bgCream: string;
    surface: string;
    dark: string;
    darkSoft: string;
    textPrimary: string;
    textSecondary: string;
    textMuted: string;
    border: string;
    borderSoft: string;
  };
  fonts: {
    display: string;
    serifAccent: string;
    sans: string;
    body: string;
  };
  radii: {
    card: string;
    btn: string;
  };
  texture: 'none' | 'wood-grain' | 'floral' | 'geometric' | 'confetti';
}

export const THEMES: Record<string, ThemeTokens> = {
  'royal-luxury': {
    name: 'Royal Luxury',
    colors: {
      primary: '#8B4513',
      primaryLight: '#A0623A',
      primaryDark: '#6B3410',
      secondary: '#C89B3C',
      secondaryLight: '#D9B968',
      secondaryDark: '#A67F2C',
      accent: '#A0522D',
      bg: '#FAF8F3',
      bgLight: '#FFFDF9',
      bgCream: '#F5F0E6',
      surface: '#FEFCF8',
      dark: '#2D2A26',
      darkSoft: '#46403A',
      textPrimary: '#2D2A26',
      textSecondary: '#6B6259',
      textMuted: '#9C9186',
      border: '#E8E0D0',
      borderSoft: '#F0EAE0',
    },
    fonts: {
      display: '"Playfair Display", Georgia, serif',
      serifAccent: '"Cormorant Garamond", Georgia, serif',
      sans: '"Poppins", system-ui, sans-serif',
      body: '"Inter", system-ui, sans-serif',
    },
    radii: { card: '1rem', btn: '0.5rem' },
    texture: 'wood-grain',
  },

  'vintage-heritage': {
    name: 'Vintage Heritage',
    colors: {
      primary: '#6B3F2A',
      primaryLight: '#8A5B3F',
      primaryDark: '#4E2C1C',
      secondary: '#B08D57',
      secondaryLight: '#C7AD7D',
      secondaryDark: '#8F6F3F',
      accent: '#7A4B2E',
      bg: '#F3ECDD',
      bgLight: '#FBF7EE',
      bgCream: '#EDE2CB',
      surface: '#F8F2E4',
      dark: '#2B2018',
      darkSoft: '#40332A',
      textPrimary: '#2B2018',
      textSecondary: '#5E4E3D',
      textMuted: '#948267',
      border: '#DCCBA8',
      borderSoft: '#E9DFC7',
    },
    fonts: {
      display: '"Cormorant Garamond", Georgia, serif',
      serifAccent: '"Playfair Display", Georgia, serif',
      sans: '"Poppins", system-ui, sans-serif',
      body: '"Inter", system-ui, sans-serif',
    },
    radii: { card: '0.375rem', btn: '0.25rem' },
    texture: 'floral',
  },

  'traditional-indian': {
    name: 'Traditional Indian',
    colors: {
      primary: '#A63D3D',
      primaryLight: '#C25858',
      primaryDark: '#7E2C2C',
      secondary: '#D4A017',
      secondaryLight: '#E3BC4C',
      secondaryDark: '#AB800F',
      accent: '#B85C38',
      bg: '#FFF8EF',
      bgLight: '#FFFDF7',
      bgCream: '#FBEBD4',
      surface: '#FFFCF5',
      dark: '#3A1F1F',
      darkSoft: '#522C2C',
      textPrimary: '#3A1F1F',
      textSecondary: '#6E4A3F',
      textMuted: '#A78878',
      border: '#F0D9B5',
      borderSoft: '#F7E9CE',
    },
    fonts: {
      display: '"Playfair Display", Georgia, serif',
      serifAccent: '"Cormorant Garamond", Georgia, serif',
      sans: '"Poppins", system-ui, sans-serif',
      body: '"Inter", system-ui, sans-serif',
    },
    radii: { card: '1.25rem', btn: '0.75rem' },
    texture: 'geometric',
  },

  'modern-minimal': {
    name: 'Modern Minimal',
    colors: {
      primary: '#22201D',
      primaryLight: '#42403C',
      primaryDark: '#0F0E0C',
      secondary: '#B08D57',
      secondaryLight: '#C7AD7D',
      secondaryDark: '#8F6F3F',
      accent: '#5A5650',
      bg: '#FFFFFF',
      bgLight: '#FFFFFF',
      bgCream: '#F5F5F3',
      surface: '#FAFAFA',
      dark: '#141414',
      darkSoft: '#2A2A2A',
      textPrimary: '#141414',
      textSecondary: '#5A5650',
      textMuted: '#9C9891',
      border: '#E5E3DE',
      borderSoft: '#F0EEE9',
    },
    fonts: {
      display: '"Inter", system-ui, sans-serif',
      serifAccent: '"Inter", system-ui, sans-serif',
      sans: '"Inter", system-ui, sans-serif',
      body: '"Inter", system-ui, sans-serif',
    },
    radii: { card: '0.25rem', btn: '0.125rem' },
    texture: 'none',
  },

  festive: {
    name: 'Festive',
    colors: {
      primary: '#A6293D',
      primaryLight: '#C24A5D',
      primaryDark: '#7E1A29',
      secondary: '#E0A526',
      secondaryLight: '#EDBE55',
      secondaryDark: '#B9840F',
      accent: '#2F6B4F',
      bg: '#FFF6EC',
      bgLight: '#FFFDF9',
      bgCream: '#FBE8D1',
      surface: '#FFFBF5',
      dark: '#321518',
      darkSoft: '#4A2226',
      textPrimary: '#321518',
      textSecondary: '#6B3A3D',
      textMuted: '#A9847F',
      border: '#F2D6A8',
      borderSoft: '#F8E7C7',
    },
    fonts: {
      display: '"Playfair Display", Georgia, serif',
      serifAccent: '"Cormorant Garamond", Georgia, serif',
      sans: '"Poppins", system-ui, sans-serif',
      body: '"Inter", system-ui, sans-serif',
    },
    radii: { card: '1.25rem', btn: '0.75rem' },
    texture: 'confetti',
  },
};

/**
 * ACTIVE THEME — change this single line to re-skin the entire site.
 */
export const ACTIVE_THEME_KEY: keyof typeof THEMES = 'royal-luxury';

export const activeTheme: ThemeTokens = THEMES[ACTIVE_THEME_KEY];

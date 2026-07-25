import { activeTheme } from './themes';

/**
 * Writes the active theme's tokens onto :root as CSS custom properties.
 * theme.css's @theme block provides fallback/default values (royal-luxury);
 * this overrides them at runtime so switching ACTIVE_THEME_KEY in themes.ts
 * is the only change needed to re-skin the site.
 */
export function applyTheme() {
  const root = document.documentElement;
  const { colors, fonts, radii } = activeTheme;

  const map: Record<string, string> = {
    '--color-primary': colors.primary,
    '--color-primary-light': colors.primaryLight,
    '--color-primary-dark': colors.primaryDark,
    '--color-secondary': colors.secondary,
    '--color-secondary-light': colors.secondaryLight,
    '--color-secondary-dark': colors.secondaryDark,
    '--color-accent': colors.accent,
    '--color-bg': colors.bg,
    '--color-bg-light': colors.bgLight,
    '--color-bg-cream': colors.bgCream,
    '--color-dark': colors.dark,
    '--color-dark-soft': colors.darkSoft,
    '--color-text-primary': colors.textPrimary,
    '--color-text-secondary': colors.textSecondary,
    '--color-text-muted': colors.textMuted,
    '--color-border': colors.border,
    '--color-border-soft': colors.borderSoft,
    '--font-display': fonts.display,
    '--font-serif-accent': fonts.serifAccent,
    '--font-sans': fonts.sans,
    '--font-body': fonts.body,
    '--radius-card': radii.card,
    '--radius-btn': radii.btn,
  };

  Object.entries(map).forEach(([key, value]) => root.style.setProperty(key, value));
  root.dataset.theme = activeTheme.name;
  root.dataset.texture = activeTheme.texture;
}

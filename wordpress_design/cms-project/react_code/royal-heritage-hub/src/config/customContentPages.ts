/**
 * CUSTOM CONTENT PAGES
 * ---------------------------------------------------------------------------
 * Add an entry here to create a new page at /pages/:pageKey without writing
 * any new page component. Each entry can render:
 *   - an iframe (set `iframeUrl`)
 *   - raw HTML (set `html` — use sparingly, only for trusted content)
 *   - or just a title/description with no embedded content
 *
 * Example: add a Google Form, a YouTube embed, a map, a third-party widget,
 * or an external tool by pointing iframeUrl at it.
 */

export interface CustomContentPage {
  pageKey: string;
  title: string;
  description?: string;
  iframeUrl?: string;
  /** Raw HTML block, rendered via dangerouslySetInnerHTML. Only use with trusted, sanitized content. */
  html?: string;
  /** Iframe height in pixels (width is always 100%) */
  iframeHeight?: number;
}

export const CUSTOM_CONTENT_PAGES: CustomContentPage[] = [
  {
    pageKey: 'store-locator',
    title: 'Find Us',
    description: 'Locate our workshop and partner stores.',
    iframeUrl: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3061.34!2d78.4867!3d16.6152',
    iframeHeight: 480,
  },
  {
    pageKey: 'virtual-tour',
    title: 'Virtual Workshop Tour',
    description: 'Take a look inside our Kondapalli workshop. (Add a YouTube/Vimeo embed URL below to activate.)',
    // iframeUrl: 'https://www.youtube.com/embed/VIDEO_ID',
    iframeHeight: 480,
  },
];

export function getCustomContentPage(pageKey: string): CustomContentPage | undefined {
  return CUSTOM_CONTENT_PAGES.find((p) => p.pageKey === pageKey);
}

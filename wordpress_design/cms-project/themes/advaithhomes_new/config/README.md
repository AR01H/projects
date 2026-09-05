# config/

Version-controlled **reference copies** of SEO/config files that are actually
served live by other means. None of the files in this folder are read by
WordPress or by the theme at runtime — keeping them here is purely so the
intended configuration is visible in git history and doesn't only exist as
live state on the production server.

| File | What actually serves it live |
|---|---|
| `robots.txt` | WordPress's virtual `/robots.txt` (the `do_robots` hook), fed by Rank Math. A physical file placed here has no effect - WordPress/Apache only look for a real `robots.txt` at the **site root** (next to `wp-config.php`), never inside `wp-content/themes/<theme>/`. |
| `sitemap-index-reference.xml` | Rank Math generates `/sitemap_index.xml`, `/post-sitemap.xml` and `/page-sitemap.xml` dynamically - nothing here is served directly. |
| `calculators-reference.xml` | Not served anywhere - a sitemap-shaped list of the calculator tool pages that are currently `status = 'active'` in the `wp_ah_calculators` table, for reference/planning (e.g. deciding whether these should be added to the real sitemap, since Rank Math currently excludes the `?ah_calc=` query-string form via `noindex`). |

**If you change robots.txt / sitemap behaviour**, do it in Rank Math's
settings (Search Console found it as `Sitemap: https://advaithhomes.co.uk/sitemap_index.xml`)
or in `wp_ah_calculators`, then update the matching file here to keep this
folder an accurate reference - editing only the file here changes nothing
live.

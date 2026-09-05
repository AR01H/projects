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
| `calculators-sitemap.xml` | **This one IS actually live** - unlike the other two files in this folder, `calculators-sitemap.xml` is served for real at `https://advaithhomes.co.uk/calculators-sitemap.xml` by `adn_serve_calculators_sitemap()` in `includes/core_routing.php`, which reads this exact file straight from the theme on every request. Lists the calculator tool pages currently `status = 'active'` in the `wp_ah_calculators` table, at their pretty URL form `/calculators/{key}/` - the full themed page (already `index,follow`), **not** the `?ah_calc={key}` bare iframe-embed document (which correctly stays `noindex`). |

`robots.txt` and `sitemap-index-reference.xml` both list
`calculators-sitemap.xml` as a second, independent `Sitemap:`/`<sitemap>`
entry alongside Rank Math's own `sitemap_index.xml` - it isn't nested
inside Rank Math's generated index (this theme has no hook into that), so
robots.txt's two separate `Sitemap:` lines are what actually let Google
discover it, once this folder's `robots.txt` content is pasted into Rank
Math's settings.

**Regenerate `calculators-sitemap.xml` by hand** whenever a calculator is
activated or deactivated in wp-admin - since it's read live from this exact
file, it does not update itself automatically.

**If you change robots.txt / sitemap behaviour**, do it in Rank Math's
settings (Search Console found it as `Sitemap: https://advaithhomes.co.uk/sitemap_index.xml`)
or in `wp_ah_calculators`, then update the matching file here to keep this
folder an accurate reference - editing only the file here changes nothing
live.

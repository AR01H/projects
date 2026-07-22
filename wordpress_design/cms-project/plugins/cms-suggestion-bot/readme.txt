=== CMS Suggestion Bot ===
Contributors: akileshr
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.2
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An independent CMS knowledge engine and suggestion bot. Reads, caches, and
indexes site content into a queryable knowledge base with an extensible
front-end chat widget - no theme or other plugin dependency, and no AI
required by default.

== Description ==

CMS Suggestion Bot scans your Pages, Posts, and any .txt/.md files dropped
into its /resources folder, turns that content into a fast, FULLTEXT-indexed
cache, and answers visitor questions from it through a floating chat widget -
plus a manually curated Knowledge Base for exact question/answer pairs.

Questions the bot can't answer are logged under Knowledge Base -> Unanswered
so you can review and fill them in later.

Everything works without AI by default. Configuration -> AI Approach lets you
optionally plug in a provider (OpenAI, Claude, Gemini, Ollama, or your own)
to handle unmatched questions instead - the architecture doesn't need to
change to add one.

= Key features =

* Reader + Cache pipeline with incremental (hash-based) regeneration
* FULLTEXT search - fast even as your content grows
* Manually curated Knowledge Base, with common questions cached for instant lookup
* Conversation memory - short follow-up questions carry context from earlier in the chat
* Page/post suggestions alongside every cache-sourced answer
* Restricted-word filtering (mask or block) for both indexed content and bot answers
* Per-visitor usage limits (session + daily caps)
* Optional AI provider hand-off for unmatched questions - off by default
* Full admin suite: Dashboard, Admin Tools, Configuration, Reader, Knowledge Base, Logs, API, Settings, Help

== Installation ==

1. Upload the `cms-suggestion-bot` folder to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to CMS Suggestion Bot -> Admin Tools -> Generate Cache to index your content.
4. Configure behaviour under CMS Suggestion Bot -> Configuration.

== Changelog ==

= 0.1.0 =
* Initial release.

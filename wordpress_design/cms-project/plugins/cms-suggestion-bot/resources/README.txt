Drop .txt or .md files in this folder to have them read as an extra
knowledge source, alongside Pages/Posts/etc.

- Handled by: app/Readers/FileReader.php
- Picked up on the next "Generate Cache" / "Rebuild Cache" run (Admin Tools),
  or automatically on the Reader's scheduled scan if enabled in Configuration.
- Supported extensions: .txt, .md
- For .md files, the first line (if it's a Markdown heading, e.g. "# Title")
  is used as the entry's title; otherwise the filename is used.
- This folder is blocked from direct web access (see .htaccess) - files are
  only ever read server-side.
- Subfolders are supported and scanned recursively.

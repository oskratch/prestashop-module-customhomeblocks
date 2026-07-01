# TODO / Roadmap

Possible improvements for future versions, roughly ordered by priority.

---

## Back office UX

- **Drag & drop reordering** — replace the up/down arrow buttons with drag handles using SortableJS (already bundled in PrestaShop admin). Requires a small AJAX endpoint to persist the new order without a full page reload.

- **Duplicate block** — a "Copy" button in the block list that creates a clone of an existing block as a starting point for a new one.

- **CodeMirror editor** — replace the plain `<textarea>` with a proper HTML editor (syntax highlighting, tag auto-close, bracket matching). Can be loaded from a CDN or bundled.

---

## Features

- **Scheduled visibility** — set a date range (from / to) per block so seasonal content (promotions, banners) is shown and hidden automatically without manual intervention.

- **Multi-language content** — store a different HTML body per PrestaShop language, the same way native modules handle multilingual content.

- **Support additional hooks** — allow attaching blocks to other display hooks beyond `displayHome` (e.g. `displayTop`, `displayFooter`, `displayLeftColumn`). Would require storing the target hook per block or registering hooks selectively.

- **Import / export** — download all blocks as a JSON file and re-import them, useful for moving content between environments (dev → staging → production).

---

## Code & infrastructure

- **Translation files** — add `translations/` and `_ModuleDirectory_en.php` / `_ModuleDirectory_ca.php` etc. so the back office labels are translatable via the PrestaShop translation system.

- **GitHub Actions: automated zip** — add a workflow step that, on each release, packages the `customhomeblocks/` folder into a `.zip` ready to attach to the GitHub Release for easy download and install.

# Custom Home Blocks

![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)
![PrestaShop: 1.7+](https://img.shields.io/badge/PrestaShop-1.7%2B-informational)
![Version](https://img.shields.io/badge/version-1.0.2-green)

A PrestaShop module to add custom HTML content blocks to the homepage via the `displayHome` hook. Blocks are managed from the back office — no database tables required.

## Features

- Add, edit, delete and reorder multiple HTML blocks from the back office
- Enable/disable individual blocks without deleting them
- Content preview in the block list so you can identify blocks at a glance
- Plain monospace HTML editor — all tags, attributes and inline styles are preserved exactly as written, no WYSIWYG
- No extra database tables — stored via the PrestaShop `Configuration` API
- Can be attached to multiple hook positions (e.g. both top and bottom of `displayHome`)

## Requirements

- PrestaShop 1.7 or later (tested on PS 9)
- PHP 7.1 or later

## Installation

1. Copy the `customhomeblocks/` folder into your PrestaShop `/modules/` directory.
2. Go to **Modules > Module Manager** and install **Custom Home Blocks**.
3. Go to **Design > Positions**, click **Attach a module**, select **Custom Home Blocks** and attach it to the `displayHome` hook.

## Configuration

Go to **Modules > Module Manager**, find "Custom Home Blocks" and click **Configure**. From there:

1. Click **Add block** to create a new content block.
2. Fill in:
   - **Block title** — an internal label to identify the block in the back office. Not shown on the front office.
   - **HTML content** — paste your raw HTML. All tags, attributes and inline styles are preserved exactly as written.
3. Click **Save**.

All active blocks are rendered in order at the hook position. Use the up/down arrows to reorder them, and the **Active/Disabled** toggle to show or hide individual blocks without deleting them.

To display the blocks in multiple positions (e.g. top and bottom of the homepage), go to **Design > Positions** and attach the module again to a different hook position. Both instances will render the same set of blocks.

## Module Structure

```
customhomeblocks/
├── customhomeblocks.php
├── config.xml
└── views/
    └── templates/
        └── hook/
            └── block.tpl
```

## Support

Open an issue on GitHub or contact `info@metalinked.net`.

## License

This module is licensed under the [GNU General Public License v2 or later](LICENSE).

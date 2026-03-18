# Custom Home Blocks

**Version:** 1.0.0  
**Compatible with PrestaShop:** 9.x (tested on PS9)

## Description

This module allows you to add multiple custom HTML content blocks to the homepage via the `displayHome` hook. Each module instance can display its own independent HTML content, configurable directly from the back office — no database tables required.

## Features

- Supports multiple hook placements (`allow_push = true`) — add as many blocks as you need from **Design > Positions**
- Each instance stores its own title and HTML content
- Back office form with a plain monospace HTML editor (no WYSIWYG — tags are preserved as-is)
- Smarty template output wrapped in a Bootstrap-compatible `div.custom-home-block`
- No database tables — uses PrestaShop `Configuration` API with base64-safe storage

## Installation

1. Upload the `customhomeblocks/` folder to your PrestaShop `/modules/` directory.
2. Go to **Modules > Module Manager** and install **Custom Home Blocks**.
3. Go to **Design > Positions**, click **Attach a module**, select **Custom Home Blocks** and attach it to the `displayHome` hook.

## Configuration

After installation, go to **Modules > Module Manager**, find "Custom Home Blocks" and click **Configure**. Then:

1. Click **Add block** to create a new content block.
2. Fill in:
   - **Block Title** — an internal label to identify the block in the back office.
   - **Custom HTML Content** — paste your raw HTML directly. Tags are preserved exactly as written.
3. Click **Save**.

To display multiple independent blocks, go to **Design > Positions** and attach the module again to `displayHome` as many times as needed. Each attachment is a separate, independently configurable instance.

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

If you need help or have questions about this module, you can contact `info@metalinked.net`.

## License

This module is licensed under the GPLv2 or later. See [LICENSE](LICENSE) for details.

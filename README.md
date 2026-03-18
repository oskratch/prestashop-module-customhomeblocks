# Custom Home Blocks

**Version:** 1.0.0  
**Compatible with PrestaShop:** 1.7.x – 9.x

## Description

This module allows you to add multiple custom HTML content blocks to the homepage via the `displayHome` hook. Each module instance can display its own independent HTML content, configurable directly from the back office — no database tables required.

## Features

- Supports multiple hook placements (`allow_push = true`)
- Each instance stores its own title and HTML content, keyed by module ID
- Back office form with TinyMCE rich-text editor support
- Smarty template output wrapped in a Bootstrap-compatible `div.custom-home-block`
- No database tables — uses PrestaShop `Configuration` API

## Installation

1. Upload the `customhomeblocks/` folder to your PrestaShop `/modules/` directory.
2. Go to **Modules > Module Manager** and install **Custom Home Blocks**.
3. Go to **Design > Positions** and drag the module into the `displayHome` hook.

## Configuration

After installation, go to **Modules > Module Manager**, find "Custom Home Blocks" and click **Configure**. Fill in:

- **Block Title** — an internal label to identify the block in the back office.
- **Custom HTML Content** — the HTML to display on the homepage (TinyMCE editor loaded automatically).

To display multiple blocks with different content, drag the module multiple times into the `displayHome` hook from **Design > Positions**.

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

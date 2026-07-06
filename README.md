# RY Invoice for Amego

A WordPress plugin that integrates [Amego](https://www.amego.com.tw/) E-invoice with WooCommerce, enabling Taiwan's government e-invoice system for your online store.

## Requirements

- **WordPress**: 6.8 or higher
- **PHP**: 8.2 or higher
- **WooCommerce**: required for invoice functionality

## Installation

1. Download or clone this repository into your WordPress plugins directory:
   ```
   wp-content/plugins/ry-invoice-for-amego/
   ```
2. Log in to your WordPress admin panel and navigate to **Plugins**.
3. Activate **RY Invoice for Amego**.
4. Navigate to **RY Invoice → Amego options** and enter your Amego API credentials (see [Configuration](#configuration)).
5. Activate your plugin license under **RY Invoice → License**.

## Usage

Once the plugin is installed, activated, and licensed, it adds an **Invoice** section to the WooCommerce checkout page. Customers can choose from the following invoice options:

### Invoice Types

| Type | Description |
|------|-------------|
| `personal` | Personal e-invoice (stored in carrier) |
| `company` | Company/Business invoice (requires tax ID) |
| `donate` | Donate invoice to a charity |

### Carrier Types (Personal Invoice)

| Type | Description |
|------|-------------|
| `amego_host` | Amego cloud carrier (paper invoice mailed on lottery win) |
| `MOICA` | Government citizen digital certificate |
| `phone_barcode` | Mobile barcode carrier |

### Example: Placing an Order with an Invoice

1. A customer proceeds to the WooCommerce checkout page.
2. In the **Invoice** section, the customer selects:
   - **Invoice type**: `personal`
   - **Carrier type**: `phone_barcode`
   - **Carrier number**: `/ABC1234`
3. Upon order completion, the plugin issues the e-invoice through the Amego API and stores it against the order.

## Configuration

Navigate to **RY Invoice** in the WordPress admin menu to access the settings pages.

### Amego Options (`RY Invoice → Amego options`)

| Setting | Description |
|---------|-------------|
| **Test mode** | Enable sandbox/test mode for the Amego API |
| **Invoice** | Your Amego invoice identifier |
| **AppKey** | Your Amego API application key |
| **Log** | Enable debug logging (logs stored in WooCommerce logs) |

### General Settings (`RY Invoice → General`)

| Setting | Description |
|---------|-------------|
| **Count precision** | Decimal places for item quantities |
| **Amount precision** | Decimal places for monetary amounts |

## Development

### Prerequisites

- [Node.js](https://nodejs.org/) (LTS recommended)
- [npm](https://www.npmjs.com/)
- [WP-CLI](https://wp-cli.org/) (required for i18n commands)

### Setup

```bash
npm install
```

### Available Scripts

| Command | Description |
|---------|-------------|
| `npm start` | Start webpack in watch/development mode |
| `npm run build` | Build assets and generate translation files (`.pot` / `.po`) |
| `npm run build:all` | Build assets and generate all translation artefacts |
| `npm run build:assets` | Build JavaScript/CSS assets with webpack |
| `npm run build:i18n` | Generate all i18n files (`.pot`, `.po`, `.mo`, `.php`, `.json`) |
| `npm run update` | Update npm dependencies |

### Project Structure

```
ry-invoice-for-amego/
├── admin/              # WordPress admin UI (pages, AJAX handlers)
├── assets/             # Compiled JS/CSS assets (build output)
├── assets-src/         # Source JS/CSS files
├── includes/           # Core plugin classes and abstracts
│   ├── abstracts/      # Abstract base classes
│   ├── composer/       # Composer-managed dependencies
│   └── ry-general/     # Shared utility classes (logging, etc.)
├── languages/          # Translation files (.pot, .po, .mo, .json)
├── woocommerce/        # WooCommerce integration
├── package.json
├── webpack.config.js
└── ry-invoice-for-amego.php   # Plugin entry point
```

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository and create a feature branch from `main`.
2. Make your changes, following the existing coding style (PSR-style PHP, WordPress coding standards).
3. Run `npm run build` before committing to ensure compiled assets are up to date.
4. Open a pull request with a clear description of the change and the motivation for it.

Please report bugs and feature requests via [GitHub Issues](https://github.com/RicherYang/RY-Invoice-for-Amego/issues).

## License

This plugin is licensed under the [GNU General Public License v3.0](LICENSE).

---

**Plugin URI**: https://ry-plugin.com/ry-invoice-for-amego  
**Author**: [Richer Yang](https://richer.tw/)

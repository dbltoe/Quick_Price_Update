# Quick Price Update

Find products by exact ID, ID range or category, change their prices and quantities in one table, and save them all at once — on **Zen Cart 1.5.8a through 3.0.0** and **PHP 7.4 through 8.5**, with no core files changed.

- **Catalog > Quick Price Update**: search by product IDs (`12, 15, 40-55`), by category with or without its subcategories, by name or model, and by status; sort and page the results.
- Edit prices and quantities in the table; changed rows are highlighted; one **Save Changes** writes only those rows.
- Values are checked before anything is written. A row someone else changed after the page loaded is skipped and reported, never overwritten.
- Saving a price refreshes Zen Cart's price-sort value, so storefront price sorting and "Starting at" prices stay right.
- Specials, priced-by-attributes, Call for Price and Always Free products are flagged.
- A currency icon on **Catalog > Categories/Products** opens any product, or any category with its subcategories. The icon follows the store's default currency.

## Install

1. Copy `zc_plugins/QuickPriceUpdate` into your store's `zc_plugins/` folder.
2. **Modules > Plugin Manager** → **Quick Price Update** → **Install**.
3. Open **Catalog > Quick Price Update**.

See [docs/INSTALL.md](docs/INSTALL.md), [docs/CONFIGURATION.md](docs/CONFIGURATION.md), [docs/CUSTOMIZING.md](docs/CUSTOMIZING.md) and [docs/COMPATIBILITY.md](docs/COMPATIBILITY.md). The full read-me is `readme.html`.

## Credits

Inspired by ianhg's *Quick Price Update* (Plugins Library 2338) and *Quick Price & Quantity Update in Admin* (2358), and Scott Wilson's *Quick Quantity Update* (1847), all core-file edits for Zen Cart 1.5.8 or earlier. This is a new, encapsulated plugin written from scratch.

## License

GNU General Public License v2.0 — see [LICENSE](LICENSE). © 2026 My Zen Cart Host (dbltoe).

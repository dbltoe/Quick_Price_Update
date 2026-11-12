# Quick Price Update

Version 3.0.2 · Zen Cart 1.5.8a through 3.0.0 · PHP 7.4 through 8.5 · GPL v3 · by My Zen Cart Host (dbltoe)

Quick Price Update adds **Catalog > Quick Price Update** to your admin. Find the products you want, change their prices and quantities right in the table, and save every change with one button. No core files are changed, and there are no settings to set.

## What It Does

- **Find products by exact ID.** Type one ID, a list, or ranges: `12, 15, 40-55`. (Zen Cart's own search matches IDs loosely, so searching for 12 also finds 112 and 120.)
- **Find products by category**, with or without everything in its subcategories. Products linked into a category are included.
- **Narrow it down** by name or model and by Active/Inactive, sort by sort order, ID, name, model, price or quantity, and show 20, 50, 100 or 200 per page.
- **Change prices and quantities in the table.** Changed rows are highlighted and counted. One *Save Changes* writes only the rows you changed.
- **Every value is checked first.** Prices take up to 4 decimal places and can't be negative; quantities take up to 4 decimal places. `1,234.50` is understood; `12,50` is refused rather than misread. A row that doesn't pass is not saved, and it comes back highlighted with what you typed.
- **Two people can't overwrite each other.** If a product's price or quantity changed somewhere else after your page loaded, that row is skipped and you're told which products.
- **The storefront stays right.** Saving a price refreshes the value Zen Cart sorts by price with, so price sorting and "Starting at" prices update too.
- **Prices that shoppers don't pay as shown are flagged**: products on special (with the special price), priced by attributes, Call for Price, and Always Free.
- **A currency icon on Catalog > Categories/Products.** Click it on a product row to open that product here, or on a category row to open that category with its subcategories. The icon follows your store's default currency: `$`, `€`, `£`, `¥`, `₹` and 20 more. A currency with no icon of its own shows its symbol instead (`kr`, `CHF`, `zł`).

## Installing

1. Unzip the package. Copy the `zc_plugins/QuickPriceUpdate` folder into your store's `zc_plugins/` folder, so you have `zc_plugins/QuickPriceUpdate/v3.0.2/manifest.php`.
2. In your admin, go to **Modules > Plugin Manager**, select **Quick Price Update** and click **Install**.
3. Open **Catalog > Quick Price Update**.

Admins other than superusers need the page added to their admin profile (**Admins > Admin Profiles**, under Catalog). The currency icons only show for admins who can open the page.

Once it's installed, a superuser can open this guide from the **ReadMe** button in Plugin Manager.

## Upgrading and Uninstalling

To upgrade, copy the new version's folder beside the old one in `zc_plugins/QuickPriceUpdate/` and click **Upgrade** in Plugin Manager. To remove it, click **Uninstall**: that takes the page off the menu. The plugin creates no tables and stores no settings, so nothing else is left behind. Prices and quantities you saved stay as you saved them.

## Good to Know

> This page changes each product's own price field, the one on the product's edit page. It doesn't change specials, sale prices, quantity discounts or attribute prices. The notes column shows when one of those applies.

- Prices are in your default currency, the same as on the product edit page.
- If you leave the page with unsaved changes, your browser asks first.
- Each save is recorded in **Tools > Admin Activity Log** with the product IDs that changed.
- A search can name up to 1,000 product IDs at a time.

## Compatibility

| What | Supported |
| --- | --- |
| Zen Cart | 1.5.8a, 2.0.x, 2.1.0, 2.2.x, 2.3 and 3.0.0 |
| PHP | 7.4 through 8.5 (whatever your Zen Cart version supports) |
| Core files changed | None. The icons use notifiers that exist, with the same parameters, on every version above. |
| Templates | Admin only; your storefront template doesn't matter. |

## More in the Package

The download's `docs` folder goes further: `INSTALL.md` (installing, admin access, upgrading), `CONFIGURATION.md` (every search field, what the price and quantity boxes accept, the notes column), `CUSTOMIZING.md` (the notifiers an add-on can use) and `COMPATIBILITY.md`.

## Support

Questions and problem reports go in the Quick Price Update support thread (https://www.zen-cart.com/threads/207447?page=1#post-1347749) on the Zen Cart forum, which is also linked from the plugin's Plugin Manager panel. Please include your Zen Cart and PHP versions, and any `myDEBUG-` file from your store's `logs/` folder from that moment. Source and releases are on GitHub (https://github.com/dbltoe/Quick_Price_Update).

## Changelog

See `CHANGELOG.md`. Version 3.0.2 moves the plugin to the GNU General Public License, version 3, and replaces readme.html with this guide, opened from the ReadMe button in Plugin Manager.

## License

Quick Price Update is free software under the GNU General Public License, version 3 (https://www.gnu.org/licenses/gpl-3.0.html); the full text is in `LICENSE`. Copyright © 2026 My Zen Cart Host (dbltoe).

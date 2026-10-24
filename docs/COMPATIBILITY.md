# Compatibility

| | Supported |
|---|---|
| Zen Cart | 1.5.8a, 2.0.x, 2.1.0, 2.2.x, 2.3, 3.0.0 |
| PHP | 7.4 through 8.5, within what your Zen Cart version supports |
| Core files changed | None |
| Database | No tables or columns added; one admin-menu record |

## Why it works on every version

- **The icons** use `NOTIFY_ADMIN_PROD_LISTING_ADD_ICON` (product rows) and `NOTIFY_ADMIN_PROD_LISTING_ADD_ICON_CATEGORY` (category rows) in `admin/category_product_listing.php`. Both exist with the same parameters on every version above.
- **The icon set** is Font Awesome Free 6.4.0, which every admin from 1.5.8a to 3.0.0 loads.
- **The page name** is defined in `admin/includes/extra_datafiles/` as well as in the plugin's root `filenames.php`, because Zen Cart before 2.2.0 doesn't load the root one.
- **The search boxes** are registered with core's admin request sanitizer, scoped to this page, so `&` and quotes reach the search as typed.

## Zen Cart 1.5.8 (not 1.5.8a)

Plain 1.5.8 has the same two notifiers, but testing is on 1.5.8a (the 1.5.8 maintenance line). Zen Cart recommends 1.5.8a over 1.5.8 in any case.

## Other plugins

Anything that also adds row icons on Categories/Products works alongside it: each plugin appends its own. If another plugin replaces `admin/category_product_listing.php` with a copy that lacks the two notifiers, the icons won't show, but Catalog > Quick Price Update still works.

**Replacing ianhg's Quick Price Update or Quick Price & Quantity Update:** those replace core's `admin/category_product_listing.php` with an edited copy of the 1.5.8 file. On 1.5.8a and later that copy also undoes core fixes made since. Put core's own file for your version back (from the Zen Cart release you run), remove the language lines their instructions had you add, then install this plugin.

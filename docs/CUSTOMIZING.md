# Customizing Quick Price Update

## Wording

All text is in two array-format language files inside the plugin:

- `admin/includes/languages/english/lang.quick_price_update.php`: the page.
- `admin/includes/languages/english/extra_definitions/lang.quick_price_update_menu.php`: the menu label and the icon tooltips, which every admin page needs.

To translate, copy both into a folder named for your admin language (for example `languages/french/`) and translate the values.

## Notifiers for add-ons

The page raises four notifiers so another plugin (Quick Price Update Pro, or your own) can extend it without editing it:

| Notifier | Parameters | Use |
|---|---|---|
| `NOTIFY_QUICK_PRICE_UPDATE_ACTION` | `$action` (the `action` query value), `&$filters` | Handle an action of your own before the page's; redirect when done. |
| `NOTIFY_QUICK_PRICE_UPDATE_FILTERS_FORM` | `$filters`, `&$html` | Append form groups to the search form. Read your fields from `$_GET` yourself. |
| `NOTIFY_QUICK_PRICE_UPDATE_SEARCH` | `$filters`, `&$extraWhere` (array) | Add SQL conditions. Each must be a complete fragment you have escaped yourself; tables are aliased `p` (products) and `pd` (products_description). |
| `NOTIFY_QUICK_PRICE_UPDATE_PRODUCT_UPDATED` | `['products_id', 'price_old', 'price_new', 'qty_old', 'qty_new']` | Fires once per saved row, after the write (for example, to keep a change history). Values are strings with 4 decimal places. |

Keep any fields you add on the search form prefixed (for example `qpup_`) so they don't collide with the page's own `qpu_` fields.

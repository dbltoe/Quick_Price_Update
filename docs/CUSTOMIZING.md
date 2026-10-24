# Customizing Quick Price Update

## Wording

All text is in two array-format language files inside the plugin:

- `admin/includes/languages/english/lang.quick_price_update.php`: the page.
- `admin/includes/languages/english/extra_definitions/lang.quick_price_update_menu.php`: the menu label and the icon tooltips, which every admin page needs.

To translate, copy both into a folder named for your admin language (for example `languages/french/`) and translate the values.

## Notifiers for add-ons

The page raises these notifiers so another plugin (Quick Price Update Pro, or your own) can extend it without editing it. All of them are in `admin/quick_price_update.php` except the two marked as being in the class.

| Notifier | Parameters | Use |
|---|---|---|
| `NOTIFY_QUICK_PRICE_UPDATE_ACTION` | `$action` (the `action` query value), `&$filters` | Handle an action of your own before the page's; redirect when done. |
| `NOTIFY_QUICK_PRICE_UPDATE_FILTERS_FORM` | `$filters`, `&$html` | Append form groups to the search form. Read your fields from `$_GET` yourself. |
| `NOTIFY_QUICK_PRICE_UPDATE_SEARCH` (class) | `$filters`, `&$extraWhere` (array) | Add SQL conditions. Each must be a complete fragment you have escaped yourself; tables are aliased `p` (products) and `pd` (products_description). |
| `NOTIFY_QUICK_PRICE_UPDATE_ROWS` (since 3.0.1) | `$filters`, `&$found` (`rows`, `total`, `page`, `pages`) | Add data of your own to the rows on the page, with one query for all of them. |
| `NOTIFY_QUICK_PRICE_UPDATE_PANELS` (since 3.0.1) | `$filters`, `&$html`, `$found` | Add panels between the search form and the table. They sit outside both of the page's forms, so they can carry forms of their own; an input in the table can join one with the HTML `form="..."` attribute. |
| `NOTIFY_QUICK_PRICE_UPDATE_TABLE_HEADINGS` (since 3.0.1) | `$filters`, `&$headings` (`['before' => [], 'after' => []]`) | Add columns before the ID column or after Quantity. Each entry is `['text' => heading HTML, 'class' => CSS classes]`. |
| `NOTIFY_QUICK_PRICE_UPDATE_TABLE_CELLS` (since 3.0.1) | `$row`, `&$cells` (same shape) | The matching cells for each row; give every row the same number of cells as your headings. |
| `NOTIFY_QUICK_PRICE_UPDATE_SAVE_RESULT` (since 3.0.1) | `$filters`, `&$result` | Save your own columns after the page saves prices and quantities, and add your products to `$result['updated']`, `['conflict']`, `['missing']` (IDs) or `['invalid']` (`id => []`, or `id => ['price' => ..., 'qty' => ...]` to re-show typed values), so one set of messages covers the whole save. |
| `NOTIFY_QUICK_PRICE_UPDATE_PRODUCT_UPDATED` (class) | `['products_id', 'price_old', 'price_new', 'qty_old', 'qty_new']` | Fires once per saved row, after the write (for example, to keep a change history). Values are strings with 4 decimal places. |

Keep any fields you add prefixed (for example `qpup_`) so they don't collide with the page's own `qpu_` fields.

**Inputs in the table.** Give an input or select in your cells the class `qpu-input`, `data-was="<value as loaded>"` and `data-row="qpuRow<products_id>"`, and the page highlights and counts its changes like its own. When the form is submitted, every field with a `data-row` in a row that didn't change is left out of the post (so a full page stays under PHP's `max_input_vars`), so put `data-row` on your hidden fields too.

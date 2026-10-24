# Using Quick Price Update

There are no settings. Everything is on the page itself.

## Finding products

| Field | What it does |
|---|---|
| Product IDs | One or more IDs or ranges: `12`, `12, 15`, `40-55`, `12 15 40-55`. Exact matches only. Up to 1,000 IDs per search. |
| Category | Products in that category, including products linked into it from elsewhere. **Include Subcategories** adds everything below it. |
| Name or Model | Every word must appear in the product's name (in the admin's language) or its model. |
| Status | Active and Inactive, Active Only, or Inactive Only. |
| Sort By / Direction | Sort Order (the default, like Categories/Products), ID, Name, Model, Price or Quantity. |
| Per Page | 20, 50, 100 or 200 rows. |

All the fields combine: IDs 1-500 in category Hardware, active only, for example. **Reset** clears them. The search is part of the page address, so you can bookmark one.

## Changing prices and quantities

Type in the Price and Quantity boxes. A changed box turns bold and its row turns yellow; the count beside **Save Changes** says how many rows will be saved. Click **Save Changes** once you're done with the page; going to another page of results first will lose the changes (your browser warns you).

What is accepted:

- **Price**: up to 11 digits before the decimal point and 4 after; not negative. `12`, `12.5`, `12.50`, `.99`, `1,234.50`. Not `12,50` (comma as decimal point), `$12` or `12.34567`.
- **Quantity**: the same, and negative quantities are allowed, as on the product edit page.

After saving, the page tells you how many products were updated and lists any that weren't:

- **Not a number the field can hold**: the row comes back highlighted with what you typed, so you can fix it and save again.
- **Changed somewhere else after this page was loaded**: someone (or an order) changed the price or quantity meanwhile. Nothing is overwritten; the row shows the current values.
- **No longer exists**: the product was deleted meanwhile.

## The notes column

The price box is the product's own price. A note means shoppers don't pay that price as it stands:

- **Special: $x**: an active special sets what shoppers pay.
- **Priced by Attributes**: the price shown to shoppers comes from the attributes.
- **Call for Price** and **Always Free**.

Specials, attribute prices and quantity discounts are edited on their own admin pages, as usual.

## The currency icon

On **Catalog > Categories/Products**, each product row shows the icon beside its status squares, and each category row has it as a button beside Edit, Delete and Move. The icon is your default currency's sign when Font Awesome has one (25 signs, covering 43 currencies); otherwise it's the currency's symbol from **Localization > Currencies** (for example `kr` or `CHF`).

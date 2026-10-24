# Changelog

## v1.0.0 — 2026-10-24

First release. An encapsulated rewrite of the idea behind ianhg's Quick Price Update (Library 2338) and Quick Price & Quantity Update (2358) and Scott Wilson's Quick Quantity Update (1847), for Zen Cart 1.5.8a through 3.0.0 on PHP 7.4 through 8.5. No code from those plugins is reused and no core file is changed.

- Catalog > Quick Price Update: find products by exact ID or ID ranges, by category with or without its subcategories, by name or model, and by status; sort and page the results.
- Change prices and quantities in the table and save every changed row with one button. Values are checked first; a row changed elsewhere after the page loaded is skipped and reported.
- Saving a price refreshes the storefront price-sort value.
- Specials, priced-by-attributes, Call for Price and Always Free products are flagged.
- A currency icon on Catalog > Categories/Products (product and category rows) that follows the store's default currency.

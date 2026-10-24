# Installing Quick Price Update

## New install

1. Unzip the package.
2. Copy `zc_plugins/QuickPriceUpdate/` into your store's `zc_plugins/` folder, so you have `zc_plugins/QuickPriceUpdate/v3.0.0/manifest.php`.
3. In the admin, go to **Modules > Plugin Manager**, select **Quick Price Update**, and click **Install**.
4. The page appears under **Catalog > Quick Price Update**.

No files outside `zc_plugins/` are added or changed, and no database tables are created. The installer adds one record to the admin menu (`catalogQuickPriceUpdate`).

## Admin access

Superusers can use the page at once. For other admins, add **Quick Price Update** to their profile under **Admins > Admin Profiles** (it's in the Catalog group). The currency icons on Catalog > Categories/Products show only for admins who can open the page.

## Upgrading

Copy the new version folder (for example `v3.0.1/`) into `zc_plugins/QuickPriceUpdate/` beside the old one, then click **Upgrade** in Plugin Manager. Once it reports the new version, you can delete the old version's folder.

## Uninstalling

Click **Uninstall** in Plugin Manager. That removes the menu entry. Prices and quantities you saved stay as they are; there's nothing else to clean up. Delete `zc_plugins/QuickPriceUpdate/` afterwards if you like.

<?php
/**
 * Quick Price Update -- puts the currency icon on Catalog > Categories/Products.
 *
 * A product row's icon opens that product on Catalog > Quick Price Update; a
 * category row's opens the category with its subcategories. Both notifiers
 * exist, with the same parameters, on every release from 1.5.8a to 3.0.0, so
 * no core file is changed.
 *
 * Core finds this file by its name (init_observers.php scans an installed
 * plugin's observers/ folder for auto.*.php) and constructs the class
 * camelize() derives from it. It must never be listed in an auto_loader too:
 * that includes it twice and fatals every admin page.
 *
 * @package  QuickPriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// Guarded: during a Plugin Manager upgrade the old version's copy of the class
// may already be loaded from its own folder.
if (!class_exists('QuickPriceUpdate', false)) {
    require dirname(__DIR__) . '/QuickPriceUpdate.php';
}

class zcObserverQuickPriceUpdate extends base
{
    public function __construct()
    {
        $this->attach($this, [
            'NOTIFY_ADMIN_PROD_LISTING_ADD_ICON',
            'NOTIFY_ADMIN_PROD_LISTING_ADD_ICON_CATEGORY',
        ]);
    }

    /**
     * A product row: the icon sits with the row's status squares.
     */
    public function notify_admin_prod_listing_add_icon(&$class, $eventID, $product, &$additional_icons)
    {
        if (!is_array($product) || empty($product['products_id']) || !QuickPriceUpdate::canAccess()) {
            return;
        }
        $title = $this->title(QPU_ICON_PRODUCT_TITLE);
        $additional_icons .= '<a href="' . zen_href_link(FILENAME_QUICK_PRICE_UPDATE, 'qpu_ids=' . (int)$product['products_id']) . '"'
            . ' title="' . $title . '" aria-label="' . $title . '" style="margin-right:4px;text-decoration:none;">'
            . QuickPriceUpdate::iconMarkup() . '</a>';
    }

    /**
     * A category row: a button beside Edit, Delete and Move.
     */
    public function notify_admin_prod_listing_add_icon_category(&$class, $eventID, $category, &$additional_icons)
    {
        if (!is_array($category) || !isset($category['categories_id']) || !QuickPriceUpdate::canAccess()) {
            return;
        }
        $title = $this->title(QPU_ICON_CATEGORY_TITLE);
        $additional_icons .= '<a href="' . zen_href_link(FILENAME_QUICK_PRICE_UPDATE, 'qpu_cid=' . (int)$category['categories_id'] . '&qpu_sub=1') . '"'
            . ' class="btn btn-sm btn-default btn-quick-price-update" role="button"'
            . ' title="' . $title . '" aria-label="' . $title . '">'
            . QuickPriceUpdate::iconMarkup() . '</a>';
    }

    /**
     * "Quick Price Update (EUR)", escaped for an attribute.
     */
    protected function title($pattern)
    {
        $badge = QuickPriceUpdate::badge();
        return zen_output_string_protected(sprintf($pattern, $badge['code'] !== '' ? $badge['code'] : '-'));
    }
}

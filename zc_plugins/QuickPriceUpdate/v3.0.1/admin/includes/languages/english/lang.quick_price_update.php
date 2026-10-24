<?php
/**
 * Quick Price Update -- Catalog > Quick Price Update page strings (English).
 *
 * Titles, column headings and buttons are Title Case; help text and messages
 * are sentences.
 *
 * @package  QuickPriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

$define = [
    'HEADING_TITLE' => 'Quick Price Update',
    'QPU_INTRO' => 'Find products by ID, category, name or model, change their prices and quantities in the table, then save them all at once. Prices are in your default currency (%s). Only the rows you change are written.',

    // The search form.
    'QPU_LEGEND_FIND' => 'Find Products',
    'QPU_LABEL_IDS' => 'Product IDs',
    'QPU_HELP_IDS' => 'One or more IDs or ranges, e.g. 12, 15, 40-55',
    'QPU_LABEL_CATEGORY' => 'Category',
    'QPU_CATEGORY_ANY' => 'All Categories',
    'QPU_LABEL_SUBCATEGORIES' => 'Include Subcategories',
    'QPU_LABEL_KEYWORDS' => 'Name or Model',
    'QPU_LABEL_STATUS' => 'Status',
    'QPU_STATUS_ANY' => 'Active and Inactive',
    'QPU_STATUS_ON' => 'Active Only',
    'QPU_STATUS_OFF' => 'Inactive Only',
    'QPU_LABEL_SORT' => 'Sort By',
    'QPU_SORT_SORT' => 'Sort Order',
    'QPU_SORT_ID' => 'ID',
    'QPU_SORT_NAME' => 'Name',
    'QPU_SORT_MODEL' => 'Model',
    'QPU_SORT_PRICE' => 'Price',
    'QPU_SORT_QTY' => 'Quantity',
    'QPU_LABEL_DIRECTION' => 'Direction',
    'QPU_DIR_ASC' => 'Ascending',
    'QPU_DIR_DESC' => 'Descending',
    'QPU_LABEL_PER_PAGE' => 'Per Page',
    'QPU_BUTTON_FIND' => 'Find Products',
    'QPU_BUTTON_RESET' => 'Reset',

    // The table.
    'QPU_CAPTION' => 'Products found: %1$s. Page %2$s of %3$s.',
    'QPU_HEADING_ID' => 'ID',
    'QPU_HEADING_MODEL' => 'Model',
    'QPU_HEADING_NAME' => 'Product Name',
    'QPU_HEADING_CATEGORY' => 'Master Category',
    'QPU_HEADING_PRICE' => 'Price',
    'QPU_HEADING_QTY' => 'Quantity',
    'QPU_HEADING_STATUS' => 'Status',
    'QPU_HEADING_NOTES' => 'Notes',
    'QPU_LABEL_PRICE_FOR' => 'Price for %s',
    'QPU_LABEL_QTY_FOR' => 'Quantity for %s',
    'QPU_EDIT_PRODUCT' => 'Edit %s',
    'QPU_NO_NAME' => '(no name)',
    'QPU_STATUS_ACTIVE' => 'Active',
    'QPU_STATUS_INACTIVE' => 'Inactive',
    'QPU_NOTE_SPECIAL' => 'Special: %s',
    'QPU_NOTE_PRICED_BY_ATTRIBUTES' => 'Priced by Attributes',
    'QPU_NOTE_CALL' => 'Call for Price',
    'QPU_NOTE_FREE' => 'Always Free',
    'QPU_NOTE_HELP' => 'A note means shoppers don\'t pay the price shown here as it stands; this page changes only the product\'s own price.',
    'QPU_NONE_FOUND' => 'No products match.',
    'QPU_BUTTON_SAVE' => 'Save Changes',
    'QPU_CHANGED_COUNT' => 'Rows changed: %s',
    'QPU_PAGE_PREVIOUS' => 'Previous Page',
    'QPU_PAGE_NEXT' => 'Next Page',
    'QPU_PAGINATION_LABEL' => 'Result Pages',
    'QPU_RETRY_HELP' => 'Not saved: this row\'s value isn\'t a number the field can hold.',

    // Search problems.
    'QPU_ERROR_IDS_CHARACTERS' => 'Product IDs: use only numbers, commas, spaces and dashes, e.g. 12, 15, 40-55.',
    'QPU_ERROR_IDS_RANGE' => 'Product IDs: a range must start with the smaller number, e.g. 40-55.',
    'QPU_ERROR_IDS_TOO_MANY' => 'Product IDs: name no more than 1,000 IDs at a time.',

    // After saving.
    'QPU_MSG_UPDATED' => 'Products updated: %s.',
    'QPU_MSG_NOTHING' => 'Nothing was changed, so nothing was saved.',
    'QPU_MSG_INVALID' => 'Not saved, because a value isn\'t one its field can hold (prices: a number with up to 4 decimal places, not negative; quantities: up to 4 decimal places): product ID(s) %s. They are highlighted below with what you typed.',
    'QPU_MSG_CONFLICT' => 'Not saved, because the price or quantity changed somewhere else after this page was loaded: product ID(s) %s. They now show the current values.',
    'QPU_MSG_MISSING' => 'Not saved, because the product no longer exists: product ID(s) %s.',
    'QPU_ACTIVITY_LOG' => 'Quick Price Update: updated product ID(s) %s.',
];

return $define;

<?php
/**
 * Quick Price Update -- everything the admin page and the Categories/Products
 * icons share: finding products, checking typed numbers, saving them, and
 * picking the icon for the store's default currency.
 *
 * Written for one codebase on Zen Cart 1.5.8a through 3.0.0 and PHP 7.4
 * through 8.5: no union types, no match, no named arguments.
 *
 * @package  QuickPriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class QuickPriceUpdate
{
    const VERSION = 'v3.0.0';

    /** Most IDs one search may name, so a typo like 1-999999 can't build a huge query. */
    const MAX_IDS = 1000;

    /**
     * Rows per page. 200 rows post 600 fields (price, quantity, original values),
     * which stays under PHP's default max_input_vars of 1000.
     */
    const PER_PAGE_CHOICES = [20, 50, 100, 200];
    const PER_PAGE_DEFAULT = 50;

    const SORTS = [
        'sort' => 'p.products_sort_order, pd.products_name, p.products_id',
        'id' => 'p.products_id',
        'name' => 'pd.products_name, p.products_id',
        'model' => 'p.products_model, p.products_id',
        'price' => 'p.products_price, p.products_id',
        'qty' => 'p.products_quantity, p.products_id',
    ];

    /**
     * ISO 4217 code => Font Awesome Free 6.4.0 icon, the version every admin from
     * 1.5.8a to 3.0.0 loads. A code that isn't here shows the currency's own symbol
     * instead. fa-peso-sign is the Philippine peso (₱); the Mexican peso writes "$".
     */
    const CURRENCY_ICONS = [
        'USD' => 'fa-dollar-sign', 'CAD' => 'fa-dollar-sign', 'AUD' => 'fa-dollar-sign',
        'NZD' => 'fa-dollar-sign', 'HKD' => 'fa-dollar-sign', 'SGD' => 'fa-dollar-sign',
        'MXN' => 'fa-dollar-sign', 'TWD' => 'fa-dollar-sign', 'JMD' => 'fa-dollar-sign',
        'TTD' => 'fa-dollar-sign', 'BSD' => 'fa-dollar-sign', 'BBD' => 'fa-dollar-sign',
        'BZD' => 'fa-dollar-sign', 'FJD' => 'fa-dollar-sign', 'XCD' => 'fa-dollar-sign',
        'EUR' => 'fa-euro-sign',
        'GBP' => 'fa-sterling-sign',
        'JPY' => 'fa-yen-sign', 'CNY' => 'fa-yen-sign',
        'INR' => 'fa-indian-rupee-sign',
        'PKR' => 'fa-rupee-sign', 'LKR' => 'fa-rupee-sign', 'NPR' => 'fa-rupee-sign', 'MUR' => 'fa-rupee-sign',
        'KRW' => 'fa-won-sign',
        'RUB' => 'fa-ruble-sign',
        'TRY' => 'fa-turkish-lira-sign',
        'ILS' => 'fa-shekel-sign',
        'BRL' => 'fa-brazilian-real-sign',
        'PHP' => 'fa-peso-sign',
        'NGN' => 'fa-naira-sign',
        'UAH' => 'fa-hryvnia-sign',
        'THB' => 'fa-baht-sign',
        'VND' => 'fa-dong-sign',
        'KZT' => 'fa-tenge-sign',
        'GEL' => 'fa-lari-sign',
        'AZN' => 'fa-manat-sign',
        'IDR' => 'fa-rupiah-sign',
        'BDT' => 'fa-bangladeshi-taka-sign',
        'GHS' => 'fa-cedi-sign',
        'CRC' => 'fa-colon-sign',
        'PYG' => 'fa-guarani-sign',
        'LAK' => 'fa-kip-sign',
    ];

    /** Shown when nothing better is known. */
    const FALLBACK_ICON = 'fa-money-bill';

    /** @var array|null */
    protected static $badge = null;

    /** @var bool|null */
    protected static $canAccess = null;

    // -----
    // Typed input.
    //

    /**
     * "12, 15, 40-55" => [12, 15, 40, 41, ... 55]. Spaces work as separators too.
     * Anything else in the box, a reversed range, or more than MAX_IDS IDs adds a
     * message to $errors and contributes nothing.
     *
     * @param string $raw
     * @param array $errors language-constant names, appended to
     * @return int[] unique, ascending
     */
    public static function parseIdList($raw, array &$errors)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }
        if (!preg_match('/^[0-9,\s-]+$/', $raw)) {
            $errors[] = 'QPU_ERROR_IDS_CHARACTERS';
            return [];
        }
        $ids = [];
        foreach (preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $part) {
            if (preg_match('/^(\d{1,10})$/', $part, $m)) {
                $ids[(int)$m[1]] = true;
            } elseif (preg_match('/^(\d{1,10})-(\d{1,10})$/', $part, $m)) {
                $from = (int)$m[1];
                $to = (int)$m[2];
                if ($from > $to) {
                    $errors[] = 'QPU_ERROR_IDS_RANGE';
                    return [];
                }
                if ($to - $from >= self::MAX_IDS) {
                    $errors[] = 'QPU_ERROR_IDS_TOO_MANY';
                    return [];
                }
                for ($i = $from; $i <= $to; $i++) {
                    $ids[$i] = true;
                }
            } else {
                $errors[] = 'QPU_ERROR_IDS_CHARACTERS';
                return [];
            }
            if (count($ids) > self::MAX_IDS) {
                $errors[] = 'QPU_ERROR_IDS_TOO_MANY';
                return [];
            }
        }
        unset($ids[0]);
        $ids = array_keys($ids);
        sort($ids);
        return $ids;
    }

    /**
     * A typed or stored number in one canonical form, so "12.5", "12.50" and the
     * database's "12.5000" compare equal: "12.5000". Accepts "1,234.50" (comma
     * thousands only, so "12,50" is refused rather than misread). Up to 11 digits
     * before the point and 4 after, matching products.products_price decimal(15,4).
     *
     * @param mixed $raw
     * @param bool $allowNegative
     * @return string|null null if it isn't a number this field can hold
     */
    public static function canonicalNumber($raw, $allowNegative = false)
    {
        if (!is_scalar($raw)) {
            return null;
        }
        $s = trim((string)$raw);
        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d*)?$/', $s)) {
            $s = str_replace(',', '', $s);
        }
        if (!preg_match('/^(-?)(\d*)(?:\.(\d*))?$/', $s, $m) || ($m[2] === '' && ($m[3] ?? '') === '')) {
            return null;
        }
        $negative = $m[1] === '-';
        $int = ltrim($m[2], '0');
        $dec = rtrim($m[3] ?? '', '0');
        if (strlen($int) > 11 || strlen($dec) > 4) {
            return null;
        }
        $int = $int === '' ? '0' : $int;
        $dec = str_pad($dec, 4, '0');
        if ($int === '0' && $dec === '0000') {
            $negative = false;
        }
        if ($negative && !$allowNegative) {
            return null;
        }
        return ($negative ? '-' : '') . $int . '.' . $dec;
    }

    /**
     * A canonical number for an input box: trailing zeros trimmed, but at least
     * $minDecimals places ("12.50", "12.3456"; quantities "5", "2.5").
     *
     * @param string $canonical
     * @param int $minDecimals
     * @return string
     */
    public static function displayNumber($canonical, $minDecimals = 2)
    {
        $c = self::canonicalNumber($canonical, true);
        if ($c === null) {
            return (string)$canonical;
        }
        list($int, $dec) = explode('.', $c);
        $dec = rtrim($dec, '0');
        if (strlen($dec) < $minDecimals) {
            $dec = str_pad($dec, $minDecimals, '0');
        }
        return $dec === '' ? $int : $int . '.' . $dec;
    }

    // -----
    // The currency icon.
    //

    /**
     * Pure part of the icon choice, for a given currency.
     *
     * @param string $code
     * @param string $symbolLeft
     * @param string $symbolRight
     * @param string $charset
     * @return array ['code' => 'EUR', 'icon' => 'fa-euro-sign' or '', 'text' => 'kr' or '',
     *                'prefix' => text before a price box, 'suffix' => text after it]
     */
    public static function badgeFor($code, $symbolLeft, $symbolRight, $charset = 'utf-8')
    {
        $code = strtoupper(trim((string)$code));
        $left = trim(html_entity_decode((string)$symbolLeft, ENT_QUOTES, $charset));
        $right = trim(html_entity_decode((string)$symbolRight, ENT_QUOTES, $charset));
        $icon = self::CURRENCY_ICONS[$code] ?? '';
        $text = '';
        if ($icon === '') {
            $text = $left !== '' ? $left : ($right !== '' ? $right : '');
            if ($text === '') {
                $icon = self::FALLBACK_ICON;
            }
        }
        return [
            'code' => $code,
            'icon' => $icon,
            'text' => $text,
            'prefix' => $left,
            'suffix' => $left === '' ? $right : '',
        ];
    }

    /**
     * The badge for this store's DEFAULT_CURRENCY, read once per request. Prices
     * are stored in the default currency, so that is the one the icon shows.
     *
     * @return array see badgeFor()
     */
    public static function badge()
    {
        if (self::$badge !== null) {
            return self::$badge;
        }
        global $db;
        $code = defined('DEFAULT_CURRENCY') ? (string)DEFAULT_CURRENCY : '';
        $left = '';
        $right = '';
        if ($code !== '') {
            $row = $db->Execute(
                "SELECT symbol_left, symbol_right FROM " . TABLE_CURRENCIES
                . " WHERE code = '" . zen_db_input($code) . "' LIMIT 1"
            );
            if (!$row->EOF) {
                $left = (string)$row->fields['symbol_left'];
                $right = (string)$row->fields['symbol_right'];
            }
        }
        return self::$badge = self::badgeFor($code, $left, $right, defined('CHARSET') ? CHARSET : 'utf-8');
    }

    /**
     * The icon (or the symbol, in the icon's place) as markup, hidden from screen
     * readers: the link or button around it carries the words.
     *
     * @param array|null $badge
     * @return string
     */
    public static function iconMarkup($badge = null)
    {
        $badge = $badge === null ? self::badge() : $badge;
        if ($badge['icon'] !== '') {
            return '<i class="fa-solid ' . $badge['icon'] . ' fa-lg" aria-hidden="true"></i>';
        }
        return '<span aria-hidden="true" style="font-weight:700;">'
            . htmlspecialchars($badge['text'], ENT_QUOTES, defined('CHARSET') ? CHARSET : 'utf-8') . '</span>';
    }

    /**
     * Whether the signed-in admin may open the page (superusers always; others
     * when their profile includes it). Read once per request, since the
     * Categories/Products listing asks for every row.
     *
     * @return bool
     */
    public static function canAccess()
    {
        if (self::$canAccess === null) {
            self::$canAccess = zen_is_superuser() || check_page(FILENAME_QUICK_PRICE_UPDATE, []);
        }
        return self::$canAccess;
    }

    // -----
    // Finding products.
    //

    /**
     * Normalized filters from the query string.
     *
     * @param array $get
     * @return array
     */
    public static function filtersFrom(array $get)
    {
        $perPage = (int)($get['qpu_per'] ?? self::PER_PAGE_DEFAULT);
        if (!in_array($perPage, self::PER_PAGE_CHOICES, true)) {
            $perPage = self::PER_PAGE_DEFAULT;
        }
        $sort = (string)($get['qpu_sort'] ?? 'sort');
        if (!isset(self::SORTS[$sort])) {
            $sort = 'sort';
        }
        $status = (string)($get['qpu_status'] ?? '');
        if ($status !== '1' && $status !== '0') {
            $status = '';
        }
        $cid = (string)($get['qpu_cid'] ?? '');
        $cid = ($cid !== '' && ctype_digit($cid)) ? $cid : '';
        return [
            'ids' => trim((string)($get['qpu_ids'] ?? '')),
            'cid' => $cid,
            'sub' => !empty($get['qpu_sub']) ? '1' : '',
            'kw' => trim((string)($get['qpu_kw'] ?? '')),
            'status' => $status,
            'sort' => $sort,
            'dir' => (($get['qpu_dir'] ?? '') === 'desc') ? 'desc' : 'asc',
            'per' => $perPage,
        ];
    }

    /**
     * The filters as query-string parameters, for links and redirects that keep
     * the same search. Empty ones are left out.
     *
     * @param array $filters
     * @return string
     */
    public static function filterParams(array $filters)
    {
        $map = [
            'qpu_ids' => $filters['ids'], 'qpu_cid' => $filters['cid'], 'qpu_sub' => $filters['sub'],
            'qpu_kw' => $filters['kw'], 'qpu_status' => $filters['status'],
            'qpu_sort' => $filters['sort'] === 'sort' ? '' : $filters['sort'],
            'qpu_dir' => $filters['dir'] === 'asc' ? '' : $filters['dir'],
            'qpu_per' => (int)$filters['per'] === self::PER_PAGE_DEFAULT ? '' : (string)$filters['per'],
        ];
        $parts = [];
        foreach ($map as $k => $v) {
            if ((string)$v !== '') {
                $parts[] = $k . '=' . rawurlencode((string)$v);
            }
        }
        return implode('&', $parts);
    }

    /**
     * Category $cid and, with $withSubcategories, everything below it.
     *
     * @param int $cid
     * @param bool $withSubcategories
     * @return int[]
     */
    public static function categoryIds($cid, $withSubcategories)
    {
        $ids = [(int)$cid];
        if ($withSubcategories) {
            $sub = [];
            zen_get_subcategories($sub, (int)$cid);
            foreach ($sub as $s) {
                $ids[] = (int)$s;
            }
        }
        return array_values(array_unique($ids));
    }

    /**
     * One page of products matching $filters.
     *
     * @param array $filters from filtersFrom()
     * @param int $page 1-based; clamped to the last page
     * @param array $errors language-constant names, appended to
     * @return array ['rows' => [...], 'total' => int, 'page' => int, 'pages' => int]
     */
    public static function search(array $filters, $page, array &$errors)
    {
        global $db, $zco_notifier;

        $languageId = (int)$_SESSION['languages_id'];
        $where = [];

        if ($filters['ids'] !== '') {
            $ids = self::parseIdList($filters['ids'], $errors);
            if ($ids === []) {
                return ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
            }
            $where[] = 'p.products_id IN (' . implode(',', $ids) . ')';
        }

        if ($filters['cid'] !== '') {
            $cats = self::categoryIds((int)$filters['cid'], $filters['sub'] === '1');
            $where[] = 'p.products_id IN (SELECT p2c.products_id FROM ' . TABLE_PRODUCTS_TO_CATEGORIES
                . ' p2c WHERE p2c.categories_id IN (' . implode(',', $cats) . '))';
        }

        if ($filters['kw'] !== '') {
            foreach (preg_split('/\s+/', $filters['kw'], -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $like = "'%" . zen_db_input(addcslashes($word, '%_\\')) . "%'";
                $where[] = '(pd.products_name LIKE ' . $like . ' OR p.products_model LIKE ' . $like . ')';
            }
        }

        if ($filters['status'] !== '') {
            $where[] = 'p.products_status = ' . (int)$filters['status'];
        }

        // Quick Price Update Pro (or any other add-on) adds its own conditions here,
        // as complete SQL fragments that it has escaped itself.
        $extraWhere = [];
        $zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_SEARCH', $filters, $extraWhere);
        if (is_array($extraWhere)) {
            foreach ($extraWhere as $w) {
                $where[] = '(' . $w . ')';
            }
        }

        $joinName = ' LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON (pd.products_id = p.products_id AND pd.language_id = ' . $languageId . ')';
        $joinCategory = ' LEFT JOIN ' . TABLE_CATEGORIES_DESCRIPTION . ' cd ON (cd.categories_id = p.master_categories_id AND cd.language_id = ' . $languageId . ')';
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        $count = $db->Execute('SELECT COUNT(*) AS total FROM ' . TABLE_PRODUCTS . ' p' . $joinName . $whereSql);
        $total = $count->EOF ? 0 : (int)$count->fields['total'];
        $perPage = (int)$filters['per'];
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min(max(1, (int)$page), $pages);

        $order = self::SORTS[$filters['sort']];
        if ($filters['dir'] === 'desc') {
            $order = implode(', ', array_map(static function ($col) {
                return trim($col) . ' DESC';
            }, explode(',', $order)));
        }

        $sql = 'SELECT p.products_id, p.products_type, p.products_model, p.products_price, p.products_quantity,'
            . ' p.products_status, p.master_categories_id, p.products_priced_by_attribute, p.product_is_call,'
            . ' p.product_is_free, pd.products_name, cd.categories_name AS master_category_name'
            . ' FROM ' . TABLE_PRODUCTS . ' p' . $joinName . $joinCategory . $whereSql
            . ' ORDER BY ' . $order
            . ' LIMIT ' . (($page - 1) * $perPage) . ', ' . $perPage;

        $rows = [];
        $result = $db->Execute($sql);
        foreach ($result as $row) {
            $row['special_price'] = null;
            $rows[(int)$row['products_id']] = $row;
        }

        if ($rows !== []) {
            $specials = $db->Execute(
                'SELECT products_id, specials_new_products_price FROM ' . TABLE_SPECIALS
                . ' WHERE status = 1 AND products_id IN (' . implode(',', array_keys($rows)) . ')'
            );
            foreach ($specials as $s) {
                $rows[(int)$s['products_id']]['special_price'] = $s['specials_new_products_price'];
            }
        }

        return ['rows' => array_values($rows), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    // -----
    // Saving.
    //

    /**
     * Writes every row whose price or quantity was changed on the page.
     *
     * A row is skipped, and reported, when a value isn't a number the field can
     * hold, when the product no longer exists, or when its price or quantity
     * changed in the database after the page was loaded (someone else edited it):
     * the page's "was" values are compared with a fresh read first.
     *
     * @param array $prices products_id => typed price
     * @param array $quantities products_id => typed quantity
     * @param array $was products_id => "price|quantity" as shown when the page loaded
     * @return array ['updated' => int[], 'invalid' => [id => ['price' => raw, 'qty' => raw]],
     *                'conflict' => int[], 'missing' => int[]]
     */
    public static function save(array $prices, array $quantities, array $was)
    {
        global $db, $zco_notifier;

        $out = ['updated' => [], 'invalid' => [], 'conflict' => [], 'missing' => []];
        foreach ($was as $pid => $original) {
            $pid = (int)$pid;
            if ($pid <= 0) {
                continue;
            }
            $rawPrice = $prices[$pid] ?? null;
            $rawQty = $quantities[$pid] ?? null;
            if (!is_string($original) || strpos($original, '|') === false) {
                $original = '|';
            }
            list($wasPrice, $wasQty) = explode('|', $original, 2);
            $wasPrice = self::canonicalNumber($wasPrice, true);
            $wasQty = self::canonicalNumber($wasQty, true);
            $newPrice = $rawPrice === null ? $wasPrice : self::canonicalNumber($rawPrice);
            $newQty = $rawQty === null ? $wasQty : self::canonicalNumber($rawQty, true);
            if ($newPrice === null || $newQty === null || $wasPrice === null || $wasQty === null) {
                $out['invalid'][$pid] = ['price' => is_scalar($rawPrice) ? (string)$rawPrice : '', 'qty' => is_scalar($rawQty) ? (string)$rawQty : ''];
                continue;
            }
            $priceChanged = $newPrice !== $wasPrice;
            $qtyChanged = $newQty !== $wasQty;
            if (!$priceChanged && !$qtyChanged) {
                continue;
            }

            // Fresh read: the same SELECT may already sit in core's query cache.
            $current = $db->Execute(
                'SELECT products_price, products_quantity FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . $pid,
                null, false, 0, true
            );
            if ($current->EOF) {
                $out['missing'][] = $pid;
                continue;
            }
            if (self::canonicalNumber($current->fields['products_price'], true) !== $wasPrice
                || self::canonicalNumber($current->fields['products_quantity'], true) !== $wasQty) {
                $out['conflict'][] = $pid;
                continue;
            }

            $set = [];
            if ($priceChanged) {
                $set[] = "products_price = '" . $newPrice . "'";
            }
            if ($qtyChanged) {
                $set[] = "products_quantity = '" . $newQty . "'";
            }
            $set[] = 'products_last_modified = now()';
            $db->Execute('UPDATE ' . TABLE_PRODUCTS . ' SET ' . implode(', ', $set) . ' WHERE products_id = ' . $pid);
            if ($priceChanged) {
                // Keeps the storefront's price sort and "Starting at" prices right.
                zen_update_products_price_sorter($pid);
            }
            $out['updated'][] = $pid;

            $change = [
                'products_id' => $pid,
                'price_old' => $wasPrice, 'price_new' => $newPrice,
                'qty_old' => $wasQty, 'qty_new' => $newQty,
            ];
            $zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_PRODUCT_UPDATED', $change);
        }
        return $out;
    }
}

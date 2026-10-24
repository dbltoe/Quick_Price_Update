<?php
/**
 * Quick Price Update -- Catalog > Quick Price Update.
 *
 * Find products by exact ID or ID ranges, by category (with or without its
 * subcategories), by name or model and by status; change prices and
 * quantities in the table; save every changed row with one button.
 *
 * Reached as admin/index.php?cmd=quick_price_update, the routing Zen Cart has
 * used for plugin admin pages since v1.5.7; index.php loads the admin
 * bootstrap first, so IS_ADMIN_FLAG is always set on that path. Access is the
 * catalogQuickPriceUpdate admin_pages record the installer writes.
 *
 * Saving is a POST with action=save in the URL, so core's admin CSRF check in
 * init_sessions.php applies to it. The page then redirects (post/redirect/get),
 * so a reload never saves twice.
 *
 * @package  QuickPriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require 'includes/application_top.php';

if (!class_exists('QuickPriceUpdate', false)) {
    require __DIR__ . '/includes/classes/QuickPriceUpdate.php';
}

$qpuFilters = QuickPriceUpdate::filtersFrom($_GET);
$qpuPage = (int)($_GET['page'] ?? 1);
$qpuAction = (string)($_GET['action'] ?? '');

// An add-on (Quick Price Update Pro) handles its own actions here, before ours.
$zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_ACTION', $qpuAction, $qpuFilters);

if ($qpuAction === 'save' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $qpuResult = QuickPriceUpdate::save(
        is_array($_POST['qpu_price'] ?? null) ? $_POST['qpu_price'] : [],
        is_array($_POST['qpu_qty'] ?? null) ? $_POST['qpu_qty'] : [],
        is_array($_POST['qpu_was'] ?? null) ? $_POST['qpu_was'] : []
    );
    // An add-on saves its own columns here and adds its products to the same lists
    // ('updated' and 'conflict'/'missing' as IDs; 'invalid' as id => typed values),
    // so one set of messages covers the whole save.
    $zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_SAVE_RESULT', $qpuFilters, $qpuResult);
    foreach (['updated', 'conflict', 'missing'] as $qpuKey) {
        $qpuResult[$qpuKey] = array_values(array_unique(array_map('intval', (array)($qpuResult[$qpuKey] ?? []))));
        sort($qpuResult[$qpuKey]);
    }
    $qpuResult['invalid'] = (array)($qpuResult['invalid'] ?? []);
    ksort($qpuResult['invalid']);
    if ($qpuResult['updated'] !== []) {
        $messageStack->add_session(sprintf(QPU_MSG_UPDATED, count($qpuResult['updated'])), 'success');
        if (function_exists('zen_record_admin_activity')) {
            zen_record_admin_activity(sprintf(QPU_ACTIVITY_LOG, implode(', ', $qpuResult['updated'])), 'info');
        }
    }
    if ($qpuResult['invalid'] !== []) {
        $messageStack->add_session(sprintf(QPU_MSG_INVALID, implode(', ', array_keys($qpuResult['invalid']))), 'error');
        $_SESSION['qpu_retry'] = $qpuResult['invalid'];
    }
    if ($qpuResult['conflict'] !== []) {
        $messageStack->add_session(sprintf(QPU_MSG_CONFLICT, implode(', ', $qpuResult['conflict'])), 'warning');
    }
    if ($qpuResult['missing'] !== []) {
        $messageStack->add_session(sprintf(QPU_MSG_MISSING, implode(', ', $qpuResult['missing'])), 'warning');
    }
    if ($qpuResult['updated'] === [] && $qpuResult['invalid'] === [] && $qpuResult['conflict'] === [] && $qpuResult['missing'] === []) {
        $messageStack->add_session(QPU_MSG_NOTHING, 'caution');
    }
    $qpuBack = QuickPriceUpdate::filterParams($qpuFilters);
    zen_redirect(zen_href_link(FILENAME_QUICK_PRICE_UPDATE, $qpuBack . ($qpuBack === '' ? '' : '&') . 'page=' . max(1, $qpuPage)));
}

// Rows that weren't saved last time, with what was typed, shown once.
$qpuRetry = (isset($_SESSION['qpu_retry']) && is_array($_SESSION['qpu_retry'])) ? $_SESSION['qpu_retry'] : [];
unset($_SESSION['qpu_retry']);

$qpuErrors = [];
$qpuFound = QuickPriceUpdate::search($qpuFilters, $qpuPage, $qpuErrors);
foreach (array_unique($qpuErrors) as $qpuError) {
    $messageStack->add(constant($qpuError), 'error');
}

// An add-on adds data of its own to the rows on this page (one query for all of
// them, keyed by $row['products_id']) before they're shown.
$zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_ROWS', $qpuFilters, $qpuFound);

// Extra table columns from an add-on: 'before' the ID column and 'after' Quantity,
// each ['text' => heading html, 'class' => css classes]. NOTIFY_QUICK_PRICE_UPDATE_TABLE_CELLS
// must then give every row the same number of cells in the same places.
$qpuHeadings = ['before' => [], 'after' => []];
$zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_TABLE_HEADINGS', $qpuFilters, $qpuHeadings);

/** Extra cells (or headings) for one position, from an add-on. */
$qpuExtraCells = static function ($list, $tag) {
    $html = '';
    foreach (is_array($list) ? $list : [] as $cell) {
        $html .= '<' . $tag . ($tag === 'th' ? ' scope="col"' : '') . ' class="' . ($tag === 'th' ? 'dataTableHeadingContent ' : '')
            . htmlspecialchars((string)($cell['class'] ?? ''), ENT_QUOTES) . '">' . (string)($cell['text'] ?? '') . '</' . $tag . '>';
    }
    return $html;
};

$qpuBadge = QuickPriceUpdate::badge();
$qpuParams = QuickPriceUpdate::filterParams($qpuFilters);
$qpuCharset = defined('CHARSET') ? CHARSET : 'utf-8';

/** Escapes for HTML text and attributes. */
$qpuEsc = static function ($s) use ($qpuCharset) {
    return htmlspecialchars((string)$s, ENT_QUOTES, $qpuCharset);
};

/** This page with the current search and another page number. */
$qpuPageUrl = static function ($n) use ($qpuParams) {
    return zen_href_link(FILENAME_QUICK_PRICE_UPDATE, $qpuParams . ($qpuParams === '' ? '' : '&') . 'page=' . (int)$n);
};

/** A price with the store's symbol, for the notes column. */
$qpuMoney = static function ($amount) use ($qpuBadge, $qpuEsc) {
    $shown = QuickPriceUpdate::displayNumber((string)$amount);
    return $qpuEsc($qpuBadge['prefix'] . $shown . ($qpuBadge['suffix'] === '' ? '' : ' ' . $qpuBadge['suffix']));
};

$qpuCategoryOptions = array_merge([['id' => '', 'text' => QPU_CATEGORY_ANY]], zen_get_category_tree());
$qpuStatusOptions = [
    ['id' => '', 'text' => QPU_STATUS_ANY],
    ['id' => '1', 'text' => QPU_STATUS_ON],
    ['id' => '0', 'text' => QPU_STATUS_OFF],
];
$qpuSortOptions = [];
foreach (array_keys(QuickPriceUpdate::SORTS) as $qpuKey) {
    $qpuSortOptions[] = ['id' => $qpuKey, 'text' => constant('QPU_SORT_' . strtoupper($qpuKey))];
}
$qpuDirOptions = [['id' => 'asc', 'text' => QPU_DIR_ASC], ['id' => 'desc', 'text' => QPU_DIR_DESC]];
$qpuPerOptions = [];
foreach (QuickPriceUpdate::PER_PAGE_CHOICES as $qpuN) {
    $qpuPerOptions[] = ['id' => (string)$qpuN, 'text' => (string)$qpuN];
}

// An add-on adds its own search fields here (complete form-group markup).
$qpuExtraFilters = '';
$zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_FILTERS_FORM', $qpuFilters, $qpuExtraFilters);
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
<style>
/* display:block because the admin stylesheet sets form{display:inline} (1.5.8 through 3.0.0),
   which otherwise breaks this panel into stray grey slivers. */
.qpu-page .qpu-find{display:block;background:#f7f7f7;border:1px solid #ddd;border-radius:4px;padding:12px 15px 4px;margin-bottom:15px}
.qpu-page .qpu-find legend{font-size:1.3em;border:0;margin:0 0 8px;width:auto}
.qpu-page .qpu-find .form-group{margin-right:12px;margin-bottom:10px;vertical-align:top}
.qpu-page .qpu-find .help-block{margin:2px 0 0;font-size:.95em;color:#555}
.qpu-page .qpu-table td{vertical-align:middle}
.qpu-page .qpu-table .qpu-num{width:9em}
.qpu-page .qpu-table .qpu-qty{width:7em}
.qpu-page .qpu-table .qpu-name{min-width:12em}
.qpu-page .qpu-table tr.qpu-row-changed td{background:#fff3cd}
.qpu-page .qpu-table input.qpu-changed{border-color:#8a6d3b;font-weight:700}
.qpu-page .qpu-table .label{display:inline-block;margin:1px 2px 1px 0;font-size:.9em}
.qpu-page .qpu-actions{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:10px 0}
.qpu-page .qpu-changed-count{font-weight:700}
.qpu-page .qpu-currency-mark{display:inline-block;margin-right:6px}
</style>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->
<main class="container-fluid qpu-page" aria-labelledby="qpuHeading">
  <h1 id="qpuHeading" class="pageHeading"><span class="qpu-currency-mark"><?php echo QuickPriceUpdate::iconMarkup($qpuBadge); ?></span><?php echo HEADING_TITLE; ?></h1>
  <p><?php echo sprintf(QPU_INTRO, $qpuEsc($qpuBadge['code'])); ?></p>

  <?php echo zen_draw_form('qpuFind', FILENAME_QUICK_PRICE_UPDATE, '', 'get', 'class="form-inline qpu-find" role="search"'); ?>
    <fieldset>
      <legend><?php echo QPU_LEGEND_FIND; ?></legend>
      <div class="form-group">
        <label for="qpuIds"><?php echo QPU_LABEL_IDS; ?></label><br>
        <input type="text" class="form-control" id="qpuIds" name="qpu_ids" value="<?php echo $qpuEsc($qpuFilters['ids']); ?>" size="22" inputmode="numeric" autocomplete="off" aria-describedby="qpuIdsHelp">
        <p class="help-block" id="qpuIdsHelp"><?php echo QPU_HELP_IDS; ?></p>
      </div>
      <div class="form-group">
        <label for="qpuCid"><?php echo QPU_LABEL_CATEGORY; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qpu_cid', $qpuCategoryOptions, $qpuFilters['cid'], 'id="qpuCid" class="form-control"'); ?>
        <div class="checkbox" style="display:block;margin-top:4px;">
          <label for="qpuSub"><input type="checkbox" id="qpuSub" name="qpu_sub" value="1"<?php echo $qpuFilters['sub'] === '1' ? ' checked' : ''; ?>> <?php echo QPU_LABEL_SUBCATEGORIES; ?></label>
        </div>
      </div>
      <div class="form-group">
        <label for="qpuKw"><?php echo QPU_LABEL_KEYWORDS; ?></label><br>
        <input type="search" class="form-control" id="qpuKw" name="qpu_kw" value="<?php echo $qpuEsc($qpuFilters['kw']); ?>" size="22">
      </div>
      <div class="form-group">
        <label for="qpuStatus"><?php echo QPU_LABEL_STATUS; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qpu_status', $qpuStatusOptions, $qpuFilters['status'], 'id="qpuStatus" class="form-control"'); ?>
      </div>
      <div class="form-group">
        <label for="qpuSort"><?php echo QPU_LABEL_SORT; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qpu_sort', $qpuSortOptions, $qpuFilters['sort'], 'id="qpuSort" class="form-control"'); ?>
      </div>
      <div class="form-group">
        <label for="qpuDir"><?php echo QPU_LABEL_DIRECTION; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qpu_dir', $qpuDirOptions, $qpuFilters['dir'], 'id="qpuDir" class="form-control"'); ?>
      </div>
      <div class="form-group">
        <label for="qpuPer"><?php echo QPU_LABEL_PER_PAGE; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qpu_per', $qpuPerOptions, (string)$qpuFilters['per'], 'id="qpuPer" class="form-control"'); ?>
      </div>
      <?php echo $qpuExtraFilters; ?>
      <div class="form-group">
        <span>&nbsp;</span><br>
        <button type="submit" class="btn btn-primary"><?php echo QPU_BUTTON_FIND; ?></button>
        <a href="<?php echo zen_href_link(FILENAME_QUICK_PRICE_UPDATE); ?>" class="btn btn-default" role="button"><?php echo QPU_BUTTON_RESET; ?></a>
      </div>
    </fieldset>
  </form>

<?php
// An add-on's own panels, each with its own forms (they sit outside both of the
// page's forms). The third parameter is what was found.
$qpuPanels = '';
$zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_PANELS', $qpuFilters, $qpuPanels, $qpuFound);
echo $qpuPanels;
?>

<?php if ($qpuFound['rows'] === []) { ?>
  <p class="alert alert-info"><?php echo QPU_NONE_FOUND; ?></p>
<?php } else { ?>
  <?php echo zen_draw_form('qpuForm', FILENAME_QUICK_PRICE_UPDATE, 'action=save' . ($qpuParams === '' ? '' : '&' . $qpuParams) . '&page=' . (int)$qpuFound['page'], 'post', 'id="qpuForm" autocomplete="off"'); ?>
  <div class="table-responsive">
  <table class="table table-striped table-condensed qpu-table">
    <caption><?php echo sprintf(QPU_CAPTION, (int)$qpuFound['total'], (int)$qpuFound['page'], (int)$qpuFound['pages']); ?></caption>
    <thead>
      <tr class="dataTableHeadingRow">
        <?php echo $qpuExtraCells($qpuHeadings['before'] ?? [], 'th'); ?>
        <th scope="col" class="dataTableHeadingContent text-right"><?php echo QPU_HEADING_ID; ?></th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QPU_HEADING_MODEL; ?></th>
        <th scope="col" class="dataTableHeadingContent qpu-name"><?php echo QPU_HEADING_NAME; ?></th>
        <th scope="col" class="dataTableHeadingContent hidden-xs"><?php echo QPU_HEADING_CATEGORY; ?></th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QPU_HEADING_PRICE; ?> (<?php echo $qpuEsc($qpuBadge['code']); ?>)</th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QPU_HEADING_QTY; ?></th>
        <?php echo $qpuExtraCells($qpuHeadings['after'] ?? [], 'th'); ?>
        <th scope="col" class="dataTableHeadingContent text-center"><?php echo QPU_HEADING_STATUS; ?></th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QPU_HEADING_NOTES; ?></th>
      </tr>
    </thead>
    <tbody>
<?php
    foreach ($qpuFound['rows'] as $qpuRow) {
        $qpuId = (int)$qpuRow['products_id'];
        $qpuName = (string)$qpuRow['products_name'] !== '' ? (string)$qpuRow['products_name'] : QPU_NO_NAME;
        $qpuWasPrice = QuickPriceUpdate::canonicalNumber($qpuRow['products_price'], true);
        $qpuWasQty = QuickPriceUpdate::canonicalNumber($qpuRow['products_quantity'], true);
        $qpuShowPrice = QuickPriceUpdate::displayNumber((string)$qpuWasPrice);
        $qpuShowQty = QuickPriceUpdate::displayNumber((string)$qpuWasQty, 0);
        $qpuRetryRow = $qpuRetry[$qpuId] ?? null;
        // What was typed arrived through core's strict sanitizer (htmlspecialchars), so it's
        // decoded once here before being escaped again for the box.
        // An add-on's invalid entry may carry no price/qty (its own column was the problem).
        $qpuRetryPrice = $qpuRetryRow !== null && isset($qpuRetryRow['price']);
        $qpuRetryQty = $qpuRetryRow !== null && isset($qpuRetryRow['qty']);
        $qpuPriceValue = $qpuRetryPrice ? html_entity_decode((string)$qpuRetryRow['price'], ENT_QUOTES, $qpuCharset) : $qpuShowPrice;
        $qpuQtyValue = $qpuRetryQty ? html_entity_decode((string)$qpuRetryRow['qty'], ENT_QUOTES, $qpuCharset) : $qpuShowQty;
        $qpuCells = ['before' => [], 'after' => []];
        $zco_notifier->notify('NOTIFY_QUICK_PRICE_UPDATE_TABLE_CELLS', $qpuRow, $qpuCells);
        $qpuCPath = zen_get_product_path($qpuId);
        $qpuNotes = [];
        if ($qpuRow['special_price'] !== null) {
            $qpuNotes[] = '<span class="label label-warning">' . sprintf(QPU_NOTE_SPECIAL, $qpuMoney($qpuRow['special_price'])) . '</span>';
        }
        if ((int)$qpuRow['products_priced_by_attribute'] === 1) {
            $qpuNotes[] = '<span class="label label-info">' . QPU_NOTE_PRICED_BY_ATTRIBUTES . '</span>';
        }
        if ((int)$qpuRow['product_is_call'] === 1) {
            $qpuNotes[] = '<span class="label label-default">' . QPU_NOTE_CALL . '</span>';
        }
        if ((int)$qpuRow['product_is_free'] === 1) {
            $qpuNotes[] = '<span class="label label-default">' . QPU_NOTE_FREE . '</span>';
        }
?>
      <tr id="qpuRow<?php echo $qpuId; ?>"<?php echo $qpuRetryRow !== null ? ' class="danger"' : ''; ?>>
        <?php echo $qpuExtraCells($qpuCells['before'] ?? [], 'td'); ?>
        <td class="text-right"><?php echo $qpuId; ?></td>
        <td><?php echo $qpuEsc($qpuRow['products_model']); ?></td>
        <td class="qpu-name"><a href="<?php echo zen_href_link(FILENAME_PRODUCT,'cPath=' . $qpuCPath . '&product_type=' . (int)$qpuRow['products_type'] . '&pID=' . $qpuId . '&action=new_product'); ?>" title="<?php echo $qpuEsc(sprintf(QPU_EDIT_PRODUCT, $qpuName)); ?>"><?php echo $qpuEsc($qpuName); ?></a></td>
        <td class="hidden-xs"><a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $qpuCPath . '&pID=' . $qpuId); ?>"><?php echo $qpuEsc($qpuRow['master_category_name']); ?></a></td>
        <td<?php echo $qpuRetryPrice ? ' class="has-error"' : ''; ?>>
          <?php echo zen_draw_hidden_field('qpu_was[' . $qpuId . ']', $qpuWasPrice . '|' . $qpuWasQty, 'data-row="qpuRow' . $qpuId . '"'); ?>
          <div class="input-group input-group-sm qpu-num">
<?php if ($qpuBadge['prefix'] !== '') { ?>
            <span class="input-group-addon" aria-hidden="true"><?php echo $qpuEsc($qpuBadge['prefix']); ?></span>
<?php } ?>
            <input type="text" inputmode="decimal" class="form-control qpu-input" name="qpu_price[<?php echo $qpuId; ?>]" value="<?php echo $qpuEsc($qpuPriceValue); ?>" data-was="<?php echo $qpuEsc($qpuShowPrice); ?>" data-row="qpuRow<?php echo $qpuId; ?>" aria-label="<?php echo $qpuEsc(sprintf(QPU_LABEL_PRICE_FOR, $qpuName)); ?>"<?php echo $qpuRetryPrice ? ' aria-invalid="true" title="' . $qpuEsc(QPU_RETRY_HELP) . '"' : ''; ?>>
<?php if ($qpuBadge['suffix'] !== '') { ?>
            <span class="input-group-addon" aria-hidden="true"><?php echo $qpuEsc($qpuBadge['suffix']); ?></span>
<?php } ?>
          </div>
        </td>
        <td<?php echo $qpuRetryQty ? ' class="has-error"' : ''; ?>>
          <input type="text" inputmode="decimal" class="form-control input-sm qpu-input qpu-qty" name="qpu_qty[<?php echo $qpuId; ?>]" value="<?php echo $qpuEsc($qpuQtyValue); ?>" data-was="<?php echo $qpuEsc($qpuShowQty); ?>" data-row="qpuRow<?php echo $qpuId; ?>" aria-label="<?php echo $qpuEsc(sprintf(QPU_LABEL_QTY_FOR, $qpuName)); ?>"<?php echo $qpuRetryQty ? ' aria-invalid="true"' : ''; ?>>
        </td>
        <?php echo $qpuExtraCells($qpuCells['after'] ?? [], 'td'); ?>
        <td class="text-center">
<?php if ((int)$qpuRow['products_status'] === 1) { ?>
          <i class="fa-solid fa-square fa-lg txt-status-on" aria-hidden="true" title="<?php echo QPU_STATUS_ACTIVE; ?>"></i><span class="sr-only"><?php echo QPU_STATUS_ACTIVE; ?></span>
<?php } else { ?>
          <i class="fa-solid fa-square fa-lg txt-status-off" aria-hidden="true" title="<?php echo QPU_STATUS_INACTIVE; ?>"></i><span class="sr-only"><?php echo QPU_STATUS_INACTIVE; ?></span>
<?php } ?>
        </td>
        <td><?php echo implode(' ', $qpuNotes); ?></td>
      </tr>
<?php
    }
?>
    </tbody>
  </table>
  </div>
  <p class="help-block"><?php echo QPU_NOTE_HELP; ?></p>
  <div class="qpu-actions">
    <button type="submit" class="btn btn-primary"><?php echo QPU_BUTTON_SAVE; ?></button>
    <span class="qpu-changed-count" id="qpuChanged" aria-live="polite" data-pattern="<?php echo $qpuEsc(QPU_CHANGED_COUNT); ?>"></span>
  </div>
  </form>

<?php if ($qpuFound['pages'] > 1) { ?>
  <nav aria-label="<?php echo QPU_PAGINATION_LABEL; ?>">
    <ul class="pagination">
<?php
        $qpuCurrent = (int)$qpuFound['page'];
        $qpuFirst = max(1, $qpuCurrent - 3);
        $qpuLast = min((int)$qpuFound['pages'], $qpuCurrent + 3);
        if ($qpuCurrent > 1) {
            echo '      <li><a href="' . $qpuPageUrl($qpuCurrent - 1) . '" rel="prev">' . QPU_PAGE_PREVIOUS . '</a></li>' . "\n";
        }
        if ($qpuFirst > 1) {
            echo '      <li><a href="' . $qpuPageUrl(1) . '">1</a></li>' . "\n";
            if ($qpuFirst > 2) {
                echo '      <li class="disabled"><span>&hellip;</span></li>' . "\n";
            }
        }
        for ($qpuN = $qpuFirst; $qpuN <= $qpuLast; $qpuN++) {
            if ($qpuN === $qpuCurrent) {
                echo '      <li class="active"><span aria-current="page">' . $qpuN . '</span></li>' . "\n";
            } else {
                echo '      <li><a href="' . $qpuPageUrl($qpuN) . '">' . $qpuN . '</a></li>' . "\n";
            }
        }
        if ($qpuLast < (int)$qpuFound['pages']) {
            if ($qpuLast < (int)$qpuFound['pages'] - 1) {
                echo '      <li class="disabled"><span>&hellip;</span></li>' . "\n";
            }
            echo '      <li><a href="' . $qpuPageUrl($qpuFound['pages']) . '">' . (int)$qpuFound['pages'] . '</a></li>' . "\n";
        }
        if ($qpuCurrent < (int)$qpuFound['pages']) {
            echo '      <li><a href="' . $qpuPageUrl($qpuCurrent + 1) . '" rel="next">' . QPU_PAGE_NEXT . '</a></li>' . "\n";
        }
?>
    </ul>
  </nav>
<?php } ?>
<?php } ?>
</main>
<!-- body_eof //-->

<script>
(function () {
    'use strict';
    var form = document.getElementById('qpuForm');
    if (!form) {
        return;
    }
    // Boxes and selects alike (an add-on's columns use the same class and data-* attributes).
    var inputs = form.querySelectorAll('.qpu-input');
    var counter = document.getElementById('qpuChanged');
    var pattern = counter ? counter.getAttribute('data-pattern') : '';
    var changedRows = 0;
    var rows = {};
    var submitting = false;

    // Highlighting only: the server decides what really changed.
    function same(a, b) {
        a = String(a).replace(/,/g, '').trim();
        b = String(b).replace(/,/g, '').trim();
        if (a === b) {
            return true;
        }
        return /^-?\d*\.?\d+$/.test(a) && /^-?\d*\.?\d+$/.test(b) && parseFloat(a) === parseFloat(b);
    }

    function refresh() {
        var i;
        rows = {};
        for (i = 0; i < inputs.length; i++) {
            var changed = !same(inputs[i].value, inputs[i].getAttribute('data-was'));
            inputs[i].classList.toggle('qpu-changed', changed);
            var rowId = inputs[i].getAttribute('data-row');
            rows[rowId] = rows[rowId] || changed;
        }
        changedRows = 0;
        for (var id in rows) {
            if (Object.prototype.hasOwnProperty.call(rows, id)) {
                var tr = document.getElementById(id);
                if (tr) {
                    tr.classList.toggle('qpu-row-changed', rows[id]);
                }
                if (rows[id]) {
                    changedRows++;
                }
            }
        }
        if (counter) {
            counter.textContent = changedRows > 0 ? pattern.replace('%s', String(changedRows)) : '';
        }
    }

    form.addEventListener('input', refresh);
    form.addEventListener('submit', function () {
        submitting = true;
        refresh();
        // Only changed rows are posted, which keeps a full page (and an add-on's extra
        // columns) under PHP's max_input_vars. Without JavaScript every row posts and the
        // server still writes only what changed.
        var fields = form.querySelectorAll('[data-row]');
        for (var i = 0; i < fields.length; i++) {
            if (!rows[fields[i].getAttribute('data-row')]) {
                fields[i].disabled = true;
            }
        }
    });
    // Back/forward cache: a page restored after submitting must have its boxes back.
    window.addEventListener('pageshow', function () {
        var fields = form.querySelectorAll('[data-row]');
        for (var i = 0; i < fields.length; i++) {
            fields[i].disabled = false;
        }
        submitting = false;
    });
    window.addEventListener('beforeunload', function (e) {
        if (!submitting && changedRows > 0) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
    refresh();
}());
</script>

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>

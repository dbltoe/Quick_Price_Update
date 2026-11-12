<?php
/**
 * Quick Price Update -- plugin manifest.
 *
 * Zen Cart stores pluginDescription in plugin_control.description and echoes
 * it into the Plugin Manager's info box as raw HTML on every release from
 * 1.5.8 to 3.0.0, which is where the ReadMe / Forum Thread / GitHub buttons
 * go (the Social Contact Footer pattern). This plugin's admin page is the
 * tool itself, so there is no Settings button.
 *
 * ReadMe opens README.md through the plugin's own admin page
 * (index.php?cmd=quick_price_update_readme): behind the admin login,
 * superusers only, in its own window. It is shown only to superusers; the page
 * enforces that too, because Zen Cart 1.5.8-2.1 keep the description they
 * first saw. Forum Thread and GitHub go off-site, so they open in a new
 * window with rel="noopener noreferrer" (zencart/zencart#8031).
 *
 * On 1.5.8, 2.0 and 2.1 the description and pluginId are written only by the
 * INSERT that first creates a store's plugin_control row, so the forum URL and
 * the Library ID must be right in the release a store first installs.
 *
 * @package  QuickPriceUpdate
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
$qpuReadmeUrl = 'index.php?cmd=quick_price_update_readme';
// Zen Cart 3.0.0 reads every manifest on every admin page, early (template resolver),
// before admin access is set up: zen_is_superuser() there is a fatal "Undefined constant
// SUPERUSER_PROFILE" that takes down the whole admin. Ask only once that exists.
$qpuShowReadme = true;
if (function_exists('zen_is_superuser') && defined('SUPERUSER_PROFILE')) {
    $qpuShowReadme = zen_is_superuser();
}
$qpuGithubUrl = 'https://github.com/dbltoe/Quick_Price_Update';

// The opening-post permalink of the plugin's Zen Cart forum thread. Empty
// renders no button: a link that 404s is worse than none.
$qpuForumUrl = 'https://www.zen-cart.com/threads/207447?page=1#post-1347749';

// One row of buttons: ReadMe | Forum Thread | GitHub.
$qpuButtonGap = '6px';
$qpuButton = static function ($url, $label) use ($qpuButtonGap) {
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer"'
        . ' class="btn btn-primary" role="button"'
        . ' style="margin:0 ' . $qpuButtonGap . ' 0 0">' . $label . '</a>';
};

$qpuLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $qpuButtonGap . '">'
    . ($qpuShowReadme ? $qpuButton($qpuReadmeUrl, 'ReadMe') : '')
    . ($qpuForumUrl !== '' ? $qpuButton($qpuForumUrl, 'Forum Thread') : '')
    . ($qpuGithubUrl !== '' ? $qpuButton($qpuGithubUrl, 'GitHub') : '')
    . '</div>';

return [
    'pluginVersion' => 'v3.0.2',
    'pluginName' => 'Quick Price Update',
    'pluginDescription' =>
        'Adds Catalog > Quick Price Update: find products by exact ID or ID range, by category '
        . '(with or without its subcategories), by name or model, then change prices and quantities '
        . 'in one table and save them all at once. A currency icon on Catalog > Categories/Products '
        . 'opens any product or category there. No core files are changed.'
        . $qpuLinks,
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    // The Plugins Library ID: this is a new release of Quick Price Update,
    // https://www.zen-cart.com/plugins/quick-price-update-vb2338 (the listing held V2 before it).
    // Plugin Manager's "new version available" check keys on it.
    'pluginId' => 2338,
    'zcVersions' => ['v158', 'v200', 'v210', 'v220', 'v230', 'v300'],
    'changelog' => 'changelog.txt',
    'github_repo' => $qpuGithubUrl,
    'pluginGroups' => [],
];

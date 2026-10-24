<?php
/**
 * Quick Price Update -- plugin manifest.
 *
 * Zen Cart stores pluginDescription in plugin_control.description and echoes
 * it into the Plugin Manager's info box as raw HTML on every release from
 * 1.5.8 to 3.0.0, which is where the Read Me / GitHub / Forum Support Thread
 * buttons go (the Social Contact Footer pattern).
 *
 * On 1.5.8, 2.0 and 2.1 the description and pluginId are written only by the
 * INSERT that first creates a store's plugin_control row, so the forum URL and
 * the Library ID must be right in the release a store first installs.
 *
 * @package  QuickPriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */
$qpuPluginDir = 'zc_plugins/QuickPriceUpdate/v1.0.0/';
$qpuReadmeUrl = (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/') . $qpuPluginDir . 'readme.html';
$qpuGithubUrl = 'https://github.com/dbltoe/Quick_Price_Update';

// The opening-post permalink of the plugin's Zen Cart forum thread. Empty
// renders no button: a link that 404s is worse than none.
$qpuForumUrl = '';

$qpuButtonGap = '6px';

$qpuLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $qpuButtonGap . '">'
    . '<a href="' . $qpuReadmeUrl . '" target="_blank" rel="noopener noreferrer"'
    . ' class="btn btn-primary" role="button"'
    . ' style="margin:0 ' . $qpuButtonGap . ' 0 0">Read Me</a>'
    . ($qpuGithubUrl !== ''
        ? '<a href="' . $qpuGithubUrl . '" target="_blank" rel="noopener noreferrer"'
          . ' class="btn btn-primary" role="button"'
          . ' style="margin:0 ' . $qpuButtonGap . ' 0 0">GitHub</a>'
        : '')
    . ($qpuForumUrl !== ''
        ? '<a href="' . $qpuForumUrl . '" target="_blank" rel="noopener noreferrer"'
          . ' class="btn btn-primary" role="button"'
          . ' style="margin:0 ' . $qpuButtonGap . ' 0 0">Forum Support Thread</a>'
        : '')
    . '</div>';

return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Quick Price Update',
    'pluginDescription' =>
        'Adds Catalog > Quick Price Update: find products by exact ID or ID range, by category '
        . '(with or without its subcategories), by name or model, then change prices and quantities '
        . 'in one table and save them all at once. A currency icon on Catalog > Categories/Products '
        . 'opens any product or category there. No core files are changed.'
        . $qpuLinks,
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    // The Plugins Library ID (the number after "vb" in the listing URL); 0 until
    // the Library assigns one.
    'pluginId' => 0,
    'zcVersions' => ['v158', 'v200', 'v210', 'v220', 'v230', 'v300'],
    'changelog' => 'changelog.txt',
    'github_repo' => $qpuGithubUrl,
    'pluginGroups' => [],
];

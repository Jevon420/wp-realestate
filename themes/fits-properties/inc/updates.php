<?php
/**
 * Lets WordPress check for and install updates to this theme from GitHub
 * Releases, same as the fits-properties-core plugin. Cut a new release by
 * bumping the "Version" header in style.css, then attaching a zip named
 * "fits-properties-theme.zip" (with the theme folder at its root) to a
 * GitHub Release tagged vX.Y.Z in the wp-realestate repo.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    require_once FP_THEME_DIR . '/vendor/plugin-update-checker/plugin-update-checker.php';

    $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/Jevon420/wp-realestate',
        FP_THEME_DIR,
        'fits-properties'
    );

    $api = $updateChecker->getVcsApi();

    if ($api && method_exists($api, 'enableReleaseAssets')) {
        $api->enableReleaseAssets('/^fits-properties-theme.*\.zip$/i');
    }
});

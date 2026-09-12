<?php

namespace FitsPropertiesCore\Updates;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lets WordPress check for and install updates to this plugin from GitHub
 * Releases, the same way it does for wordpress.org-hosted plugins. Cut a
 * new release by bumping the "Version" header above and in the plugin's
 * main file, then attaching a zip named "fits-properties-core.zip" (with
 * the plugin folder at its root) to a new GitHub Release tagged vX.Y.Z.
 */
class UpdateChecker
{
    const REPO_URL = 'https://github.com/Jevon420/wp-realestate';
    const ASSET_NAME_PATTERN = '/^fits-properties-core.*\.zip$/i';

    public function register()
    {
        require_once FPC_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

        $updateChecker = PucFactory::buildUpdateChecker(
            self::REPO_URL,
            FPC_PLUGIN_FILE,
            'fits-properties-core'
        );

        $api = $updateChecker->getVcsApi();

        if ($api && method_exists($api, 'enableReleaseAssets')) {
            $api->enableReleaseAssets(self::ASSET_NAME_PATTERN);
        }
    }
}

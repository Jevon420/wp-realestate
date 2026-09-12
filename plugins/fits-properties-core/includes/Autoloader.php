<?php

namespace FitsPropertiesCore;

if (!defined('ABSPATH')) {
    exit;
}

class Autoloader
{
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'load']);
    }

    public static function load($class)
    {
        $prefix = 'FitsPropertiesCore\\';

        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path = FPC_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $relative) . '.php';

        if (file_exists($path)) {
            require_once $path;
        }
    }
}

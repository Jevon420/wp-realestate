<?php

namespace FitsPropertiesCore;

use FitsPropertiesCore\PostTypes\PostTypes;
use FitsPropertiesCore\PostTypes\ContactMessage;
use FitsPropertiesCore\Taxonomies\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

class Activator
{
    public static function activate()
    {
        (new PostTypes())->register();
        (new ContactMessage())->register();
        (new Taxonomies())->register();
        flush_rewrite_rules();
    }
}

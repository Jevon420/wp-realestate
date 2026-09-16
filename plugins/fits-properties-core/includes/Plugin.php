<?php

namespace FitsPropertiesCore;

use FitsPropertiesCore\PostTypes\PostTypes;
use FitsPropertiesCore\PostTypes\ContactMessage;
use FitsPropertiesCore\Taxonomies\Taxonomies;
use FitsPropertiesCore\Admin\MetaBoxes;
use FitsPropertiesCore\Admin\ListColumns;
use FitsPropertiesCore\Admin\AdminAssets;
use FitsPropertiesCore\Admin\ContactInbox;
use FitsPropertiesCore\Admin\SetupPage;
use FitsPropertiesCore\Admin\HelpTabs;
use FitsPropertiesCore\Admin\TermImage;
use FitsPropertiesCore\Frontend\ContactForm;
use FitsPropertiesCore\Frontend\CookieConsent;
use FitsPropertiesCore\Frontend\Analytics;
use FitsPropertiesCore\Email\SmtpMailer;
use FitsPropertiesCore\Updates\UpdateChecker;
use FitsPropertiesCore\Updates\ContentMigration;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        (new PostTypes())->register();
        (new ContactMessage())->register();
        (new Taxonomies())->register();
        (new MetaBoxes())->register();
        (new ListColumns())->register();
        (new AdminAssets())->register();
        (new ContactInbox())->register();
        (new ContactForm())->register();
        (new CookieConsent())->register();
        (new Analytics())->register();
        (new SmtpMailer())->register();
        (new SetupPage())->register();
        (new HelpTabs())->register();
        (new TermImage())->register();
        (new UpdateChecker())->register();
        (new ContentMigration())->register();
    }
}

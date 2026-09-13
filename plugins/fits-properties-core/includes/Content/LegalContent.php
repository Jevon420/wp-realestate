<?php

namespace FitsPropertiesCore\Content;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Replaces the one-line "replace this with your actual policy" demo text
 * on the Privacy/Terms/Cookie pages with a proper starting template.
 *
 * IMPORTANT: this is a starting point, not legal advice. It should be
 * reviewed (and the bracketed details filled in) by someone familiar with
 * the laws that apply where the business operates before the site goes
 * live for real.
 *
 * Only replaces a page's content if it still exactly matches the original
 * placeholder text, so it never overwrites anything you've already
 * edited by hand.
 */
class LegalContent
{
    private static function placeholders()
    {
        return [
            'privacy-policy' => [
                'old' => 'This is placeholder privacy policy content. Replace this with your actual privacy policy before the site goes live.',
                'new' => self::privacyPolicy(),
            ],
            'terms-of-use' => [
                'old' => 'This is placeholder terms of use content. Replace this with your actual terms before the site goes live.',
                'new' => self::termsOfUse(),
            ],
            'cookie-policy' => [
                'old' => "This site uses cookies to keep the site working correctly and, if you agree, to understand how the site is used. You can accept or decline non-essential cookies using the banner shown on your first visit.\n\nThis is placeholder cookie policy content. Replace this with your actual policy — listing exactly which cookies are set and by whom — before the site goes live.",
                'new' => self::cookiePolicy(),
            ],
        ];
    }

    public static function apply()
    {
        $summary = ['updated' => 0, 'skipped' => 0];

        foreach (self::placeholders() as $slug => $content) {
            $page = get_page_by_path($slug);

            if (!$page || trim($page->post_content) !== trim($content['old'])) {
                $summary['skipped']++;
                continue;
            }

            wp_update_post([
                'ID' => $page->ID,
                'post_content' => $content['new'],
            ]);

            $summary['updated']++;
        }

        return $summary;
    }

    private static function business()
    {
        return get_bloginfo('name') ?: '[Business Name]';
    }

    private static function contactLine()
    {
        $email = get_theme_mod('fp_contact_email', get_option('admin_email'));

        return 'You can reach us any time at ' . $email . ' with questions or requests about this policy.';
    }

    private static function privacyPolicy()
    {
        $business = self::business();
        $contactLine = self::contactLine();

        return <<<TEXT
Last updated: [date]

{$business} ("we", "us") operates this website. This policy explains what personal information we collect, why, and what choices you have.

**Information we collect**
- Contact form and viewing-request submissions: name, email address, phone number, and any message you send us.
- Property inquiries: which listings you've asked about, so we can follow up appropriately.
- Basic technical information collected automatically by any website (browser type, pages visited) — see our Cookie Policy for details on how this works here specifically.

**How we use it**
We use this information to respond to your enquiries, arrange property viewings, and improve the site. We do not sell your personal information to third parties.

**Who we share it with**
We share information only where necessary to run this site and respond to you — for example, our email delivery provider (to send you a reply) and our web hosting provider (to keep the site running). We do not share your information with third parties for their own marketing purposes.

**Cookies**
This site uses a small number of cookies, described in full in our Cookie Policy. You control non-essential cookies through the banner shown on your first visit.

**Data retention**
We keep contact form and inquiry submissions for as long as reasonably needed to respond to you and maintain business records, after which they are deleted.

**Your rights**
You can ask us what personal information we hold about you, ask us to correct it, or ask us to delete it. {$contactLine}

**Changes to this policy**
We may update this policy from time to time. The "Last updated" date above will reflect the most recent change.

---
*This page is a starting template and should be reviewed by someone familiar with applicable privacy law before this site goes live with real customer data.*
TEXT;
    }

    private static function termsOfUse()
    {
        $business = self::business();

        return <<<TEXT
Last updated: [date]

These terms govern your use of this website, operated by {$business} ("we", "us").

**Property information**
We make reasonable efforts to keep listing details (price, availability, specifications) accurate and up to date, but details are subject to change without notice and should be independently verified before you make any decision. Errors and omissions excepted.

**Acceptable use**
You agree not to misuse this site — including attempting to access it in an unauthorized way, submitting false information through our forms, or using content from this site without permission.

**Intellectual property**
The text, photos, and design of this site belong to {$business} or are used with permission, and may not be copied or reused without our consent.

**No warranty**
This site and its content are provided "as is" without warranties of any kind. We are not liable for any loss arising from your use of this site or reliance on information published on it.

**Governing law**
These terms are governed by the laws of [jurisdiction].

**Contact**
Questions about these terms can be sent to us using the contact details on our Contact page.

---
*This page is a starting template and should be reviewed by someone familiar with applicable law in your jurisdiction before this site goes live.*
TEXT;
    }

    private static function cookiePolicy()
    {
        return <<<TEXT
Last updated: [date]

This page explains how and why this site uses cookies, and the choices you have.

**What are cookies?**
Small text files stored in your browser that let a website remember information between visits.

**Cookies we use**

*Essential*
- `fpc_cookie_consent` — remembers your choice from the cookie banner, so we don't ask again on every visit. Expires after 180 days.

*Analytics (only set if you accept them)*
- Google Analytics cookies (`_ga`, `_gid`, and related) — help us understand how visitors use the site, in an aggregated, non-identifying way. Only loaded after you choose "Accept All" in the cookie banner.

**Your choices**
When you first visit, a banner lets you choose "Accept All" or "Necessary Only." You can also control cookies through your browser's settings, though blocking essential cookies may affect how the site works.

**Changes to this policy**
We may update this page as the cookies we use change. The "Last updated" date above reflects the most recent change.

---
*This page is a starting template. If you add other tools later (ad pixels, chat widgets, etc.), update this list to match what's actually running on the site.*
TEXT;
    }
}

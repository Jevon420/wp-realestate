<?php

namespace FitsPropertiesCore\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds plain-language guidance to the "Help" dropdown (top-right of the
 * screen) on the property/agent/testimonial edit and list screens, so a
 * non-technical client has an answer without needing to ask a developer.
 */
class HelpTabs
{
    public function register()
    {
        add_action('load-post.php', [$this, 'addForCurrentPostType']);
        add_action('load-post-new.php', [$this, 'addForCurrentPostType']);
        add_action('load-edit.php', [$this, 'addForCurrentPostType']);
    }

    public function addForCurrentPostType()
    {
        global $typenow;

        $screen = get_current_screen();

        if (!$screen) {
            return;
        }

        switch ($typenow) {
            case 'property':
                $this->addTabs($screen, 'property');
                break;
            case 'agent':
                $this->addTabs($screen, 'agent');
                break;
            case 'testimonial':
                $this->addTabs($screen, 'testimonial');
                break;
        }
    }

    private function addTabs($screen, $type)
    {
        $content = $this->content($type);

        foreach ($content as $tab) {
            $screen->add_help_tab([
                'id' => 'fpc-help-' . $type . '-' . sanitize_title($tab['title']),
                'title' => $tab['title'],
                'content' => $tab['content'],
            ]);
        }

        $screen->set_help_sidebar(
            '<p><strong>Need more help?</strong></p>' .
            '<p><a href="' . esc_url(admin_url('admin.php?page=fits-properties-setup')) . '">Fits Properties Setup Guide</a></p>'
        );
    }

    private function content($type)
    {
        if ($type === 'property') {
            return [
                [
                    'title' => 'Adding a Property',
                    'content' => '<p><strong>To add a new listing:</strong> click "Add New" at the top of the page.</p>' .
                        '<ol>' .
                        '<li>Enter the property name as the Title (e.g. "3BR House on Maple St").</li>' .
                        '<li>Write a description in the main text box.</li>' .
                        '<li>Fill in the "Listing & Pricing", "Specifications" and "Address" boxes below the description.</li>' .
                        '<li>Add photos in the "Photo Gallery" box, and set a cover photo using "Featured Image" in the right-hand sidebar.</li>' .
                        '<li>Choose a Property Type, Location, and any Features in the sidebar.</li>' .
                        '<li>Click "Publish" (or "Update" if editing) when you\'re ready to make it live.</li>' .
                        '</ol>',
                ],
                [
                    'title' => 'Featured Properties',
                    'content' => '<p>Turning on "Featured Listing" (in the "Listing & Pricing" box) makes a property appear in the "Featured Properties" section on the homepage. Feature only your best 3–6 listings for the biggest impact.</p>',
                ],
                [
                    'title' => 'Locations & Property Types',
                    'content' => '<p>"Locations" groups properties by Zone and City (city links under a zone in the sidebar). "Property Types" is for categories like House, Apartment, or Commercial. Both work like the "Categories" box you may have used on a blog before — check a box, or click "Add New" to create a new one on the fly.</p>',
                ],
            ];
        }

        if ($type === 'agent') {
            return [
                [
                    'title' => 'Adding an Agent',
                    'content' => '<p><strong>To add a team member:</strong> click "Add New", enter their name as the Title, write their bio in the main text box, upload their photo as the "Featured Image" (sidebar), and fill in their phone/social links in the "Agent Details" box.</p>' .
                        '<p>Once saved, you can select this agent as the "Listing Agent" on any property.</p>',
                ],
            ];
        }

        return [
            [
                'title' => 'Adding a Testimonial',
                'content' => '<p>Use the Title field for the reviewer\'s name (e.g. "The Thompson Family") and the main text box for their review. Set a star rating and, optionally, link it to the property or agent it\'s about.</p>',
            ],
        ];
    }
}

<?php

namespace FitsPropertiesCore\Content;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * One-click sample data so a new site doesn't look empty. Safe to run more
 * than once: everything is keyed by title/slug and skipped if it already
 * exists, so clicking "Install Demo Content" again won't create duplicates.
 */
class DemoContent
{
    public static function seed()
    {
        $summary = [
            'terms' => 0,
            'properties' => 0,
            'agents' => 0,
            'testimonials' => 0,
            'pages' => 0,
            'skipped' => 0,
        ];

        $agentIds = self::createAgents($summary);
        self::createTaxonomyTerms($summary);
        self::createProperties($summary, $agentIds);
        self::createTestimonials($summary, $agentIds);
        self::createPages($summary);
        self::createMenu();

        flush_rewrite_rules();

        return $summary;
    }

    private static function postExistsByTitle($title, $postType)
    {
        $query = new \WP_Query([
            'post_type' => $postType,
            'title' => $title,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
        ]);

        return $query->have_posts() ? (int) $query->posts[0] : 0;
    }

    private static function createTaxonomyTerms(&$summary)
    {
        $types = ['House', 'Apartment', 'Villa', 'Townhouse', 'Commercial', 'Land'];
        foreach ($types as $type) {
            if (!term_exists($type, 'property_type')) {
                wp_insert_term($type, 'property_type');
                $summary['terms']++;
            }
        }

        $features = ['Swimming Pool', 'Home Gym', 'Garden', '24/7 Security', 'Parking Garage', 'Air Conditioning', 'Ocean View', 'Smart Home System'];
        foreach ($features as $feature) {
            if (!term_exists($feature, 'property_feature')) {
                wp_insert_term($feature, 'property_feature');
                $summary['terms']++;
            }
        }

        $zones = [
            'North Zone' => ['Northgate', 'Lakeside'],
            'Central Zone' => ['Midtown', 'Riverside'],
            'South Zone' => ['Bayview', 'Hillcrest'],
        ];

        foreach ($zones as $zoneName => $cities) {
            $zoneTerm = term_exists($zoneName, 'location');
            if (!$zoneTerm) {
                $zoneTerm = wp_insert_term($zoneName, 'location');
                $summary['terms']++;
            }
            $zoneId = is_array($zoneTerm) ? $zoneTerm['term_id'] : $zoneTerm;

            foreach ($cities as $cityName) {
                if (!term_exists($cityName, 'location')) {
                    wp_insert_term($cityName, 'location', ['parent' => $zoneId]);
                    $summary['terms']++;
                }
            }
        }
    }

    private static function createAgents(&$summary)
    {
        $agents = [
            [
                'title' => 'Sarah Mitchell',
                'bio' => 'Sarah has spent nearly a decade helping families find homes they love, specializing in residential sales across the North and Central zones.',
                'photo' => 'https://i.pravatar.cc/400?img=47',
                'meta' => ['fpc_phone' => '(868) 555-0142', 'fpc_specialization' => 'Residential Sales', 'fpc_years_of_experience' => 8, 'fpc_license_number' => 'RE-10234'],
            ],
            [
                'title' => 'James Carter',
                'bio' => 'James focuses on luxury properties and commercial real estate, with a track record of closing high-value deals for discerning clients.',
                'photo' => 'https://i.pravatar.cc/400?img=12',
                'meta' => ['fpc_phone' => '(868) 555-0198', 'fpc_specialization' => 'Luxury & Commercial', 'fpc_years_of_experience' => 12, 'fpc_license_number' => 'RE-10567'],
            ],
            [
                'title' => 'Elena Rodriguez',
                'bio' => 'Elena loves guiding first-time buyers through every step of the process, making a big decision feel simple and stress-free.',
                'photo' => 'https://i.pravatar.cc/400?img=32',
                'meta' => ['fpc_phone' => '(868) 555-0176', 'fpc_specialization' => 'First-Time Buyers', 'fpc_years_of_experience' => 5, 'fpc_license_number' => 'RE-10891'],
            ],
        ];

        $ids = [];

        foreach ($agents as $agent) {
            $existing = self::postExistsByTitle($agent['title'], 'agent');

            if ($existing) {
                $ids[] = $existing;
                $summary['skipped']++;
                continue;
            }

            $postId = wp_insert_post([
                'post_type' => 'agent',
                'post_title' => $agent['title'],
                'post_status' => 'publish',
            ]);

            if (is_wp_error($postId) || !$postId) {
                continue;
            }

            update_post_meta($postId, 'fpc_bio', $agent['bio']);
            update_post_meta($postId, 'fpc_photo_url', $agent['photo']);

            foreach ($agent['meta'] as $key => $value) {
                update_post_meta($postId, $key, $value);
            }

            $ids[] = $postId;
            $summary['agents']++;
        }

        return $ids;
    }

    private static function createProperties(&$summary, $agentIds)
    {
        $properties = [
            [
                'title' => 'Modern Family Home in Northgate',
                'desc' => 'A bright, move-in-ready family home with an open-plan living area, updated kitchen, and a fenced backyard perfect for kids and pets.',
                'type' => 'House', 'location' => 'Northgate', 'features' => ['Garden', 'Parking Garage', 'Air Conditioning'],
                'meta' => ['fpc_listing_type' => 'sale', 'fpc_price' => 450000, 'fpc_bedrooms' => 4, 'fpc_bathrooms' => 3, 'fpc_garage' => 2, 'fpc_area' => 2400, 'fpc_year_built' => 2015, 'fpc_address_line' => '14 Northgate Lane', 'fpc_is_featured' => 1],
            ],
            [
                'title' => 'Luxury Villa with Private Pool',
                'desc' => 'An entertainer\'s dream — five spacious bedrooms, a chef\'s kitchen, and a resort-style pool overlooking landscaped gardens.',
                'type' => 'Villa', 'location' => 'Lakeside', 'features' => ['Swimming Pool', 'Home Gym', '24/7 Security', 'Smart Home System'],
                'meta' => ['fpc_listing_type' => 'sale', 'fpc_price' => 1250000, 'fpc_bedrooms' => 5, 'fpc_bathrooms' => 5, 'fpc_garage' => 3, 'fpc_area' => 5200, 'fpc_year_built' => 2019, 'fpc_address_line' => '2 Lakeside Drive', 'fpc_is_featured' => 1],
            ],
            [
                'title' => 'Cozy Downtown Apartment',
                'desc' => 'A comfortable two-bedroom apartment steps from cafes, shops, and public transit — ideal for young professionals.',
                'type' => 'Apartment', 'location' => 'Midtown', 'features' => ['Air Conditioning', 'Parking Garage'],
                'meta' => ['fpc_listing_type' => 'rent', 'fpc_rental_price' => 1800, 'fpc_rental_frequency' => 'monthly', 'fpc_lease_term' => 12, 'fpc_bedrooms' => 2, 'fpc_bathrooms' => 2, 'fpc_area' => 1100, 'fpc_year_built' => 2012, 'fpc_address_line' => '88 Midtown Ave, Unit 4B'],
            ],
            [
                'title' => 'Riverside Townhouse',
                'desc' => 'A well-kept three-bedroom townhouse just minutes from the river walk, with a private patio and attached garage.',
                'type' => 'Townhouse', 'location' => 'Riverside', 'features' => ['Garden', 'Parking Garage'],
                'meta' => ['fpc_listing_type' => 'sale', 'fpc_price' => 320000, 'fpc_bedrooms' => 3, 'fpc_bathrooms' => 2, 'fpc_garage' => 1, 'fpc_area' => 1800, 'fpc_year_built' => 2008, 'fpc_address_line' => '27 Riverside Court', 'fpc_is_featured' => 1],
            ],
            [
                'title' => 'Modern Studio in Midtown',
                'desc' => 'A stylish, efficient studio with floor-to-ceiling windows, perfect for a single professional or student.',
                'type' => 'Apartment', 'location' => 'Midtown', 'features' => ['Air Conditioning'],
                'meta' => ['fpc_listing_type' => 'rent', 'fpc_rental_price' => 1200, 'fpc_rental_frequency' => 'monthly', 'fpc_lease_term' => 6, 'fpc_bedrooms' => 1, 'fpc_bathrooms' => 1, 'fpc_area' => 650, 'fpc_year_built' => 2020, 'fpc_address_line' => '5 Midtown Square, Unit 12'],
            ],
            [
                'title' => 'Executive Home with Ocean View',
                'desc' => 'Wake up to breathtaking ocean views in this executive four-bedroom home, featuring a wraparound deck and gourmet kitchen.',
                'type' => 'House', 'location' => 'Bayview', 'features' => ['Ocean View', '24/7 Security', 'Air Conditioning'],
                'meta' => ['fpc_listing_type' => 'sale', 'fpc_price' => 875000, 'fpc_bedrooms' => 4, 'fpc_bathrooms' => 4, 'fpc_garage' => 2, 'fpc_area' => 3600, 'fpc_year_built' => 2017, 'fpc_address_line' => '9 Bayview Heights'],
            ],
            [
                'title' => 'Downtown Commercial Space',
                'desc' => 'A versatile ground-floor commercial unit with high foot traffic, ideal for retail or office use.',
                'type' => 'Commercial', 'location' => 'Midtown', 'features' => ['Air Conditioning', '24/7 Security'],
                'meta' => ['fpc_listing_type' => 'rent', 'fpc_rental_price' => 3500, 'fpc_rental_frequency' => 'monthly', 'fpc_lease_term' => 24, 'fpc_area' => 2200, 'fpc_year_built' => 2005, 'fpc_address_line' => '150 Main Street'],
            ],
            [
                'title' => 'Prime Building Lot in Hillcrest',
                'desc' => 'A cleared, ready-to-build lot in a desirable hillside neighborhood with pre-approved plans available on request.',
                'type' => 'Land', 'location' => 'Hillcrest', 'features' => [],
                'meta' => ['fpc_listing_type' => 'sale', 'fpc_price' => 180000, 'fpc_area' => 10000, 'fpc_address_line' => 'Lot 14, Hillcrest Ridge Road'],
            ],
        ];

        foreach ($properties as $index => $property) {
            if (self::postExistsByTitle($property['title'], 'property')) {
                $summary['skipped']++;
                continue;
            }

            $postId = wp_insert_post([
                'post_type' => 'property',
                'post_title' => $property['title'],
                'post_status' => 'publish',
            ]);

            if (is_wp_error($postId) || !$postId) {
                continue;
            }

            update_post_meta($postId, 'fpc_description', $property['desc']);

            foreach ($property['meta'] as $key => $value) {
                update_post_meta($postId, $key, $value);
            }

            if ($agentIds) {
                update_post_meta($postId, 'fpc_agent_id', $agentIds[$index % count($agentIds)]);
            }

            $typeTerm = get_term_by('name', $property['type'], 'property_type');
            if ($typeTerm) {
                wp_set_object_terms($postId, [$typeTerm->term_id], 'property_type');
            }

            $locationTerm = get_term_by('name', $property['location'], 'location');
            if ($locationTerm) {
                wp_set_object_terms($postId, [$locationTerm->term_id], 'location');
            }

            if (!empty($property['features'])) {
                $featureIds = [];
                foreach ($property['features'] as $featureName) {
                    $term = get_term_by('name', $featureName, 'property_feature');
                    if ($term) {
                        $featureIds[] = $term->term_id;
                    }
                }
                wp_set_object_terms($postId, $featureIds, 'property_feature');
            }

            $seed = sanitize_title($property['title']);
            $photoUrls = [
                "https://picsum.photos/seed/{$seed}-1/1200/800",
                "https://picsum.photos/seed/{$seed}-2/1200/800",
                "https://picsum.photos/seed/{$seed}-3/1200/800",
            ];
            update_post_meta($postId, 'fpc_featured_image_url', $photoUrls[0]);
            update_post_meta($postId, 'fpc_gallery_urls', implode("\n", $photoUrls));

            $summary['properties']++;
        }
    }

    private static function createTestimonials(&$summary, $agentIds)
    {
        $testimonials = [
            ['title' => 'The Thompson Family', 'content' => 'Sarah made buying our first home so much easier than we expected. She answered every question and never made us feel rushed.', 'rating' => 5],
            ['title' => 'Michael Chen', 'content' => 'James found us the perfect commercial space in under a month. Professional, responsive, and genuinely knew the market.', 'rating' => 5],
            ['title' => 'Priya Basdeo', 'content' => 'Elena was patient with all of our questions as first-time buyers. We couldn\'t have asked for a better experience.', 'rating' => 4],
        ];

        foreach ($testimonials as $index => $testimonial) {
            if (self::postExistsByTitle($testimonial['title'], 'testimonial')) {
                $summary['skipped']++;
                continue;
            }

            $postId = wp_insert_post([
                'post_type' => 'testimonial',
                'post_title' => $testimonial['title'],
                'post_status' => 'publish',
            ]);

            if (is_wp_error($postId) || !$postId) {
                continue;
            }

            update_post_meta($postId, 'fpc_testimonial_text', $testimonial['content']);
            update_post_meta($postId, 'fpc_rating', $testimonial['rating']);

            if (!empty($agentIds[$index])) {
                update_post_meta($postId, 'fpc_related_post', $agentIds[$index]);
            }

            $summary['testimonials']++;
        }
    }

    private static function createPages(&$summary)
    {
        $pages = [
            [
                'title' => 'About',
                'slug' => 'about',
                'content' => "Fits Properties has been helping people buy, sell, and rent homes for years. Our team of experienced agents is dedicated to making your real estate journey simple and successful.\n\nWhether you're searching for your first home, upgrading to your dream house, or investing in commercial property, we're here to guide you every step of the way.",
            ],
            [
                'title' => 'Contact',
                'slug' => 'contact',
                'content' => "We'd love to hear from you. Reach out using the form below, give us a call, or stop by our office — our team typically responds within one business day.",
                'template' => 'page-contact.php',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => 'This is placeholder privacy policy content. Replace this with your actual privacy policy before the site goes live.',
            ],
            [
                'title' => 'Terms of Use',
                'slug' => 'terms-of-use',
                'content' => 'This is placeholder terms of use content. Replace this with your actual terms before the site goes live.',
            ],
        ];

        foreach ($pages as $page) {
            $existing = get_page_by_path($page['slug']);

            if ($existing) {
                $summary['skipped']++;
                continue;
            }

            $postId = wp_insert_post([
                'post_type' => 'page',
                'post_title' => $page['title'],
                'post_name' => $page['slug'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
            ]);

            if (is_wp_error($postId) || !$postId) {
                continue;
            }

            if (!empty($page['template'])) {
                update_post_meta($postId, '_wp_page_template', $page['template']);
            }

            $summary['pages']++;
        }
    }

    private static function createMenu()
    {
        $menuName = 'Fits Properties Main Menu';
        $menu = wp_get_nav_menu_object($menuName);

        if ($menu) {
            return;
        }

        $menuId = wp_create_nav_menu($menuName);

        $items = [
            ['title' => 'Home', 'url' => home_url('/')],
            ['title' => 'Properties', 'url' => get_post_type_archive_link('property')],
            ['title' => 'Agents', 'url' => get_post_type_archive_link('agent')],
        ];

        $about = get_page_by_path('about');
        if ($about) {
            $items[] = ['title' => 'About', 'url' => get_permalink($about)];
        }

        $contact = get_page_by_path('contact');
        if ($contact) {
            $items[] = ['title' => 'Contact', 'url' => get_permalink($contact)];
        }

        foreach ($items as $item) {
            wp_update_nav_menu_item($menuId, 0, [
                'menu-item-title' => $item['title'],
                'menu-item-url' => $item['url'],
                'menu-item-status' => 'publish',
            ]);
        }

        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['primary'] = $menuId;
        $locations['footer'] = $menuId;
        set_theme_mod('nav_menu_locations', $locations);
    }
}

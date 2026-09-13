<?php
/**
 * Lightweight built-in SEO: meta description, Open Graph/Twitter Card tags,
 * and JSON-LD structured data. Deliberately not a full SEO plugin (no
 * per-page override fields) — it auto-generates sensible tags from content
 * that already exists, which covers a simple site without adding UI.
 */
if (!defined('ABSPATH')) {
    exit;
}

function fp_seo_trim($text, $length = 160)
{
    $text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($text)));

    if (strlen($text) <= $length) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $length)) . '…';
}

function fp_seo_context()
{
    $siteName = get_bloginfo('name');

    if (is_singular('property')) {
        $id = get_the_ID();

        return [
            'title' => get_the_title() . ' | ' . $siteName,
            'description' => fp_seo_trim(get_post_meta($id, 'fpc_description', true)) ?: fp_seo_trim(get_bloginfo('description')),
            'image' => fp_featured_image_url($id, 'fpc_featured_image_url', 'large'),
            'type' => 'product',
        ];
    }

    if (is_singular('agent')) {
        $id = get_the_ID();

        return [
            'title' => get_the_title() . ' | ' . $siteName,
            'description' => fp_seo_trim(get_post_meta($id, 'fpc_bio', true)) ?: fp_seo_trim(get_bloginfo('description')),
            'image' => fp_featured_image_url($id, 'fpc_photo_url', 'large'),
            'type' => 'profile',
        ];
    }

    if (is_singular()) {
        $id = get_the_ID();
        $excerpt = has_excerpt($id) ? get_the_excerpt($id) : get_post_field('post_content', $id);

        return [
            'title' => get_the_title() . ' | ' . $siteName,
            'description' => fp_seo_trim($excerpt) ?: fp_seo_trim(get_bloginfo('description')),
            'image' => has_post_thumbnail($id) ? get_the_post_thumbnail_url($id, 'large') : '',
            'type' => 'article',
        ];
    }

    if (is_post_type_archive('property') || is_tax('location') || is_tax('property_type')) {
        return [
            'title' => wp_get_document_title(),
            'description' => fp_seo_trim(get_bloginfo('description')) ?: 'Browse our current property listings.',
            'image' => '',
            'type' => 'website',
        ];
    }

    return [
        'title' => wp_get_document_title(),
        'description' => fp_seo_trim(get_bloginfo('description')),
        'image' => '',
        'type' => 'website',
    ];
}

function fp_seo_current_url()
{
    if (is_singular()) {
        return wp_get_canonical_url() ?: get_permalink();
    }

    global $wp;

    return home_url(add_query_arg([], $wp->request));
}

add_action('wp_head', function () {
    $context = fp_seo_context();
    ?>
    <?php if ($context['description']) : ?>
        <meta name="description" content="<?php echo esc_attr($context['description']); ?>">
    <?php endif; ?>

    <meta property="og:type" content="<?php echo esc_attr($context['type']); ?>">
    <meta property="og:title" content="<?php echo esc_attr($context['title']); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <meta property="og:url" content="<?php echo esc_url(fp_seo_current_url()); ?>">
    <?php if ($context['description']) : ?>
        <meta property="og:description" content="<?php echo esc_attr($context['description']); ?>">
    <?php endif; ?>
    <?php if ($context['image']) : ?>
        <meta property="og:image" content="<?php echo esc_url($context['image']); ?>">
    <?php endif; ?>

    <meta name="twitter:card" content="<?php echo $context['image'] ? 'summary_large_image' : 'summary'; ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($context['title']); ?>">
    <?php if ($context['description']) : ?>
        <meta name="twitter:description" content="<?php echo esc_attr($context['description']); ?>">
    <?php endif; ?>
    <?php if ($context['image']) : ?>
        <meta name="twitter:image" content="<?php echo esc_url($context['image']); ?>">
    <?php endif; ?>
    <?php
}, 5);

/**
 * JSON-LD structured data: an Organization on every page (so search
 * engines can associate the whole site with the business), plus a
 * Product+Offer for property pages and a Person for agent pages.
 */
add_action('wp_head', function () {
    $currency = get_theme_mod('fp_currency_code', 'USD') ?: 'USD';

    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateAgent',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'description' => get_bloginfo('description'),
    ];

    $phone = get_theme_mod('fp_contact_phone');
    $email = get_theme_mod('fp_contact_email');
    $address = get_theme_mod('fp_contact_address');

    if ($phone) {
        $organization['telephone'] = $phone;
    }
    if ($email) {
        $organization['email'] = $email;
    }
    if ($address) {
        $organization['address'] = $address;
    }
    if (has_custom_logo()) {
        $logoId = get_theme_mod('custom_logo');
        $logoUrl = $logoId ? wp_get_attachment_image_url($logoId, 'medium') : '';
        if ($logoUrl) {
            $organization['logo'] = $logoUrl;
        }
    }

    echo '<script type="application/ld+json">' . wp_json_encode($organization) . '</script>' . "\n";

    if (is_singular('property')) {
        $id = get_the_ID();
        $listingType = get_post_meta($id, 'fpc_listing_type', true);
        $status = get_post_meta($id, 'fpc_status', true) ?: 'active';
        $price = $listingType === 'rent' ? get_post_meta($id, 'fpc_rental_price', true) : get_post_meta($id, 'fpc_price', true);

        $product = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => get_the_title(),
            'description' => fp_seo_trim(get_post_meta($id, 'fpc_description', true), 500),
            'url' => get_permalink(),
        ];

        $image = fp_featured_image_url($id, 'fpc_featured_image_url', 'large');
        if ($image) {
            $product['image'] = $image;
        }

        if ($price !== '') {
            $product['offers'] = [
                '@type' => 'Offer',
                'price' => (string) $price,
                'priceCurrency' => $currency,
                'availability' => in_array($status, ['sold', 'rented'], true) ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
                'url' => get_permalink(),
            ];
        }

        echo '<script type="application/ld+json">' . wp_json_encode($product) . '</script>' . "\n";
    }

    if (is_singular('agent')) {
        $id = get_the_ID();

        $person = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => get_the_title(),
            'url' => get_permalink(),
            'jobTitle' => get_post_meta($id, 'fpc_specialization', true) ?: 'Real Estate Agent',
        ];

        $agentPhone = get_post_meta($id, 'fpc_phone', true);
        if ($agentPhone) {
            $person['telephone'] = $agentPhone;
        }

        $agentImage = fp_featured_image_url($id, 'fpc_photo_url', 'large');
        if ($agentImage) {
            $person['image'] = $agentImage;
        }

        echo '<script type="application/ld+json">' . wp_json_encode($person) . '</script>' . "\n";
    }
});

<?php

if (!defined('ABSPATH')) {
    exit;
}

function fp_format_price($amount)
{
    if ($amount === '' || $amount === null) {
        return null;
    }

    return '$' . number_format((float) $amount, 0);
}

function fp_property_price_label($postId)
{
    $listingType = get_post_meta($postId, 'fpc_listing_type', true);

    if ($listingType === 'rent') {
        $rentalPrice = get_post_meta($postId, 'fpc_rental_price', true);
        $frequency = get_post_meta($postId, 'fpc_rental_frequency', true) ?: 'monthly';
        $frequencyLabels = ['monthly' => 'month', 'weekly' => 'week', 'yearly' => 'year'];
        $price = fp_format_price($rentalPrice);

        return $price ? $price . ' / ' . ($frequencyLabels[$frequency] ?? $frequency) : 'Price on request';
    }

    $price = fp_format_price(get_post_meta($postId, 'fpc_price', true));

    return $price ?: 'Price on request';
}

/**
 * A property's status (Sold/Rented/Pending) takes priority over its plain
 * Sale/Rent listing type for badge display — visitors should see "Sold"
 * before "For Sale" on a property that's no longer available.
 */
function fp_property_badge($postId)
{
    $status = get_post_meta($postId, 'fpc_status', true) ?: 'active';

    if ($status !== 'active') {
        $labels = ['pending' => 'Pending', 'sold' => 'Sold', 'rented' => 'Rented'];

        return ['label' => $labels[$status] ?? ucfirst($status), 'modifier' => $status];
    }

    $listingType = get_post_meta($postId, 'fpc_listing_type', true);

    return [
        'label' => $listingType === 'rent' ? 'For Rent' : 'For Sale',
        'modifier' => $listingType === 'rent' ? 'rent' : 'sale',
    ];
}

function fp_property_gallery_ids($postId)
{
    $ids = get_post_meta($postId, 'fpc_gallery', true);

    return $ids ? array_filter(array_map('intval', explode(',', $ids))) : [];
}

function fp_property_locations($postId)
{
    return get_the_terms($postId, 'location');
}

function fp_property_location_label($postId)
{
    $terms = fp_property_locations($postId);

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    // Prefer the most specific (child/city) term.
    usort($terms, function ($a, $b) {
        return $b->parent <=> $a->parent;
    });

    $city = $terms[0];
    $zone = $city->parent ? get_term($city->parent, 'location') : null;

    if ($zone && !is_wp_error($zone)) {
        return $city->name . ', ' . $zone->name;
    }

    return $city->name;
}

function fp_stars($rating, $max = 5)
{
    $rating = (int) $rating;
    $out = '';

    for ($i = 1; $i <= $max; $i++) {
        $out .= $i <= $rating ? '&#9733;' : '&#9734;';
    }

    return $out;
}

function fp_agent_for_property($postId)
{
    $agentId = (int) get_post_meta($postId, 'fpc_agent_id', true);

    if (!$agentId) {
        return null;
    }

    $agent = get_post($agentId);

    return ($agent && $agent->post_type === 'agent' && $agent->post_status === 'publish') ? $agent : null;
}

function fp_property_specs($postId)
{
    return [
        'bedrooms' => get_post_meta($postId, 'fpc_bedrooms', true),
        'bathrooms' => get_post_meta($postId, 'fpc_bathrooms', true),
        'area' => get_post_meta($postId, 'fpc_area', true),
        'garage' => get_post_meta($postId, 'fpc_garage', true),
    ];
}

/**
 * Renders a plain-text field (saved via a <textarea> meta box, not the
 * block editor) as paragraphs, escaping it since it isn't rich HTML.
 */
function fp_rich_text($postId, $metaKey)
{
    $value = get_post_meta($postId, $metaKey, true);

    if ($value === '') {
        return '';
    }

    return wpautop(esc_html($value));
}

/**
 * Attributes added to every lazy-loaded <img> we output: native lazy
 * loading, async decoding, and an inline onload that fades the image in
 * and removes its skeleton placeholder (see .fp-img-wrap in main.css).
 * Inline is used instead of a JS observer so it works for cards inserted
 * later by "Load More" without any extra wiring.
 */
function fp_lazy_img_attrs($attrs = [])
{
    return array_merge([
        'loading' => 'lazy',
        'decoding' => 'async',
        'onload' => "this.closest('.fp-img-wrap').classList.add('is-loaded')",
    ], $attrs);
}

function fp_wrap_img($html)
{
    return $html ? '<span class="fp-img-wrap">' . $html . '</span>' : '';
}

/**
 * Cover/featured image markup for a post: prefers the native WordPress
 * Featured Image, falls back to a pasted image URL (fpc_featured_image_url
 * for properties, fpc_photo_url for agents), or '' if neither is set.
 */
/**
 * Same fallback logic as fp_featured_image_html() (Featured Image, else
 * the pasted URL field) but returns a plain URL string — for use in meta
 * tags (og:image) rather than rendered <img> markup.
 */
function fp_featured_image_url($postId, $urlMetaKey, $size = 'large')
{
    if (has_post_thumbnail($postId)) {
        $url = get_the_post_thumbnail_url($postId, $size);

        return $url ?: '';
    }

    return get_post_meta($postId, $urlMetaKey, true);
}

function fp_featured_image_html($postId, $urlMetaKey, $size = 'large', $attrs = [])
{
    $attrs = fp_lazy_img_attrs($attrs);

    if (has_post_thumbnail($postId)) {
        return fp_wrap_img(get_the_post_thumbnail($postId, $size, $attrs));
    }

    $url = get_post_meta($postId, $urlMetaKey, true);

    if (!$url) {
        return '';
    }

    $attrString = '';
    foreach ($attrs as $key => $value) {
        $attrString .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }

    return fp_wrap_img('<img src="' . esc_url($url) . '" alt="' . esc_attr(get_the_title($postId)) . '"' . $attrString . '>');
}

/**
 * Combined photo gallery for a property: uploaded media (fpc_gallery) plus
 * pasted links (fpc_gallery_urls), each item shaped the same way so
 * templates don't need to care which kind it is.
 */
function fp_gallery_items($postId)
{
    $items = [];

    foreach (fp_property_gallery_ids($postId) as $id) {
        $items[] = ['type' => 'id', 'id' => $id];
    }

    $urls = get_post_meta($postId, 'fpc_gallery_urls', true);

    if ($urls) {
        foreach (preg_split('/\r\n|\r|\n/', $urls) as $url) {
            $url = trim($url);
            if ($url !== '') {
                $items[] = ['type' => 'url', 'url' => $url];
            }
        }
    }

    return $items;
}

function fp_gallery_item_thumb_html($item, $size = [80, 80])
{
    if ($item['type'] === 'id') {
        $attrs = fp_lazy_img_attrs(['style' => 'object-fit:cover;']);

        return fp_wrap_img(wp_get_attachment_image($item['id'], $size, false, $attrs));
    }

    $style = 'width:' . (int) $size[0] . 'px;height:' . (int) $size[1] . 'px;object-fit:cover;';
    $attrs = fp_lazy_img_attrs(['style' => $style]);

    $attrString = '';
    foreach ($attrs as $key => $value) {
        $attrString .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }

    return fp_wrap_img('<img src="' . esc_url($item['url']) . '"' . $attrString . '>');
}

function fp_gallery_item_full_url($item)
{
    if ($item['type'] === 'id') {
        return wp_get_attachment_image_url($item['id'], 'large');
    }

    return $item['url'];
}

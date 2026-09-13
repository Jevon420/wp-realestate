<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$locations = fp_get_top_level_locations();
$cities = fp_get_cities();
$types = fp_get_property_types(20);
$features = fp_get_property_features();
$archiveLink = get_post_type_archive_link('property');
?>

<section class="fp-page-header">
    <div class="fp-container">
        <h1>Properties</h1>
    </div>
</section>

<section class="fp-section">
    <div class="fp-container fp-listing">
        <aside class="fp-filters">
            <form method="get">
                <div class="fp-filters__group">
                    <label for="fp-filter-s">Keyword</label>
                    <input type="text" id="fp-filter-s" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
                </div>

                <div class="fp-filters__group">
                    <label for="fp-filter-sort">Sort By</label>
                    <select id="fp-filter-sort" name="fp_sort">
                        <?php
                        $sortOptions = [
                            'newest' => 'Newest',
                            'price_low' => 'Price: Low to High',
                            'price_high' => 'Price: High to Low',
                            'beds_high' => 'Most Bedrooms',
                        ];
                        $currentSort = $_GET['fp_sort'] ?? 'newest';
                        foreach ($sortOptions as $value => $label) :
                        ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($currentSort, $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="fp-filters__group">
                    <label for="fp-filter-listing-type">Listing Type</label>
                    <select id="fp-filter-listing-type" name="fp_listing_type">
                        <option value="">Any</option>
                        <option value="sale" <?php selected($_GET['fp_listing_type'] ?? '', 'sale'); ?>>For Sale</option>
                        <option value="rent" <?php selected($_GET['fp_listing_type'] ?? '', 'rent'); ?>>For Rent</option>
                    </select>
                </div>

                <?php if (!empty($types) && !is_wp_error($types)) : ?>
                    <div class="fp-filters__group">
                        <label for="fp-filter-type">Property Type</label>
                        <select id="fp-filter-type" name="fp_type">
                            <option value="">Any</option>
                            <?php foreach ($types as $type) : ?>
                                <option value="<?php echo esc_attr($type->slug); ?>" <?php selected($_GET['fp_type'] ?? '', $type->slug); ?>>
                                    <?php echo esc_html($type->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (!empty($locations) && !is_wp_error($locations)) : ?>
                    <div class="fp-filters__group">
                        <label for="fp-filter-location">Zone</label>
                        <select id="fp-filter-location" name="fp_location">
                            <option value="">Any</option>
                            <?php foreach ($locations as $location) : ?>
                                <option value="<?php echo esc_attr($location->slug); ?>" <?php selected($_GET['fp_location'] ?? '', $location->slug); ?>>
                                    <?php echo esc_html($location->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if (!empty($cities)) : ?>
                    <div class="fp-filters__group">
                        <label for="fp-filter-city">City</label>
                        <select id="fp-filter-city" name="fp_city">
                            <option value="">Any</option>
                            <?php foreach ($cities as $city) : ?>
                                <option value="<?php echo esc_attr($city->slug); ?>" <?php selected($_GET['fp_city'] ?? '', $city->slug); ?>>
                                    <?php echo esc_html($city->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="fp-filters__group fp-filters__group--split">
                    <div>
                        <label for="fp-filter-min">Min Price</label>
                        <input type="number" id="fp-filter-min" name="fp_min_price" value="<?php echo esc_attr($_GET['fp_min_price'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="fp-filter-max">Max Price</label>
                        <input type="number" id="fp-filter-max" name="fp_max_price" value="<?php echo esc_attr($_GET['fp_max_price'] ?? ''); ?>">
                    </div>
                </div>

                <details class="fp-filters__more" <?php echo (!empty($_GET['fp_min_beds']) || !empty($_GET['fp_min_baths']) || !empty($_GET['fp_furnished']) || !empty($_GET['fp_features'])) ? 'open' : ''; ?>>
                    <summary>More Filters</summary>

                    <div class="fp-filters__group fp-filters__group--split">
                        <div>
                            <label for="fp-filter-beds">Min Beds</label>
                            <select id="fp-filter-beds" name="fp_min_beds">
                                <option value="">Any</option>
                                <?php foreach ([1, 2, 3, 4, 5] as $n) : ?>
                                    <option value="<?php echo $n; ?>" <?php selected($_GET['fp_min_beds'] ?? '', (string) $n); ?>><?php echo $n; ?>+</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="fp-filter-baths">Min Baths</label>
                            <select id="fp-filter-baths" name="fp_min_baths">
                                <option value="">Any</option>
                                <?php foreach ([1, 2, 3, 4] as $n) : ?>
                                    <option value="<?php echo $n; ?>" <?php selected($_GET['fp_min_baths'] ?? '', (string) $n); ?>><?php echo $n; ?>+</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="fp-filters__group">
                        <label class="fp-filters__checkbox">
                            <input type="checkbox" name="fp_furnished" value="1" <?php checked(!empty($_GET['fp_furnished'])); ?>>
                            Furnished only
                        </label>
                    </div>

                    <?php if (!empty($features) && !is_wp_error($features)) : ?>
                        <div class="fp-filters__group">
                            <label>Features</label>
                            <?php $selectedFeatures = $_GET['fp_features'] ?? []; ?>
                            <?php foreach ($features as $feature) : ?>
                                <label class="fp-filters__checkbox">
                                    <input type="checkbox" name="fp_features[]" value="<?php echo esc_attr($feature->slug); ?>" <?php checked(in_array($feature->slug, (array) $selectedFeatures, true)); ?>>
                                    <?php echo esc_html($feature->name); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </details>

                <button type="submit" class="fp-btn fp-btn--primary fp-filters__submit">Apply Filters</button>
                <a class="fp-filters__clear" href="<?php echo esc_url($archiveLink); ?>">Clear all filters</a>
            </form>
        </aside>

        <div class="fp-listing__results">
            <?php if (have_posts()) : ?>
                <p class="fp-listing__count"><?php echo esc_html($wp_query->found_posts); ?> propert<?php echo $wp_query->found_posts === 1 ? 'y' : 'ies'; ?> found</p>
                <div class="fp-grid fp-grid--3" id="fp-property-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/property-card'); ?>
                    <?php endwhile; ?>
                </div>

                <?php if ($wp_query->max_num_pages > 1) : ?>
                    <div class="fp-load-more">
                        <button type="button" id="fp-load-more-btn" class="fp-btn fp-btn--primary" data-target="fp-property-grid" data-max-pages="<?php echo (int) $wp_query->max_num_pages; ?>">
                            Load More
                        </button>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <p>No properties matched your search. Try adjusting your filters.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>

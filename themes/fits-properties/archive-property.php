<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$locations = fp_get_top_level_locations();
$types = fp_get_property_types(20);
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

                <button type="submit" class="fp-btn fp-btn--primary fp-filters__submit">Apply Filters</button>
            </form>
        </aside>

        <div class="fp-listing__results">
            <?php if (have_posts()) : ?>
                <div class="fp-grid fp-grid--3">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/property-card'); ?>
                    <?php endwhile; ?>
                </div>

                <div class="fp-pagination">
                    <?php the_posts_pagination(); ?>
                </div>
            <?php else : ?>
                <p>No properties matched your search. Try adjusting your filters.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>

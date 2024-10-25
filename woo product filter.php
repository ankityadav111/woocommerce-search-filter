<?php
function custom_woocommerce_product_display_shortcode($atts) {
    ob_start();
    ?>
    <!-- Filter Form -->
    <form id="ajax-search-form" method="GET">
        <div>
            <!-- Search Input -->
            <input type="text" id="ajax-search-input" name="search" placeholder="Search products...">
        </div>
        
        <div>
            <!-- Product Category Dropdown -->
            <?php 
            $categories = get_terms('product_cat', array('hide_empty' => false));
            if (!empty($categories) && !is_wp_error($categories)) { ?>
                <select id="ajax-category-select" name="category">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?php echo esc_attr($category->slug); ?>">
                            <?php echo esc_html($category->name); ?>
                        </option>
                    <?php } ?>
                </select>
            <?php } ?>
        </div>
        
        <div>
            <!-- Search Button -->
            <button type="submit" id="ajax-search-button">Search</button>
        </div>
    </form>

    <!-- Results Area (Initially Hidden) -->
    <div id="ajax-search-results" style="display:none;"></div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#ajax-search-form').on('submit', function(e) {
                e.preventDefault();

                var search = $('#ajax-search-input').val();
                var category = $('#ajax-category-select').val();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'GET',
                    data: {
                        action: 'custom_woocommerce_ajax_filter',
                        search: search,
                        category: category
                    },
                    success: function(response) {
                        $('#ajax-search-results').html(response).slideDown(); // Show the results area with animation
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('woocommerce_ajax_filter', 'custom_woocommerce_product_display_shortcode');


function custom_woocommerce_ajax_filter() {
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $category_slug = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1, // Show all matching products
        's' => $search_query,
    );

    if (!empty($category_slug)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category_slug,
            ),
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) : ?>
        <div class="custom-products-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="custom-product-item">
                    <div class="swiper-slide bg-product-img" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>');">
                        <a href="<?php echo get_permalink(); ?>">
                            <h3><?php the_title(); ?></h3>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p>No products found.</p>
    <?php endif;

    wp_reset_postdata();

    wp_die();
}
add_action('wp_ajax_custom_woocommerce_ajax_filter', 'custom_woocommerce_ajax_filter');
add_action('wp_ajax_nopriv_custom_woocommerce_ajax_filter', 'custom_woocommerce_ajax_filter');

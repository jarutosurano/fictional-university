<?php

function pageBanner($args = NULL) {
    // Fallback to post title if no title is provided
    if(!isset($args['title'])) {
        $args['title'] = get_the_title();
    }
    // Fallback to custom field subtitle if no subtitle is provided
    if(!isset($args['subtitle'])) {
        $args['subtitle'] = get_field('page_banner_subtitle');
    }
    // Fallback to custom field image or default image if no image is provided
    if (empty($args['photo'])) {
        if(get_field('page_banner_background_image') AND !is_archive() AND !is_home()) {
            $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
        } else {
            $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
        }
    }
    ?>
    <div class="page-banner">
        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>)"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title']; ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle']; ?></p>
            </div>
        </div>
    </div>
<?php }

function university_files() {
    // Enqueue JavaScript and styles for the theme
    $google_map_api_key = defined('GOOGLE_MAPS_API_KEY') ? constant('GOOGLE_MAPS_API_KEY') : '';
    wp_enqueue_script( 'googleMap', '//maps.googleapis.com/maps/api/js?key=' . $google_map_api_key, NULL, '1.0', true);
    wp_enqueue_script( 'main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
    wp_enqueue_style( 'fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style( 'custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style( 'university_main_style', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style( 'university_extra_style', get_theme_file_uri('/build/index.css'));
}
add_action('wp_enqueue_scripts', 'university_files');

function university_features() {
    // Register navigation menus
    register_nav_menu('headerMenuLocation', 'Header Menu');
    register_nav_menu('footerLocationOne', 'Footer Location One');
    register_nav_menu('footerLocationTwo', 'Footer Location Two');

    // Enable support for title tag and post thumbnails
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    // Register custom image sizes
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500,350, true);
}
add_action('after_setup_theme', 'university_features');

function university_adjust_queries($query) {
    // Modify campus archive query: show all markers
    if (!is_admin() AND is_post_type_archive('campus') && is_main_query()) {
        $query->set('posts_per_page', '-1');
    }
    // Modify program archive query: order by title, show all posts
    if (!is_admin() AND is_post_type_archive('program') && is_main_query()) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', '-1');
    }

    // Modify event archive query: show only future events, ordered by event date
    if (!is_admin() && is_post_type_archive('event') && $query->is_main_query()) {
        $today = current_time('Ymd'); // Get the current date

        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');

        // Only show events happening today or later
        $meta_query = array(
            array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
            )
        );
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'university_adjust_queries');

function universityMapKey($api) {
    if(defined('GOOGLE_MAPS_API_KEY')) {
        $api['key'] = constant('GOOGLE_MAPS_API_KEY');
    }
    return $api;
}

add_filter('acf/fields/google_map/api', 'universityMapKey');

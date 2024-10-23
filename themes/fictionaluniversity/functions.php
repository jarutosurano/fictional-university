<?php

require get_theme_file_path('/inc/search-route.php');
function university_custom_rest() {
    register_rest_field('post','authorName',array(
        'get_callback' =>  function () {return get_the_author();}
    ) );
}
add_action('rest_api_init', 'university_custom_rest');

// Display page banner with customizable title, subtitle, and background image
function pageBanner($args = NULL) {
    // Use the current post title if no title is provided
    if (!isset($args['title'])) {
        $args['title'] = get_the_title();
    }
    // Use the custom field 'page_banner_subtitle' if no subtitle is provided
    if (!isset($args['subtitle'])) {
        $args['subtitle'] = get_field('page_banner_subtitle');
    }
    // Use a custom field image or default image if no background photo is provided
    if (empty($args['photo'])) {
        if (get_field('page_banner_background_image') && !is_archive() && !is_home()) {
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

// Enqueue styles and scripts for the theme
function university_files() {
    $google_map_api_key = defined('GOOGLE_MAPS_API_KEY') ? constant('GOOGLE_MAPS_API_KEY') : '';

    // Enqueue Google Maps API if API key is defined
    wp_enqueue_script('googleMap', '//maps.googleapis.com/maps/api/js?key=' . $google_map_api_key, NULL, '1.0', true);

    // Enqueue custom JavaScript and styles
    wp_enqueue_script('main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
    wp_enqueue_style('fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('university_main_style', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_style', get_theme_file_uri('/build/index.css'));

    // Localize root URL for use in JavaScript
    wp_localize_script('main-university-js', 'universityLocalize', array(
        'root_url' => get_site_url()
    ));
}
add_action('wp_enqueue_scripts', 'university_files');

// Theme setup: register menus, add support for title tags and post thumbnails
function university_features() {
    register_nav_menu('headerMenuLocation', 'Header Menu');
    register_nav_menu('footerLocationOne', 'Footer Location One');
    register_nav_menu('footerLocationTwo', 'Footer Location Two');

    // Enable title and thumbnail support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    // Register custom image sizes
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
}
add_action('after_setup_theme', 'university_features');

// Modify queries for custom post types
function university_adjust_queries($query) {
    // Display all campuses in the archive
    if (!is_admin() && is_post_type_archive('campus') && $query->is_main_query()) {
        $query->set('posts_per_page', '-1');
    }
    // Sort programs alphabetically, show all posts
    if (!is_admin() && is_post_type_archive('program') && $query->is_main_query()) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', '-1');
    }
    // Show only future events, sorted by event date
    if (!is_admin() && is_post_type_archive('event') && $query->is_main_query()) {
        $today = current_time('Ymd');

        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');

        // Show events happening today or later
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

// Inject Google Maps API key for Advanced Custom Fields (ACF)
function universityMapKey($api) {
    if (defined('GOOGLE_MAPS_API_KEY')) {
        $api['key'] = constant('GOOGLE_MAPS_API_KEY');
    }
    return $api;
}
add_filter('acf/fields/google_map/api', 'universityMapKey');

// Redirect subscriber accounts away from the admin dashboard to the homepage
add_action('admin_init', 'redirectSubscribersToFrontend');

function redirectSubscribersToFrontend() {
    // Get the current user object
    $currentUser = wp_get_current_user();

    // Check if the user has the 'subscriber' role
    if (count($currentUser->roles) === 1 && $currentUser->roles[0] === 'subscriber') {
        // Redirect the subscriber to the homepage
        wp_redirect(site_url('/'));
        exit;
    }
}

// Disable the admin bar for subscriber accounts
add_action('wp_loaded', 'disableAdminBarForSubscribers');

function disableAdminBarForSubscribers() {
    // Get the current user object
    $currentUser = wp_get_current_user();

    // Check if the user has the 'subscriber' role
    if (count($currentUser->roles) === 1 && $currentUser->roles[0] === 'subscriber') {
        // Hide the admin bar for subscribers
        show_admin_bar(false);
    }
}

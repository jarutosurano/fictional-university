<?php
function university_files() {
    wp_enqueue_script( 'main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
    wp_enqueue_style( 'fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style( 'custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style( 'university_main_style', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style( 'university_extra_style', get_theme_file_uri('/build/index.css'));
}
add_action('wp_enqueue_scripts', 'university_files');


function university_features() {
    register_nav_menu('headerMenuLocation', 'Header Menu');
    register_nav_menu('footerLocationOne', 'Footer Location One');
    register_nav_menu('footerLocationTwo', 'Footer Location Two');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
}
add_action('after_setup_theme', 'university_features');


function university_adjust_queries($query) {
    if (!is_admin() AND is_post_type_archive('program') && is_main_query()) {
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', '-1');
    }
    // Ensure this runs only on the front end, for the event post type, and for the main query
    if (!is_admin() && is_post_type_archive('event') && $query->is_main_query()) {
        $today = current_time('Ymd'); // Get current date with timezone support

        // Set query parameters to only show upcoming events
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num'); // Use 'meta_value_num' for numeric ordering
        $query->set('order', 'ASC');

        // Meta query to filter events happening today or later
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


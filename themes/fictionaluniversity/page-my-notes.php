<?php

// Redirect to homepage if the user is not logged in
if (!is_user_logged_in()) {
    wp_redirect(esc_url(home_url()));
    exit;
}

get_header();

while (have_posts()) {
    the_post();
    pageBanner();
    ?>

    <div class="container container--narrow page-section">
        <ul class="min-list link-list" id="my-notes">
            <?php
            // Query to get all notes for the logged-in user
            $userNotes = new WP_Query(array(
                'post_type' => 'note',
                'posts_per_page' => -1,
                'author' => get_current_user_id()
            ));

            // Loop through each note
            while ($userNotes->have_posts()) {
                $userNotes->the_post(); ?>
                <li>
                    <input class="note-title-field" value="<?php echo esc_attr(get_the_title()); ?>">
                    <span class="edit-note"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</span>
                    <span class="delete-note"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</span>
                    <textarea class="note-body-field"><?php echo esc_attr(wp_strip_all_tags(get_the_content())); ?></textarea>
                </li>
            <?php }
            wp_reset_postdata(); // Reset post data after custom query
            ?>
        </ul>
    </div>
<?php }

get_footer();
?>

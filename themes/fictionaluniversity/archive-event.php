<?php
get_header();
pageBanner(array(
    'title' => 'University Events Calendar',
    'subtitle' => 'Stay Updated on Campus Events and Workshops.'
));
?>

<div class="container container--narrow page-section">
    <?php
    while ( have_posts() ) {
        the_post();
        get_template_part('template-parts/content','event');
        }
    echo paginate_links();
    ?>
    <hr class="section-break">
    <p>Looking for a recap of past events? <a href="<?php echo site_url('/past-events'); ?>">Check out our past events archive &raquo;</a>.</p>
</div>
<?php get_footer();

?>


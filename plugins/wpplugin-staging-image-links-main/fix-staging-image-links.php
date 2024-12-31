<?php

/**
 * Plugin Name: Fix Staging Image Links
 * Plugin URI: https://jarutosurano.io
 * Description: Simple plugin to replace image domain URLs from staging to live in your WordPress site. Fixes broken image links on the staging site by replacing staging site URLs with your live domain, ensuring images from the staging site are correctly linked without copying the large uploads folder.
 * Version: 1.0.0
 * Author: jarutosurano
 * Author URI: https://jarutosurano.io
 * License: GPLv2 or later
 * Text Domain: fix-staging-image-links
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class FixStagingImageLinks
{
    function __construct()
    {
        add_action('admin_menu', [$this, 'adminPage']); // Add menu page for the plugin
        add_action('wp_footer', 'fix_staging_image_links_enqueue_scripts', 99999);
    }

    function adminPage()
    {
        add_menu_page(
            'Fix Staging Image Links',
            'Fix Staging Image Links',
            'manage_options',
            'fix-staging-image-links',
            [$this, 'adminPageDisplay'],
            'dashicons-image-crop', 100
        );
    }

    function fix_staging_image_links_enqueue_scripts()
    {
        if (!is_admin()) {
            // Retrieve the saved live URL from the database
            $live_domain = get_option('plugins_live_url', '');
            // Automatically determine the staging domain by modifying the current site URL
            $staging_url = site_url();
            $staging_domain = $this->getStagingDomain($staging_url);

            ?>
            <script>
                jQuery(document).ready(function($) {
                    let get_bg_img = $('*').css('background-image');

                    // Dynamically set the staging domain
                    let stg_domain = "<?php echo esc_js($staging_domain); ?>"; // Use the dynamically detected staging domain

                    $('*').each(function() {
                        if (get_bg_img.indexOf(stg_domain) == -1) {
                            $(this).css('background-image', function(i, old) {
                                return old.replace('<?php echo $staging_url; ?>', 'https://<?php echo $live_domain; ?>');
                            });
                        }
                    });

                    let regexPattern = "/wp-content/uploads/";
                    let regExp = new RegExp(regexPattern);
                    let images = document.querySelectorAll('img');
                    [].forEach.call(images, function(img) {
                        if (regExp.test(img.src)) {
                            img.src = img.src.replace('<?php echo $staging_url; ?>', 'https://<?php echo $live_domain; ?>');
                        }
                        if (regExp.test(img.srcset)) {
                            img.srcset = img.srcset.replace('<?php echo $staging_url; ?>', 'https://<?php echo $live_domain; ?>');
                        }
                    });
                });
            </script>
            <?php
        }
    }

    // Function to detect the staging domain based on the current URL
    function getStagingDomain($url)
    {
        // Example: if the URL is "https://stg.github.com", we'll replace "stg" with the live domain.
        $parsed_url = parse_url($url);
        $domain = $parsed_url['host'];

        // Replace common staging subdomains (stg, dev, etc.) with the live domain
        $staging_subdomains = ['stg', 'dev', 'test'];
        foreach ($staging_subdomains as $subdomain) {
            if (strpos($domain, $subdomain) === 0) {
                $domain = str_replace($subdomain, '', $domain);
                break;
            }
        }

        return $domain;
    }

    function handleForm()
    {
        if (wp_verify_nonce($_POST['ourNonce'], 'saveLiveURL') && current_user_can('manage_options')) {
            if (isset($_POST['plugins_live_url'])) {
                $live_url = sanitize_text_field($_POST['plugins_live_url']);

                // Validate the live URL to ensure it starts with https://
                if (filter_var($live_url, FILTER_VALIDATE_URL) && strpos($live_url, 'https://') === 0) {
                    update_option('plugins_live_url', $live_url); // Save the live URL
                    ?>
                    <div class="updated">
                        <p>Your live domain URL was saved successfully.</p>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="error">
                        <p>Please enter a valid URL starting with https://</p>
                    </div>
                    <?php
                }
            }
        } else {
            ?>
            <div class="error">
                <p>Sorry, you do not have permission to perform that action.</p>
            </div>
        <?php }
    }

    function adminPageDisplay()
    { ?>
        <div class="wrap">
            <h1>Fix Staging Image Links</h1>
            <p class="description">
                This plugin helps fix broken image links on your staging site by replacing staging site URLs with your live domain URL.
                When creating a staging site, the <code>uploads</code> folder is often too large to copy, which can cause images to break.<br>
                This plugin resolves that issue by ensuring that images from the staging site correctly point to the live domain,
                allowing the staging site to replicate the live site without broken images.<br>
                Simply enter your live domain URL below to restore the image links on your staging site.
            </p>

            <?php
            if (isset($_POST['submitted']) && $_POST['submitted'] === "true") $this->handleForm();
            ?>

            <form method="POST">
                <input type="hidden" name="submitted" value="true">
                <?php wp_nonce_field('saveLiveURL', 'ourNonce') ?>

                <label for="plugins_live_url"><p>Enter the full live domain URL below.</p></label>
                <input
                    name="plugins_live_url"
                    id="plugins_live_url"
                    type="text"
                    placeholder="https://www.example.com"
                    value="<?php echo esc_attr(get_option('plugins_live_url', '')); ?>"
                    class="regular-text"
                    style="width: 200px; padding: 10px; margin-top: 10px; margin-bottom: 10px;"
                > <br>

                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </form>
        </div>
    <?php }
}

$fixStagingImageLinks = new FixStagingImageLinks();
<?php

/*
 * Plugin Name: Word Filter
 * Plugin URI: https://jarutosurano.io
 * Description: A WordPress plugin that helps you filter unwanted words or phrases from your site's content with customizable replacement options.
 * Version: 1.0.0
 * Author: jarutosurano
 * Author URI: https://jarutosurano.io
 * Text Domain: word-filter
 */


if ( ! defined( 'ABSPATH' ) ) exit;

class CallMeWordFilter
{
    function __construct()
    {
        add_action('admin_menu', [$this, 'ourMenu']);
        add_action('admin_init', [$this, 'ourSettings']);
        if(get_option('plugins_words_to_filter')) add_filter('the_content', [$this, 'filterLogic']);
    }

    function ourSettings()
    {
        add_settings_section('replacement-text-section', null, null, 'callme-wordfilteroptions');
        register_setting('replacementFields', 'replacementText');
        add_settings_field(
            'replacement-text',
            'Filtered Text',
            [$this, 'replacementFieldHTML'],
            'callme-wordfilteroptions',
            'replacement-text-section'
        );
    }

    function replacementFieldHTML()
    { ?>
        <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '****')); ?>">
        <p class="description">Leave blank to simply remove the filtered words.</p>
    <?php }

    function filterLogic($content)
    {
        $badWordsOption = get_option('plugins_words_to_filter');
        if (!$badWordsOption) {
            return $content;
        }
        $badWords = explode(",", get_option('plugins_words_to_filter'));
        $badWordsTrimmed = array_map('trim', $badWords);
        return str_ireplace($badWordsTrimmed, esc_html(get_option('replacementText', '****')), $content);
    }

    function ourMenu()
    {
        // add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url, int $position)
        $mainPageHook = add_menu_page ('Words to Filter', 'Word Filter', 'manage_options', 'callme-wordfilter', [$this, 'wordFilterPage'], 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTE1IDEwLjVBMy41MDIgMy41MDIgMCAwIDAgMTguMzU1IDhIMjFhMSAxIDAgMSAwIDAtMmgtMi42NDVhMy41MDIgMy41MDIgMCAwIDAtNi43MSAwSDNhMSAxIDAgMCAwIDAgMmg4LjY0NUEzLjUwMiAzLjUwMiAwIDAgMCAxNSAxMC41ek0zIDE2YTEgMSAwIDEgMCAwIDJoMi4xNDVhMy41MDIgMy41MDIgMCAwIDAgNi43MSAwSDIxYTEgMSAwIDEgMCAwLTJoLTkuMTQ1YTMuNTAyIDMuNTAyIDAgMCAwLTYuNzEgMEgzeiIgZmlsbD0iIzAwMDAwMCIvPjwvc3ZnPg', 100);
        // customize the parent menu title
        add_submenu_page ('callme-wordfilter', 'Word Filter', 'Words List', 'manage_options', 'callme-wordfilter', [$this, 'wordFilterPage']);
        // add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback, int $position)
        add_submenu_page ('callme-wordfilter', 'Word Filter Options', 'Options', 'manage_options', 'callme-wordfilteroptions', [$this, 'optionsSubPage']);
        add_action("load-{$mainPageHook}", [$this, 'mainPageAssets']);
    }

    function mainPageAssets()
    {
        wp_enqueue_style('filterAdminCSS', plugin_dir_url(__FILE__) . 'style.css');
    }

    function optionsSubPage()
    { ?>
        <div class="wrap">
            <h1>Word Filter Options</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_errors();
                    settings_fields('replacementFields');
                    do_settings_sections('callme-wordfilteroptions');
                    submit_button();
                ?>
            </form>
        </div>
    <?php }

    function handleForm()
    {
        if(wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') && current_user_can('manage_options')) {
            if (isset($_POST['plugins_words_to_filter'])) {
                update_option('plugins_words_to_filter', sanitize_text_field($_POST['plugins_words_to_filter']));
            } ?>
            <div class="updated">
                <p>Your filtered words were saved.</p>
            </div>
        <?php } else { ?>
            <div class="error">
                <p>Sorry, you do not have permission to perform that action.</p>
            </div>
        <?php }
    }

    function wordFilterPage()
    { ?>
        <div class="wrap">
            <h1>Word Filter</h1>
            <?php if (isset($_POST['justsubmitted']) && $_POST['justsubmitted'] === "true") $this->handleForm(); ?>
            <form method="POST">
                <input type="hidden" name="justsubmitted" value="true">
                <?php wp_nonce_field('saveFilterWords', 'ourNonce') ?>
                <label for="plugins_words_to_filter"><p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content.</p></label>
                <div class="word-filter__flex-container">
                    <textarea name="plugins_words_to_filter" id="plugins_words_to_filter" placeholder="bad, mean, awful, horrible"><?php echo esc_textarea(get_option('plugins_words_to_filter')); ?></textarea>
                </div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </form>
        </div>
    <?php }
}

$callMeWordFilter = new CallMeWordFilter();
<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Prevent unauthorized access
}

// List of options to delete
$options = [
    'wc_display_location',
    'wc_headline_text',
    'wc_word_count',
    'wc_character_count',
    'wc_read_time',
];

// Loop through each option and delete it
foreach ($options as $option) {
    delete_option($option);
}

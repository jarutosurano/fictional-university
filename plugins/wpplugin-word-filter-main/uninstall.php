<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Prevent unauthorized access
}

// List of options added by the Word Filter plugin
$options = [
    'plugins_words_to_filter', // Stores the comma-separated list of words to filter
    'replacementText'         // Stores the text that replaces filtered words
];

// Delete each option from the database
foreach ($options as $option) {
    delete_option($option);
}

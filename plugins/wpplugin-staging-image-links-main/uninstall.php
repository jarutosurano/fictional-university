<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Prevent unauthorized access
}

// List of options added by the Fix Staging Image Links plugin
$options = [
    'plugins_live_url' // Stores the live domain URL
];

// Delete each option from the database
foreach ($options as $option) {
    delete_option($option);
}

<?php

/**
 * Declare a custom route for the styleguide
 */
add_action('init', function() {
    // Regex for: ://example.com
    add_rewrite_rule(
        '^styleguide/?$',
        'index.php?custom_template=styleguide',
        'top'
    );
});

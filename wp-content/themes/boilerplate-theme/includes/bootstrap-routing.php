<?php

/**
 * Add support for the custom_template query variable in permalinks
 */
add_filter('query_vars', function($vars) {
    $vars[] = 'custom_template';
    return $vars;
});

/**
 * Allow to specify the PHP template to be included based on the custom_template query variable in permalink
 */
add_filter('template_include', function($template) {
    if (get_query_var('custom_template')) {
        $template = filter_var(get_query_var('custom_template')) . ".php";
        $template_path = get_template_directory() . '/' . $template;

        if (file_exists($template_path)) {
            return $template_path;
        }
    }
    return $template;
});

<?php

function theme_get_menu(string $location): ?WP_Term {
    $locations = get_nav_menu_locations();

    if (!isset($locations[$location])) {
        return null;
    }

    return get_term( $locations[$location], 'nav_menu' );
}

function theme_get_menu_items(string $location): ?array {
    return wp_get_nav_menu_items($location);
}


<?php

/**
 * Register the menu locations
 */
add_action( 'init', function () : void {
    register_nav_menus([
        'main-menu'   => _x('Main menu', 'main-menu', 'theme-boilerplate'),
        'footer-menu' => _x('Footer menu', 'main-menu', 'theme-boilerplate'),
    ]);
});

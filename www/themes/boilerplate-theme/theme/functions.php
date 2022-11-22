<?php

namespace App\Theme;

use App\Theme\Site;
use Timber\Timber;

const THEME_VERSION    = '202301010000';
const THEME_ASSETS_DIR = '../static/assets';

/**
 * If you are installing Composer dependencies within the theme,
 * you'll need this statement to load your dependencies.
 * If you are installing and loading Composer dependencies outside of the theme,
 * this statement will be ignored.
 */
$composer_autoload = dirname( __DIR__ ) . '/../vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
}

require_once __DIR__ . '/../includes/class-site.php';

/**
* Initialize Timber
*/
Timber::init();

/**
 * Sets the directories (inside your theme) to find .twig files
 */
Timber::$dirname = [ '../views' ];

new Site();

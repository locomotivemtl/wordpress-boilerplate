<?php

/**
 * File: Base Template
 *
 * This is Sage Theme Wrapper. Its goal is to remove any repeated markup
 * from individual templates and put it into a single file.
 *
 * This file, `base.php` becomes the single, unambiguous, authoritative
 * representation of knowledge (i.e. the base format code). By doing this
 * we can put the focus entirely on the page specific markup and loop,
 * simplifying our templates.
 *
 * @link http://roots.io/an-introduction-to-the-roots-theme-wrapper/
 *
 * @package Boilerplate\Templates
 */

use Roots\Sage\Wrapper;

boilerplate_get_template_view('head');

?>
    <body <?php body_class(); ?> role="document">
<?php

do_action('get_header');
boilerplate_get_template_view('site', 'header');

?>
        <main id="content" role="main">
<?php

include Wrapper\template_path();

?>
        </main>
<?php

boilerplate_get_template_view('site', 'footer');
do_action('get_footer');
wp_footer();

?>
    </body>
</html>

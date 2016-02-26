<?php

/**
 * File: Main Template
 *
 * This is the most generic template file in a WordPress theme and one
 * of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query,
 * e.g., it puts together the home page when no home.php file exists.
 *
 * @package Boilerplate\Templates
 */

if ( have_posts() ) :

    while ( have_posts() ) : the_post();

        boilerplate_get_template_view( 'content', ( get_post_type() !== 'post' ? get_post_type() : get_post_format() ) );

    endwhile;

    if ( $wp_query->max_num_pages > 1 ) :

?>
    <nav>
        <ul>
            <li><?php next_posts_link(__('&larr; Older posts', 'boilerplate')); ?></li>
            <li><?php previous_posts_link(__('Newer posts &rarr;', 'boilerplate')); ?></li>
        </ul>
    </nav>
<?php

    endif;

else:

?>
    <div><?php
        _e('Sorry, no results were found.', 'boilerplate');
    ?></div>
    <?php get_search_form(); ?>
<?php

endif;

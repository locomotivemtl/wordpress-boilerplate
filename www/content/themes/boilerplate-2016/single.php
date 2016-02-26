<?php

/**
 * File: Basic single template
 * @package Boilerplate\Templates
 */

// ==========================================================================
// Setup
// ==========================================================================

// ==========================================================================
// Output
// ==========================================================================

if (have_posts()) {

    while (have_posts()) {

        the_post();

?>
<div class="o-content">
    <article class="o-section">
        <div class="o-container">
            <h1><?php the_title(); ?></h1>
            <div class="s-wysiwyg">
                <?php the_content(); ?>
            </div>
        </div>
    </article>
</div>
<?php

    }

}

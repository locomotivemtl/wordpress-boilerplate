<?php

/**
 * File: Default Page Template
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
            <div class="s-wysiwyg">
                <?php the_content(); ?>
            </div>
        </div>
    </article>
</div>
<?php

    }

}

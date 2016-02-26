<?php

/**
 * File: Front Page Template
 *
 * @package Boilerplate\Templates
 */

if (have_posts()) {

    while (have_posts()) {

        the_post();

// ==========================================================================
// Setup
// ==========================================================================

// ==========================================================================
// Output
// ==========================================================================

?>

<h1>Things are working. You have posts</h1>

<?php

    }

} else {

?>

<h1>Things are working. No posts.</h1>

<?php

}

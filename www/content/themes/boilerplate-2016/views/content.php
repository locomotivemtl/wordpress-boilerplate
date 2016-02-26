<?php

/**
 * File: Default Entry Content
 *
 * @package Boilerplate\Templates
 */

?>

<div class="o-content">
<?php

if (is_search()) {

	the_excerpt();

} else {

	the_content();

	// Entry Pagination
	wp_link_pages([
		'before' => '<nav>',
		'after'  => '</nav>'
	]);

	boilerplate_get_template_view( 'content', 'attachments' );

}

?>
</div>

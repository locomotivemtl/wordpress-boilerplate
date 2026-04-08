<?php

/**
 * Template Name: Index des nouvelles
 */

use Locomotive\PageForPosts\PageForPosts;

global $wp_query;
global $post;

$post_type = $wp_query->query['post_type'] ?? 'post';
$post_id = PageForPosts::get_page_id_for_post_type( $post_type );

if ( $post_id ) {
    $post = get_post( $post_id );
}

$page = $wp_query->query['paged'] ?? 1;
$args = [
    'post_type'      => $post_type ?: 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $page,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$wp_query = new WP_Query($args);

get_header();

?>
<main class="">
    <?php while ( have_posts() ) : the_post() ?>
        <article class="">
            <h2><?php the_title(); ?></h2>
            <a href="">View</a>
        </article>
    <?php endwhile ?>
</main>

<?php

get_footer();

return;

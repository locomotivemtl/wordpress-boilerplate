<?php

/**
 * Responsive Image component.
 */

// Set default arguments
use Theme\Loading;

extract( wp_parse_args( $args, [
    'image_id'         => false,
    'src'             => null,
    'alt'             => '',
    'width'           => null,
    'height'          => null,
    'caption'         => '',
    'display_caption' => false,
    'loading'         => 'lazy',
    'classes'         => [],
    'modifiers'       => [],
    'attributes'      => [],
] ) );

// Validation
if ( ! $image_id && !$src) {
    _theme_doing_template_part_wrong(
        __FILE__,
        _x( 'Expected at least `image_id` or `src`.', 'template partial', 'theme-common' )
    );
}

// Get image metadata
if ( $image_id ) {
    $image_metadata = wp_get_attachment_metadata( $image_id );

    // Return early if no image metadata
    if ( ! $image_metadata ) {
        return;
    }

    $src = wp_get_attachment_image_src( $image_id, 'full' )[0];
    $alt = theme_get_attachment_image_alt( $image_id );
    $caption = theme_get_attachment_image_caption( $image_id );
    $width = $image_metadata['width'];
    $height = $image_metadata['height'];
} elseif (!$width && !$height) {
    // Attempt to find the width and height of the image if not provided.
    list($width, $height) = getimagesize($src) ?: [null, null];
}

$classes = array_merge([ 'c-image' ], $modifiers, $classes);

if ($loading instanceof Loading) {
    $loading = $loading->value;
}

if( $loading === 'lazy' ) {
    $classes[] = '-lazy-load';
}

$attributes = array_merge( [
    'class' => $classes,
], $attributes );

$img_attributes = [
    'loading' => $loading,
    'width'   => (int)$width,
    'height'  => (int)$height,
    'class'   => 'c-image_img',
    'src'     => $src,
    'alt'     => $alt,
    'onload'  => 'this.closest(".c-image")?.classList?.add("is-loaded");',
];
?>

<figure <?php echo html_build_attributes( $attributes ); ?>>
    <picture class="c-image_inner">
        <img <?php echo html_build_attributes( $img_attributes ); ?> />
    </picture>

    <?php if ( $display_caption === true && ! empty( $caption ) ) : ?>
        <figcaption class="c-image_caption"><?php echo $caption ?></figcaption>
    <?php endif; ?>
</figure>


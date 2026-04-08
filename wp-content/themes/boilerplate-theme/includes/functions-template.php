<?php

/**
 * Functions for templating (template tags).
 *
 * @phpstan-type Link array{
 *     label: string,
 *     url: string,
 *     target: ?string,
 *     icon: ?string,
 *     modifiers: list<string>|string|null,
 * }
 */

/**
 * Renders a button snippet.
 */
function theme_button_component(
    string $href,
    string $label,
    string|null $icon = null,
    string|null $target = null,
    array|string $classes = [],
    array|string $modifiers = [],
    array $attributes = [],
) : void {

    if ( is_string( $classes ) ) {
        $classes = [ $classes ];
    }

    if ( is_string( $modifiers ) ) {
        $modifiers = [ $modifiers ];
    }

    get_template_part( 'partial/button', null, [
        'href'       => $href,
        'label'      => $label,
        'icon'       => $icon,
        'target'     => $target,
        'classes'    => $classes,
        'modifiers'  => $modifiers,
        'attributes' => $attributes,
    ] );
}

function theme_image_component(
    int|null $image_id = null,
    string|null $src = null,
    string|null $alt = null,
    int|null $width = null,
    int|null $height = null,
    string|null $caption = null,
    bool $display_caption = true,
    Theme\Loading|string $loading = 'lazy',
    array|string $classes = [],
    array|string $modifiers = [],
    array $attributes = [],
): void {
    get_template_part( 'partial/image', null, [
        'image_id'        => $image_id,
        'src'             => $src,
        'alt'             => $alt,
        'width'           => $width,
        'height'          => $height,
        'caption'         => $caption,
        'display_caption' => $display_caption,
        'loading'         => $loading,
        'classes'         => $classes,
        'modifiers'       => $modifiers,
        'attributes'      => $attributes,
    ] );
}

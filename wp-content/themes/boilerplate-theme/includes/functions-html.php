<?php

/**
 * Functions for the interacting with HTML for templating.
 */

use Theme\Video\Asset as VideoAsset;

/**
 * Generates an HTML iframe for a video.
 */
function theme_build_video_iframe( VideoAsset|string $url, array $args = [] ) : string {
    if ( $url instanceof VideoAsset ) {
        $url = $url->get_embed_url() ?? $url->playback_url;
    } elseif ( $_embed_url = theme_get_video_asset_from_url( $url )?->get_embed_url() ) {
        $url = $_embed_url;
    }

    $args = wp_parse_args( $args, [
        'width'  => 1280,
        'height' => 720,
    ] );

    $args['autoplay']   ??= false;
    $args['fullscreen'] ??= true;

    $allow = [ 'encrypted-media', 'picture-in-picture' ];
    $query = [];

    if ( $args['autoplay'] ) {
        $allow[] = 'autoplay';
        $query['autoplay'] = 1;
    }

    if ( $args['fullscreen'] ) {
        $allow[] = 'fullscreen';
    }

    return sprintf(
        '<iframe %s></iframe>',
        ltrim( html_build_attributes( $args + [
            'src'             => add_query_arg( $query, $url ),
            'width'           => $args['width'],
            'height'          => $args['height'],
            'frameborder'     => 0,
            'allow'           => implode( '; ', $allow ),
            'allowfullscreen' => $args['fullscreen'],
            'tabindex'        => 0
        ] ) )
    );
}

/**
 * Retrieves the alternate text to represent an attachment.
 *
 * @param  int $attachment_id Image attachment ID.
 * @return ?string
 */
function theme_get_attachment_image_alt( int $attachment_id ) : ?string
{
    $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

    if ( is_string( $alt ) ) {
        $alt = trim( strip_tags( $alt ) );

        if ( $alt ) {
            return $alt;
        }
    }

    return null;
}

/**
 * Retrieves the caption text to represent an attachment.
 *
 * @param  int $attachment_id Image attachment ID.
 * @return ?string
 */
function theme_get_attachment_image_caption( int $attachment_id ) : ?string
{
    $caption = get_post_field( 'post_excerpt', $attachment_id, 'db' );

    if ( is_string( $caption ) ) {
        $caption = trim( strip_tags( $caption, ['sup'] ) );

        if ( $caption ) {
            return $caption;
        }
    }

    return null;
}

/**
 * Retrieves the focal point of an image attachment.
 *
 * @param  int $attachment_id Image attachment ID.
 * @return int[]|null
 */
function theme_get_attachment_image_focal_point( int $attachment_id ) : ?array
{
    if ( function_exists('sanitize_focal_point') ) {
        return sanitize_focal_point( get_post_meta( $attachment_id, 'focal_point', true ) );
    }

    return null;
}

/**
 * Formats a list of MIME types as file extensions.
 *
 * @param  string[] $mime_types
 * @return string[]
 */
function theme_get_extensions_from_mime_types( array $mime_types ) : array {
    $extensions = [];

    foreach ( $mime_types as $mime_type ) {
        if ( str_contains( $mime_type, '/' ) ) {
            $extensions[ wp_get_default_extension_for_mime_type( $mime_type ) ] = true;
        } elseif ( str_starts_with( $mime_type, '.' ) ) {
            $extensions[ substr( $mime_type, 1 ) ] = true;
        }
    }

    return array_keys( $extensions );
}

/**
 * Formats an `<svg>` and `<use>` tag pair with the
 * URL of the symbol in the theme's SVG spritesheet.
 *
 * @param  string               $symbol         The target symbol.
 * @param  array<string, mixed> $svg_attributes Key-value pairs
 *     representing `<svg>` tag attributes.
 * @param  array<string, mixed> $use_attributes Key-value pairs
 *     representing `<use>` tag attributes.
 * @return String containing `<svg>` and `<use>` element.
 */
function theme_get_svg_sprite_tag(
    string $symbol,
    array $svg_attributes = [],
    array $use_attributes = []
) : string {
    return sprintf(
        '<svg%s>%s</svg>',
        html_build_attributes( $svg_attributes ),
        theme_get_svg_sprite_use_tag( $symbol, $use_attributes )
    );
}

/**
 * Formats an SVG `<use>` tag with the URL of the symbol
 * in the theme's SVG spritesheet.
 *
 * @param  string               $symbol     The target symbol.
 * @param  array<string, mixed> $attributes Key-value pairs
 *     representing `<use>` tag attributes.
 * @return String containing `<use>` void element.
 */
function theme_get_svg_sprite_use_tag( string $symbol, array $attributes = [] ) : string {
    $attributes['href'] = theme_get_svg_sprite_symbol_uri( $symbol );

    return sprintf( '<use%s />', html_build_attributes( $attributes ) );
}

/**
 * Displays a formatted SVG `<svg>` and `<use>` tag pair with the
 * URL of the symbol in the theme's SVG spritesheet.
 *
 * @param  string               $symbol         The target symbol.
 * @param  array<string, mixed> $svg_attributes Key-value pairs
 *     representing `<svg>` tag attributes.
 * @param  array<string, mixed> $use_attributes Key-value pairs
 *     representing `<use>` tag attributes.
 */
function theme_svg_sprite_tag(
    string $symbol,
    array $svg_attributes = [],
    array $use_attributes = []
) : void {
    echo theme_get_svg_sprite_tag( $symbol, $svg_attributes, $use_attributes );
}

/**
 * Displays a formatted SVG `<use>` tag with the URL of the symbol
 * in the theme's SVG spritesheet.
 *
 * @param  string               $symbol     The target symbol.
 * @param  array<string, mixed> $attributes Key-value pairs
 *     representing `<use>` tag attributes.
 */
function theme_svg_sprite_use_tag( string $symbol, array $attributes = [] ) : void {
    echo theme_get_svg_sprite_use_tag( $symbol, $attributes );
}

/**
 * Alias of {@see wp_sanitize_script_attributes()}.
 *
 * Based on {@link https://packagist.org/packages/mcaskill/php-html-build-attributes}.
 *
 * Note:
 *
 * - The `sizes` attribute uses comma-separation for the `<img>` tag
 *   and space-separation for the `<link>` tag. This function will
 *   check for the `img` tag otherwise fallback to space-separation.
 *
 * @param  array<string, mixed> $attributes Key-value pairs
 *     representing HTML attributes.
 * @param  ?string              $tag        The HTML tag related
 *     to the attributes.
 * @return string String made of sanitized HTML tag attributes.
 */
function html_build_attributes( array $attributes, ?string $tag = null ) : string {
    foreach ( $attributes as $attribute_name => $attribute_value ) {
        if ( is_null( $attribute_value ) ) {
            unset( $attributes[ $attribute_name ] );
            continue;
        }

        $attributes[ $attribute_name ] = match ( $attribute_name ) {
            /** Space-separated tokens. */
            'aria-controls',
            'aria-describedby',
            'aria-dropeffect',
            'aria-errormessage',
            'aria-flowto',
            'aria-keyshortcuts',
            'aria-labelledby',
            'aria-owns',
            'aria-relevant',
            'accesskey',
            'blocking',
            'class',
            'for',
            'headers',
            'itemprop',
            'itemref',
            'itemtype',
            'ping',
            'rel',
            'sandbox' => $attribute_value
                ? implode( ' ', (array) $attribute_value )
                : null,
            /** Comma-separated tokens. */
            'accept',
            'imagesrcset',
            'srcset' => $attribute_value
                ? implode( ',', (array) $attribute_value )
                : null,
            /** Delimiter depends on the tag */
            'sizes' => $attribute_value
                ? implode(
                    match ( $tag ) {
                        'img'   => ',',
                        default => ' ',
                    },
                    (array) $attribute_value
                )
                : null,
            /** WAIARIA boolean attributes. */
            'aria-atomic',
            'aria-busy',
            'aria-disabled',
            'aria-expanded',
            'aria-grabbed',
            'aria-hidden',
            'aria-invalid',
            'aria-modal',
            'aria-multiline',
            'aria-multiselectable',
            'aria-readonly',
            'aria-required',
            'aria-selected' => is_bool( $attribute_value )
                ? ( $attribute_value ? 'true' : 'false' )
                : $attribute_value,
            /** WHATWG boolean attributes. */
            'allowfullscreen',
            'alpha',
            'async',
            'autofocus',
            'autoplay',
            'checked',
            'controls',
            'default',
            'defer',
            'disabled',
            'formnovalidate',
            'inert',
            'ismap',
            'itemscope',
            'loop',
            'multiple',
            'muted',
            'nomodule',
            'novalidate',
            'open',
            'playsinline',
            'readonly',
            'required',
            'reversed',
            'selected',
            'shadowrootclonable',
            'shadowrootdelegatesfocus',
            'shadowrootserializable' => (bool) $attribute_value,
            /** Leave value intact. */
            default => $attribute_value,
        };
    }

    return wp_sanitize_script_attributes( $attributes );
}

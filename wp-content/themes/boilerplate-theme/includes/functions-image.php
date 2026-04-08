<?php

/**
 * Functions for the interacting with images for templating.
 *
 * @phpstan-type ImageBreakpoint array{0:int, 1?:int}|int
 * @phpstan-type ImageSize       array{width:int, height:int, crop:mixed}
 * @phpstan-type ImageSource     array{src:string, width:int, height:int}
 * @phpstan-type ImageStruct     array{
 *     id:int,
 *     original_height:int,
 *     original_ratio:int,
 *     original_width:int,
 *     ratio?:int
 * }
 */

/**
 * Retrieves a list of image size candidates for the given breakpoints.
 *
 * @param ImageStruct       $image       Image data structure.
 * @param ImageBreakpoint[] $breakpoints List of breakpoints
 *     as widths or a list of width/height pairs.
 *
 * @return ImageSize[] List of image sizes and cropping.
 *     For the `crop` element, one of the following:
 *
 *     1. `false` or `0` to NOT crop, just rescale.
 *     2. `true` or `1` to use the configured or default focal point.
 *     3. Array with string x-axis and y-axis parameters: `[ 'right', 'bottom' ]`.
 *     4. Array with numeric x-axis and y-axis parameters: `[ 0.5, 0.8 ]`.
 *     5. String `face` to automatically detect face position, if supported.
 *        Can be resource intensive.
 */
function theme_get_image_sizes_from_breakpoints( array $image, array $breakpoints ) : array {
    $sizes = [];

    foreach ( $breakpoints as $breakpoint ) {
        // Determine if cropping is needed
        $crop = ( $image['original_ratio'] !== ( $image['ratio'] ?? $image['original_ratio'] ) )
            ? 1
            : 0;

        // Handle array-style breakpoints [width, height]
        if ( is_array( $breakpoint ) ) {
            $width  = $breakpoint[0];
            $height = $breakpoint[1] ?? ( $width / $image['original_ratio'] );
            $ratio  = ( $width / $height );

            // Override cropping if the ratio is different
            $crop = ( $image['original_ratio'] !== $ratio ) ? 1 : 0;
        }
        // Handle single-value breakpoints (e.g., [320, 480, ...])
        else {
            $width  = $breakpoint;
            $height = isset( $image['ratio'] )
                ? ( $width / $image['ratio'] )
                : ( $width / $image['original_ratio'] );
        }

        // Skip if the breakpoint is larger than the original image dimensions
        if ( $width > $image['original_width'] || $height > $image['original_height'] ) {
            continue;
        }

        $sizes[] = [
            'width'  => $width,
            'height' => $height,
            'crop'   => $crop,
        ];
    }

    return $sizes;
}

/**
 * Get images candidates sources.
 *
 * @param ImageStruct $image Image data structure.
 * @param ImageSize[] $sizes List of image sizes and cropping.
 *
 * @return ImageSource[] List of image sources.
 */
function theme_get_image_sources_from_sizes( array $image, array $sizes ) : array {
    $sources = [];

    if( ! function_exists('bis_get_attachment_image_src') ) {
        return $sources;
    }

    foreach ( $sizes as $size ) {
        $bis_image = bis_get_attachment_image_src(
            $image['id'],
            [ $size['width'], $size['height'] ],
            $size['crop']
        );
        if ( isset( $bis_image['src'], $bis_image['width'] ) ) {
            $sources[] = $bis_image;
        }
    }

    return $sources;
}

/**
 * Formats a HTML `srcset` attribute value.
 *
 * @param ImageSource[] $sources List of image sources.
 *
 * @return string A `srcset` value string.
 */
function theme_format_image_srcset_from_sources( array $sources ) : string {
    $srcset = [];

    foreach( $sources as $source ) {
        $srcset[] = $source['src'] . ' ' . $source['width'] . 'w';
    }

    return implode(', ', $srcset);
}

<?php

/**
 * General functions for videos.
 */

/**
 * Cloudflare Stream iframe embed handler callback.
 *
 * Catches Cloudflare Stream iframe embed URLs that are not parsable by
 * oEmbed but can be translated into a URL that is.
 *
 * @global WP_Embed $wp_embed WordPress Embed object.
 *
 * @param  array  $matches The RegEx matches from the provided regex when calling
 *                         wp_embed_register_handler().
 * @param  array  $attr    Embed attributes.
 * @param  string $url     The original URL that was matched by the regex.
 * @param  array  $rawattr The original unmodified attributes.
 * @return string The embed HTML.
 */
function theme_cloudflare_stream_embed_handler( array $matches, array $attr, $url, array $rawattr ) : string {
    if ( ! empty( $attr['width'] ) && ! empty( $attr['height'] ) ) {
        $width  = (int) $attr['width'];
        $height = (int) $attr['height'];
    }

    $embed = sprintf(
        '<iframe%s></iframe>',
        wp_sanitize_script_attributes( [
            'src'             => sprintf(
                'https://customer-%s.cloudflarestream.com/%s/iframe?dnt=1',
                $matches['environment_id'],
                $matches['playback_id']
            ),
            'width'           => ( $width ?? 640 ),
            'height'          => ( $height ?? 360 ),
            'allow'           => 'encrypted-media; picture-in-picture',
            'allowfullscreen' => true,
        ] )
    );

    /**
     * Filters the Cloudflare Stream iframe embed HTML.
     *
     * @param string $embed   Cloudflare Stream iframe embed HTML.
     * @param array  $attr    The array of embed attributes.
     * @param string $url     The original URL that was matched by the regex.
     * @param array  $rawattr The original unmodified attributes.
     */
    return apply_filters( 'theme_cloudflare_stream_embed_handler', $embed, $attr, $url, $rawattr );
}

/**
 * Determines the video provider of the given URL.
 */
function theme_determine_video_provider_from_url( string $url ) : ?Theme\Video\Provider {
    return Theme\Video\Registry::determine_provider_from_url( $url );
}

/**
 * Retrieves a video asset from the given URL.
 */
function theme_get_video_asset_from_url( string $url ) : ?Theme\Video\Asset {
    return Theme\Video\Registry::get_asset_from_url( $url );
}

/**
 * Retrieves the embed URL from the given video URL.
 *
 * @param array<string, mixed> $query Optional query parameters to pass to the embed URL.
 */
function theme_get_video_embed_from_url( string $url, array $query = [] ) : ?string {
    return Theme\Video\Registry::get_asset_from_url( $url )?->get_embed_url( $query );
}

/**
 * Retrieves the thumbnail URL from the given video URL.
 *
 * @param array<string, mixed> $args Optional arguments to pass to the thumbnail provider.
 */
function theme_get_video_thumbnail_from_url( string $url, array $args = [] ) : ?string {
    return Theme\Video\Registry::get_asset_from_url( $url )?->get_thumbnail_url( $args );
}

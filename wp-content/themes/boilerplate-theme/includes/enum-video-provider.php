<?php

namespace Theme\Video;

enum Provider : string {
    case CLOUDFLARE = 'cloudflare';
    case MUX        = 'mux';
    case VIMEO      = 'vimeo';
    case YOUTUBE    = 'youtube';

    /**
     * Determines the video provider of the given URL.
     */
    public static function try_playback_url( string $url, ?array &$data = null ) : ?static {
        foreach ( static::cases() as $provider ) {
            if ( \preg_match( $provider->get_playback_regexp(), $url, $matches ) ) {
                $data = static::_filter_asset_preg_matches( $matches );
                $data['playback_url'] = $url;
                $data['provider']     = $provider;
                $data['embeddable']   = $provider->is_embeddable();
                $data['streamable']   = $provider->is_streamable( $url );
                return $provider;
            }
        }

        return null;
    }

    /**
     * Retrieves the regular expression pattern for the provider.
     */
    public function get_playback_regexp() : string {
        return match ( $this ) {
            self::CLOUDFLARE => '!^(?:https?://)?customer-(?<environment_id>[^\.]+)\.cloudflarestream\.com/(?<playback_id>[a-z\d]+)/manifest/video\.m3u8!i',
            self::MUX        => '!^(?:https?://)?stream\.mux\.com/(?<playback_id>[a-z\d]+)\.m3u8!i',
            self::VIMEO      => '!^(?:https?://)?(?:.+\.)?vimeo\.com(?:/(?:album/[a-z\d]+/video|channels(?:/[\w\-]+)?|groups/[^/]+/videos|progressive_redirect/playback|video))?/(?<playback_id>\d+)!i',
            self::YOUTUBE    => '!^(?:https?://)?(?:.+\.)?(?:youtube(?:-nocookie)?\.com\/(?:(?:v|e(?:mbed)?)/|watch/|[^/\s]+\/.+/|.*[?&]v=)|youtu\.be/)(?<playback_id>[\w\-]{11})!i',
        };
    }

    /**
     * Determines if the provider is embeddable (for example, an iframe).
     */
    public function is_embeddable() : bool {
        return match ( $this ) {
            self::CLOUDFLARE => true,
            self::MUX        => true,
            self::VIMEO      => true,
            self::YOUTUBE    => true,
        };
    }

    /**
     * Determines if the URL is streamable (HLS, DASH, or direct file).
     */
    public function is_streamable( string $url ) : bool {
        if ( self::VIMEO === $this && \str_contains( $url, '.mp4' ) ) {
            return true;
        }

        return \str_contains( $url, '.m3u8' );
    }

    /**
     * Filters the array of PREG matches to only keep named capture groups.
     *
     * @param  array<array-key, mixed> $matches
     * @return array<string, mixed>
     */
    private static function _filter_asset_preg_matches( array $matches ) : array {
        $data = [];

        foreach ( $matches as $key => $match ) {
            if ( \is_string( $key ) ) {
                $data[ $key ] = $match;
            }
        }

        return $data;
    }
}

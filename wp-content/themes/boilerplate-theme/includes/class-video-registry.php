<?php

namespace Theme\Video;

/**
 * Video provider registry
 */
class Registry {
    /**
     * Cache of video playback URLs and their playback data and provider information.
     *
     * @var array<string, Asset|false>
     */
    private static array $playbackCache = [];

    /**
     * Determines the video provider of the given URL.
     */
    public static function determine_provider_from_url( string $url ) : ?Provider {
        return static::get_asset_from_url( $url )?->provider;
    }

    /**
     * Retrieves a video asset from the given URL.
     */
    public static function get_asset_from_url( string $url ) : ?Asset {
        return ( ( static::$playbackCache[ $url ] ??= static::_resolve_asset_from_url( $url ) ) ?: null );
    }

    private function __construct() {
    }

    private static function _resolve_asset_from_url( string $url ) : Asset|false {
        if ( Provider::try_playback_url( $url, $data ) ) {
            return new Asset( ...$data );
        }

        return false;
    }
}

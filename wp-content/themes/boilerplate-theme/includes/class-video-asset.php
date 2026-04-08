<?php

namespace Theme\Video;

use UnexpectedValueException;

/**
 * Video asset
 */
class Asset {
    public function __construct(
        public readonly string $playback_url,
        public readonly Provider $provider,
        public readonly int|string $playback_id,
        public readonly int|string|null $environment_id = null,
        public readonly bool $embeddable = false,
        public readonly bool $streamable = false,
    ) {}

    /**
     * Retrieves the embed URL.
     *
     * @param array<string, mixed> $query Optional query parameters to pass to the embed URL.
     */
    public function get_embed_url( array $query = [] ) : ?string {
        return match ( $this->provider ) {
            Provider::CLOUDFLARE => static::_get_embed_url_for_cloudflare( $query ),
            Provider::MUX        => static::_get_embed_url_for_mux( $query ),
            Provider::VIMEO      => static::_get_embed_url_for_vimeo( $query ),
            Provider::YOUTUBE    => static::_get_embed_url_for_youtube( $query ),
            default              => null,
        };
    }

    /**
     * Retrieves the thumbnail URL.
     *
     * A thumbnail is only retrievable if the video is public.
     * For example, signed URLs for Cloudflare Stream and Vimeo won't be resolvable.
     *
     * @param array<string, mixed> $args Optional arguments to pass to the thumbnail resolver.
     */
    public function get_thumbnail_url( array $args = [] ) : ?string {
        return match ( $this->provider ) {
            Provider::CLOUDFLARE => static::_get_thumbnail_url_for_cloudflare( $args ),
            Provider::MUX        => static::_get_thumbnail_url_for_mux( $args ),
            Provider::VIMEO      => static::_get_thumbnail_url_for_vimeo( $args ),
            Provider::YOUTUBE    => static::_get_thumbnail_url_for_youtube( $args ),
            default              => null,
        };
    }

    /**
     * @throws UnexpectedValueException If the Asset has no customer ID.
     */
    private function _get_embed_url_for_cloudflare( array $query = [] ) : ?string {
        if ( ! $this->environment_id ) {
            throw new UnexpectedValueException( \sprintf(
                'Expected video asset to have a customer ID: %s',
                $this->playback_url
            ) );
        }

        return \add_query_arg( $query, \sprintf(
            'https://customer-%s.cloudflarestream.com/%s/iframe',
            $this->environment_id,
            $this->playback_id
        ) );
    }

    private function _get_embed_url_for_mux( array $query = [] ) : ?string {
        return \add_query_arg( $query, \sprintf(
            'https://player.mux.com/%s',
            $this->playback_id
        ) );
    }

    private function _get_embed_url_for_vimeo( array $query = [] ) : ?string {
        return \add_query_arg( $query, \sprintf(
            'https://player.vimeo.com/video/%s',
            $this->playback_id
        ) );
    }

    private function _get_embed_url_for_youtube( array $query = [] ) : ?string {
        return \add_query_arg( $query, \sprintf(
            'https://www.youtube-nocookie.com/embed/%s',
            $this->playback_id
        ) );
    }

    /**
     * @throws UnexpectedValueException If the Asset has no customer ID.
     */
    private function _get_thumbnail_url_for_cloudflare( array $args = [] ) : ?string {
        if ( ! $this->environment_id ) {
            throw new UnexpectedValueException( \sprintf(
                'Expected video asset to have a customer ID: %s',
                $this->playback_url
            ) );
        }

        $format = $args['format'] ?? 'jpg';
        $args   = \array_intersect_key( $args, \array_flip( [
            'format',
        ] ) );

        return \add_query_arg( $args, \sprintf(
            'https://customer-%s.cloudflarestream.com/%s/thumbnails/thumbnail.%s',
            $this->environment_id,
            $this->playback_id,
            $format
        ) );
    }

    private function _get_thumbnail_url_for_mux( array $args = [] ) : ?string {
        $format = $args['format'] ?? 'jpg';
        $args   = \array_intersect_key( $args, \array_flip( [
            'format',
        ] ) );

        return \add_query_arg( $args, \sprintf(
            'https://image.mux.com/%s/thumbnail.%s',
            $this->playback_id,
            $format
        ) );
    }

    private function _get_thumbnail_url_for_vimeo( array $args = [] ) : ?string {
        $transient = "theme_vimeo_api_{$this->playback_id}";

        if ( false === ( $data = \get_transient( $transient ) ) ) {
            $response = \wp_remote_get( \sprintf( 'http://vimeo.com/api/v2/video/%s.json', $this->playback_id ) );

            if ( 200 !== ( $code = \wp_remote_retrieve_response_code( $response ) ) ) {
                \set_transient( $transient, $code, WEEK_IN_SECONDS );
                return null;
            }

            if ( ! ( $body = \wp_remote_retrieve_body( $response ) ) ) {
                \set_transient( $transient, $code, WEEK_IN_SECONDS );
                return null;
            }

            if ( ! ( $data = \json_decode( $body, true ) ) ) {
                \set_transient( $transient, $code, WEEK_IN_SECONDS );
                return null;
            }

            \set_transient( $transient, $data, WEEK_IN_SECONDS );
        }

        if ( ! \is_array( $data ) ) {
            return null;
        }

        $size = $args['size'] ?? 'large';
        $args = \array_diff_key( $args, \array_flip( [
            'size',
        ] ) );

        if ( empty( $data[ "thumbnail_{$size}" ] ) ) {
            return null;
        }

        return \add_query_arg( $args, $data[ "thumbnail_{$size}" ] );
    }

    private function _get_thumbnail_url_for_youtube( array $args = [] ) : ?string {
        $format = $args['format'] ?? 'jpg';
        $size   = $args['size'] ?? 'maxresdefault';
        $args   = \array_diff_key( $args, \array_flip( [
            'format',
            'size',
        ] ) );

        return \add_query_arg( $args, \sprintf(
            'https://img.youtube.com/vi/%s/%s.%s',
            $this->playback_id,
            $size,
            $format
        ) );
    }
}

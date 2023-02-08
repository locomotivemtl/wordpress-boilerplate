<?php

namespace App\Theme\Support;

use function array_pad;
use function array_pop;
use function count;
use function ctype_alpha;
use function explode;
use function implode;
use function in_array;
use function parse_url;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;
use function wp_allowed_protocols;

use const PHP_URL_SCHEME;
use const PHP_URL_PATH;

/**
 * Path Helpers
 *
 * Based on {@link https://github.com/symfony/filesystem/blob/v6.2.5/Path.php `Path` class from symfony/filesystem v6.2.5}.
 */
class Path {
    /**
     * The cache of {@link canonicalize()}.
     *
     * @var array
     */
    private static $canonicalizeCache = [];

    /**
     * Canonicalizes the given path.
     *
     * During canonicalization:
     *
     * - All slashes are replaced by forward slashes (`/`).
     * - All `.` and `..` segments are removed as far as possible.
     * - Any `..` segments at the beginning of relative paths are not removed.
     * - Any extra `/` chracters are removed.
     *
     * @param  string $path A path string.
     * @return string The canonical path.
     */
    public static function canonicalize( string $path ) : string
    {
        if ( '' === $path ) {
            return '';
        }

        $path = self::normalize( $path );

        if ( isset( self::$canonicalizeCache[ $path ] ) ) {
            return self::$canonicalizeCache[ $path ];
        }

        [ $root, $pathWithoutRoot, $extra ] = self::split( $path );

        $canonicalParts = self::findCanonicalParts( $root, $pathWithoutRoot );

        self::$canonicalizeCache[ $path ] = $canonicalPath = $root . implode( '/', $canonicalParts ) . $extra;

        return $canonicalPath;
    }

    /**
     * Normalizes the given path.
     *
     * All slashes are replaced by forward slashes (`/`).
     *
     * @param  string $path A path string.
     * @return string The normalized path.
     */
    public static function normalize( string $path ) : string
    {
        return str_replace( '\\', '/', $path );
    }

    /**
     * @return string[]
     */
    private static function findCanonicalParts( string $root, string $pathWithoutRoot ): array
    {
        $parts = explode( '/', $pathWithoutRoot );

        $canonicalParts = [];

        // Collapse `.` and `..`, if possible
        foreach ( $parts as $part ) {
            if ('.' === $part || '' === $part) {
                continue;
            }

            // Collapse `..` with the previous part, if one exists
            // Don't collapse `..` if the previous part is also `..`
            if (
                '..' === $part &&
                count( $canonicalParts ) > 0 &&
                '..' !== $canonicalParts[ count( $canonicalParts ) - 1 ]
            ) {
                array_pop($canonicalParts);

                continue;
            }

            // Only add `..` prefixes for relative paths
            if ( '..' !== $part || '' === $root ) {
                $canonicalParts[] = $part;
            }
        }

        return $canonicalParts;
    }

    /**
     * Splits a part into its root directory or domain name and the remainder.
     *
     * If the path has no root directory or domain name, an empty root directory will be
     * returned.
     *
     * @param  string $path The canonical path or URL to split.
     * @return string[] An array with:
     *     1. If a URL, the scheme, authority, and host, otherwise the root directory.
     *     2. The remaining relative path.
     *     3. If a URL, the query and fragment.
     */
    private static function split( string $path ) : array
    {
        if ( '' === $path ) {
            return [ '', '', '' ];
        }

        if ( false !== ( $schemeSeparatorPosition = strpos( $path, '://' ) ) ) {
            // Check if it's a URL
            if ( in_array( parse_url( $path, PHP_URL_SCHEME ), wp_allowed_protocols(), true ) ) {
                $url  = $path;
                $path = parse_url( $path, PHP_URL_PATH );
                [ $root, $extra ] = array_pad( explode( $path, $url ), 2, '' );
            } else {
                // Remember scheme as part of the root, if any
                $root  = substr( $path, 0, $schemeSeparatorPosition + 3 );
                $path  = substr( $path, $schemeSeparatorPosition + 3 );
                $extra = '';
            }
        } else {
            $root  = '';
            $extra = '';
        }

        $length = strlen( $path );

        // Remove and remember root directory
        if ( str_starts_with( $path, '/' ) ) {
            $root .= '/';
            $path = $length > 1 ? substr( $path, 1 ) : '';
        } elseif ( $length > 1 && ctype_alpha( $path[0] ) && ':' === $path[1] ) {
            if ( 2 === $length ) {
                // Windows special case: `C:`
                $root .= $path.'/';
                $path = '';
            } elseif ( '/' === $path[2] ) {
                // Windows normal case: `C:/`..
                $root .= substr($path, 0, 3);
                $path = $length > 3 ? substr($path, 3) : '';
            }
        }

        return [ $root, $path, $extra ];
    }
}

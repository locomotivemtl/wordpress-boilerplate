<?php

/**
 * File: Formatting Utilities
 *
 * @package Boilerplate\Utilities
 */

/**
 * Quick Line Breaks & Tab Characters
 *
 * @const string N Line Feed
 * @const string R Carriage Return
 * @const string T Horizontal Tab
 */

if ( ! defined('N') ) define( 'N', ( defined('LF')  ? LF : "\n" ) );
if ( ! defined('R') ) define( 'R', ( defined('CR')  ? CR : "\r" ) );
if ( ! defined('T') ) define( 'T', ( defined('HT')  ? HT : "\t" ) );
if ( ! defined('D') ) define( 'D', SEP );
if ( ! defined('S') ) define( 'S', D );



/**
 * Convert a ISO 3166-2 identifiers to their full name,
 * wrapped in an abbreviation tag.
 *
 * @param string $region Either the postal abbreviation or full name.
 *
 * @return string HTML-wrapped administrative division
 */

function boilerplate_administrative_division( $region )
{
    $divisions = [
        /** Canada */
        _x( 'AB', 'ISO 3166-2 identifier' ) => _x( 'Alberta',                   'Canadian province'  ),
        _x( 'BC', 'ISO 3166-2 identifier' ) => _x( 'British Columbia',          'Canadian province'  ),
        _x( 'MB', 'ISO 3166-2 identifier' ) => _x( 'Manitoba',                  'Canadian province'  ),
        _x( 'NB', 'ISO 3166-2 identifier' ) => _x( 'New Brunswick',             'Canadian province'  ),
        _x( 'NL', 'ISO 3166-2 identifier' ) => _x( 'Newfoundland and Labrador', 'Canadian province'  ),
        _x( 'NT', 'ISO 3166-2 identifier' ) => _x( 'Northwest Territories',     'Canadian territory' ),
        _x( 'NS', 'ISO 3166-2 identifier' ) => _x( 'Nova Scotia',               'Canadian province'  ),
        _x( 'NU', 'ISO 3166-2 identifier' ) => _x( 'Nunavut',                   'Canadian territory' ),
        _x( 'ON', 'ISO 3166-2 identifier' ) => _x( 'Ontario',                   'Canadian province'  ),
        _x( 'PE', 'ISO 3166-2 identifier' ) => _x( 'Prince Edward Island',      'Canadian province'  ),
        _x( 'QC', 'ISO 3166-2 identifier' ) => _x( 'Quebec',                    'Canadian province'  ),
        _x( 'SK', 'ISO 3166-2 identifier' ) => _x( 'Saskatchewan',              'Canadian province'  ),
        _x( 'YT', 'ISO 3166-2 identifier' ) => _x( 'Yukon',                     'Canadian territory' )

        /** @todo USA */
    ];

    $code   = '';
    $output = '';

    if ( isset( $divisions[ strtoupper( $region ) ] ) ) {
        $code   = strtoupper( $region );
        $region = $divisions[ $region ];
    }
    else {
        $code = array_search( $region, $divisions );
    }

    if ( $code ) {
        return abbr( $code, $region );
    }
    else {
        return $region;
    }
}



/**
 * Display a formatted address from an ACF Address Field.
 *
 * @param object $address The address to be parsed.
 * @param string $options If present, specifies a specific element to be returned;
 *                        one of BOILERPLATE_ADDRESS_CIVIC, BOILERPLATE_ADDRESS_MUNICIPALITY,
 *                        BOILERPLATE_ADDRESS_REGION, BOILERPLATE_ADDRESS_COUNTRY, BOILERPLATE_ADDRESS_POSTAL_CODE.
 *                        IF $options is not specified, returns all available elements.
 */

function boilerplate_address( $address, $options = BOILERPLATE_ADDRESS_ALL )
{
    echo boilerplate_get_address( $address, $options );
}



/**
 * Retrieve a formatted address from an ACF Address Field.
 *
 * @param object $address The address to be parsed.
 * @param string $options If present, specifies a specific element to be returned;
 *                        one of BOILERPLATE_ADDRESS_CIVIC, BOILERPLATE_ADDRESS_MUNICIPALITY,
 *                        BOILERPLATE_ADDRESS_REGION, BOILERPLATE_ADDRESS_COUNTRY, BOILERPLATE_ADDRESS_POSTAL_CODE.
 *                        IF $options is not specified, returns all available elements.
 *
 * @return string
 */

define( 'BOILERPLATE_ADDRESS_ALL',          1  );
define( 'BOILERPLATE_ADDRESS_CIVIC',        2  );
define( 'BOILERPLATE_ADDRESS_MUNICIPALITY', 4  );
define( 'BOILERPLATE_ADDRESS_REGION',       8  );
define( 'BOILERPLATE_ADDRESS_COUNTRY',      16 );
define( 'BOILERPLATE_ADDRESS_POSTAL_CODE',  32 );

function boilerplate_get_address( $address, $options = BOILERPLATE_ADDRESS_ALL )
{
    $FLAG_ALL          = ( ( $options | BOILERPLATE_ADDRESS_ALL          ) == $options );
    $FLAG_CIVIC        = ( ( $options | BOILERPLATE_ADDRESS_CIVIC        ) == $options );
    $FLAG_MUNICIPALITY = ( ( $options | BOILERPLATE_ADDRESS_MUNICIPALITY ) == $options );
    $FLAG_REGION       = ( ( $options | BOILERPLATE_ADDRESS_REGION       ) == $options );
    $FLAG_COUNTRY      = ( ( $options | BOILERPLATE_ADDRESS_COUNTRY      ) == $options );
    $FLAG_POSTAL_CODE  = ( ( $options | BOILERPLATE_ADDRESS_POSTAL_CODE  ) == $options );

    $output = '';
    $break  = '<br>';

    if ( $address ) {
        $address = (object) $address;

        if ( $FLAG_CIVIC || $FLAG_ALL ) {
            if ( ! empty( $address->street1 ) ) {
                $output .= $address->street1 . $break;
            }

            if ( ! empty( $address->street2 ) ) {
                $output .= $address->street2 . $break;
            }

            if ( ! empty( $address->street3 ) ) {
                $output .= $address->street3 . $break;
            }
        }

        if ( $FLAG_MUNICIPALITY || $FLAG_ALL ) {
            if ( ! empty( $address->city ) ) {
                $output .= $address->city;
            }
        }

        if ( $FLAG_REGION || $FLAG_ALL ) {
            if ( ! empty( $address->state ) ) {
                $region = boilerplate_administrative_division( $address->state );

                if ( ( $FLAG_MUNICIPALITY || $FLAG_ALL ) && ! empty( $address->city ) ) {
                    $output .= ' (' . $region . ')';
                }
                else {
                    $output .= $region;
                }
            }
        }

        if ( ( $FLAG_MUNICIPALITY && $FLAG_POSTAL_CODE ) || $FLAG_ALL ) {
            if ( ! empty( $address->city ) && ! empty( $address->zip ) ) {
                $output .= ' ';
            }
        }

        if ( $FLAG_POSTAL_CODE || $FLAG_ALL ) {
            if ( ! empty( $address->zip ) ) {
                $output .= $address->zip;
            }
        }

        if ( ( $FLAG_MUNICIPALITY && $FLAG_POSTAL_CODE ) || $FLAG_ALL ) {
            if ( ! empty( $address->city ) || ! empty( $address->zip ) ) {
                $output .= $break;
            }
        }

        if ( $FLAG_COUNTRY || $FLAG_ALL ) {
            if ( ! empty( $address->country ) ) {
                $output .= $address->country;
            }
        }

    }

    return preg_replace( '/' . preg_quote( $break ) . '$/', '', $output );
}



/**
 * Retrieve a formatted link for a value.
 *
 * @param string   $value
 * @param string   $type
 * @param string[] $classes
 *
 * @return string
 */

function boilerplate_link_to( $value, $type = 'url', $classes = null )
{
    $output = '';

    switch ( $type ) {
        case 'url':
            $value = esc_url( $value );
            $label = parse_url( $value, PHP_URL_HOST );
            $label = preg_replace( '#^www\d?\.#', '', $label );
            break;

        case 'mail':
        case 'email':
        case 'mailto':
            $value = sanitize_email( $value );
            $label = $value;
            $value = 'mailto:' . $value;
            break;

        case 'tel':
        case 'phone':
        case 'telephone':
            $value = sanitize_text_field( $value );
            $label = $value;
            $value = 'tel:' . $value;
            break;

        case 'fax':
        case 'telefax':
        case 'telecopier':
            $value = sanitize_text_field( $value );
            $label = $value;
            $value = 'fax:' . $value;
            break;

        default:
            $label = $value;
            break;
    }

    if ( $value && $label ) {
        $link_attr = [
            'href' => $value
        ];

        if ( $classes ) {
            if ( ! is_array( $classes ) ) {
                $classes = [ $classes ];
            }
            $link_attr['class'] = implode( ' ', $classes );
        }

        $output = '<a ' . html_build_attributes( $link_attr ) . '>' . $label . '</a>';
    }

    return $output;
}

/**
 * Return human readable time interval
 *
 * @param string|integer $time
 * @return string
 * @see http://stackoverflow.com/a/2916189
 */

function boilerplate_format_social_interval($old_date_string) {
    if (is_numeric($old_date_string)) {
        $old_date = new \DateTime();
        $old_date->setTimestamp($old_date_string);
    } else {
        $old_date = new \DateTime($old_date_string);
    }

    $now_date = new \DateTime('now');
    $diff = $now_date->getTimestamp() - $old_date->getTimestamp();
    $diff = ($diff < 1) ? 1 : $diff;

    $tokens = [
        31536000 => 'année',
        2592000 => 'mois',
        604800 => 'semaine',
        86400 => 'jour',
        3600 => 'heure',
        60 => 'minute',
        1 => 'seconde'
    ];

    foreach ($tokens as $unit => $text) {
        if ($diff < $unit) {
            continue;
        }

        $numberOfUnits = floor($diff / $unit);

        if ($unit >= 86400) {
            return $old_date->format('M d');
        } else {
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }
    }
}

/**
 * Return human readable date
 *
 * @param string $time
 * @return string
 * @see http://stackoverflow.com/a/2916189
 */

function boilerplate_format_social_date($time_string) {
    if (is_numeric($time_string)) {
        $date = new \DateTime();
        $date->setTimestamp($time_string);
    } else {
        $date = new \DateTime($time_string);
    }
    return $date->format('Y-m-d H:i');
}

/**
 * Return time in seconds to minutes
 *
 * @param int $seconds
 * @return string
 */

function boilerplate_seconds_to_minutes($seconds) {
    if (is_numeric($seconds)) {
        $minutes = ceil((int)$seconds / 60);
        return $minutes . ' min';
    } else {
        return $seconds;
    }
}

/**
 * Function that will turn all HTTP URLs, Twitter @usernames, and #tags into links:
 * @see  https://davidwalsh.name/linkify-twitter-feed
 *
 * @param  string  $text  Tweet object->text
 * @return string
 */
function boilerplate_linkify_twitter_status($status_text) {

    // linkify URLs
    $status_text = preg_replace(
        '/(https?:\/\/\S+)/',
        '<a target="_blank" href="\1">\1</a>',
        $status_text
    );

    // linkify twitter users
    $status_text = preg_replace(
        '/(^|\s)@(\w+)/',
        '\1@<a target="_blank" href="https://twitter.com/\2">\2</a>',
        $status_text
    );

    // linkify tags
    $status_text = preg_replace(
        '/(^|\s)#(\w+)/',
        '\1#<a target="_blank" href="https://search.twitter.com/search?q=%23\2">\2</a>',
        $status_text
    );

    return $status_text;
}

/**
 * Return double digits from single digits
 *
 * @param mixed $number
 * @return string
 */

function boilerplate_single_to_double_digits($number) {
    return ($number >= 10) ? (string)$number : '0' . $number;
}

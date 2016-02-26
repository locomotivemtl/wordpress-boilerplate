<?php

/**
 * File : WordPress Attachments
 *
 * @package  Boilerplate
 */

namespace Boilerplate\Attachments;

/**
 * Set default media link to 'none'
 *
 * @used-by Filter: 'pre_option_image_default_link_type'
 * @param   string  $value  Option value
 * @return  string
 */

function default_link_type( $value )
{
    return 'none';
}

add_filter( 'pre_option_image_default_link_type', __NAMESPACE__ . '\\default_link_type' );

<?php

/**
 * File : WordPress Users
 *
 * @package Boilerplate
 */

namespace Boilerplate;

/**
 * Class : Users
 */

class Users
{

    public static function init()
    {
        $class = get_called_class();

        $class = new $class();

        add_filter('get_avatar',    [ &$class, 'get_avatar' ], 10, 5);
        add_action('user_register', [ &$class, 'set_default_lang' ], 10, 1);
    }

    /**
     * Filter the avatar to retrieve.
     *
     * @see WordPress\get_avatar()
     *
     * @param string            $avatar      Image tag for the user's avatar.
     * @param int|object|string $id_or_email A user ID, email address, or comment object.
     * @param int               $size        Square avatar width and height in pixels to retrieve.
     * @param string            $alt         Alternative text to use in the avatar image tag.
     *                                       Default empty.
     */

    public function get_avatar( $avatar, $id_or_email, $size, $default, $alt )
    {
        $user = false;

        if ( is_numeric( $id_or_email ) ) {

            $id   = (int) $id_or_email;
            $user = get_user_by( 'id' , $id );

        } elseif ( is_object( $id_or_email ) ) {

            if ( ! empty( $id_or_email->user_id ) ) {
                $id   = (int) $id_or_email->user_id;
                $user = get_user_by( 'id' , $id );
            }

        } else {
            $user = get_user_by( 'email', $id_or_email );
        }

        if ( $user && is_object( $user ) ) {
            $image = get_field('avatar', 'user_' . $user->data->ID );

            if ( ! empty( $image ) ) {
                $avatar = '<img alt="' . $alt . '" src="' . $image['sizes']['thumbnail'] . '" class="avatar avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />';
            }

        }

        return $avatar;
    }

    /**
     * Set a default language for the backend
     *
     * @param int  $user_id   User ID
     */

    public function set_default_lang( $user_id )
    {
        update_user_meta($user_id, 'pll_filter_content', pll_default_language());
    }

}

Users::init();

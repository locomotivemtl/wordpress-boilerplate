<?php

/**
 * File : Singleton Model Trait
 *
 * @package Boilerplate
 */

namespace Boilerplate;

/**
 * Trait : Singleton Model
 */

trait SingletonModel
{
    /**
     * Singleton
     *
     * @return self
     */

    public static function get_instance()
    {
        static $__instance = null;

        if ( $__instance instanceof self ) {
            return $__instance;
        }
        else {
            $__instance = new self;

            return $__instance;
        }
    }
}

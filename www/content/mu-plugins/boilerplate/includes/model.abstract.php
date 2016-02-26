<?php

/**
 * File : Abstract Model Type Class
 *
 * @package Boilerplate
 */

namespace Boilerplate;

use Boilerplate\ModelInterface;

/**
 * Abstract : Model Type
 */

abstract class AbstractModel implements ModelInterface
{
	protected static $instances = [];

	/**
	 * @param array $args
	 */

	abstract public function register( $args = [] );

	/**
	 * Check the current object against a WP_Query.
	 *
	 * @param object $obj
     *
	 * @return bool
	 */

	abstract public function is_related( $obj );

}

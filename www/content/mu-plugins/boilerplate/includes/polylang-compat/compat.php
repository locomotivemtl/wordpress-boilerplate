<?php

/**
 * File : Polylang Compatibility Base
 *
 * @package Boilerplate
 */

namespace Boilerplate;

if ( function_exists('pll_register_string') ) {
	return;
}

/**
 * Class : Polylang Compatibility
 */

abstract class PLL_Compatibility
{
	const OPTION_NAME = 'polylang_compat_strings';

	/**
	 * @var array         $strings       Registered values from compatible plugin
	 * @var PLL_Language  $__curlang     Backup copy of Polylang's current language.
	 * @var string        $default_lang  Polylang's default language slug.
	 * @var string        $current_lang  Polylang's current language slug.
	 */

	protected $strings;

	protected $__curlang;

	public $default_lang;
	public $current_lang;

	/**
	 * Filter WordPress current locale with Polylang's current locale.
	 *
	 * @used-by  Filters\"locale"
	 * @param    string  $locale  The locale ID.
	 */

	public function pll_get_locale( $locale )
	{
		$pll_locale = pll_current_language('locale');

		if ( $locale !== $pll_locale ) {
			$locale = $pll_locale;
		}

		return $locale;
	}

	/**
	 * Set a new current language for Polylang.
	 *
	 * @param string  $lang  The new language.
	 */

	public function pll_set_language( $lang )
	{
		global $polylang;

		if ( empty( $this->__curlang ) ) {
			$this->__curlang = $polylang->curlang;
		}

		$polylang->curlang = $polylang->model->get_language( $lang );
	}

	/**
	 * Reset current language for Polylang.
	 */

	public function pll_reset_language()
	{
		global $polylang;

		$polylang->curlang = $this->__curlang;
	}

	/**
	 * Adds strings registered from plugin to
	 * those registered by pll_register_string.
	 *
	 * @used-by  Filters\"pll_get_strings"
	 * @param    array  $strings  Existing registered strings
	 * @return   array  Registered strings with added strings from plugin
	 */

	public function get_strings( $strings )
	{
		if ( empty( $this->strings ) ) {
			$this->strings = get_option( static::OPTION_NAME );
		}

		return ( empty( $this->strings ) ? $strings : array_merge( $strings, $this->strings ) );
	}

	/**
	 * Parse $source and extract strings
	 *
	 * @param   mixed   $source
	 * @param   array   $keys
	 * @param   array   $data
	 * @param   string  $prefix
	 * @return  array   $data
	 */

	public function pluck_strings( $source, $keys, &$data = [], $prefix = '' )
	{
		foreach ( $keys as $key => $multiline ) {
			$namespaces = explode( '.', $key );
			$property   = &$source;

			foreach ( $namespaces as $name ) {
				if ( isset( $property[ $name ] ) ) {
					if ( is_array( $property[ $name ] ) ) {
						$property = &$property[ $name ];
					}
					elseif ( '' != $property[ $name ] ) {
						$data[ $prefix . $key ] = [
							'string'    => $property[ $name ],
							'multiline' => $multiline
						];
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Parse $source and inject strings
	 *
	 * @param   mixed   $source
	 * @param   array   $keys
	 * @return  array   $source
	 */

	public function inject_strings( &$source, $keys )
	{
		foreach ( $keys as $key => $multiline ) {
			$namespaces = explode( '.', $key );
			$property   = &$source;

			foreach ( $namespaces as $name ) {
				if ( isset( $property[ $name ] ) ) {
					if ( is_array( $property[ $name ] ) ) {
						$property = &$property[ $name ];
					}
					elseif ( '' != $property[ $name ] ) {
						$property[ $name ] = pll_translate_string( $property[ $name ], $this->current_lang );
					}
				}
			}
		}

		return $source;
	}

	/**
	 * Register strings with Polylang.
	 *
	 * @param  array   $string_data
	 * @param  string  $group
	 * @param  string  $destination
	 */

	public function _register_strings( array $string_data, $group = 'polylang', $destination = 'register' )
	{
		if ( ! empty( $string_data ) ) {
			foreach ( $string_data as $name => $data ) {
				if ( '' != $data['string'] ) {
					switch ( $destination ) {
						case 'option':
						case 'options':
							if ( empty( $this->strings ) ) {
								$this->strings = get_option( static::OPTION_NAME, []);
							}

							$to_register = [
								'name'      => $name,
								'string'    => $data['string'],
								'context'   => $group,
								'multiline' => ( $data['multiline'] ?: false )
							];

							if ( ! in_array( $to_register, $this->strings ) && $to_register['string'] ) {
								$this->strings[] = $to_register;
								update_option( static::OPTION_NAME, $this->strings );
							}
							break;

						default:
							pll_register_string( $name, $data['string'], $group, ( $data['multiline'] ?: false ) );
							break;
					}
				}
			}
		}
	}

	/**
	 * Extract translatable items from the $source
	 *
	 * @param   mixed   $source
	 * @param   array   $data
	 * @return  array   $data
	 */

	/* abstract */ protected function _extract_strings( $source, &$data = [] )
	{
		return $data;
	}

}

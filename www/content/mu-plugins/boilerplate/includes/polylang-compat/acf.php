<?php

/**
 * File : Polylang Compatibility for ACF + Options Section
 *
 * @package Boilerplate
 */

namespace Boilerplate;

if ( ! class_exists('acf') ) {
	return;
}

/**
 * Class : Advanced Custom Fields Compatibility
 */

class ACF_Polylang_Compatibility extends PLL_Compatibility
{
	const OPTION_NAME = 'polylang_acf_strings';

	/**
	 * @see parent::init()
	 */

	public static function init()
	{
		$class = get_called_class();
		$class = new $class();

		# add_action( 'init', [ &$class, 'wp_init' ], 99 );

		add_filter( 'pll_get_strings',            [ &$class, 'get_strings'  ] );
		add_action( 'pll_language_defined',       [ &$class, 'set_language' ], 1, 2 );
/*
		add_action( 'acf/update_field_group',     [ &$class, 'update_field_group' ], 11 );
		add_action( 'acf/duplicate_field_group',  [ &$class, 'update_field_group' ], 11 );
		add_action( 'acf/untrash_field_group',    [ &$class, 'update_field_group' ], 11 );
		add_action( 'acf/trash_field_group',      [ &$class, 'delete_field_group' ], 11 );
		add_action( 'acf/delete_field_group',     [ &$class, 'delete_field_group' ], 11 );
*/
		add_action( 'acf/include_fields',         [ &$class, 'include_fields'   ], 11 );

		add_action( 'acf/get_valid_field',        [ &$class, 'acf_get_valid_field' ] );
		add_action( 'acf/field_group/admin_head', [ &$class, 'acf_admin_head'      ] );
		add_action( 'acf/input/admin_head',       [ &$class, 'acf_admin_head'      ] );
	}

	/**
	 * Action: Inject into the WordPress Dashboard <head>
	 */

	public function acf_admin_head()
	{
?>
		<script type="text/javascript">
			acf.add_filter('prepare_for_ajax', function ( args ) {
				args.lang = '<?php echo $this->current_lang; ?>';

				return args;
			});
		</script>
<?php
	}

	/**
	 * Action: Set Language
	 *
	 * @param string  $lang_slug
	 * @param object  $current_lang  Current language
	 */

	public function set_language( $lang_slug, $current_lang )
	{
		$this->default_lang = pll_default_language();
		$this->current_lang = pll_current_language();

		acf_update_setting( 'default_language', $this->default_lang );
		acf_update_setting( 'current_language', $this->current_lang );
	}

	/**
	 * Action: Update Field Group
	 *
	 * This function is hooked into the `acf/update_field_group` action
	 * and will save all field group data to a `.json` file.
	 *
	 * @param array $field_group
	 */

	public function update_field_group( $field_group )
	{
		$this->register_strings( $field_group, 'option' );
	}

	/**
	 * Register Gravity Forms with Polylang.
	 *
	 * @used-by Actions\"acf/include_fields"
	 */

	public function include_fields()
	{
		$groups = acf_get_field_groups();

		if ( is_array( $groups ) ) {
			foreach ( $groups as $group ) {
				$this->register_strings( $group, 'option' );
			}
		}
	}

	/**
	 * Apply translations with Polylang.
	 *
	 * @used-by Actions\"acf/get_valid_field"
	 */

	public function acf_get_valid_field( $field )
	{
		global $polylang;

		if ( $this->current_lang === $this->default_lang ) {
			return $field;
		}

		if ( isset( $this->_fields[ $field['key'] ][ $this->current_lang ] ) ) {
			return $this->_fields[ $field['key'] ][ $this->current_lang ];
		}

		/** Trick Polylang into loading from the proper language file. Current one is set by get_locale() */
		$this->pll_set_language( $this->default_lang );

		$field_keys = $this->_get_field_keys();

		$this->inject_strings( $field, $field_keys );

		switch ( $field['type'] ) {
			case 'text':
			case 'textarea':
			case 'number':
			case 'email':
			case 'url':
			case 'password':
			case 'select':
				if ( isset( $field['placeholder'] ) && '' != $field['placeholder'] ) {
					$field['placeholder'] = pll_translate_string( $field['placeholder'], $this->current_lang );
				}
				break;

			case 'text':
			case 'textarea':
			case 'number':
			case 'email':
			case 'url':
			case 'password':
				$affix_keys = $this->_get_affix_keys();

				foreach ( $affix_keys as $key ) {
					if ( isset( $field[ $key ] ) && '' != $field[ $key ] ) {
						$field[ $key ] = pll_translate_string( $field[ $key ], $this->current_lang );
					}
				}
				break;

			case 'true_false':
			case 'message':
				if ( isset( $field['message'] ) && '' != $field['message'] ) {
					$field['message'] = pll_translate_string( $field['message'], $this->current_lang );
				}

			case 'select':
			case 'checkbox':
			case 'radio':
			case 'true_false':
				if ( ! empty( $field['choices'] ) ) {
					foreach ( $field['choices'] as &$choice ) {
						$choice = pll_translate_string( $choice, $this->current_lang );
					}
				}
				break;

			case 'google_map':
				$location_keys = $this->_get_location_keys();

				foreach ( $location_keys as $key ) {
					if ( isset( $field[ $key ] ) && '' != $field[ $key ] ) {
						$field[ $key ] = pll_translate_string( $field[ $key ], $this->current_lang );
					}
				}
				break;

			case 'date_picker':
				$date_keys = $this->_get_date_keys();

				foreach ( $date_keys as $key ) {
					if ( isset( $field[ $key ] ) && '' != $field[ $key ] ) {
						$field[ $key ] = pll_translate_string( $field[ $key ], $this->current_lang );
					}
				}
				break;

			case 'repeater':
			case 'flexible_content':
				if ( isset( $field['button_label'] ) && '' != $field['button_label'] ) {
					$field['button_label'] = pll_translate_string( $field['button_label'], $this->current_lang );
				}

			case 'flexible_content':
				if ( ! empty( $field['layouts'] ) ) {
					foreach ( $field['layouts'] as &$layout ) {
						$prefix = $_prefix . $field['key'] . '.layouts.' . $layout['key'] . '.';
						$data[ $prefix . 'label' ] = [
							'string'    => $choice,
							'multiline' => false
						];
						$layout['label'] = pll_translate_string( $layout['label'], $this->current_lang );
					}
				}
				break;
		}

		$this->pll_reset_language();

		$this->_fields[ $field['id'] ][ $this->current_lang ] = $field;

		return $field;
	}

	/**
	 * Register ACF Fields with Polylang.
	 *
	 * @param  array   $field_group
	 * @param  string  $destination
	 */

	public function register_strings( $field_group, $destination = 'register' )
	{
		$field_group['fields'] = acf_get_fields( $field_group );

		if ( ! isset( $field_group['key'] ) ) {
			return;
		}

		$group = 'acf-' . $field_group['key'];

		$string_data = [];

		$this->_extract_strings( $field_group, $string_data );

		if ( ! empty( $string_data ) ) {
			$this->_register_strings( $string_data, $group, $destination );
		}
	}

	/**
	 * List of properties as an associative array:
	 * - (string) 'key' => (bool) $multiline
	 */

	protected function _get_group_keys()
	{
		$group_keys = [
			'title' => false
		];

		return apply_filters( 'pll/acf/field_group_keys', $group_keys );
	}

	/**
	 *
	 */

	protected function _get_field_keys()
	{
		$field_keys = [
			'label'         => false,
		#	'value'         => false,
		#	'default_value' => false,
			'instructions'  => true
		];

		return apply_filters( 'pll/acf/field_keys', $field_keys );
	}

	/**
	 * @param array $field
	 */

	protected function _get_value_keys( $field )
	{
		$field_keys = [
			'value'         => false,
			'default_value' => false
		];

		return apply_filters( 'pll/acf/value_keys', $field_keys, $field );
	}

	/**
	 *
	 */

	protected function _get_affix_keys()
	{
		$field_keys = [
			'prepend' => false,
			'append'  => false
		];

		return apply_filters( 'pll/acf/affix_keys', $field_keys );
	}

	/**
	 *
	 */

	protected function _get_location_keys()
	{
		$field_keys = [
			'center_lat' => false,
			'center_lng' => false,
			'zoom'       => false
		];

		return apply_filters( 'pll/acf/location_keys', $field_keys );
	}

	/**
	 *
	 */

	protected function _get_date_keys()
	{
		$field_keys = [
			'display_format' => false,
			'return_format'  => false,
		#	'first_day'      => false
		];

		return apply_filters( 'pll/acf/date_keys', $field_keys );
	}

	/**
	 * Extract translatable items from the $field_group
	 *
	 * Supported Field Types:
	 * - Basic
	 *   - text
	 *   - textarea
	 *   - number
	 *   - email
	 *   - url
	 *   - password
	 * - Content
	 *   - wysiwyg
	 *   - oembed
	 *   - image
	 *   - file
	 *   - gallery (pro)
	 * - Choice
	 *   - select
	 *   - checkbox
	 *   - radio
	 *   - true_false
	 * - Relational
	 *   - post_object
	 *   - page_link
	 *   - relationship
	 *   - taxonomy
	 *   - user
	 * - jQuery
	 *   - google_map
	 *   - date_picker
	 *   - color_picker
	 * - Layout
	 *   - message
	 *   - tab
	 *   - repeater (pro)
	 *   - flexible_content (pro)
	 *
	 * Unsupported Field Types:
	 * - N/A
	 *
	 * @todo    Register custom locations, fields, and field types.
	 *
	 * @param   mixed        $field_group
	 * @param   array        $data
	 * @param   string|bool  $sub_fields
	 * @return  array        $data
	 */

	protected function _extract_strings( $field_group, &$data = [], $sub_fields = false )
	{
		$is_options_group = $this->is_options_group( $field_group );

		if ( $sub_fields ) {
			$fields = ( isset( $field_group['sub_fields'] ) ? $field_group['sub_fields'] : $field_group );
			$_prefix = ( is_string( $sub_fields ) ? $sub_fields : '' );
		}
		else {
			$group_keys = $this->_get_group_keys();

			$this->pluck_strings( $field_group, $group_keys, $data );

			$fields = $field_group['fields'];
			$_prefix = '';
		}

		if ( ! empty( $fields ) ) {
			$field_keys = $this->_get_field_keys();

			foreach ( $fields as $i => $field ) {
				$prefix = ( $_prefix . $field['key'] . '.' );

				$value_keys = $this->_get_value_keys( $field );

				$this->pluck_strings( $field, $field_keys, $data, $prefix );

				if ( $is_options_group ) {
					if ( $field['value'] === null ) {
						$field['value'] = acf_get_value( 'options', $field );
					}

					$this->pluck_strings( $field, $value_keys, $data, $prefix );
				}

				$prefix = '';

				switch ( $field['type'] ) {
					case 'text':
					case 'textarea':
					case 'number':
					case 'email':
					case 'url':
					case 'password':
					case 'select':
						if ( isset( $field['placeholder'] ) && '' != $field['placeholder'] ) {
							$data[ $_prefix . $field['key'] . '.placeholder' ] = [
								'string'    => $field['placeholder'],
								'multiline' => ( 'textarea' === $field['type'] )
							];
						}

					case 'text':
					case 'textarea':
					case 'number':
					case 'email':
					case 'url':
					case 'password':
						$affix_keys = $this->_get_affix_keys();

						foreach ( $affix_keys as $key ) {
							if ( isset( $field[ $key ] ) && '' != $field[ $key ] ) {
								$data[ $_prefix . $field['key'] . '.' . $key ] = [
									'string'    => $field[ $key ],
									'multiline' => false
								];
							}
						}
						break;

					case 'true_false':
					case 'message':
						if ( isset( $field['message'] ) && '' != $field['message'] ) {
							$data[ $_prefix . $field['key'] . '.message' ] = [
								'string'    => $field['message'],
								'multiline' => true
							];
						}

					case 'select':
					case 'checkbox':
					case 'radio':
					case 'true_false':
						if ( ! empty( $field['choices'] ) ) {
							foreach ( $field['choices'] as $j => $choice ) {
								$prefix = $_prefix . $field['key'] . '.choices.' . $j;
								$data[ $prefix ] = [
									'string'    => $choice,
									'multiline' => false
								];
							}
						}
						break;

					case 'google_map':
						$location_keys = $this->_get_location_keys();

						foreach ( $location_keys as $key ) {
							if ( isset( $field[ $key ] ) && '' != $field[ $key ] ) {
								$data[ $_prefix . $field['key'] . '.' . $key ] = [
									'string'    => $field[ $key ],
									'multiline' => false
								];
							}
						}
						break;

					case 'date_picker':
						$date_keys = $this->_get_date_keys();

						foreach ( $date_keys as $key ) {
							if ( isset( $field[ $key ] ) && '' != $field[ $key ] ) {
								$data[ $_prefix . $field['key'] . '.' . $key ] = [
									'string'    => $field[ $key ],
									'multiline' => false
								];
							}
						}
						break;

					case 'repeater':
					case 'flexible_content':
						if ( isset( $field['button_label'] ) && '' != $field['button_label'] ) {
							$data[ $_prefix . $field['key'] . '.button_label' ] = [
								'string'    => $field['button_label'],
								'multiline' => false
							];
						}

					case 'repeater':
						if ( ! empty( $field['sub_fields'] ) ) {
							$prefix = $_prefix . $field['key'] . '.sub_fields.';
							$this->_extract_strings( $field['sub_fields'], $data, $prefix );
						}
						break;

					case 'flexible_content':
						if ( ! empty( $field['layouts'] ) ) {
							foreach ( $field['layouts'] as $layout ) {
								$prefix = $_prefix . $field['key'] . '.layouts.' . $layout['key'] . '.';
								$data[ $prefix . 'label' ] = [
									'string'    => $choice,
									'multiline' => false
								];

								if ( ! empty( $field['sub_fields'] ) ) {
									$prefix = $prefix . $field['key'] . '.sub_fields.';
									$this->_extract_strings( $field['sub_fields'], $data, $prefix );
								}
							}
						}
						break;
				}
			}

			$data = apply_filters( 'pll/acf/extract_strings', $data, $field, $field_group );
		}

		return $data;
	}

	/**
	 * @param array $field_group
	 */

	public function is_options_group( array $field_group )
	{
		$is_options_group = false;

		if ( ! empty( $field_group['location'] ) ) {
			foreach ( $field_group['location'] as $rules ) {
				foreach ( $rules as $rule ) {
					if ( isset( $rule['param'] ) && $rule['param'] === 'options_page' ) {
						$is_options_group = true;
					}
				}
			}
		}

		return $is_options_group;
	}

}

ACF_Polylang_Compatibility::init();

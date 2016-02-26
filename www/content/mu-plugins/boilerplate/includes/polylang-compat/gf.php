<?php

/**
 * File : Polylang Compatibility for Gravity Forms
 *
 * @package Boilerplate
 */

namespace Boilerplate;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/** Can't test for `class_exists('GFForms')` yet. */
if ( ! is_plugin_active( 'gravityforms/gravityforms.php' ) && function_exists('pll_register_string') ) {
	return;
}

/**
 * Class : Gravity Forms Compatibility
 */

class PLL_Gravity_Forms_Compatibility extends PLL_Compatibility
{
	const OPTION_NAME = 'polylang_gforms_strings';

	/**
	 * @see parent::init()
	 */

	public static function init()
	{
		$class = get_called_class();
		$class = new $class();

		# add_action( 'init', [ &$class, 'wp_init' ], 99 );

		add_filter( 'pll_get_strings',             [ &$class, 'get_strings'  ] );
		add_action( 'pll_language_defined',        [ &$class, 'set_language' ], 1, 2 );

		add_filter( 'gform_pre_render',            [ &$class, 'gform_pre_render'            ], 11, 2 );
		add_filter( 'gform_pre_submission_filter', [ &$class, 'gform_pre_submission_filter' ] );
		add_filter( 'gform_notification',          [ &$class, 'gform_notification'          ], 11, 3 );
		add_filter( 'gform_field_validation',      [ &$class, 'gform_field_validation'      ], 11, 4 );
		add_filter( 'gform_merge_tag_filter',      [ &$class, 'gform_merge_tag_filter'      ], 11, 5 );

		add_action( 'gform_after_save_form',       [ &$class, 'update_form_translations'          ], 11, 2 );
		add_action( 'gform_pre_confirmation_save', [ &$class, 'update_confirmation_translations'  ], 11, 2 );
		add_action( 'gform_pre_notification_save', [ &$class, 'update_notifications_translations' ], 11, 2 );
		add_action( 'gform_after_delete_form',     [ &$class, 'remove_form_translations'          ], 11 );
		add_action( 'gform_after_delete_field',    [ &$class, 'remove_field_translations'         ], 11, 2 );
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
	}

	/**
	 * @used-by  Actions\"gform_after_save_form"
	 * @param    array  $form
	 * @param    bool   $is_new
	 */

	public function update_form_translations( $form, $is_new )
	{
		$this->register_strings( $form, 'option' );
	}

	/**
	 * @used-by  Actions\"gform_pre_notification_save"
	 * @param    array  $notification
	 * @param    array  $form
	 */

	function update_notifications_translations( $notification, $form )
	{
		$this->register_strings( $form, 'option' );
		return $notification;
	}

	/**
	 * @used-by  Actions\"gform_pre_confirmation_save"
	 * @param    array  $confirmation
	 * @param    array  $form
	 */

	function update_confirmation_translations( $confirmation, $form )
	{
		$this->register_strings( $form, 'option' );
		return $confirmation;
	}

	/**
	 * @used-by  Actions\"gform_after_delete_form"
	 * @param    int  $form_id
	 */

	public function remove_form_translations( $form_id )
	{
		// $this->unregister_strings( $form, 'option' );
	}

	/**
	 * Remove translations of deleted field
	 *
	 * @used-by  Actions\"gform_after_delete_form"
	 * @param    int  $form_id
	 * @param    int  $field_id
	 */

	function remove_field_translations( $form_id, $field_id )
	{
		$form = RGFormsModel::get_form_meta( $form_id );
		$this->register_strings( $form, 'option' );
	}

	/**
	 * Register Gravity Forms with Polylang.
	 *
	 * @used-by Actions\"init"
	 */

	public function wp_init()
	{
		$forms = GFAPI::get_forms();

		if ( is_array( $forms ) ) {
			foreach ( $forms as $form ) {
				$this->register_strings( $form );
			}
		}
	}

	/**
	 * Register Gravity Form with Polylang.
	 *
	 * @param  array   $form
	 * @param  string  $destination
	 */

	public function register_strings( $form, $destination = 'register' )
	{
		if ( is_numeric( $form ) ) {
			$form = RGFormsModel::get_form_meta( $form );
		}

		if ( ! isset( $form['id'] ) ) {
			return;
		}

		$group = 'gravity-form-' . $form['id'];

		$string_data = [];

		$this->_extract_strings( $form, $string_data );

		if ( ! empty( $string_data ) ) {
			$this->_register_strings( $string_data, $group, $destination );
		}
	}

	/**
	 * List of properties as an associative array:
	 * - (string) 'key' => (bool) $multiline
	 */

	protected function _get_form_keys()
	{
		$form_keys = [
			'title'                                  => false,
			'description'                            => true,
			'button.text'                            => false,
			'button.imageUrl'                        => false,
			'postTitleTemplate'                      => false,
			'postContentTemplate'                    => true,
			'lastPageButton.text'                    => false,
			'lastPageButton.imageUrl'                => false,
			'pagination.progressbar_completion_text' => false,
			'save.button.text'                       => false,
			'limitEntriesMessage'                    => true,
			'schedulePendingMessage'                 => true,
			'scheduleMessage'                        => true,
			'requireLoginMessage'                    => true
		];

		return apply_filters( 'pll/gform/form_keys', $form_keys );
	}

	/**
	 *
	 */

	protected function _get_field_keys()
	{
		$field_keys = [
			'label'          => false,
			'adminLabel'     => false,
			'description'    => true,
			'defaultValue'   => false,
			'errorMessage'   => true,
			'placeholder'    => false,
			'inputMaskValue' => false
		];

		return apply_filters( 'pll/gform/field_keys', $field_keys );
	}

	/**
	 * @used-by  Filters\"gform_pre_render"
	 * @param    array  $form
	 * @param    bool   $ajax
	 */

	public function gform_pre_render( $form, $ajax )
	{
		global $polylang;

		if ( $this->current_lang === $this->default_lang ) {
			return $form;
		}

		if ( isset( $this->_forms[ $form['id'] ][ $this->current_lang ] ) ) {
			return $this->_forms[ $form['id'] ][ $this->current_lang ];
		}

		/** Trick Polylang into loading from the proper language file. Current one is set by get_locale() */
		$this->pll_set_language( $this->default_lang );

		$form_keys = $this->_get_form_keys();

		$this->inject_strings( $form, $form_keys );

		if ( isset( $form['pagination']['pages'] ) ) {
			foreach ( $form['pagination']['pages'] as $key => $page_title ) {
				$form['pagination']['pages'][ $key ] = pll_translate_string( $form['pagination']['pages'][ $key ], $this->current_lang );
			}
		}

		if ( isset( $form['fields'] ) ) {
			$field_keys = $this->_get_field_keys();

			foreach ( $form['fields'] as $i => $field ) {
				if ( $field['type'] != 'page' ) {
					$this->inject_strings( $form['fields'][ $i ], $field_keys );
				}

				switch ( $field['type'] ) {
					case 'html':
						$form['fields'][ $i ]['content'] = pll_translate_string( $field['content'], $this->current_lang );
						break;

					case 'page':
						foreach ( [ 'text', 'imageUrl' ] as $key ) {
							if ( isset( $form['fields'][ $i ]['nextButton'][ $key ] ) ) {
								$form['fields'][ $i ]['nextButton'][ $key ] = pll_translate_string( $field['nextButton'][ $key ], $this->current_lang );
							}
							if ( isset( $form['fields'][ $i ]['previousButton'][ $key ] ) ) {
								$form['fields'][ $i ]['previousButton'][ $key ] = pll_translate_string( $field['previousButton'][ $key ], $this->current_lang );
							}
						}
						break;

					case 'select':
					case 'multiselect':
					case 'checkbox':
					case 'radio':
					case 'list':
					case 'product':
					case 'option':
						if ( ! empty( $field['choices'] ) ) {
							/** Prevent indirect modification of an overloaded element. */
							$choices = $field['choices'];
							foreach ( $choices as &$choice ) {
								$choice['text'] = pll_translate_string( $choice['text'], $this->current_lang );

								if ( isset( $choice['price'] ) ) {
									$choice['price'] = pll_translate_string( $choice['price'], $this->current_lang );
								}
							}
							$form['fields'][ $i ]['choices'] = $choices;
						} elseif ( isset( $field['basePrice'] ) ) {
							$form['fields'][ $i ]['basePrice'] = pll_translate_string( $form['fields'][ $i ]['basePrice'], $this->current_lang );
						}
						break;

					case 'post_custom_field':
						$form['fields'][ $i ]['customFieldTemplate'] = pll_translate_string( $field['customFieldTemplate'], $this->current_lang );
						break;

					case 'post_category':
						$form['fields'][ $i ]['categoryInitialItem'] = pll_translate_string( $field['categoryInitialItem'], $this->current_lang );
						break;
				}
			}
		}

		$this->pll_reset_language();

		$this->_forms[ $form['id'] ][ $this->current_lang ] = $form;

		return $form;
	}

	/**
	 * @used-by  Filters\"gform_pre_submission_filter"
	 * @param    array  $form
	 */

	public function gform_pre_submission_filter( $form )
	{
		global $polylang;

		$form = $this->gform_pre_render( $form, false );

		$this->pll_set_language( $this->default_lang );

		if ( ! empty( $form['confirmations'] ) ) {
			foreach( $form['confirmations'] as $key => &$confirmation ) {
				switch ( $confirmation['type'] ) {
					case 'message':
						$confirmation['message'] = pll_translate_string( $confirmation['message'], $this->current_lang );
					break;
					case 'redirect':
						$url = pll_translate_string( $confirmation['url'], $this->current_lang );
						$confirmation['url'] = str_replace( '&amp;lang=','&lang=', $url );
					break;
					case 'page':
						$page_id = pll_translate_string( $confirmation['pageId'], $this->current_lang );

						$confirmation['pageId'] = ( isset( $polylang ) && ( $tr_id = $polylang->model->get_translation( 'page', $page_id, $lang ) ) ? $tr_id : $page_id );
					break;
				}
			}
		}

		$this->pll_reset_language();

		$this->_forms[ $form['id'] ][ $this->current_lang ] = $form;

		return $form;
	}

	/**
	 * @used-by  Filters\"gform_notification"
	 * @param    array  $notification
	 * @param    array  $form
	 * @param    array  $lead
	 */

	public function gform_notification( $notification, $form, $lead )
	{
		$this->pll_set_language( $this->default_lang );

		if ( $form['notifications'][ $notification['id'] ]['toType'] == 'email' || $form['notifications'][ $notification['id'] ]['toType'] == 'field' ) {
			$notification['subject'] = pll_translate_string( $notification['subject'], $this->current_lang );
			$notification['message'] = pll_translate_string( $notification['message'], $this->current_lang );
		}

		$this->pll_reset_language();

		return $notification;
	}

	/**
	 * @used-by  Filters\"gform_field_validation"
	 * @param    array  $result
	 * @param    mixed  $value
	 * @param    array  $form
	 * @param    array  $field
	 */

	public function gform_field_validation( $result, $value, $form, $field )
	{
		$this->pll_set_language( $this->default_lang );

		if ( ! $result['is_valid'] ) {
			$result['message'] = pll_translate_string( $result['message'], $this->current_lang );
		}

		$this->pll_reset_language();

		return $result;
	}

	/**
	 * @used-by  Filters\"gform_merge_tag_filter"
	 * @param    mixed  $value
	 * @param    int    $input_id
	 * @param    array  $match
	 * @param    array  $field
	 * @param    mixed  $raw_value
	 */

	public function gform_merge_tag_filter( $value, $input_id, $match, $field, $raw_value )
	{
		if ( \GFFormsModel::get_input_type( $field ) != 'multiselect' ) {
			return $value;
		}

		$options = [];
		$value = explode( ',', $value );
		foreach ( $value as $selected ) {
			$options[] = GFCommon::selection_display( $selected, $field, $currency = null, $use_text = true );
		}

		return implode( ', ', $options );
	}

	/**
	 * Extract translatable items from the $form
	 *
	 * Supported Field Types:
	 * - Basic
	 *   - text
	 *   - textarea
	 *   - email
	 *   - number
	 *   - section
	 * - Custom
	 *   - html
	 *   - page
	 *   - select
	 *   - multiselect
	 *   - checkbox
	 *   - radio
	 *   - list
	 *   - product
	 *   - option
	 *   - post_custom_field
	 *   - post_category
	 *
	 * Unsupported Field Types:
	 * - N/A
	 *
	 * @see     parent::_extract_strings()
	 * @param   mixed   $form
	 * @param   array   $data
	 * @return  array   $data
	 */

	protected function _extract_strings( $form, &$data = [] )
	{
		$form_keys = $this->_get_form_keys();

		$this->pluck_strings( $form, $form_keys, $data );

		if ( isset( $form['pagination']['pages'] ) ) {
			foreach ( $form['pagination']['pages'] as $key => $page_title ) {
				$data[ 'page.' . ( $key + 1 ) . '.title' ] = $page_title;
			}
		}

		if ( ! empty( $form['fields'] ) ) {
			$field_keys = $this->_get_field_keys();

			foreach ( $form['fields'] as $i => $field ) {
				if ( $field['type'] != 'page' ) {
					$this->pluck_strings( $field, $field_keys, $data, ( 'fields.' . $field['id'] . '.' ) );
				}

				switch ( $field['type'] ) {
					case 'html':
						if ( isset( $field['content'] ) && '' != $field['content'] ) {
							$data[ 'fields.' . $field['id'] . '.content'] = [
								'string'    => $field['content'],
								'multiline' => false
							];
						}
						break;

					case 'page':
						// Page breaks are stored as belonging to the next page,
						// but their buttons are actually displayed in the previous page
						foreach ( [ 'text', 'imageUrl' ] as $key ) {
							if ( isset( $form['fields'][ $i ]['nextButton'][ $key ] ) ) {
								$data[ 'page.' . ( $field['pageNumber'] - 1 ) . '.nextButton.' . $key ] = [
									'string'    => $field['nextButton'][ $key ],
									'multiline' => false
								];
							}
							if ( isset( $form['fields'][ $i ]['previousButton'][ $key ] ) ) {
								$data[ 'page.' . ( $field['pageNumber'] - 1 ) . '.previousButton.' . $key ] = [
									'string'    => $field['previousButton'][ $key ],
									'multiline' => false
								];
							}
						}
						break;

					case 'select':
					case 'multiselect':
					case 'checkbox':
					case 'radio':
					case 'list':
					case 'product':
					case 'option':
						if ( ! empty( $field['choices'] ) ) {
							foreach ( $field['choices'] as $j => $choice ) {
								$prefix = 'fields.' . $field['id'] . '.choices.' . $j;
								$data[ $prefix ] = [
									'string'    => $choice['text'],
									'multiline' => false
								];

								if ( isset( $choice['price'] ) ) {
									$data[ $prefix . '-price'] = [
										'string'    => $choice['price'],
										'multiline' => false
									];
								}
							}
						} elseif ( isset( $field['basePrice'] ) ) {
							$data[ 'fields.' . $field['id'] . '.basePrice' ] = [
								'string'    => $field['basePrice'],
								'multiline' => false
							];
						}
						break;

					case 'post_custom_field':
						if ( isset( $field['customFieldTemplate'] ) && '' != $field['customFieldTemplate'] ) {
							$data[ 'fields.' . $field['id'] . '.customFieldTemplate' ] = [
								'string'    => $field['customFieldTemplate'],
								'multiline' => false
							];
						}
						break;

					case 'post_category':
						if ( isset( $field['categoryInitialItem'] ) && '' != $field['categoryInitialItem'] ) {
							$data[ 'fields.' . $field['id'] . '.categoryInitialItem'] = [
								'string'    => $field['categoryInitialItem'],
								'multiline' => false
							];
						}
						break;
				}
			}

			$data = apply_filters( 'pll/gform/extract_strings', $data, $field, $form );
		}

		if ( isset( $form['notifications'] ) ) {
			$notification_keys = [
				'name'     => false,
				'subject'  => false,
				'message'  => true,
				'from'     => false,
				'fromName' => false,
				'replyTo'  => false
			];

			$notification_keys = apply_filters('pll/gform/notification_keys', $notification_keys );

			foreach ( $form['notifications'] as $id => $notification ) {
				$this->pluck_strings( $notification, $notification_keys, $data, ( 'notifications.' . $id . '.' ) );
			}
		}

		if ( isset( $form['confirmations'] ) ) {
			$confirmation_keys = [
				'name'    => false,
				'message' => true,
				'url'     => false,
				'pageId'  => false
			];

			$confirmation_keys = apply_filters('pll/gform/confirmation_keys', $confirmation_keys );

			foreach ( $form['confirmations'] as $id => $confirmation ) {
				$this->pluck_strings( $confirmation, $confirmation_keys, $data, ( 'confirmations.' . $id . '.' ) );
			}
		}

	}

}

PLL_Gravity_Forms_Compatibility::init();

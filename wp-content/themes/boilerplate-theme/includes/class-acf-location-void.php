<?php

namespace Theme;

/**
 * Void ACF Location.
 *
 * This custom location ensures a field group will not be displayed anywhere.
 * Useful for field groups that remain unassigned such as for clone fields.
 *
 * This location is mainly for ACF's user interface which requires one
 * to choose a location.
 *
 * If writing and field group programmatically, leave the location array empty
 * instead of assigning the void location.
 *
 * @phpstan-type Rule array{
 *     id: string,
 *     group: string,
 *     param: string,
 *     operator: string,
 *     value: string,
 * }
 */
class ACF_Location_Void extends \ACF_Location {
    /**
     * @return void
     */
    public function initialize() {
        $this->name = 'void';
        $this->label = _x( 'Void', 'acf location', 'theme-boilerplate' );
        $this->category = 'Custom';
    }

    /**
     * @param  array<string, mixed> $rule
     * @return array<string, string>
     *
     * @phpstan-type Rule $rule
     */
    public function get_values( $rule ) {
        return [
            '-' => _x( 'None', 'acf location', 'theme-boilerplate' ),
        ];
    }

    /**
     * @param  array<string, mixed> $rule
     * @param  array<string, mixed> $screen
     * @param  array<string, mixed> $field_group
     * @return bool
     *
     * @phpstan-type Rule $rule
     */
    public function match( $rule, $screen, $field_group ) {
        return false;
    }

    /**
     * @param  array<string, mixed> $rule
     * @return array<string, string>
     *
     * @phpstan-type Rule $rule
     */
    public static function get_operators( $rule ) {
        return [
            '-' => _x( 'None', 'acf location', 'theme-boilerplate' ),
        ];
    }
}

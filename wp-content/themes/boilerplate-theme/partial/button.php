<?php

/**
 * Icon snippet.
 */

extract( wp_parse_args( $args, [
    'href'        => null,
    'label'       => null,
    'icon'        => null,
    'target'      => null,
    'classes'     => [],
    'modifiers'   => [],
    'attributes'  => [],
] ) );

// Bail early if the href is not set.
if (!$href) {
    return;
}

$is_external = theme_is_external_url( $href );

// Validation
if ( ! $label && !$icon ) {
    _theme_doing_template_part_wrong(
        __FILE__,
        _x( 'Expected at least `label` or `icon`.', 'template partial', 'theme-common' )
    );
}

// Defaults
if ( !$target && $is_external) {
    $target = '_blank';
}

if ( !$icon && $is_external)

// Computed
$html_tag = 'a';
$classes = array_merge([ 'c-button' ], $modifiers, $classes);
$attributes = array_merge($attributes, [
    'href'     => $href,
    'target'  => $target,
    'class'    => $classes,
]);
?>

<a <?php echo html_build_attributes( $attributes ); ?>>
    <?php if ( $label ) : ?>
        <span><?php echo $label; ?></span>
    <?php endif; ?>

    <?php if ( $icon ) : ?>
        <?php if ( mb_strlen($icon) === 1) : ?>
            <?php echo $icon; ?>
        <?php else : ?>
            <?php get_template_part( 'partial/icon', null, [
                'icon'       => $icon,
                'classes'    => ['c-button-icon'],
                'attributes' => [
                    'aria-hidden' => true,
                ],
            ] ); ?>
        <?php endif; ?>
    <?php endif; ?>
</a>


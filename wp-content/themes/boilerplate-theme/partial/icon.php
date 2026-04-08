<?php

/**
 * Icon snippet.
 */

extract( wp_parse_args( $args, [
    'icon'       => null,
    'aria_label'  => null,
    'classes'    => [],
    'modifiers'  => [],
    'attributes' => [],
] ) );

if (!$icon) {
    return;
}

$classes = array_merge([ 'c-icon' ], $modifiers, $classes);
$attributes = array_merge($attributes, [
    'class' => $classes,
]);
$svg_classes = 'svg-' . $icon;

?>
<span <?php echo html_build_attributes( $attributes ); ?>>
    <?php if ($aria_label) : ?>
        <span class="sr-only"><?php echo $aria_label ?></span>
    <?php endif; ?>
    <svg class="<?php echo $svg_classes ?>" focusable="false" aria-hidden="true">
        <use xlink:href="<?php echo theme_get_svg_sprite_symbol_uri($icon) ?>"></use>
    </svg>
</span>

<?php

extract( wp_parse_args( $args, [
    'title'       => null,
    'content'     => null,
    'classes'     => [],
    'modifiers'   => [],
    'attributes'  => [],
] ) );

// Validation
if ( ! $title && !$content ) {
    _theme_doing_template_part_wrong(
        __FILE__,
        _x( 'Expected at least `title` or `content`.', 'template partial', 'theme-common' )
    );
}

// Computed
$classes = array_merge([ 'c-accordion' ], $modifiers, $classes);
$attributes = array_merge($attributes, [
    'class'    => $classes,
]);
?>

<c-accordion <?php echo html_build_attributes( $attributes ); ?>>
    <details class="c-accordion_details">
        <summary class="c-accordion_summary">
            <span class="c-accordion_label || c-heading -h3"><?php echo $title; ?></span>
            <span class="c-accordion_icon" aria-hidden="true">&darr;</span>
        </summary>
        <div class="c-accordion_content" data-accordion="content">
            <?php echo $content; ?>
        </div>
    </details>
</c-accordion>

<?php theme_enqueue_vite_script( 'src/scripts/components/Accordion.ts' ); ?>

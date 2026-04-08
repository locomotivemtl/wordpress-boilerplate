<!DOCTYPE html>
<html <?php language_attributes(); ?> class="has-no-js">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/dist/favicons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?php echo get_template_directory_uri(); ?>/dist/favicons/favicon.svg" />
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/dist/favicons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/dist/favicons/apple-touch-icon.png" />
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/dist/favicons/site.webmanifest" />

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <script>
        <?php // This needs and should be executed before any rendering to prevent flickering on page load  ?>
        document.documentElement.classList.remove("has-no-js");
        document.documentElement.classList.add("has-js");
    </script>

    <noscript>
        <p class="noscript-message" style="display: none;">Attention! JavaScript est requis pour que ce site fonctionne correctement.</p>
        <style>
            html {
                opacity: 1 !important;
            }
            .noscript-message {
                display: block !important;
                background-color: crimson;
                color: white;
                padding: 10px;
            }
        </style>
    </noscript>

    <div class="c-page-spinner"></div>

    <div class="c-preloader">

    </div>

    <div id="swup" class="transition-default">

        <?php get_template_part( 'partial/site-header' ); ?>


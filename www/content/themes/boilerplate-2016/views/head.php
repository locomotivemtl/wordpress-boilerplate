<?php

/**
 * File : Document <head>
 *
 * Loaded by {@see base.php}.
 *
 * @package Boilerplate\Templates
 */

?><!doctype html>
<!--[if IE 9]>        <html <?php language_attributes(); ?> <?php html_class([ 'lt-ie10' ]); ?>> <![endif]-->
<!--[if gt IE 9]><!--><html <?php language_attributes(); ?> <?php html_class(); ?>><!--<![endif]-->
    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php wp_head(); ?>

        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'xxxxxxxx', 'auto');
            ga('send', 'pageview');
        </script>

    </head>
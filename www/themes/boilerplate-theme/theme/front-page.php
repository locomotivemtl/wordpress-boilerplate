<?php

/**
 * Template Name: Homepage
 * Description: The home page.
 */

use Timber\Timber;

$context = Timber::context();

$context['title'] = 'Welcome to the Locomotive WordPress Boilerplate';

Timber::render( 'front-page.twig', $context );

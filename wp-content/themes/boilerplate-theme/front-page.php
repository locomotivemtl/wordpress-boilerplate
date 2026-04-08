<?php

/**
 * Template Name: Page d'accueil
 */

get_header();

?>
    <header class="container">
        <h1 class="heading-h1">Welcome to the Locomotive WordPress Boilerplate.</h1>
    </header>

    <div class="container my-fluid-xl">
        <div class="grid grid-cols-12 gap-x-gutter gap-y-fluid-lg">
            <div class="col-span-full flex flex-col items-start gap-gutter md:col-span-4">
                <div class="c-wysiwyg">
                    <p>
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Itaque quam, nam
                        omnis sunt laudantium tenetur obcaecati? Inventore eos vitae id repellat cum
                        possimus voluptates nam voluptatum, assumenda, atque nesciunt debitis?
                    </p>
                </div>

                <?php theme_button_component(
                    href: '/styleguide',
                    label: "Styleguide",
                ); ?>
            </div>

            <div class="col-span-full flex flex-col items-start gap-gutter md:col-span-4">
                <div class="c-wysiwyg">
                    <p>
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Itaque quam, nam
                        omnis sunt laudantium tenetur obcaecati? Inventore eos vitae id repellat cum
                        possimus voluptates nam voluptatum, assumenda, atque nesciunt debitis?
                    </p>
                </div>

                <?php theme_button_component(
                    href: 'https://locomotive.ca',
                    label: "Visit Us",
                ); ?>
            </div>

            <div class="col-span-full flex flex-col items-start gap-gutter md:col-span-4">
                <div class="c-wysiwyg">
                    <p>
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Itaque quam, nam
                        omnis sunt laudantium tenetur obcaecati? Inventore eos vitae id repellat cum
                        possimus voluptates nam voluptatum, assumenda, atque nesciunt debitis?
                    </p>
                </div>
            </div>

            <div class="col-span-full flex flex-col md:col-span-4">
                <?php foreach ( range(1, 4) as $i ) : ?>
                <?php get_template_part( 'partial/accordion', null, [
                    'title' => 'Accordion summary',
                    'content' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Itaque quam, nam omnis sunt laudantium tenetur obcaecati? Inventore eos vitae id repellat cum possimus voluptates nam voluptatum, assumenda, atque nesciunt debitis?',
                ] ); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php

get_footer();

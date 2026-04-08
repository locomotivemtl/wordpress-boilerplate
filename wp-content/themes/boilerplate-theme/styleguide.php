<?php

/**
 * Template Name: Page d'accueil
 */

get_header();

?>
    <header class="container">
        <h1 class="heading-xl">Styleguide</h1>
    </header>


        <div class="container">
            <h2 class="heading-lg my-fluid-xl">Basic UI</h2>



            <h3 class="heading-md my-fluid-xl">Typography</h3>

            <div class="flex flex-col gap-0 border p-fluid-sm">
                <span class="heading-lg">Heading Lg</span>
                <span class="heading-md">Heading Md</span>
                <span class="heading-sm">Heading Sm</span>
                <span class="heading-xs">Heading Xs</span>
                <span class="heading-2xs">Heading 2xs</span>

                <hr class="my-fluid-sm">

                <span class="body-lg">Body Lg</span>
                <span class="body-md">Body Md</span>
                <span class="body-sm">Body Sm</span>
            </div>

            <h3 class="heading-md  my-fluid-xl">Buttons</h3>

            <div class="flex p-fluid-sm gap-4 border">
                <?php theme_button_component(
                    href: '#',
                    label: "Default Button",
                ); ?>

                <?php theme_button_component(
                    href: '#',
                    label: "Icon Button",
                    icon: "→"
                ); ?>
                <?php theme_button_component(
                    href: '#',
                    label: "Icon Button",
                    icon: "external"
                ); ?>
            </div>

            <div class="flex gap-4 p-fluid-sm  bg-black">
                <?php theme_button_component(
                    href: '#',
                    label: "Default Button",
                    modifiers: "-white",
                ); ?>

                <?php theme_button_component(
                    href: '#',
                    label: "Icon Button",
                    icon: "→",
                    modifiers: "-white",
                ); ?>

                <?php theme_button_component(
                    href: '#',
                    label: "Icon Button",
                    icon: "external",
                    modifiers: "-white",
                ); ?>
            </div>

            <h3 class="heading-md my-fluid-xl">Colors</h3>

            <div class="flex gap-4">
                <div class="flex aspect-square w-24 items-center justify-center border bg-white label">
                    White
                </div>
                <div class="flex aspect-square w-24 items-center justify-center border bg-black label text-white">
                    Black
                </div>
            </div>
        </div>

        <div class="container">
            <h2 class="heading-lg my-fluid-xl">Snippets</h2>
            <h3 class="heading-md my-fluid-xl">Image</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <?php theme_image_component(
                    src: 'https://placehold.co/600x800',
                    caption: "In consectetur esse in ut minim anim labore excepteur deserunt.",
                ); ?>
                <?php theme_image_component(
                    src: 'https://placehold.co/600x800',
                ); ?>
                <?php theme_image_component(
                    src: 'https://placehold.co/600x800',
                    caption: "In consectetur esse in ut minim anim labore excepteur deserunt.",
                    display_caption: false,
                ); ?>
                <?php theme_image_component(
                    src: 'https://placehold.co/600x800',
                ); ?>
            </div>
        </div>
    </div>

    <div class="container">
        <h2 class="heading-lg my-fluid-xl">Components</h2>

        <h3 class="heading-md my-fluid-xl">Accordions</h3>
        <?php foreach ( range(1, 4) as $i ) : ?>
            <?php get_template_part( 'partial/accordion', null, [
                'title' => 'Accordion summary',
                'content' => 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Itaque quam, nam omnis sunt laudantium tenetur obcaecati? Inventore eos vitae id repellat cum possimus voluptates nam voluptatum, assumenda, atque nesciunt debitis?',
            ] ); ?>
        <?php endforeach; ?>
    </div>
<?php

get_footer();

<header>
    <c-header class="container mb-fluid-lg grid grid-cols-2 py-fluid-md">
        <a href="/" class="flex items-center gap-gutter justify-self-start hocus:opacity-80">
            <div class="sr-only">Home</div>

            <?php get_template_part( 'partial/icon', null, ['icon' => 'logo'] ); ?>
            <?php get_template_part( 'partial/icon', null, ['icon' => 'wordpress'] ); ?>
        </a>

        <nav class="flex gap-gutter justify-self-end">
            <a href="/">Index</a>
            <a href="/styleguide">Styleguide</a>
        </nav>
    </c-header>
</header>

<footer class="container mt-fluid-lg flex justify-between py-fluid-xs">
    <div>
        &copy; <?php echo (new \DateTimeImmutable())->format('Y') ?>
    </div>

    <?php $menu = theme_get_menu_items('footer-menu'); ?>
    <?php if ( ! empty($menu) ) : ?>
        <nav class="flex gap-gutter">
            <?php foreach ( $menu as $menu_item ) : ?>
                <a href="/">Index</a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</footer>

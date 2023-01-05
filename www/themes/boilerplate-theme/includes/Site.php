<?php

namespace App\Theme;

use Timber\Timber;
use Twig\Environment as TwigEnvironment;

/**
 * App Theme Site
 *
 * For configuring the Timber theme.
 */
class Site extends \Timber\Site
{
    /** @var string URI to active theme's static assets directory. */
    public $assets_uri;

    /** @var string Absolute path to the active theme's static assets directory. */
    public $assets_path;

    /** @var string URI to active theme's stylesheet directory. */
    public $theme_uri;

    /** @var string Absolute path to the active theme's stylesheet directory. */
    public $theme_path;

    /** Add timber support. */
    public function __construct()
    {
        /**
         * Retrieve this WordPress Theme instance directly to use methods that
         * are inaccessible from Timber's {@see Timber\Theme::$theme reference}.
         *
         * @var \WP_Theme
         */
        $theme = wp_get_theme();

        $this->theme_uri  = $theme->get_stylesheet_directory_uri();
        $this->theme_path = $theme->get_stylesheet_directory();

        $this->assets_uri  = $this->theme_uri . '/' . THEME_ASSETS_DIR;
        $this->assets_path = $this->theme_path . '/' . THEME_ASSETS_DIR;

        $this->register_actions();
        $this->register_filters();

        parent::__construct();
    }

    /**
     * Clean up wordpress stuff
     *
     * @todo
     *
     * @listens action:init
     *
     * @return void
     */
    public function cleanup_wordpress(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }

    /**
     * Enqueue theme scripts
     *
     * @listens action:wp_enqueue_scripts
     */
    public function enqueue_theme_assets(): void
    {
        // Disable core block styles.
        wp_dequeue_style('wp-block-library');

        // Enqueue theme scripts.
        wp_enqueue_script('theme-vendors', $this->get_assets_uri('scripts/vendors.js'), null, THEME_VERSION);
        wp_script_add_data('theme-vendors', 'defer', true);

        wp_enqueue_script('theme-app', $this->get_assets_uri('scripts/app.js'), null, THEME_VERSION);
        wp_script_add_data('theme-app', 'defer', true);

        // Enqueue theme styles.
        wp_enqueue_style('theme-critical', $this->get_assets_uri('styles/critical.css'), null, THEME_VERSION);
        wp_style_add_data('theme-critical', 'critical', 'inline');

        wp_enqueue_style('theme-app', $this->get_assets_uri('styles/main.css'), ['theme-critical'], THEME_VERSION);
        wp_style_add_data('theme-app', 'critical', 'delay');
    }

    /**
     * Filters the HTML script tag of an enqueued script.
     *
     * This function cleans up the output of `<script>` tags and
     * also adds attributes like async/defer.
     *
     * If {@link @link https://core.trac.wordpress.org/ticket/12009 #12009} lands
     * in WordPress, this function can no-op since it would be handled in core.
     *
     * @listens filter:script_loader_tag
     *
     * @param  string $html   The `<script>` tag for the enqueued script.
     * @param  string $handle The script's registered handle.
     * @return string
     */
    public function filter_script_loader_tag(string $html, string $handle): string
    {
        $html = str_replace("type='text/javascript' ", '', $html);
        $html = preg_replace_callback(
            '!document.write\(\s*\'(.+)\'\s*\)!is',
            function ($m) {
                return str_replace($m[1], addcslashes($m[1], '"'), $m[0]);
            },
            $html
        );

        $html = str_replace("'", '"', $html);

        $atts = [
            'async'       => false,
            'crossorigin' => false,
            'defer'       => false,
            'nomodule'    => false,
            'id'          => "{$handle}-js",
        ];

        foreach ($atts as $name => $value) {
            if ('id' !== $name) {
                $value = wp_scripts()->get_data($handle, $name);

                if (!$value) {
                    continue;
                }
            }

            // Prevent adding attribute when already added in #12009.
            if (!preg_match("!\s{$name}(=|>|\s)!i", $html)) {
                if (true === $value) {
                    $attr = esc_html(" {$name}");
                } else {
                    $attr = sprintf(' %s="%s"', esc_html($name), esc_attr($value));
                }

                $html = preg_replace('!(?=></script>)!i', $attr, $html, 1);
            }
        }

        return $html;
    }

    /**
     * Filters the HTML link tag of an enqueued style.
     *
     * This function cleans up the output of stylesheet `<link>` tags and
     * also inlines or delays any CSS via the "critical" style data.
     *
     * @listens filter:style_loader_tag
     *
     * @param  string $html  The link tag for the enqueued style.
     * @param  string $handle The style's registered handle.
     * @return string
     */
    public function filter_style_loader_tag(string $html, string $handle): string
    {
        preg_match_all(
            "!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!",
            $html,
            $matches
        );

        if (empty($matches[2][0])) {
            return $html;
        }

        $crit = wp_styles()->get_data($handle, 'critical');

        if ('inline' === $crit) {
            $src = str_replace(get_theme_file_uri(), '', $matches[2][0]);
            $src = strtok($src, '?');
            if (file_exists(get_theme_file_path($src))) {
                $css = file_get_contents(get_theme_file_path($src));

                if (!empty($css)) {
                    return sprintf(
                        "<style id=\"%s-inline-css\">\n%s\n</style>\n",
                        esc_attr($handle),
                        $css
                    );
                }
            }
        }

        $media  = '';
        $onload = '';

        if (!empty($matches[3][0])) {
            $_media = $matches[3][0];

            if ('delay' === $crit) {
                // Assign the real media type when the stylesheet is loaded.
                $media  = ' media="print"';
                $onload = ' onload="this.media=\'' . $_media . '\'; this.onload=null; this.isLoaded=true"';
            } elseif ($_media !== 'all') {
                // Only display media if it is meaningful.
                $media = ' media="' . $_media . '"';
            }
        }

        return '<link rel="stylesheet" id="' . esc_attr($handle) . '-css" href="' . $matches[2][0] . '"' . $media . $onload . '>' . "\n";
    }

    /**
     * Filters the global Timber context.
     *
     * @listens filter:timber/context
     *
     * @param  array $context The global context.
     * @return array
     */
    public function filter_timber_context(array $context)
    {
        $context['menu'] = Timber::get_menu();
        $context['site'] = $this;

        return $context;
    }

    /**
     * Adds extension to Twig.
     *
     * @listens filter:timber/twig
     *
     * @param  TwigEnvironment $twig The Twig Environment to which
     *     you can add additional functionality.
     * @return TwigEnvironment
     */
    public function filter_twig_environment(TwigEnvironment $twig): TwigEnvironment
    {
        $twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        $twig->addExtension(new \Twig\Extra\Html\HtmlExtension());

        $twig->addGlobal('debug', (wp_get_environment_type() === 'development'));

        $twig->addGlobal('assets_uri', $this->assets_uri);
        $twig->addGlobal('assets_path', $this->assets_path);

        $twig->addGlobal('theme_uri', $this->theme_uri);
        $twig->addGlobal('theme_path', $this->theme_path);

        return $twig;
    }

    /**
     * Adds filters to Twig.
     *
     * @listens filter:timber/twig/filters
     *
     * @param  array $filters An associative array of Twig filter definitions.
     * @return array
     */
    public function filter_twig_filters(array $filters): array
    {
        $filters['assets_path'] = [
            'callable' => [$this, 'get_assets_path'],
        ];
        $filters['assets_uri']  = [
            'callable' => [$this, 'get_assets_uri'],
        ];
        $filters['themes_path'] = [
            'callable' => [$this, 'get_themes_path'],
        ];
        $filters['themes_uri']  = [
            'callable' => [$this, 'get_themes_uri'],
        ];

        return $filters;
    }

    /**
     * Retrieves the path to the active theme's static assets.
     *
     * @fires filter:app/theme/assets_path
     *
     * @param string|null $to Optional. Path relative to the assets path.
     *     Default empty.
     * @return string Theme path with optional path appended.
     */
    public function get_assets_path($to = null): string
    {
        $assets_to = THEME_ASSETS_DIR;

        if ($to && is_string($to)) {
            $assets_to .= '/' . ltrim($to, '/');
        }

        $path = $this->get_theme_path($assets_to);

        /**
         * Filters the assets path.
         *
         * @event filter:app/theme/assets_path
         *
         * @param string      $path The absolute assets path including given path.
         * @param string|null $to   Path relative to the assets path.
         *     NULL if no path is specified.
         */
        return apply_filters('app/theme/assets_path', $path, $to);
    }

    /**
     * Retrieves the URL to the active theme's static assets.
     *
     * @fires filter:app/theme/assets_url
     *
     * @param string|null $path   Optional. Path relative to the assets URL.
     *     Default empty.
     * @param string|null $scheme Optional. Scheme to give the assets URL context.
     *     Accepts 'http', 'https', 'relative', 'rest', or null. Default null.
     * @return string Theme URL with optional path appended.
     */
    public function get_assets_uri($path = null, $scheme = null): string
    {
        $orig_scheme = $scheme;

        $assets_path = THEME_ASSETS_DIR;

        if ($path && is_string($path)) {
            $assets_path .= '/' . ltrim($path, '/');
        }

        $url = $this->get_theme_uri($assets_path, $scheme);

        /**
         * Filters the assets URL.
         *
         * @event filter:app/theme/assets_url
         *
         * @param string      $url         The complete assets URL including scheme and path.
         * @param string|null $path        Path relative to the assets URL.
         *     NULL if no path is specified.
         * @param string|null $orig_scheme Scheme to give the assets URL context.
         *     Accepts 'http', 'https', 'relative', 'rest', or NULL.
         */
        return apply_filters('app/theme/assets_url', $url, $path, $orig_scheme);
    }

    /**
     * Retrieves the path to the active theme.
     *
     * @fires filter:app/theme/theme_path
     *
     * @param string|null $to Optional. Path relative to the theme path.
     *     Default empty.
     * @return string Theme path with optional path appended.
     */
    public function get_theme_path($to = null): string
    {
        $path = $this->theme_path;

        if ($to && is_string($to)) {
            $path .= realpath('/' . ltrim($to, '/'));
        }

        /**
         * Filters the theme path.
         *
         * @event filter:app/theme/theme_path
         *
         * @param string      $path The absolute theme path including given path.
         * @param string|null $to   Path relative to the theme path.
         *     NULL if no path is specified.
         */
        return apply_filters('app/theme/theme_path', $path, $to);
    }

    /**
     * Retrieves the URL to the active theme.
     *
     * @fires filter:app/theme/theme_url
     *
     * @param string|null $path   Optional. Path relative to the theme URL.
     *     Default empty.
     * @param string|null $scheme Optional. Scheme to give the theme URL context.
     *     Accepts 'http', 'https', 'relative', 'rest', or null. Default null.
     * @return string Theme URL with optional path appended.
     */
    public function get_theme_uri($path = null, $scheme = null): string
    {
        $orig_scheme = $scheme;

        $base_url = $this->theme_uri;

        if (!in_array($scheme, ['http', 'https', 'relative'], true)) {
            if (is_ssl()) {
                $scheme = 'https';
            } else {
                $scheme = parse_url($base_url, PHP_URL_SCHEME);
            }
        }

        $url = set_url_scheme($base_url, $scheme);

        if ($path && is_string($path)) {
            $url .= '/' . ltrim($path, '/');
        }

        /**
         * Filters the theme URL.
         *
         * @event filter:app/theme/theme_url
         *
         * @param string      $url         The complete theme URL including scheme and path.
         * @param string|null $path        Path relative to the theme URL.
         *     NULL if no path is specified.
         * @param string|null $orig_scheme Scheme to give the theme URL context.
         *     Accepts 'http', 'https', 'relative', 'rest', or NULL.
         */
        return apply_filters('app/theme/theme_url', $url, $path, $orig_scheme);
    }

    /**
     * Registers action hooks.
     */
    public function register_actions(): void
    {
        add_action('after_setup_theme', [$this, 'register_theme_features']);
        add_action('init', [$this, 'cleanup_wordpress']);
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_theme_assets']);
        add_action('acf/init', [$this, 'register_acf_settings']);
    }

    /**
     * Registers ACF settings.
     *
     * @todo Registration should occur in a mu-plugin.
     */
    public function register_acf_settings()
    {
        acf_update_setting('acfe/modules/single_meta', true);
    }

    /**
     * Registers filter hooks.
     */
    public function register_filters(): void
    {
        add_filter('timber/context', [$this, 'filter_timber_context']);
        add_filter('timber/twig', [$this, 'filter_twig_environment']);
        add_filter('timber/twig/filters', [$this, 'filter_twig_filters']);

        /**
         * @todo https://locomotivemtl.teamwork.com/#/tasks/34821234
         */
        if (!is_admin()) {
            add_filter('style_loader_tag',  [$this, 'filter_style_loader_tag'],  15, 2);
            add_filter('script_loader_tag', [$this, 'filter_script_loader_tag'], 15, 2);
        }
    }

    /**
     * Registers custom post types.
     *
     * @todo Registration should occur in a mu-plugin.
     */
    public function register_post_types()
    {
    }

    /**
     * Registers custom taxonomies.
     *
     * @todo Registration should occur in a mu-plugin.
     */
    public function register_taxonomies()
    {
    }

    /**
     * Registers theme support for given features.
     *
     * @listens action:after_setup_theme
     */
    public function register_theme_features(): void
    {
        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            [
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
            ]
        );

        /*
         * Enable support for Post Formats.
         *
         * See: https://codex.wordpress.org/Post_Formats
         */
        add_theme_support(
            'post-formats',
            [
                'aside',
                'image',
                'video',
                'quote',
                'link',
                'gallery',
                'audio',
            ]
        );

        add_theme_support('menus');
    }
}

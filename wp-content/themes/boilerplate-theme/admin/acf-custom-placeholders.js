/**
 * Adds support for placeholders based on the value of other fields.
 */

(function ($, acf) {
    if (acf == null) {
        console.warn('ACF is unavailable');
        return;
    }

    /**
     * Update the placeholders with the default title.
     */
    acf.DefaultTitle = acf.Model.extend({
        $_watcher: false,
        $_watched: false,

        data: {
            defaultValue: '',
            untitledValue: ''
        },

        /**
         * Called during the constructor function to setup this model for initialization.
         *
         * @param  {jQuery.Element} $watcher   - The output element.
         * @param  {jQuery.Element} [$watched] - The input element.
         * @param  {object}         props      - Custom settings for the model.
         * @return {void}
         */
        setup: function ($watcher, $watched, props) {
            this.$el = this.$_watcher = $watcher;
            this.$_watched = $watched;

            if (typeof props === 'object') {
                $.extend(this.data, props);
            }
        },

        initialize: function () {
            var $watched = this.$watched();

            // Bail early
            if (!this.$el.length) {
                return;
            }

            if ($watched.selector) {
                $(document).on('input.theme.acf.title', $watched.selector, this.proxy(this.onInput));
                $(document).on('change.theme.acf.title', $watched.selector, this.proxy(this.onInput));
            } else {
                $watched.on('input.theme.acf.title', this.proxy(this.onInput));
                $watched.on('change.theme.acf.title', this.proxy(this.onInput));
            }

            if ($watched.length) {
                var value = this.getValue($watched);

                this.updateTitle(value);
            }
        },

        destroy: function () {
            var $watched = this.$watched();

            if ($watched.selector) {
                $(document).off('.theme.acf.title');
            } else {
                $watched.off('.theme.acf.title');
            }
        },

        $watcher: function () {
            return this.$_watcher;
        },

        $watched: function () {
            return this.$_watched;
        },

        render: function () {
            var placeholder = this.get('defaultValue');

            /** Microsoft be like: "Let's spare 0.000000000073% of our money." */
            /*
            if ( ie11 ) {
                ie11killswitch = true;
            }
            */

            /** Converts special characters without running scripts. */
            var span = document.createElement('span');
            span.innerHTML = placeholder;

            if (this.$el.is(':input')) {
                this.$el.prop('placeholder', span.textContent);
            } else {
                this.$el.text(span.textContent);
            }

            //= Promise.
            /*
            ie11 && setTimeout( function() {
                ie11killswitch = false;
            }, 0 );
            */
        },

        /**
         * Update the placeholder title on input change.
         *
         * @listens input
         * @return  {void}
         */
        onInput: function (event) {
            var value = this.getValue(this.$watched());

            console.group('DefaultTitle.onInput');
            console.log('Event:', event.type);
            console.log('Value:', value);
            console.groupEnd();

            this.updateTitle(value);
        },

        /**
         * Update the placeholder title.
         *
         * @param  {string} val
         * @return {void}
         */
        updateTitle: function (val) {
            var val = val.trim();

            if (val.length) {
                this.set('defaultValue', this.escapeString(val));
            } else {
                this.set('defaultValue', this.get('untitledValue'));
            }

            this.render();
        },

        /**
         * Returns the element's value.
         *
         * @param  {Element} elem
         * @return {mixed}
         */
        getValue: function (elem) {
            var $elem = $(elem),
                value = [];

            $elem.each(function () {
                var $el = $(this),
                    val = this.value.trim();

                if (val !== '') {
                    if ($el.is(':checkbox,:radio')) {
                        if ($el.is(':checked')) {
                            value.push(val);
                        }
                    } else if ($el.is('select')) {
                        val = $el.find(':selected').html();
                        value.push(val);
                    } else {
                        value.push(val);
                        return false;
                    }
                }
            });

            return value.join(' / ');
        },

        /**
         * Escapes input string.
         *
         * @see https://stackoverflow.com/a/4835406
         *
         * @param  {string} str
         * @return {string}
         */
        escapeString: function (str) {
            if (!str.length) {
                return '';
            }

            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return str.replace(/[&<>"']/g, function (m) {
                return map[m];
            });
        }
    });

    /**
     * Setup Spy Network
     */
    $('[data-spy="title"],[data-spy="value"]').each(function () {
        var $spy = $(this),
            model = $spy.data('theme.defaultTitle'),
            config = $spy.data();

        if (!model) {
            config = acf.parseArgs(config, {
                watch: '#edittag #name, #titlewrap #title',
                target: $spy.find('> .acf-input input:text,> .acf-input textarea'),
                fallback: ''
            });

            var $watcher, $watched, $context;

            if (config.target instanceof jQuery) {
                $watcher = config.target;
            } else {
                $watcher = $(config.target);
            }

            if (config.watch instanceof jQuery) {
                $watched = config.watch;
            } else {
                if (config.context) {
                    $context =
                        config.context === '%row%' ? $spy.closest('.acf-row') : $(config.context);

                    if (!$context.length) {
                        $context = undefined;
                    }
                }

                $watched = $();
                $watched.selector = config.watch;

                config.watch.split(',').forEach(function (watch) {
                    $watched.push.apply($watched, $(watch, $context).get());
                });
            }

            model = new acf.DefaultTitle($watcher, $watched, {
                untitledValue: config.fallback
            });

            $spy.data('theme.defaultTitle', model);
        }
    });
})(jQuery, acf);

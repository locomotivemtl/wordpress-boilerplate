/**
 * WordPress Dashboard Customizations
 */

/**
 * ACF Customizations
 */
jQuery(document).ready(function ($) {
    if (acf == null) {
        console.warn('ACF is unavailable');
        return;
    }

    /**
     * Filters the quick tag settings before the WYSIWYG instance is created.
     *
     * This hook swpas the toolbar buttons based on the toolbar identifier.
     *
     * @param   {Object}    qt_init   - Arguments given to the quick tag
     *     function.
     * @param   {String}    editor_id - Quick tag object ID.
     * @param   {acf.Field} field     - ACF field instance.
     *
     * @return  {Object} - Arguments given to the quick tag function.
     */
    acf.addFilter('wysiwyg_quicktags_settings', function (qt_init, editor_id, field) {
        const toolbar = field.get('toolbar');

        switch (toolbar) {
            case 'decorative_title': {
                qt_init.buttons = 'em,close';
                break;
            }

            case 'accordion_item': {
                qt_init.buttons = 'strong,em,ul,ol,link,close';
                break;
            }

            case 'simple': {
                qt_init.buttons = 'strong,em,link,close';
                break;
            }

            case 'flexible_text': {
                qt_init.buttons = 'strong,em,ul,ol,link,close';
                break;
            }
        }

        return qt_init;
    });
});

(function ($) {
    /**
     * Filters the TinyMCE settings before the WYSIWYG instance is created.
     *
     * This hook allows customization of the TinyMCE editor parameters
     * based on specific needs of the WYSIWYG field.
     *
     * @param   {Object}    mceInit   - TinyMCE initialization parameters.
     * @param   {String}    id        - Unique identifier for the editor.
     * @param   {acf.Field} field     - ACF field instance.
     *
     * @return  {Object} - Modified TinyMCE initialization parameters.
     */
    acf.addFilter('wysiwyg_tinymce_settings', function (mceInit, id, field) {
        if (field.data.toolbar === 'decorative_title') {
            mceInit.body_class += ' acf-field-decorative-title';
        }
        return mceInit;
    });
})(jQuery);

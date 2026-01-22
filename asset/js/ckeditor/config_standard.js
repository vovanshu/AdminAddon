/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    config.preset = 'standard';

    // The toolbar groups arrangement, optimized for two toolbar rows.

    config.toolbarGroups = [
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'tools' },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'indent', 'align' ] },
        { name: 'styles' },
    ];


    // Remove some buttons provided by the standard plugins, which are
    config.removeButtons = 'Image,Source';

    // Set the most common block elements.

    // Simplify the dialog windows.
    // config.removeDialogTabs = 'image;link:advanced';

    config.stylesSet = 'default:../../../../application/asset/js/custom-ckeditor-styles.js';
    // Disable content filtering
    config.allowedContent = true;
    // Add extra plugins
    config.extraPlugins = [
        'removeformat',
        'justify',
        'indentblock',
    ];

    // Add some css to support attributes to "section", "li" and "sup" for footnotes.
    // config.extraAllowedContent = 'section(footnotes);header;li[id,data-footnote-id];a[href,id,rel];cite;sup[data-footnote-id]';

    // Allow other scripts to modify configuration.
    $(document).trigger('o:ckeditor-config', config);
};

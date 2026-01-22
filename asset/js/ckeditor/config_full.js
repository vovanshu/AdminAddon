/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    config.preset = 'full';

    config.toolbarGroups = [
        { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
        { name: 'editing',     groups: [ 'find', 'selection' ] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'tools' },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'bidi', 'align' ] },
        { name: 'styles' },
        { name: 'colors' },
    ];

    config.stylesSet = 'default:../../../../application/asset/js/custom-ckeditor-styles.js';
    // Disable content filtering
    config.allowedContent = true;
    // Add extra plugins
    config.extraPlugins = [
        'bidi',
        'font',
        'removeformat',
        'justify',
        'find',
        'indentblock',
        'colorbutton',
    ];

    // Add some css to support attributes to "section", "li" and "sup" for footnotes.
    config.extraAllowedContent = 'section(footnotes);header;li[id,data-footnote-id];a[href,id,rel];cite;sup[data-footnote-id]';

    // Allow other scripts to modify configuration.
    $(document).trigger('o:ckeditor-config', config);
};

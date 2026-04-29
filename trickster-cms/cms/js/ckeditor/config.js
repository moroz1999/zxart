/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
CKEDITOR.plugins.addExternal('mediaembed', '/../../../vendor/moroz1999/ckeditor-mediaembed/mediaembed/');
CKEDITOR.plugins.addExternal('texttransform', '/../../../vendor/moroz1999/ckeditor-texttransform-plugin/');
CKEDITOR.plugins.addExternal('html5video', '/../../../vendor/moroz1999/ckeditor-html5-video/html5video/');
CKEDITOR.editorConfig = function(config) {
    // Define changes to default configuration here.
    // For the complete reference:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    config.extraPlugins = 'justify,templates,iframe,mediaembed,undo,font,removeformat,image2,texttransform,html5video';
    config.removePlugins = 'image,forms';
    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        {name: 'document', groups: ['mode', 'doctools', 'undo', 'cleanup']},
        {name: 'tools'},
        {name: 'clipboard', groups: ['clipboard']},
        {name: 'links'},
        {name: 'insert', groups: ['mediaembed', 'html5video']},
        '/',
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'texttransform']},
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'styles'},
        {name: 'colors'},
    ];
    // Remove some buttons, provided by the standard plugins, which we don't
    // need to have in the Standard(s) toolbar.
    config.removeButtons = 'Subscript,Superscript,Format';

    // Se the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Make dialogs simpler.
    config.removeDialogTabs = 'link:advanced';

    config.enterMode = CKEDITOR.ENTER_P;
    config.shiftEnterMode = CKEDITOR.ENTER_BR;
    config.stylesSet = 'my_styles:/../../../project/js/ckeditor/project.styles.js';
    config.fontSize_sizes = '0.7/0.7em;0.8/0.8em;0.9/0.9em;1/1em;1.1/1.1em;1.2/1.2em;1.3/1.3em;1.4/1.4em;1.5/1.5em;1.6/1.6em;1.7/1.7em;1.8/1.8em;1.9/1.9em;2/2em;2.5/2.5em;3/3em';
    config.htmlEncodeOutput = false;
    config.entities = false;
    config.ignoreEmptyParagraph = true;
    config.contentsCss = '/css/set:ckeditor/15072013.css';
    config.bodyClass = 'html_content';
    config.templates_files = [
        '/vendor/artweb-ou/trickster-cms/cms/js/ckeditor/templates.js',
        '/project/js/ckeditor/templates.js'
    ];
    config.templates_replaceContent = false;
    config.allowedContent = true;
    config.resize_dir = 'both';
    config.toolbarCanCollapse = true;
    config.toolbarStartupExpanded = true;
};

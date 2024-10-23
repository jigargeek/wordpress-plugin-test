jQuery(document).ready(function ($) {

    jQuery('.anuiwp_content').each(function(index,item) {
        var $message = jQuery(item).val();
        $message = $message.replace(/\n/g, '<br>');
        jQuery(item).val($message);
        var id = jQuery(item).attr("id");
        wp.editor.initialize( id, {
            mediaButtons: true,
            tinymce: {
                theme: 'modern',
                skin: 'lightgray',
                language: 'en',
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                entities: '38, amp, 60, lt, 62, gt',
                entity_encoding: 'raw',
                keep_styles: false,
                paste_webkit_styles: 'font-weight font-style color',
                preview_styles: 'font-family font-size font-weight font-style text-decoration text-transform',
                tabfocus_elements: ':prev,:next',
                plugins: 'charmap, hr, media, paste, tabfocus, textcolor, fullscreen, wordpress, wpeditimage, wpgallery, wplink, wpdialogs, wpview',
                resize: 'vertical',
                menubar: false,
                indent: false,
                toolbar1: 'bold, italic, strikethrough, bullist, numlist, blockquote, hr, link, unlink, wp_more, spellchecker, wp_adv',
                toolbar2: 'formatselect, underline, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help',
                body_class: 'id post-type-post-status-publish post-format-standard',
                wpeditimage_disable_captions: false,
                wpeditimage_html5_captions: true,
                forced_root_block: ''
            },
            quicktags: true
        });
    });
});
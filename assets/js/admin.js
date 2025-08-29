jQuery(document).ready(function($) {
    'use strict';

    var frame;

    $('#free-watermarks-upload-button').on('click', function(e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select Watermark Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('input[name="free_watermarks_settings[watermarkImageId]"]').val(attachment.id);
            $('.free-watermarks-image-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; max-height: 200px;"/>');
            $('#free-watermarks-remove-button').show();
        });

        frame.open();
    });

    $('#free-watermarks-remove-button').on('click', function(e) {
        e.preventDefault();
        $('input[name="free_watermarks_settings[watermarkImageId]"]').val(0);
        $('.free-watermarks-image-preview').html('');
        $(this).hide();
    });
});

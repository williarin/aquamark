jQuery(document).ready(function($) {
    'use strict';

    var frame;

    $('#aquamark-upload-button').on('click', function(e) {
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
            $('input[name="aquamark_settings[watermarkImageId]"]').val(attachment.id);
            $('.aquamark-image-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; max-height: 200px;"/>');
            $('#aquamark-remove-button').show();
        });

        frame.open();
    });

    $('#aquamark-remove-button').on('click', function(e) {
        e.preventDefault();
        $('input[name="aquamark_settings[watermarkImageId]"]').val(0);
        $('.aquamark-image-preview').html('');
        $(this).hide();
    });
});

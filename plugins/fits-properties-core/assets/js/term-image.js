(function ($) {
    'use strict';

    $(function () {
        var frame;
        var $idField = $('#fpc_term_image_id');
        var $preview = $('#fpc-term-image-preview');
        var $selectBtn = $('#fpc-term-image-select');
        var $removeBtn = $('#fpc-term-image-remove');

        if (!$idField.length) {
            return;
        }

        $selectBtn.on('click', function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Select Header Image',
                button: { text: 'Use this image' },
                multiple: false,
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                var url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;

                $idField.val(attachment.id);
                $preview.html('<img src="' + url + '" style="max-width:200px;height:auto;display:block;border-radius:4px;">');
                $removeBtn.show();
            });

            frame.open();
        });

        $removeBtn.on('click', function (e) {
            e.preventDefault();
            $idField.val('');
            $preview.empty();
            $removeBtn.hide();
        });
    });
})(jQuery);

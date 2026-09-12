(function ($) {
    'use strict';

    $(function () {
        initListingTypeToggle();
        initGallery();
        initStarPicker();
    });

    function initListingTypeToggle() {
        var $radios = $('input[name="fpc_listing_type"]');

        if (!$radios.length) {
            return;
        }

        function apply() {
            var value = $radios.filter(':checked').val();
            $('.fpc-when-sale').toggle(value === 'sale');
            $('.fpc-when-rent').toggle(value === 'rent');
        }

        $radios.on('change', apply);
        apply();
    }

    function initGallery() {
        var frame;
        var $ids = $('#fpc_gallery_ids');
        var $preview = $('#fpc-gallery-preview');

        if (!$ids.length) {
            return;
        }

        function currentIds() {
            return $ids.val() ? $ids.val().split(',').filter(Boolean) : [];
        }

        function renderPreview(ids) {
            $preview.empty();

            ids.forEach(function (id) {
                var attachment = wp.media.attachment(id);
                attachment.fetch().then(function () {
                    var sizes = attachment.get('sizes');
                    var url = (sizes && sizes.thumbnail) ? sizes.thumbnail.url : attachment.get('url');
                    var $wrap = $('<span class="fpc-gallery-thumb-wrap"></span>').attr('data-id', id);
                    $wrap.append($('<img>').attr('src', url).css({ width: 80, height: 80, objectFit: 'cover' }));
                    $wrap.append($('<span class="fpc-remove" title="Remove">&times;</span>'));
                    $preview.append($wrap);
                });
            });
        }

        $preview.on('click', '.fpc-remove', function () {
            var id = $(this).closest('.fpc-gallery-thumb-wrap').attr('data-id');
            var ids = currentIds().filter(function (existingId) {
                return existingId !== id;
            });
            $ids.val(ids.join(','));
            renderPreview(ids);
        });

        $('#fpc-gallery-add').on('click', function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Select Property Photos',
                button: { text: 'Use these photos' },
                multiple: true,
            });

            frame.on('select', function () {
                var selection = frame.state().get('selection');
                var existing = currentIds();

                selection.each(function (attachment) {
                    var id = String(attachment.id);
                    if (existing.indexOf(id) === -1) {
                        existing.push(id);
                    }
                });

                $ids.val(existing.join(','));
                renderPreview(existing);
            });

            frame.open();
        });

        $('#fpc-gallery-clear').on('click', function (e) {
            e.preventDefault();
            $ids.val('');
            $preview.empty();
        });
    }

    function initStarPicker() {
        var $picker = $('#fpc-star-picker');

        if (!$picker.length) {
            return;
        }

        var $labels = $picker.find('label');

        function fillUpTo(index) {
            $labels.each(function (i) {
                $(this).toggleClass('is-filled', i <= index);
            });
        }

        $labels.on('mouseenter', function () {
            fillUpTo($labels.index(this));
        });

        $picker.on('mouseleave', function () {
            var checkedIndex = $labels.index($labels.filter(':has(input:checked)'));
            fillUpTo(checkedIndex);
        });

        $labels.on('click', function () {
            fillUpTo($labels.index(this));
        });
    }
})(jQuery);

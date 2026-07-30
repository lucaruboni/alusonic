(function ($) {
    'use strict';

    /* ── Single Image Upload ── */
    $(document).on('click', '.torresan-img-upload', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.torresan-img-field');
        var frame = wp.media({
            title: 'Scegli immagine',
            button: { text: 'Usa questa immagine' },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            wrap.find('.torresan-img-id').val(attachment.id);
            var src = attachment.sizes && attachment.sizes.medium
                ? attachment.sizes.medium.url
                : attachment.url;
            wrap.find('.torresan-img-preview').attr('src', src).show();
            wrap.find('.torresan-img-remove').show();
        });

        frame.open();
    });

    $(document).on('click', '.torresan-img-remove', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.torresan-img-field');
        wrap.find('.torresan-img-id').val('0');
        wrap.find('.torresan-img-preview').attr('src', '').hide();
        $(this).hide();
    });

    /* ── Gallery Upload ── */
    $(document).on('click', '.torresan-gallery-upload', function (e) {
        e.preventDefault();
        var wrap = $(this).closest('.torresan-gallery-field');
        var idsInput = wrap.find('.torresan-gallery-ids');
        var existing = idsInput.val() ? idsInput.val().split(',').map(Number) : [];

        var frame = wp.media({
            title: 'Gestisci Gallery',
            button: { text: 'Aggiorna gallery' },
            multiple: true,
            library: { type: 'image' }
        });

        frame.on('open', function () {
            var selection = frame.state().get('selection');
            existing.forEach(function (id) {
                if (id) {
                    var attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment);
                }
            });
        });

        frame.on('select', function () {
            var ids = [];
            var thumbs = '';
            frame.state().get('selection').each(function (att) {
                var json = att.toJSON();
                ids.push(json.id);
                var src = json.sizes && json.sizes.thumbnail
                    ? json.sizes.thumbnail.url
                    : json.url;
                thumbs += '<img src="' + src + '" style="width:80px;height:80px;object-fit:cover" data-id="' + json.id + '">';
            });
            idsInput.val(ids.join(','));
            wrap.find('.torresan-gallery-preview').html(thumbs);
        });

        frame.open();
    });
})(jQuery);

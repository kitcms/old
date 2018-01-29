(function($) {
    /* Resizable side panel */
    $('.main > .aside').resizable({
        handles: 'e',
        maxWidth: $('body').width(),
        start: function(event, ui) {
            next = ui.element.next(),
            width = next.width();
        },
        resize: function(event, ui) {
            right = ui.element.next();
            width = parseInt($(this).parent().width(), 10) - parseInt(ui.size.width, 10);
            right.width(width);
        },
        stop: function(event, ui) {
            width = $(document).width(),
            left = ui.size.width * 100 / width,
            right = ui.element.next().width() * 100 / width;
            $.cookie('resizable', [left, right]);
        }
    });

    $('textarea[data-resizable=true]').resizable({
        handles: "s",
        minHeight: 94,
        create: function(event, ui) {
            var target = event.target,
                textarea = $(target).find('textarea'),
                name = $('textarea').attr('name');
            if (height = $.cookie(name)) {
                height = height.split(',')
                $(target).height(height[0]);
                $(textarea).height(height[1]);
            } else {
                $(target).height(94);
                $(textarea).height(66);
            }
        },
        stop: function(event, ui) {
            var target = event.target,
                textarea = $(target).find('textarea'),
                name = $('textarea').attr('name');
            $.cookie(name, [$(target).height(), $(textarea).height()]);
        }
    });

    $('[data-toggle="tooltip"]').tooltip();
})(jQuery);

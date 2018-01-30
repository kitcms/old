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

    /* Select2 */
    $.fn.select2.defaults.set("width", "100%");
    $.fn.select2.tags = {
        tags: true,
        tokenSeparators: [',', ' '],
        language: {
            noResults: function (params, el) {
                return null;
            }
        }
    }

    $('[role="tags"]').select2($.fn.select2.tags);

    $('[role="simple"').select2();

    $('[role="join"]').select2({
        templateResult: function(state) {
            if (!state.id || (state.id.length && state.id < 1)) return state.text;
            return $('<small class="grey">' + state.id + '.</small> ' + state.text + ' <span></span>');
        },
        templateSelection: function(state) {
            if (state.id.length && state.id < 1) return state.text;
            return $('<small class="grey">' + state.id + '.</small> ' + state.text + ' <span></span>');
        }
    });

    $('[role="template"],[role="section"]').select2({
        templateResult: function(state) {
            if (!state.id || (state.id.length && state.id < 1)) return state.text;
            ids = state.id.split('_');
            if (ids.length > 1) state.id = ids[1];
            if (!state.id.length) {
                if ($(state.element).data('id')) {
                    return $(state.text + ' [ <small class="grey">' + $(state.element).data('id') + '.</small> ' + $(state.element).data('title') + ' <small class="grey">' + $(state.element).data('keyword') + '</small> ]');
                }
                return state.text;
            }
            var padding = 20, keyword = $(state.element).data('keyword'), padding = $(state.element).data('level') * padding - padding;
            if (!keyword) { keyword = state.id; }
            return $('<small class="grey" style="padding-left:' + padding + 'px">' + state.id + '.</small> ' + state.text + ' <small class="grey">' + keyword + '</small>');
        },
        templateSelection: function(state) {
            if (state.id.length && state.id < 1) return state.text;
            if (!state.id.length) {
                if ($(state.element).data('id')) {
                    return $(state.text + ' [ <small class="grey">' + $(state.element).data('id') + '.</small> ' + $(state.element).data('title') + ' <small class="grey">' + $(state.element).data('keyword') + '</small> ]');
                }
                return state.text;
            }
            var keyword = $(state.element).data('keyword');
            if (!keyword) { keyword = state.id; }
            return $('<small class="grey">' + state.id + '.</small> ' + state.text + ' <span class="badge">' + keyword + '</span>');
        }
    });
})(jQuery);

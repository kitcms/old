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

    $(".nav-tabs a").click(function (e) {
        e.preventDefault();
        $.cookie('tabs', $(this).attr('href').substr(1));
        $(this).tab('show');
    });

    $('[data-toggle="tooltip"]').tooltip();

    /* JqTree */
    window.tree = $('#tree');
    window.tree.tree({
        dataUrl: function(node) {
            if (node) {
                id = node.id.split('_');
                return location.component + '/' + id[0] + '/tree.html?' + id[0] +'=' + id[1];
            } else {
                return $('#tree').data('url');
            }
        },
        autoOpen: false,
        closedIcon: ' ',
        openedIcon: ' ',
        dragAndDrop: true,
        saveState: true,
        slide: false,
        onCreateLi: function(node, $li) {
            id = node.id.split('_');
            if ('site' == id[0] || 'section' == id[0] || 'template' == id[0] || 'user' == id[0]) {
                $li.find('.jqtree-title')
                    .before('<small class="glyphicon ' + node.icon + ' ' + node.color + '"></small><small class="grey">'+ id[1] +'. </small>')
                    .after(' <small class="grey">' + node.keyword + '</small>');
            }
            if ('model' == id[0] || 'field' == id[0]) {
                $li.find('.jqtree-title')
                    .before('<small class="glyphicon ' + node.icon + ' ' + node.color + '"></small>');
            }
        },
        onCanMoveTo: function(moved, target, position) {
            move = moved.id.split('_');
            targe = target.id.split('_');
            // Разделы сайта
            if ('site' == move[0] && 'section' == targe[0]) return false;
            else if ('site' == move[0] && 'site' == targe[0] && 'inside' == position) return false;
            else if ('section' == move[0] && 'site' == targe[0] && ('before' == position || 'after' == position)) return false;
            else return true;
        },
        onCanSelectNode: function(node) {
            if (node.link) {
                $(location).attr('href', node.link);
            }
        }
    }).bind('tree.move', function(event) {
        event.preventDefault();
        moved = event.move_info.moved_node.id;
        target = event.move_info.target_node.id;
        position = event.move_info.position;
        path = moved.split('_')[0];
        url = location.component + '/' + path + '/move.html?moved=' + moved + '&target=' + target + '&position=' + position;
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json'
        }).then(function (result) {
            data = $.parseJSON(result);
            if (false != data) event.move_info.do_move();
        });
    });

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

    if (typeof ace != "undefined") {
        $('pre').each(function() {
            var editor = ace.edit(this), name = $(this).attr('data-name');
            editor.setTheme("ace/theme/chrome");
            editor.session.setMode("ace/mode/smarty");
            editor.session.setUseWorker(true);
            editor.setShowInvisibles(true);
            editor.setAutoScrollEditorIntoView(true);
            editor.on("change", function(e) {
                $('input[name="' + name + '"]').val(editor.getValue());
            });
        });
    }

    /* Sortable */
    $('tbody.sortable').sortable({
        axis: "y",
        cursor: "move",
        opacity: 0.7,
        delay: 150,
        helper: function(e, tr) {
            if ($(tr).attr('id')) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function(index) {
                    $(this).width($originals.eq(index).outerWidth()).css('background-color', ' #f5f5f5');
                });
                return $helper;
            }
        },
        update: function(event, ui) {
            target = $(this).data('target');
            item = $(this).data('item');
            button = $('.group-apply');
            classes = 'btn group-apply btn-primary';
            window.priority = new Array();
            $(this).find('tr').each(function() {
                window.priority.push($(this).attr('id'));
            });
            href = location.component + '/' + target + '/priority.html?' + item + '=' + window.priority.join(',');
            $(button).html('Сохранить изменения').removeClass().addClass(classes).attr('href', href);
        }
    });

    /* Selectable */
    $(document).on('click', '.selectable > *', function() {
        button = $('.pull-right .group-apply')
            .removeClass()
            .addClass('btn btn-default group-apply select-all')
            .html('Выбрать все')
            .attr('href', null);
        if ($(this).attr('id')) {
            window.item = $(this).parent().data('item');
            $(this).toggleClass('active');
        }
        selected = $('.selectable').find('.active');
        if (selected.length) {
            $('.group-action').removeClass('disabled');
            window.select = new Array();
            $(selected).each(function() {
                window.select.push($(this).attr('id'));
            });
        } else {
            $('.group-action').addClass('disabled');
        }
    });
    $(document).on('click', '.pull-right .select-all', function() {
        $('.selectable > *').each(function() {
            if ($(this).attr('id')) {
                $(this).click();
            }
        });
    });
    $(document).on('click', '.pull-right .group-select > li > a', function() {
        button = $('.pull-right .group-apply');
        html = $(this).html();
        classes = 'btn group-apply btn-' + $(this).attr('class');
        href = $(this).attr('href') + window.item + '=' + window.select;
        $(button).html(html).removeClass().addClass(classes).attr('href', href);
        $('.dropup').removeClass('open');
        return false;
    });
})(jQuery);

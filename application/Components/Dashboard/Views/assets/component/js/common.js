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
                return node.demand_url;
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
        onCanMove: function(node) {
            id = node.id.split('_');
            if ('group' == id[0] || 'user' == id[0] || 'model' == id[0]) return false;
            else return true;
        },
        onCanMoveTo: function(moved, target, position) {
            move = moved.id.split('_');
            targe = target.id.split('_');
            // Разделы сайта
            if ('site' == move[0] && 'section' == targe[0]) return false;
            else if ('site' == move[0] && 'site' == targe[0] && 'inside' == position) return false;
            else if ('section' == move[0] && 'site' == targe[0] && ('before' == position || 'after' == position)) return false;
            // Модели данных
            else if ('field' == move[0] && 'field'== targe[0] && 'inside' == position) return false;
            else if ('field' == move[0] && 'group'== targe[0] && 'after' == position) return false;
            else if ('field' == move[0] && 'model'== targe[0] && 'after' == position) return false;
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
        if ('field' === path) {
            moved = moved + '&model=' + event.move_info.moved_node.model;
        }
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
        tokenSeparators: [','],
        language: {
            noResults: function (params, el) {
                return 'не задано';
            },
        },
        templateSelection: function(state, container) {
            if ($(state.element).attr('locked')){
                $(container).addClass('locked-tag');
                state.locked = true;
            }
            return state.text;
        }
    }

    $('[role="tags"]').select2($.fn.select2.tags).on('select2:unselecting', function(e) {
        $(e.target).data('unselecting', true);
        if ($(e.params.args.data.element).attr('locked')) {
            return false;
        }
    });

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

    $("select[role=file]").select2({
        dropdownCssClass: 'no-search',
        templateSelection: function(el) {
            element = $(el.element);
            size = element.data('size');
            url = location.root + element.data('url');
            if ('number' == typeof size) {
                kb = size / 1024;
                mb = kb / 1024;
                if (mb >= 1) size = Math.round((mb)*100)/100 + ' мб';
                else if (kb >= 1) size = Math.round((kb)*100)/100 + ' кб';
                else size = size + ' б';
            }
            node = $('<div class="info"></div>').append(
                $('<a>' + el.text + '</a>')
                    .attr('href', url/* + '?' + Date.now()*/)
                    .attr('onclick', "window.open(this.href, \'_blank\');return false;")
            ).append(' <div><small class="grey">' + size + '</small></div>');
            if ('image' == element.data('type').split('/')[0]) {
                node = $('<span></span>').append(
                    $('<div class="preview"></div>')
                        .css('background-image', 'url("' + url + /*'?' + Date.now() +*/ '")')
                ).append(node);
            }
            return node;
        }
    }).on("select2:closing", function (e) {
        // ...
    }).on("select2:unselect", function (e) {
        el = e.params.data.element,
        id = e.params.data.id;
        data = $.parseJSON(id);
        if ('temporary' === $(el).data('status')) {
            action = $(this).parent().find('input[type=file]').data('url') + '&file=' + data.url;
            $.ajax({
                url: action,
                type: 'DELETE',
                dataType: 'json'
            });
        }
    }).next().bind('keyup', function (e) {
        if (e.which == 13) {
            var node = $(this).prev();
            var field = $(node).attr('name').replace('[]', '');
            var action = $(this).next().data('url');
            var files = $(this).find('.select2-search__field').val().split(' ');
            var total = files.length;
            var count = 0;
            $(this).find('.select2-search__field').val('');
            $(this).next().after('<div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>');
            $.each(files, function (index, file) {
                data = new Object();
                data.field = field;
                data.files = file.split('?')[0];
                $.ajax({
                    url: action,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                        selectedFiles(data.web, node);
                    },
                    complete: function () {
                        count = count + 1;
                        progress = parseInt(count * 100 / total, 10);
                        $(node).parent().find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(progress + '%');
                        if (progress == 100) $(node).parent().find('.progress').remove();
                    }
                });
            });
            //$(this).prev().trigger("select2:closing").trigger('select2:update');
        }
    });

    $.fn.extend({
        select2_sortable: function(){
            var select = $(this);
            var ul = $(select).next('.select2-container').first('ul.select2-selection__rendered');
            ul.sortable({
                placeholder: 'ui-state-highlight',
                //containment: 'parent',
                placeholder: {
                    element: function(currentItem) {
                        return $("<li>").addClass('ui-state-highlight')
                            .height($(currentItem).outerHeight())
                            .width($(currentItem).outerWidth());
                    },
                    update: function() {
                        return;
                    }
                },
                items: 'li:not(.select2-search)',
                tolerance: 'pointer',
                stop: function() {
                    $($(ul).find('.select2-selection__choice').get().reverse()).each(function() {
                        var id = $(this).attr('title').replace(/\\/g, '\\\\').replace(/\"/g, '\\"');
                        var option = select.find('option[value="' + id + '"]')[0];
                        $(select).prepend(option);
                    });
                }
            });
        }
    });

    $('select[multiple]').each(function() {
        $(this).select2_sortable();
        length = $(this).find('option:selected').length;
        $(this).prev('.count').html(length);
        $(this).on("change", function (e) {
            length = $(this).find('option:selected').length;
            $(this).prev('.count').html(length);
            //console.log($(e.target).find('option:selected').length);
        });
    });

    $('.btn-upload').click(function() {
        var field = $(this).parent().parent().find('.form-control').attr('name').replace('[]', '');
        $(this).parent().prev().fileupload({
            dataType: 'json',
            formData: { 'field': field },
            maxChunkSize: 2000000,
            add: function (e, data) { data.submit() },
            done: function (e, data) {
                var node = $(this).prevAll('.file:first');
                selectedFiles(data.result.files, node);
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $(e.target).next().find('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).html(progress + '%');
            },
            start: function(e, data) {
                $(e.target).after('<div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>');
            },
            stop: function(e, data) {
                $(e.target).next().remove();
            }
        }).click();
    });

    $('.btn-choice').click(function() {
        window.btnChoice = this;
        var field = $(this).parent().parent().find('.form-control').attr('name').replace('[]', '');
        var action = $(this).parent().parent().find('.file-upload').data('url');
        window.choice = function(file) {
            file.field = field;
            $.ajax({
                url: action,
                type: 'POST',
                dataType: 'json',
                data: file,
                success: function(data) {
                    var node = $(window.btnChoice).parent().prevAll('.file:first');
                    selectedFiles(data.web, node);
                }
            });
        };
        var width = screen.width / 1.25;
        var height = screen.height / 1.43;
        var filemanager = window.open(location.component + '/file/choice.html', 'filemanager', 'width=' + width + ',height=' + height);
        filemanager.focus();
    });

    $('.btn-clear').click(function() {
        $(this).parent().prevAll('select').val(null).trigger('change');
        //$(this).parent().prevAll('.select2').find('.select2-selection__choice__remove').click();
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

    /* CKEDITOR */
    if (typeof CKEDITOR != "undefined") {

        CKEDITOR.config.filebrowserBrowseUrl = location.component + '/file/choice.html';
        CKEDITOR.config.extraPlugins = 'ace';

        CKEDITOR.plugins.add('ace', {
            requires: ['sourcearea'],
            init: function(editor) {
                editor.on('mode', function() {
                    if (editor.mode == 'source') {
                        var area = $('textarea', '.' + editor.id);
                        var holder = area.parent();
                        var id = editor.id;
                        area.hide();
                        area.after('<div id="aceEditor_container_' + id + '" class="aceEditor_container" style=" background-color:white;"><div id="aceEditor_' + id + '" style="width:100%; height:100%;"></div></div>');
                        $('#aceEditor_container_' + id).css(holder.position()).width(holder.width()).height(holder.height());
                        var aceEditor = ace.edit("aceEditor_" + id);
                        aceEditor.getSession().setMode("ace/mode/smarty");
                        aceEditor.setTheme("ace/theme/chrome");
                        aceEditor.getSession().setValue(editor.getData());
                        aceEditor.getSession().setUseWrapMode(false);
                        aceEditor.getSession().setUseWorker(true);
                        aceEditor.setShowInvisibles(true);
                        aceEditor.setAutoScrollEditorIntoView(true);
                        ace.config.loadModule('ace/ext/language_tools', function () {
                            aceEditor.setOptions({
                                enableBasicAutocompletion: true,
                                enableSnippets: true
                            });
                        });
                        $('#aceEditor_container_' + id).css('z-index', '9997');
                        var view = function(e) {
                            if (e.data.name == 'source') {
                                editor.setData(aceEditor.getSession().getValue(), function() { }, false);
                                aceEditor.destroy();
                                $('#aceEditor_container_' + id).remove();
                                editor.removeListener('beforeCommandExec', view);
                                editor.removeListener('resize', resize);
                                editor.removeListener('afterCommandExec', maximize);
                                editor.fire('dataReady');
                            }
                        };
                        var maximize = function(e) {
                            if (e.data.name == 'maximize') {
                                if (e.data.command.state == 1) {
                                    $('#aceEditor_container_' + id).css('z-index', '9997');
                                } else {
                                    $('#aceEditor_container_' + id).css('z-index', 'auto');
                                }
                            }
                        };
                        var resize = function() {
                            $('#aceEditor_container_' + id).css(holder.position()).width(holder.width()).height(holder.height());
                            aceEditor.resize(true);
                        };
                        var update = function () {
                            editor.setData(aceEditor.getSession().getValue(), function () {
                                aceEditor.blur();
                                aceEditor.focus();
                            }, false);
                            return false;
                        };
                        aceEditor.getSession().on('change', update);
                        editor.on('beforeCommandExec', view);
                        editor.on('resize', resize);
                        editor.on('afterCommandExec', maximize);
                        editor.aceEditor = aceEditor;
                    }
                });
            }
        });
        CKEDITOR.on('instanceReady', function(ev) {
            var name = ev.editor.name;
            if (height = $.cookie(name)) {
                height = height.split(',')
                ev.editor.resize(false, height[0]);
            }
            ev.editor.on('resize',function(ev) {
                container = ev.editor.container.find('.cke_contents');
                height = $(container.$).height() + 50;
                $.cookie(name, [height]);
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

function selectedFiles(data, node) {
    $.each(data, function (index, object) {
        if (!object.error && object.size !== 0) {
            type = object.type.split('/');
            file = {
                name: object.name,
                type: object.type,
                size: object.size,
                width: object.width,
                height: object.height,
                url: location.root + object.url
            };
            if ('image' === type[0]) file.color = object.color;
            var option = $('<option selected>' + file.name + '</option>')
                .data('type', file.type)
                .data('size', file.size)
                .data('width', file.width)
                .data('height', file.height)
                .data('url', file.url)
                .data('status', 'temporary')
                .val(JSON.stringify(file));
            if ('image' === type[0]) option.data('color', file.color)
            $(node).append(option).trigger('change');
        }
    });
}

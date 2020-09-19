(function ($) {
    $(document).ready(function () {
        $(document).on('click', '.demo-template-action a.import-demo-btn', function (e) {
            var _this             = $(this);
            var modalSelector     = '#demo-importer-modal-section';
            var demo_id           = _this.data('demo-id');
            var json_url          = _this.data('demo-url');
            var demoTitle         = _this.data('demo-title');
            var demoType          = _this.data('demo-type');
            var plugins           = _this.parents('.demo-importer-template-item').find('.plugin-content-item').html();
            var sendReportBtnHtml = '<span class="dashicons dashicons-warning"></span> Report Problem';

            $(modalSelector).find('.bdt-template-report-button').html(sendReportBtnHtml);
            $(modalSelector).find('.demo-importer-form').removeClass('bdt-hidden');
            $(modalSelector).find('.demo-importer-callback').addClass('bdt-hidden');
            if (demoType=='free'){
                $(modalSelector).find('.bdt-free-template-import').removeClass('bdt-hidden');
                $(modalSelector).find('.bdt-pro-template-import').addClass('bdt-hidden');
            }else{
                $(modalSelector).find('.bdt-free-template-import').addClass('bdt-hidden');
                $(modalSelector).find('.bdt-pro-template-import').removeClass('bdt-hidden');
            }
            $(modalSelector).find('.demo-importer-loading').addClass('bdt-hidden');


            $(modalSelector).find('.demo-importer-callback .edit-page').html('');
            $(modalSelector).find('.demo-importer-callback .callback-message').html('');
            $(modalSelector).find('.required-plugin-list').html('');
            $(modalSelector).find('.required-plugin-list').html(plugins);


            $(modalSelector).find('.demo_id').val(demo_id);
            $(modalSelector).find('.demo_json_url').val(json_url);
            $(modalSelector).find('.default_page_title').val(demoTitle);
            $(modalSelector).find('.page_title').val('');
            bdtUIkit.modal(modalSelector).show();

        });

        $(document).on('click', '#demo-importer-modal-section .import-into-library, #demo-importer-modal-section .import-into-page', function (e) {
            e.preventDefault();
            var modalSelector    = $('#demo-importer-modal-section');
            var demo_id          = modalSelector.find('.demo_id').val();
            var json_url         = modalSelector.find('.demo_json_url').val();
            var admin_url        = modalSelector.find('.admin_url').val();
            var import_type      = '';
            var page_title       = modalSelector.find('.page_title').val();
            var defaultPageTitle = modalSelector.find('.default_page_title').val();

            var template_import = modalSelector.find('input[name=template_import]:checked').val();

            if ( template_import == 'library' ) {
                import_type = 'library';
            } else {
                import_type = 'page';
            }

            $.ajax({
                url       : ajaxurl,
                data      : {
                    'action'            : 'ep_elementor_demo_importer_data_import',
                    'demo_url'          : json_url,
                    'demo_id'           : demo_id,
                    'demo_import_type'  : import_type,
                    'page_title'        : page_title,
                    'default_page_title': defaultPageTitle
                },
                dataType  : 'JSON',
                beforeSend: function () {
                    $(modalSelector).find('.demo-importer-form').addClass('bdt-hidden');
                    $(modalSelector).find('.demo-importer-callback').removeClass('bdt-hidden');
                    $(modalSelector).find('.demo-importer-loading').removeClass('bdt-hidden');
                },
                success   : function (data) {
                    if ( data.success ) {
                        $(modalSelector).find('.demo-importer-callback .callback-message').html('Successfully <strong>' + defaultPageTitle + '</strong> has been imported.');
                        var page_url = admin_url + '/post.php?post=' + data.id + '&action=elementor';
                        $(modalSelector).find('.demo-importer-callback .edit-page').html('<a href="' + page_url + '" class="bdt-button bdt-button-secondary" target="_blank">' + data.edittxt + '</a>');
                    } else {
                        $(modalSelector).find('.demo-importer-callback .callback-message').text(data.edittxt);
                    }
                },
                complete  : function (data) {
                    $(modalSelector).find('.demo-importer-loading').addClass('bdt-hidden');
                },
                error     : function (errorThrown) {
                    $(modalSelector).find('.demo-importer-loading').addClass('bdt-hidden');
                }
            });

        });

        var $is_load_more_click    = false;
        var $is_load_more_tab_name = '';
        var $is_load_more_paged    = '';
        var $current_filter_tab    = ''

        $(document).on('click', '.bdt-demo-template-library-group .load_more_btn', function (e) {
            var _this      = $(this);
            var paged      = _this.data('paged');
            var totalPaged = _this.data('total');
            var TabName    = _this.data('tab');

            if ( $is_load_more_tab_name == TabName && $is_load_more_paged == paged && $is_load_more_click ) {
                //console.log('TabName: ' + $is_load_more_tab_name + 'Paged: ' + $is_load_more_paged + 'clicked: ' + $is_load_more_click);
                return false;
            }

            $.ajax({
                url       : ajaxurl,
                data      : {
                    'action'  : 'ep_elementor_demo_importer_data_loading_read_more',
                    'tab_name': TabName,
                    'paged'   : paged
                },
                dataType  : 'JSON',
                beforeSend: function () {
                    $is_load_more_click    = true;
                    $is_load_more_tab_name = TabName;
                    $is_load_more_paged    = paged;
                },
                complete  : function () {
                    $is_load_more_click = false;
                },
                success   : function (response) {
                    if ( response.success ) {
                        _this.before(response.data);
                        if ($current_filter_tab){
                            $('.pro-free-nagivation-item[data-filter="'+$current_filter_tab + '"]').trigger('click')
                        }
                        _this.data('paged', response.paged);
                        $is_load_more_paged = response.paged;
                        if ( totalPaged === response.paged ) {
                            _this.addClass('bdt-hidden');
                        }
                    } else {
                        $is_load_more_click = false;
                        //console.log(response);
                    }
                },
                error     : function (errorThrown) {
                    $is_load_more_click = false;
                    console.log(errorThrown);
                }
            });

        });

        $(document).on('click', 'li.template-category-item', function (e) {
            var _this    = $(this);
            var TermSlug = _this.data('demo');

            if ( !TermSlug ) {
                return false;
            }

            _this.closest('.bdt-template-library-sidebar ul').find('.template-category-item').removeClass('bdt-active');
            _this.addClass('bdt-active');

            var selector        = '#' + TermSlug + '_demo_template';
            var currentSelector = _this.closest('.bdt-template-library').find('.bdt-demo-template-library-group' + selector);

            _this.closest('.bdt-template-library').find('.bdt-demo-template-library-group').addClass('bdt-hidden');
            currentSelector.removeClass('bdt-hidden');

            if ( TermSlug === 'demo_term_all' || TermSlug === 'demo_search_result' || _this.hasClass('loaded-data') ) {
                return false;
            }

            $.ajax({
                url       : ajaxurl,
                data      : {
                    'action'   : 'ep_elementor_demo_importer_data_loading',
                    'term_slug': TermSlug
                },
                dataType  : 'JSON',
                beforeSend: function () {
                },
                success   : function (response) {
                    if ( response.success ) {
                        currentSelector.html(response.data);
                        if ($current_filter_tab){
                            $('.pro-free-nagivation-item[data-filter="'+$current_filter_tab + '"]').trigger('click')
                        }
                        _this.addClass('loaded-data');
                    } else {
                        $(currentSelector).find('p').text(response.data);
                    }
                },
                error     : function (errorThrown) {
                    console.log(errorThrown);
                }
            });

        });

        var $search_list_ajax_called = false;

        $(document).on('keyup', '.bdt-template-library .search-demo-template-value', function (e) {
            e.preventDefault();
            var _this           = $(this);
            var searchVal       = _this.val();
            var contentSelector = _this.closest('.bdt-template-library').find('.bdt-demo-template-library-group#demo_search_result_demo_template');

            if ( !_this.hasClass('loaded-data') && $search_list_ajax_called === false ) {
                $.ajax({
                    url       : ajaxurl,
                    data      : {
                        'action': 'ep_elementor_demo_importer_data_searching',
                        's'     : searchVal
                    },
                    dataType  : 'JSON',
                    beforeSend: function () {
                        $search_list_ajax_called = true;
                    },
                    success   : function (response) {
                        contentSelector.html(response.data);
                        _this.addClass('loaded-data');
                        setTimeout(function () {
                                bdtUIkit.grid(contentSelector, { masonry: true });
                            },
                            1000);
                        searchItem(_this, contentSelector);

                    },
                    error     : function (errorThrown) {
                        console.log(errorThrown);
                        $search_list_ajax_called = false;
                    }
                });
            }

            if ( _this.hasClass('loaded-data') ) {
                if ( this.timer ) {
                    window.clearTimeout(this.timer);
                }
                this.timer = window.setTimeout(function () {
                    searchItem(_this, contentSelector, searchVal);
                }, 1000);
            }
        });

        function searchItem(_this, contentSelector, searchVal) {
            if ( searchVal ) {
                $('.bdt-template-library').find('#demo_search_tab').trigger('click');
                var searchVal = searchVal.toLowerCase();
                var selector  = contentSelector.find('.demo-importer-template-item');

                var isfound = false;
                selector.each(function () {
                    var title = $(this).data('title');
                    if ( title ) {
                        title = title.toLowerCase();
                        if ( title.indexOf(searchVal) > -1 ) {
                            $(this).css('display', 'block');
                            $(this).find('.search_result').text('match');
                            isfound = true;
                        } else {
                            $(this).css('display', 'none');
                            $(this).find('.search_result').text('dont match');

                        }
                    }
                });

                if ( !isfound ) {
                    contentSelector.find('.result-not-found').removeClass('bdt-hidden');
                } else {
                    contentSelector.find('.result-not-found').addClass('bdt-hidden');
                }
                bdtUIkit.grid(contentSelector, { masonry: true });
            }
        }


        $(document).on('click', '#sync_demo_template_btn', function (e) {
            e.preventDefault();
            $(this).find('span').addClass('loading');
            $.ajax({
                url       : ajaxurl,
                data      : {
                    'action': 'ep_elementor_demo_importer_data_sync_demo_with_server'
                },
                dataType  : 'JSON',
                beforeSend: function () {},
                success   : function (response) {
                    window.location.href = window.location.href;
                },
                error     : function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        });
        $(document).on('click', '.pro-free-nagivation-item', function (e) {
            e.preventDefault();
            var _this  = $(this);
            var filter = _this.data('filter');

            if ( filter === 'free' ) {
                $current_filter_tab ='free';
                filter = 0;
            } else if ( filter === 'pro' ) {
                $current_filter_tab ='pro';
                filter = 1;
            } else {
                $current_filter_tab='';
                filter = '*';
            }

            var contentSelector = _this.closest('.bdt-template-library').find('.bdt-demo-template-library-group').not('.bdt-hidden');
            var selector        = contentSelector.find('.demo-importer-template-item');

            var isfound = false;
            if ( filter == '*' ) {
                selector.css('display', 'block');
                isfound = true;
            } else {
                selector.each(function () {
                    isfound    = true;
                    var is_pro = $(this).data('pro');
                    if ( is_pro === filter ) {
                        $(this).css('display', 'block');
                    } else {
                        $(this).css('display', 'none');
                    }
                });
            }

            if ( !isfound ) {
                contentSelector.find('.result-not-found').removeClass('bdt-hidden');
            } else {
                contentSelector.find('.result-not-found').addClass('bdt-hidden');
            }

            bdtUIkit.grid(contentSelector, { masonry: true });
        });

        $.fn.sortElements = (function () {

            var sort = [].sort;

            return function (comparator, getSortable) {

                getSortable = getSortable || function () {
                    return this;
                };

                var placements = this.map(function () {

                    var sortElement = getSortable.call(this),
                        parentNode  = sortElement.parentNode,

                        // Since the element itself will change position, we have
                        // to have some way of storing its original position in
                        // the DOM. The easiest way is to have a 'flag' node:
                        nextSibling = parentNode.insertBefore(
                            document.createTextNode(''),
                            sortElement.nextSibling
                        );

                    return function () {

                        if ( parentNode === this ) {
                            throw new Error(
                                'You can\'t sort elements if any one is a descendant of another.'
                            );
                        }

                        // Insert before flag:
                        parentNode.insertBefore(this, nextSibling);
                        // Remove flag:
                        parentNode.removeChild(nextSibling);

                    };

                });

                return sort.call(this, comparator).each(function (i) {
                    placements[i].call(getSortable.call(this));
                });

            };

        })();

        $(document).on('change', '.bdt-template-library-sort select', function (e) {
            e.preventDefault();
            var _this  = $(this);
            var sortBy = _this.val();
            if ( !sortBy ) {
                return false;
            }

            var orderBy = sortBy.split('|');
            if ( orderBy.length !== 2 ) {
                return false;
            }

            var sortField = orderBy[0];
            var sortType  = orderBy[1];

            var contentSelector = _this.closest('.bdt-template-library').find('.bdt-demo-template-library-group').not('.bdt-hidden');
            var selector        = contentSelector.find('.demo-importer-template-item');

            selector.sortElements(function (a, b) {
                if ( sortField === 'title' ) {
                    var st = $(a).data('title');
                    var sn = $(b).data('title');
                } else {
                    var st = $(a).data('demo');
                    var sn = $(b).data('demo');
                }

                if ( sortType === 'asc' ) {
                    return st > sn ? 1 : -1;
                } else {
                    return st < sn ? 1 : -1;
                }
            });
        });

        $(document).on('click', '#demo-importer-modal-section .bdt-template-report-button', function (e) {
            e.preventDefault();
            var modalSelector = '#demo-importer-modal-section';
            var demo_id       = $(modalSelector).find('.demo_id').val();
            var demo_json_url = $(modalSelector).find('.demo_json_url').val();
            var _this         = $(this);

            $(_this).find('span').removeClass('dashicons-warning');
            $(_this).find('span').addClass('dashicons-update loading');


            $.ajax({
                url       : ajaxurl,
                type      : 'post',
                data      : {
                    'action'       : 'ep_elementor_demo_importer_send_report',
                    'demo_id'      : demo_id,
                    'demo_json_url': demo_json_url
                },
                dataType  : 'JSON',
                beforeSend: function () {
                },
                success   : function (response) {
                    //console.log(response.success);
                    if ( response.success ) {
                        _this.html('Report has been sent!');
                    } else {
                        _this.html('Fail to sent report!');
                        window.location.href = window.location.href;
                    }

                },
                error     : function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        });

        $(window).scroll(function () {
            var totalGridHeight = $(document).height() - $(window).height();
            if ( $(window).scrollTop() >= (totalGridHeight - 600) ) {
                var clickedLi = $('div.bdt-template-library .bdt-list-divider').find('li.bdt-active');
                if ( clickedLi.length == 1 ) {
                    var selectorId = clickedLi.data('demo');
                    if ( selectorId ) {
                        selectorId = '#' + selectorId + '_demo_template';
                        $(selectorId).find('a.load_more_btn').trigger('click');
                    }
                }
            }
        });
    });
})(jQuery);

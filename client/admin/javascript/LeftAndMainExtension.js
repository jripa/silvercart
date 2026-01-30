(function($) {

        $(document).on('click', '.cms-menu__list > li > a .toggle-children', function (e) {
            const $toggle = $(this);
            const $li = $toggle.closest('li');
            const $submenu = $li.children('ul.cms-menu__list');

            if (!$submenu.length) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            if (window.bootstrap && bootstrap.Collapse) {
                // Close other open menus to keep only one expanded.
                $('.cms-menu__list > li > ul.cms-menu__list.show')
                    .not($submenu)
                    .each(function () {
                        bootstrap.Collapse.getOrCreateInstance(this, { toggle: false }).hide();
                    });

                bootstrap.Collapse.getOrCreateInstance($submenu[0], { toggle: false }).toggle();
            } else {
                $li.toggleClass('opened');
                $toggle.attr('aria-expanded', $li.hasClass('opened') ? 'true' : 'false');
                if ($li.hasClass('opened')) {
                    $submenu.slideDown(200);
                } else {
                    $submenu.slideUp(200);
                }
            }
        });

        // Sync caret direction with bootstrap collapse state.
        $(document).on('shown.bs.collapse hidden.bs.collapse', '.cms-menu__list > li > ul.cms-menu__list', function (e) {
            const $submenu = $(e.target);
            const $li = $submenu.closest('li');
            const isOpen = $submenu.hasClass('show');
            $li.toggleClass('opened', isOpen);
            $li.find('> a .toggle-children').attr('aria-expanded', isOpen ? 'true' : 'false');
        });
        
        $('li[aria-controls="Root_PrintPreviewTab"]').on('click', function() {
            $('iframe.print-preview').height($('.cms-content-fields').height() - 54);
        });

        $('.hover-image-preview').on('hover', function(e) {
            var imageURL = $(this).data('img-src');
            if (e.type === 'mouseenter') {
                if ($('#hover-image-preview-box').length === 0) {
                    $('body').append('<div id="hover-image-preview-box"><img/></div>');
                    $('#hover-image-preview-box').hide();
                    $('#hover-image-preview-box').css({
                        maxWidth : '1000px',
                        maxheight : '500px',
                        position: 'absolute',
                        zIndex: '100'
                    });
                    $('#hover-image-preview-box img').css({
                        width: 'auto',
                        height: 'auto',
                        maxWidth: '100%',
                        maxHeight: '100%',
                        boxShadow: '0px 0px 10px #555555',
                        padding: '20px',
                        backgroundColor: '#ffffff'
                    });
                }
                $('#hover-image-preview-box').css({
                    top: e.pageY - 10,
                    left: e.pageX + 30,
                    bottom: 'auto'
                });
                if (e.pageY > window.innerHeight / 2) {
                    $('#hover-image-preview-box').css({
                        bottom: window.innerHeight - e.pageY - 10,
                        left: e.pageX + 30,
                        top: 'auto'
                    });
                }
                $('#hover-image-preview-box img').attr('src', imageURL);
                $('#hover-image-preview-box').show();
            } else if (e.type === 'mouseleave') {
                $('#hover-image-preview-box').hide();
            }
        });
        $('.hover-image-preview').on('click', function(e) {
            $('#hover-image-preview-box').hide();
        });

    $(document).on('click', '.silvercart-permanent-notification .btn-close', function() {
        $(this).closest('.silvercart-permanent-notification').fadeOut();
    });
    $(document).on('click', '#Form_ItemEditForm_action_doSave', function(e) {
        var doSubmit = true,
            form     = $(this).closest('#Form_ItemEditForm');
        if ($('#Form_ItemEditForm_PaymentMethod', form).length > 0
         && $('#Form_ItemEditForm_ClassName', form).length > 0
         && $('#Form_ItemEditForm_PaymentChannel', form).length > 0
        ) {
            if ($('#Form_ItemEditForm_PaymentMethod', form).val() === '') {
                alert(ss.i18n._t('SilverCart.PleaseChoosePaymentMethod', 'Please choose a payment method!'));
                e.preventDefault();
                doSubmit = false;
            } else {
                var className, paymentChannel, list,
                    paymentMethod = $('#Form_ItemEditForm_PaymentMethod', form).val();
                if (paymentMethod.indexOf('--') === -1) {
                    className      = paymentMethod;
                    paymentChannel = '';
                } else {
                    list           = paymentMethod.split('--');
                    className      = list[0];
                    paymentChannel = list[1];
                }
                $('#Form_ItemEditForm_ClassName').val(className);
                $('#Form_ItemEditForm_PaymentChannel').val(paymentChannel);
            }
        }
        return doSubmit;
    });
    $.entwine('ss', function($) {

        /**
         * Loads /admin/publishsitetree, which will publish all pages of the 
         * current locale.
         */
        $('.LeftAndMain :input[name=action_publishsitetree]').entwine({
            onclick: function(e) {
                this.parents('form').trigger('submit', [this]);
                e.preventDefault();
                return false;
            }
        });
        $('.LeftAndMain :input[name=action_add_example_data]').entwine({
            onclick: function(e) {
                this.parents('form').trigger('submit', [this]);
                e.preventDefault();
                return false;
            }
        });
        $('.LeftAndMain :input[name=action_add_example_config]').entwine({
            onclick: function(e) {
                this.parents('form').trigger('submit', [this]);
                e.preventDefault();
                return false;
            }
        });
        $('.LeftAndMain :input[name=action_do_newsletter_recipients_export]').entwine({
            onclick: function(e) {
                var suffix = '/',
                    postTargetURL = document.location.pathname + '/do_newsletter_recipients_export',
                    exportContext = $('select[name="ExportContext"]').val();
                if (document.location.pathname.substr(-suffix.length) === suffix) {
                    postTargetURL = document.location.pathname + 'do_newsletter_recipients_export';
                }
                window.open(postTargetURL + '?ExportContext=' + exportContext);
            }
        });

        
    });
}(jQuery));

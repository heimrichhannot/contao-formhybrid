(function ($) {

    FormhybridPlugins = {
        init: function (action) {
            //this.scrollToMessages(action);
        },
        scrollToMessages: function (action) {
            // do not scroll if ajax request
            if (action == 'toggleSubpalette') return false;

            // sroll to first alert message or first error field, inside formhybrid modules
            var alert = $('.formhybrid:first').not('.noscroll').parent(['class^="mod_"']).find(':input.alert:first, :input.error:first, .alert-success:first, .alter-danger:first, p.alert:first, fieldset.error:first');

            if (alert.length > 0) {
                var alertOffset = alert.offset();

                $('html,body').animate({
                    scrollTop: parseInt(alertOffset.top)
                }, 500);
            }
        }
    }

    FormhybridAjaxRequest = {
        registerEvents: function () {
            this.asyncSubmit();
        },
        asyncSubmit: function (form)
        {
            if(form !== 'undefined') {
                var $form = $(form);
                if($form.length > 0) {
                    FormhybridAjaxRequest._asyncFormSubmit($form);
                }
                return false;
            }

            $('body').on('submit', '.formhybrid form[data-async]', function (e) {
                var $form = $(this);
                e.preventDefault();
                FormhybridAjaxRequest._asyncFormSubmit($form);
            });
        },
        _asyncFormSubmit: function ($form, data) {
            $formData = $form.serializeArray();

            $formData.push({
                    name: 'action',
                    value: 'asyncFormSubmit'
                },
                {
                    name: 'load',
                    value: true
                });

            if (typeof data != "undefined") {
                $formData.push(data);
            }

            $.ajax({
                url: $form.attr('action'),
                data: $formData,
                method: $form.attr('method'),
                success: function (data) {
                    var replace;

					try {
						dataJson = $.parseJSON(data);

						if (dataJson.type == 'redirect')
						{
							location.href = dataJson.url;
							return;
						}
					} catch(e) {
						// fail silently
					}

					data = '<div>' + data + '</div>';

                    if ($form.data('replace')) {
                        replace = $(data).find($form.data('replace'));
                        if (replace !== undefined) {
                            $($form.data('replace')).html(replace);
                        }

                    } else {

                        // html page returned
                        replace = $(data).find('#' + $form.attr('id'));
                        if (replace.length < 1) {
                            $form.html(data); // module handle ajax request, replace inner html
                            replace = data;
                        } else {
                            $form.replaceWith(replace);
                        }
                    }

                    // scroll to first alert message or first error field
                    var alert = replace.find('.alert:first, .error:first');

                    if (alert.length > 0 && !$('.formhybrid:first').hasClass('noscroll')) {
                        var alertOffset = alert.offset();

                        $('html,body').animate({
                            scrollTop: parseInt(alertOffset.top) - 70 + 'px'
                        }, 500);
                    }
                }
            });
        },
        toggleSubpalette: function (el, id, field) {
            el.blur();
            var $el = $(el),
                $item = $('#' + id),
                $form = $el.closest('form'),
                checked = true,
                data = {
                    action: 'toggleSubpalette',
                    id: id,
                    field: field,
                    REQUEST_TOKEN: Formhybrid.request_token,
                    FORM_SUBMIT: $form.find('input[name=FORM_SUBMIT]').val()
                };

            if ($el.is(':checkbox') || $el.is(':radio')) {
                checked = $el.is(':checked');
            }

            if (checked === false) {
                data[field] = 0;

                $.ajax({
                    type: 'post',
                    url: location.href,
                    data: data,
                    success: function () {
                        $item.remove();
                    }
                });

                return;
            }

            data[field] = $el.val();
            data['load'] = 1;

            $.ajax({
                type: 'post',
                url: location.href,
                dataType: 'html',
                data: data,
                success: function (data, textStatus, jqXHR) {
                    $item.remove();
                    // bootstrapped forms
                    if ($el.closest('form').find('.' + field).length > 0) {
                        // always try to attach subpalette after wrapper element from parent widget
                        $el.closest('form').find('.' + field).eq(0).after(data);
                    } else {
                        $el.closest('#ctrl_' + field).after(data);
                    }
                }
            });
        },
        reload: function (id) {
            var $form = $('#' + id);

            this._asyncFormSubmit($form, {
                name: 'skipValidation',
                value: true
            });
        },
    };

    var FormHybridHelper = {
        getParameterByName: function (sParam, href) {
            var sPageURL = decodeURIComponent(href),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        }
    };


    $(document).ready(function () {
        FormhybridAjaxRequest.registerEvents();
        FormhybridPlugins.init();
    })

    $(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {
        FormhybridPlugins.init(FormHybridHelper.getParameterByName('action', ajaxOptions.data));
    })

})(jQuery);
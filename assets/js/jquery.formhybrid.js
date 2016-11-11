(function ($) {

    FormhybridPlugins = {
        init: function (action) {
            this.scrollToMessages();
        },
        scrollToMessages: function ($container) {
            if(typeof $container === 'undefined'){
                $container = $('.formhybrid:first');
            }

            // scroll to first alert message or first error field
            var alert = $container.find('.alert:first, .error:first');

            if (alert.length > 0 && !$container.hasClass('noscroll')) {
                var alertOffset = alert.offset();

                $('html,body').animate({
                    scrollTop: (parseInt(alertOffset.top) - 100) + 'px'
                }, 500);
            }
        }
    };

    FormhybridAjaxRequest = {
        registerEvents: function () {
            this.asyncSubmit();
        },
        asyncSubmit: function (form) {
            if (typeof form !== 'undefined') {
                var $form = $(form);
                if ($form.length > 0) {
                    FormhybridAjaxRequest._asyncFormSubmit($form);
                }
                return false;
            }

            $(document).on('submit', '.formhybrid form[data-async]', function (e) {
                var $form = $(this);
                e.preventDefault();
                FormhybridAjaxRequest._asyncFormSubmit($form);
            });
        },
        _asyncFormSubmit: function ($form, url, data) {
            var $formData = $form.serializeArray();

            $formData.push({
                name: 'FORM_SUBMIT',
                value: $form.attr('id')
            });

            if (typeof data != "undefined") {
                $formData.push(data);
            }

            function closeModal(response, $form){

                if (typeof response.result.data == 'undefined') {
                    return;
                }

                if(!response.result.data.closeModal){
                    return;
                }

                $form.closest('.modal').modal('hide');
            }

            $.ajax({
                url: url ? url : $form.attr('action'),
                dataType: 'json',
                data: $formData,
                method: $form.attr('method'),
                error: function(jqXHR, textStatus, errorThrown){
                    console.log(errorThrown);
                    if (jqXHR.status == 300) {
                        location.href = jqXHR.responseJSON.result.data.url;
                        closeModal(jqXHR.responseJSON, $form);
                        return;
                    }
                },
                success: function (response, textStatus, jqXHR) {
                    if (typeof response == 'undefined') {
                        return;
                    }

                    if (response.result.html && response.result.data.id) {

                        var container = '<div>' + response.result.html + '</div>',
                            $replace = $(container).find('#' + response.result.data.id);

                        if (typeof $replace !== 'undefined') {
                            $form.replaceWith($replace);
                        }

                        FormhybridPlugins.scrollToMessages($replace);

                        closeModal(response, $form);
                    }
                }
            });
        },
        toggleSubpalette: function (el, id, field, url) {
            el.blur();
            var $el = $(el),
                $item = $('#' + id),
                $form = $el.closest('form'),
                checked = true;

            var $formData = $form.serializeArray();

            $formData.push(
                {name: 'FORM_SUBMIT', value: $form.attr('id')},
                {name: 'subId', value: id},
                {name: 'subField',value: field});

            if ($el.is(':checkbox') || $el.is(':radio')) {
                checked = $el.is(':checked');
            }

            if (checked === false) {

                $.ajax({
                    type: 'post',
                    url: url,
                    dataType: 'json',
                    data: $formData,
                    success: function (response) {
                        $item.remove();
                    }
                });

                return;
            }

            $formData.push(
                {name: 'subLoad', value: 1}
            );

            $.ajax({
                type: 'post',
                url: url,
                dataType: 'json',
                data: $formData,
                success: function (response, textStatus, jqXHR) {
                    $item.remove();
                    // bootstrapped forms
                    if ($el.closest('form').find('.' + field).length > 0) {
                        // always try to attach subpalette after wrapper element from parent widget
                        $el.closest('form').find('.' + field).eq(0).after(response.result.html);
                    } else {
                        $el.closest('#ctrl_' + field).after(response.result.html);
                    }
                }
            });
        },
        reload: function (id, url) {
            var $form = $('#' + id);
            this._asyncFormSubmit($form, url);
        }
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
    });

    $(document).ajaxComplete(function (event, jqXHR, ajaxOptions) {
        if(typeof ajaxOptions == 'undefined') return false;
        FormhybridPlugins.init(FormHybridHelper.getParameterByName('action', ajaxOptions.data));
    });

})(jQuery);
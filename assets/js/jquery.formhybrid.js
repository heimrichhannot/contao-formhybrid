(function ($) {

	FormhybridPlugins = {
		init : function(action){
			//this.scrollToMessages(action);
		},
		scrollToMessages : function(action){
			// do not scroll if ajax request
			if(action == 'toggleSubpalette') return false;

			// sroll to first alert message or first error field, inside formhybrid modules
			var alert = $('.formhybrid:first').not('.noscroll').parent(['class^="mod_"']).find(':input.alert:first, :input.error:first, .alert-success:first, .alter-danger:first, p.alert:first, fieldset.error:first');

			if(alert.length > 0){
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
		asyncSubmit: function () {
			$('.formhybrid').on('submit', 'form[data-async]', function (e) {
				var $form = $(this),
					$formData = $form.serializeArray();

				e.preventDefault();

				$formData.push(
					{
						name: 'action',
						value: 'asyncFormSubmit'
					},
					{
						name: 'load',
						value: true
					}
				);

				$.ajax({
					url: $form.attr('action'),
					data: $formData,
					method: $(this).attr('method'),
					success: function (data) {

						var replace,
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

						// sroll to first alert message or first error field
						var alert = replace.find('.alert:first, .error:first');

						if (alert.length > 0) {
							var alertOffset = alert.offset();

							$('html,body').animate({
								scrollTop: parseInt(alertOffset.top) - 70 + 'px'
							}, 500);
						}
					}
				});
			});
		},
		toggleSubpalette: function (el, id, field) {
			el.blur();
			var $el = $(el),
				$item = $('#' + id),
				$form = $(el).closest('form');

			if ($item.length > 0) {
				$.ajax({
					type: 'post',
					url: location.href,
					data: {
						'action': 'toggleSubpalette',
						'id': id,
						'field': field,
						'state': 0,
						'REQUEST_TOKEN': Formhybrid.request_token,
						'FORM_SUBMIT': $form.find('input[name=FORM_SUBMIT]')
					},
					success: function () {
						$item.remove();
					}
				});

				return;
			}

			$.ajax({
				type: 'post',
				url: location.href,
				dataType: 'html',
				data: {
					'action': 'toggleSubpalette',
					'id': id,
					'field': field,
					'load': 1,
					'state': 1,
					'REQUEST_TOKEN': Formhybrid.request_token,
					'FORM_SUBMIT': $form.find('input[name=FORM_SUBMIT]')
				},
				success: function (data, textStatus, jqXHR) {

					// bootstrapped forms
					if($el.closest('form').find('.' + field).length > 0)
					{
						// always try to attach subpalette after wrapper element from parent widget
						$el.closest('form').find('.' + field).eq(0).after(data);
					} else{
						$el.closest('#ctrl_' + field).after(data);
					}
				}
			});
		}
	};

	var FormHybridHelper = {
		getParameterByName : function(sParam, href) {
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

	$(document).ajaxComplete(function(event, jqXHR, ajaxOptions){
		FormhybridPlugins.init(FormHybridHelper.getParameterByName('action', ajaxOptions.data));
	})

})(jQuery);
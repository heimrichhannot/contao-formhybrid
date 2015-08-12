(function ($) {

	FormhybridPlugins = {
		init : function(){
			this.initTinyMce();
			this.scrollToMessages();
		},
		initTinyMce : function ()
		{
			if(!window.tinymce) return false;

			var lang = $('html').attr('lang');

			tinymce.remove();

			tinymce.init({
				skin: "contao",
				selector: ".formhybrid .tinyMCE",
				theme : 'modern',
				language : lang,
				plugins: ["link paste lists"],
				toolbar: "undo redo | bold italic | bullist numlist outdent indent | link unlink | table",
				menubar : false,
				statusbar : false,
				width: '100%',
				height: 250,
				autoresize_min_height: 200,
				autoresize_max_height: 400,
				//content_css : '/system/modules/pintao/assets/css/bootstrap.min.css',
				paste_as_text: true,
				setup : function(ed) {
					var $textarea = $(ed.getElement());

					ed.settings.toolbar = $textarea.data('toolbar') ? $textarea.data('toolbar') : ed.settings.toolbar;
					ed.settings.content_css =  $textarea.data('content-css') ? $textarea.data('content-css') : ed.settings.content_css;
				}
			});
		},
		scrollToMessages : function(){
			// sroll to first alert message or first error field, inside formhybrid modules
			var alert = $('.formhybrid:first').parent(['class^="mod_"']).find(':input.alert:first, :input.error:first, .alert-success:first, .alter-danger:first, p.alert:first');

			if(alert.length > 0){
				var alertOffset = alert.offset();
				console.log(alert);

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
						'REQUEST_TOKEN': Formhybrid.request_token
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
					'REQUEST_TOKEN': Formhybrid.request_token
				},
				success: function (data, textStatus, jqXHR) {
					$el.closest('.' + field).after(data);
				}
			});
		}
	};


	$(document).ready(function () {
		FormhybridAjaxRequest.registerEvents();
		FormhybridPlugins.init();
	})

	$(document).ajaxComplete(function(){
		FormhybridPlugins.init();
	})

})(jQuery);
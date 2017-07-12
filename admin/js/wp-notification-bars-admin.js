/**
 * Plugin Name: WP Notification Bars by MyThemeShop
 * Author URI: https://mythemeshop.com/
 */
(function( $ ) {

	'use strict';

	$(function() {

		// Tabs
		$(document).on('click', '.mtsnb-tabs-nav a', function(e){
			e.preventDefault();
			var $this = $(this),
				target = $this.attr('href');
			if ( !$this.hasClass('active') ) {
				$this.parent().parent().find('a.active').removeClass('active');
				$this.addClass('active');
				$this.parent().parent().next().children().siblings().removeClass('active');
				$this.parent().parent().next().find( target ).addClass('active');
				$this.parent().parent().prev().val( target.replace('#tab-',''));
			}
		});

		// Display conditions manager
		$(document).on('click', '.condition-checkbox', function(e){
			var $this = $(this),
				panel = '#'+$this.attr('id')+'-panel',
				disable = $this.data('disable');
			if ( !$this.hasClass('disabled') ) {
				if ( $this.hasClass('active') ) {
					$this.removeClass('active');
					$this.find('input').val('');
					$(panel).removeClass('active');
					if ( disable ) {
						$('#condition-'+disable).removeClass('disabled');
					}
				} else {
					$this.addClass('active');
					$(panel).addClass('active');
					$this.find('input').val('active');
					if ( disable ) {
						$('#condition-'+disable).addClass('disabled');
					}
				}
			}
		});

		// Preview Bar Button
		$(document).on('click', '#preview-bar', function(e){
			e.preventDefault();
			$('.mtsnb').remove();
			$('body').css({ "padding-top": "0", "padding-bottom": "0" }).removeClass('has-mtsnb');
			var form_data = $('form#post').serialize();
			var data = {
                action: 'preview_bar',
                form_data: form_data,
            };

            $.post( ajaxurl, data, function(response) {

                if ( response ) {
                    $('body').prepend( response );
                }
            }).done(function(result){
            	$( document ).trigger('mtsnbPreviewLoaded');
            });

		});

		// Preview Bar
		$( document ).on( 'mtsnbPreviewLoaded', function( event ) {

			var barHeight;

			if ( $('.mtsnb').length > 0 ) {
				barHeight = $('.mtsnb').outerHeight();
				$('body').css('padding-top', barHeight).addClass('has-mtsnb');
			}

			$(document).on('click', '.mtsnb-hide', function(e) {

				e.preventDefault();

				var $this = $(this);

				if ( !$this.hasClass('active') ) {
					$this.closest('.mtsnb').removeClass('mtsnb-shown').addClass('mtsnb-hidden');
					$('body').css('padding-top', 0);
				}
			});

			// Show Button
			$(document).on('click', '.mtsnb-show', function(e) {

				e.preventDefault();

				var $this = $(this);
				if ( !$this.hasClass('active') ) {
					barHeight = $('.mtsnb').outerHeight();
					$this.closest('.mtsnb').removeClass('mtsnb-hidden').addClass('mtsnb-shown');
					$('body').css('padding-top', barHeight);
				}
			});
		});

		// Color option
		$('.mtsnb-color-picker').wpColorPicker();

		// Bar select
		function mtsnbProcessPostSelectDataForSelect2( ajaxData, page, query ) {

			var items=[];

			for (var thisId in ajaxData) {
				var newItem = {
					'id': ajaxData[thisId]['id'],
					'text': ajaxData[thisId]['title']
				};
				items.push(newItem);
			}
			return { results: items };
		}

		$('input.mtsnb-bar-select').each(function() {

			var $this = $(this);

			$this.select2( {
				placeholder: mtsnb_locale.select_placeholder,
				multiple: true,
				maximumSelectionSize: 1,
				minimumInputLength: 2,
				ajax: {
					url: ajaxurl,
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term,
							action: 'mtsnb_get_bars',
						};
					},
					results: mtsnbProcessPostSelectDataForSelect2
				},
				initSelection: function(element, callback) {

					var ids=$(element).val();
					if ( ids !== "" ) {
						$.ajax({
							url: ajaxurl,
							dataType: "json",
							data: {
								action: 'mtsnb_get_bar_titles',
								post_ids: ids
							},
							
						}).done(function(response) {console.log(response);
							
							var processedData = mtsnbProcessPostSelectDataForSelect2( response );
							callback( processedData.results );
						});
					}
				},
			});
		});
	});

})( jQuery );

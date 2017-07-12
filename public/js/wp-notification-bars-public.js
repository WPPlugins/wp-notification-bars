/**
 * Plugin Name: WP Notification Bars by MyThemeShop
 * Author URI: https://mythemeshop.com/
 */
(function( $ ) {

	'use strict';

	$(function() {

		var barHeight;

		// Show notification bar
		if ( $('.mtsnb').length > 0 ) {
			barHeight = $('.mtsnb').outerHeight();
			$('body').css('padding-top', barHeight).addClass('has-mtsnb');
		}

		// Hide Button
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

})( jQuery );
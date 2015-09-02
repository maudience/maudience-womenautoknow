(function($) {

	$.fn.menumaker = function(options) {
      
		var cssmenu = $(this), settings = $.extend({
			title: "<i class=\"fa fa-bars\"></i>",
			format: "dropdown",
			sticky: false
		}, options);

		return this.each(function() {

			cssmenu.prepend('<div id="menu-button">' + settings.title + '</div>');

			$(this).find("#menu-button").on('click', function(){

				$(this).toggleClass('menu-opened');
				var mainmenu = $(this).next('ul');

				if (mainmenu.hasClass('open')) { 
					mainmenu.hide().removeClass('open');
				}
				else {
					mainmenu.show().addClass('open');
					if (settings.format === "dropdown") {
						mainmenu.find('ul').show();
					}
				}

			});

			cssmenu.find('li ul').parent().addClass('page_item_has_children');

			multiTg = function() {
				cssmenu.find(".page_item_has_children").prepend('<span class="submenu-button"></span>');
				cssmenu.find('.submenu-button').on('click', function() {
					$(this).toggleClass('submenu-opened');
					if ($(this).siblings('ul').hasClass('open')) {
						$(this).siblings('ul').removeClass('open').hide();
					}
					else {
						$(this).siblings('ul').addClass('open').show();
					}
				});
			};

			if (settings.format === 'multitoggle') multiTg();
			else cssmenu.addClass('dropdown');

			if (settings.sticky === true) cssmenu.css('position', 'fixed');

			resizeFix = function() {

				if ( $(window).width() > 768 ) {
					cssmenu.find('ul').show();
				}

				if ( $(window).width() <= 768 ) {
					cssmenu.find('ul').hide().removeClass('open');
				}

			};

			resizeFix();

			return $(window).on('resize', resizeFix);

		});
	};

	$(document).ready(function(){

		$("#website-header nav").menumaker({
			title: "<i class=\"fa fa-bars\"></i>",
			format: "multitoggle"
		});

		$("#website-header nav").prepend("<div id='menu-line'></div>");

		var foundActive = false, activeElement, linePosition = 0, menuLine = $("#website-header nav #menu-line"), lineWidth, defaultPosition, defaultWidth;

		$("#website-header nav > ul > li").each(function() {
			if ($(this).hasClass('active')) {
				activeElement = $(this);
				foundActive = true;
			}
		});

		if (foundActive === false) {
			activeElement = $("#website-header nav > ul > li").first();
		}

		defaultWidth = lineWidth = activeElement.width();

		defaultPosition = linePosition = activeElement.position().left;

		menuLine.css("width", lineWidth);
		menuLine.css("left", linePosition);

		$("#website-header nav > ul > li").hover(function() {
			activeElement = $(this);
			lineWidth = activeElement.width();
			linePosition = activeElement.position().left;
			menuLine.css("width", lineWidth);
			menuLine.css("left", linePosition);
		},
		function() {
			menuLine.css("left", defaultPosition);
			menuLine.css("width", defaultWidth);
		});

	});

})(jQuery);
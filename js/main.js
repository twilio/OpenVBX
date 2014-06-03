var currentHref = window.location.pathname.replace(/\/$/, '');

$(function() {
	// iPhone carousel
	if ($('#iphone-case').size() > 0) {
		var iphone_slider = $('#iphone-screen').bxSlider({
			controls: false,
			mode: 'fade',
			pager: false,
			onAfterSlide: function(currentSlideNum, numSlides, currentSlide) {
				$('.iphone-screen-thumb').eq(currentSlideNum).addClass('pager-active');
			}
		});
		
		$('.iphone-screen-thumb').on('click', function(e) {
			e.preventDefault();
			iphone_slider.goToSlide($('.iphone-screen-thumb').index(this));
			$('.iphone-screen-thumb').removeClass('pager-active');
		});
	}
	
	// front page carousel
	if ($('#homepage-screenshot').size() > 0) {
		var front_page_slider = $('#homepage-screenshot').bxSlider({
			controls: false,
			mode: 'fade',
			pager: false,
			onAfterSlide: function(currentSlideNum, numSlides, currentSlide) {
				$('.thumb-screen').eq(currentSlideNum).addClass('pager-active');
			}
		});
		
		$('.thumb-screen').on('click', function(e) {
			e.preventDefault();
			front_page_slider.goToSlide($('.thumb-screen').index(this));
			$('.thumb-screen').removeClass('pager-active');
		});
	}
});

$(function() {
	var populateTagData = function(callback) {
		$.get('https://api.github.com/repos/twilio/openvbx/tags', callback);
	}
	
	if ($('#download-links').length) {
		populateTagData(function(response) {
			var latest = response[0];
			$('.zipball-button').attr('href', latest['zipball_url']);
			$('.tarball-button').attr('href', latest['tarball_url']);
			$('.version-heading').text('OpenVBX ' + latest['name']);
		});
	}
	
	if ($('#homepage-sub-section .latest-release').length) {
		populateTagData(function(response) {
			var latest = response[0];
			$('#homepage-sub-section .latest-release a').text('OpenVBX ' + latest['name']);
		});
	}
	
	$('.footer-nav-section .sub-nav-item a[href^="' + currentHref + '"]')
		.closest('li').addClass('selected');
});

$(function() {
	if (!$('#docs-sidebar-nav').length) {
		return;
	}
	
	$('.section-home').each(function() {
		var _this = $(this),
			path = window.location.pathname,
			regex = new RegExp('^' + _this.attr('href'), 'g');
	
		if (path.match(regex)) {
			_this.next('ul').removeClass('hide');
		}
	});
	
	$('#docs-sidebar-nav a[href="' + currentHref + '"]')
		.addClass('docs-nav-active');
});

$(function() {
	if (!$('#playlist').length) {
		return;
	}
	
	$('.playlist-item a').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var _this = $(this);
		
		_this.closest('li').addClass('selected')
			.siblings('li').removeClass('selected');
			
		player.loadVideoById(_this.attr('href').replace('#', ''));
	});
});

$(function() {
	if (!$('#plugin-list').length) {
		return;
	}
	
	pluginsList = {
		page: 0,
		pages: null,
		perPage: 8,
		plugins: null,
		target: null,
		meta: null,
		pagination: null,
		itemTemplate: null,
		
		init: function() {
			this.target = $('#plugin-list');			
			this.itemTemplate = $('#plugin-list-template li.plugin-item').clone();
			
			$.get('plugins.json', function(response) {
				response.reverse();
				pluginsList.populate(response);
			});		
		},
		
		populate: function(plugins) {
			this.plugins = plugins;
			this.pagination();
			this.showPage(1);
			this.setMeta(1, this.perPage, this.plugins.length);
		},
		
		pagination: function() {
			this.meta = $('.plugins-pagination-meta');		
			this.pagination = $('#plugin-paging');
			this.pages = Math.ceil(this.plugins.length / this.perPage);
			
			var next = $('.next', this.pagination);
			
			for (i = 1; i <= this.pages; i++) {				
				$('<span/>', {
					'class': 'pagination-link page'
				}).append($('<a/>', {
					'href': '#page-' + i,
					'text': i 
				})).insertBefore(next);
			}
			
			$('.pagination-link a', this.pagination).on('click', function(e) {
				e.stopPropagation();
				e.preventDefault();

				var _this = $(this),
					page = pluginsList.getLinkPage(_this);
				
				if (!_this.closest('span').hasClass('disabled')) {
					pluginsList.showPage(page);
				}
				
			    $('html, body').animate({
			        scrollTop: $("#plugin-list").offset().top - 20
			    }, 1000);
			});
		},
		
		showPage: function(page) {
			var _self = this,
				start = this.perPage * (page - 1);
				end = start + this.perPage,
				plugins = this.plugins.slice(start, end);
					
			this.target.find('li').remove();
							
			$.each(plugins, function(i) {
				li = _self.itemTemplate.clone();
								
				$('.plugin-name a', li).attr('href', this.Website).text(this.Name);
				$('.plugin-url a', li).attr('href', this.Website).text(this.Website);
				
				if (this.hasOwnProperty('Description') && this.Description.length) {
					$('.plugin-desc', li).html(this.Description);
				} else {
					$('.plugin-desc', li).remove();
				}
				
				_self.target.append(li);
			});
			
			this.setMeta(start + 1, (plugins.length == this.perPage ? end : start + plugins.length));
			this.setPage(page);
		},
		
		setPage: function(page) {
			this.page = page;
			
			$('.page', this.pagination).removeClass('pagination-link-current disabled');
			$('a[href="#page-' + this.page + '"]', this.pagination)
				.closest('span')
				.addClass('pagination-link-current disabled');
			
			$('.next, .prev', this.pagination).removeClass('disabled');
						
			if (this.page == 1) {
				$('.prev', this.pagination).addClass('disabled');
			} else if (this.page == this.pages) {
				$('.next', this.pagination).addClass('disabled');
			}
		},
		
		setMeta: function(from, to, of) {
			$('.plugins-view', this.meta).text(from + ' to ' + to);
			
			if (of != undefined) {
				$('.plugins-count', this.meta).text(of);
			}
		},
		
		getLinkPage: function(link) {
			var page = $(link).attr('href').replace(/#page-/, '');
			
			if (page == 'next' || page == 'prev') {
				var current = this.getLinkPage($('.pagination-link-current a', this.pagination)),
					page = (page == 'prev' ? --current : ++current);
			} else {
				page = parseInt(page, 10);
			}
			
			return page;
		}
	};
	
	pluginsList.init();
});
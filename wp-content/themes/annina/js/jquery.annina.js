(function($) {
	"use strict";
	
	$(window).load(function() {
	
		/*-----------------------------------------------------------------------------------*/
		/*  If the Tagcloud widget exist or Edit Comments Link exist
		/*-----------------------------------------------------------------------------------*/ 
			if ( $( '.comment-metadata' ).length ) {
				$('.comment-metadata').addClass('smallPart');
			}
			if ( $( '.reply' ).length ) {
				$('.reply').addClass('smallPart');
			}
			if ( $( '.form-allowed-tags' ).length ) {
				$('.form-allowed-tags').addClass('smallPart');
			}
	});
	
	$(document).ready(function() {
	
		/*-----------------------------------------------------------------------------------*/
		/*  Masonry & ImagesLoaded
		/*-----------------------------------------------------------------------------------*/ 	
		if ( $( '#mainAnnina' ).length ) {
			var $container = $('#mainAnnina');
			$container.imagesLoaded(function(){
				$container.masonry({
				  columnWidth: '.grid-sizer',
				  itemSelector: '.anninamas',
				  transitionDuration: '0.3s'
				});
			});
		}
		var $featImage = $('.entry-featuredImg');
		$featImage.imagesLoaded(function(){
			$('.entry-featuredImg img').css({
				opacity: 1
			});			
			setTimeout(function() {
				  $featImage.removeClass('annina-loader');
			}, 1500);
		});
		
		/*-----------------------------------------------------------------------------------*/
		/*  Home icon in main menu
		/*-----------------------------------------------------------------------------------*/ 
			$('.main-navigation .menu-item-home > a').prepend('<i class="fa fa-home spaceRight"></i>');
			
		/*-----------------------------------------------------------------------------------*/
		/*  Search button
		/*-----------------------------------------------------------------------------------*/ 
			$('#open-search').click(function() {
				$('#search-full').fadeIn(400);
				if ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
				} else {
					$("#search-full #search-field").focus();
				}
			});

			$('#close-search').click(function() {
				$('#search-full').fadeOut(400);
			});
	
		/*-----------------------------------------------------------------------------------*/
		/*  Overlay Effect for Featured Image
		/*-----------------------------------------------------------------------------------*/ 	
			$(".overlay-img").hover(function () {
				$(this).stop().animate({
					opacity: .3
				}, 500);
			},
			function () {
				$(this).stop().animate({
					opacity: 0
				}, 500);
			});
			
		/*-----------------------------------------------------------------------------------*/
		/*  Scroll To Top
		/*-----------------------------------------------------------------------------------*/ 
			$("#toTop").click(function () {
			   $("body, html").animate({scrollTop: 0}, 1000);
			});	
			
		/*-----------------------------------------------------------------------------------*/
		/*  Detect Mobile Browser
		/*-----------------------------------------------------------------------------------*/ 
		if ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
		} else {
				
			/*-----------------------------------------------------------------------------------*/
			/*  If menu has submenu
			/*-----------------------------------------------------------------------------------*/ 
				$('.main-navigation').find("li").each(function(){
					if($(this).children("ul").length > 0){
						$(this).append("<span class='indicator'></span>");
					}
				});
				
			/*-----------------------------------------------------------------------------------*/
			/*  Menu Effect
			/*-----------------------------------------------------------------------------------*/ 
				var hoverTimeout;
				$('.main-navigation ul > li.menu-item-has-children, .main-navigation ul > li.page_item_has_children').hover(function() {
					var $self = $(this);
					hoverTimeout = setTimeout(function() {
						$self.find('> ul.sub-menu, > ul.children').slideDown(400);
					}, 300);
				}, function() {
					clearTimeout(hoverTimeout);
					$(this).find('> ul.sub-menu, > ul.children').slideUp(200);
				});
				
				
			/*-----------------------------------------------------------------------------------*/
			/*  If you are in a page of the submenu
			/*-----------------------------------------------------------------------------------*/ 
				$('.main-navigation ul.sub-menu').each(function() {
					if($(this).children('li').hasClass('current_page_item') || $(this).children('li').hasClass('current-menu-item') ) {
						$(this).slideDown(400);
						$(this).parent('li').parent('ul').slideDown(400);
					}
				});
		
		}
		
	
	});
	
})(jQuery);
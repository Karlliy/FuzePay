
// nav
$(function() {
	var $oe_menu		= $('#oe_menu');
	var $oe_menu_items	= $oe_menu.children('li');
	var $oe_overlay		= $('#oe_overlay');

	$oe_menu_items.bind('mouseenter',function(){
		var $this = $(this);
		$this.addClass('slided selected');
		$this.children('div').css('z-index','9999').stop(true,true).slideDown(200,function(){
			$oe_menu_items.not('.slided').children('div').hide();
			$this.removeClass('slided');
		});
	}).bind('mouseleave',function(){
		var $this = $(this);
		$this.removeClass('selected').children('div').css('z-index','1');
		$(".menuList").hide();
		
	});

	$oe_menu.bind('mouseenter',function(){
		var $this = $(this);
		$oe_overlay.stop(true,true).fadeTo(200, 0.6);
		$this.addClass('hovered');
	}).bind('mouseleave',function(){
		var $this = $(this);
		$this.removeClass('hovered');
		$oe_overlay.stop(true,true).fadeTo(200, 0);
		$oe_menu_items.children('div').hide();
	})
});


// slide banner
/*
$(function() {
	var demo1 = $("#demo1").slippry({
		transition: 'fade',
		useCSS: true,
		speed: 2000,
		pause: 1000,
		auto: true
	});

	$('.stop').click(function () {
		demo1.stopAuto();
	});

	$('.start').click(function () {
		demo1.startAuto();
	});

	$('.prev').click(function () {
		demo1.goToPrevSlide();
		return false;
	});
	$('.next').click(function () {
		demo1.goToNextSlide();
		return false;
	});
	$('.reset').click(function () {
		demo1.destroySlider();
		return false;
	});
	$('.reload').click(function () {
		demo1.reloadSlider();
		return false;
	});
	$('.init').click(function () {
		demo1 = $("#demo1").slippry();
		return false;
	});
});
*/
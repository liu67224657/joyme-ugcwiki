//rank 

$(function(){
	$('.sort-btn').click(function(){
	  var index = $(this).index();
	  $(this).parents('.sort-list').find('.sort-btn').removeClass('on');
	  $(this).addClass('on');
	  $(this).closest('.sort-list').find('.sl-option').eq(index).addClass('on').siblings().removeClass('on');
	});
	
});


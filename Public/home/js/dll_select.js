$('.dll-select-val').click(function(){
	if($(this).parents('.dll-select').hasClass('dll-select-val-select')){ //是否展开
		$(this).parents('.dll-select').removeClass('dll-select-val-select');
	}
	else{
		$('.dll-select-val').parents('.dll-select').removeClass('dll-select-val-select');
		$(this).parents('.dll-select').addClass('dll-select-val-select');
	}
	dll_select_arrow();
	return false;
});
function dll_select_arrow(){
	$('.dll-select').each(function(){
		if($(this).find('.dll-select-val').hasClass('dll-select-val-arrowUp') || $(this).find('.dll-select-val').hasClass('dll-select-val-arrowDown')){ //是否允许出现箭头
			if($(this).hasClass('dll-select-val-select')){ //是否展开
				$(this).find('.dll-select-val').removeClass('dll-select-val-arrowUp').addClass('dll-select-val-arrowDown');
				$(this).find('.dll-select-val').removeClass('dll-select-val-arrowDown').addClass('dll-select-val-arrowUp');
			}
			else{
				$(this).find('.dll-select-val').removeClass('dll-select-val-arrowUp').addClass('dll-select-val-arrowDown');
			}
		}
	});
}
$('.dll-select-click').click(function(){
	$(this).parents('.dll-select').find('.dll-select-val').text( $(this).text() );
	$(this).parents('.dll-select').removeClass('dll-select-val-select');
	dll_select_arrow();
	return false;
});
$('body').click(function(){
	$('.dll-select').removeClass('dll-select-val-select');
	dll_select_arrow();
});
$('.dll-select-down').click(function(){
	dll_select_arrow;
	return false;
});

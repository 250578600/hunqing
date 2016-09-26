// 输入框上的 叉 和 眼睛
$('body').on('input focus','.input-cha-text input',function(){
	if($(this).val()==''){
		$(this).parents('.input-cha-text').find('.input-cha-text-style').hide();
	}
	else{
		$(this).parents('.input-cha-text').find('.input-cha-text-style').show();
	}
});
$('body').on('blur','.input-cha-text input',function(){
	$(this).parents('.input-cha-text').find('.input-cha-text-style').hide();
});
$('body').on('mousedown','.input-cha-text-style',function(){
	$(this).parents('.input-cha-text').find('input').val('').focus();
	$(this).hide();
});
$('body').on('input focus','.input-yj-password input',function(){
	if($(this).val()==''){
		$(this).parents('.input-yj-password').find('.input-yj-password-style').hide();
	}
	else{
		$(this).parents('.input-yj-password').find('.input-yj-password-style').show();
	}
});
$('body').on('blur','.input-yj-password input',function(){
	if($(this).val()=='') $(this).parents('.input-yj-password').find('.input-yj-password-style').hide();
});
$('body').on('click','.input-yj-password-style',function(){
	var input = $(this).parents('.input-yj-password').find('input');
	if(input.attr('type')=='password'){
		$(this).addClass('input-yj-password-close-style');
		input.attr('type','text');
	}
	else{
		$(this).removeClass('input-yj-password-close-style');
		input.attr('type','password');
	}
	input.focus();
});
